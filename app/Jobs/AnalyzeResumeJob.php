<?php

namespace App\Jobs;

use App\Models\Candidate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Http; // Pakai HTTP Client bawaan Laravel

class AnalyzeResumeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $candidateId;

    public function __construct($candidateId)
    {
        $this->candidateId = $candidateId;
    }

    public function handle(): void
    {
        $candidate = Candidate::find($this->candidateId);
        if (!$candidate) return;

        // 1. Baca Teks dari PDF (Tetap sama)
        $filePath = storage_path('app/' . $candidate->resume_path);
        
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
            
            // Bersihkan teks sedikit agar tidak terlalu kotor
            $cleanText = preg_replace('/\s+/', ' ', trim($text));
            $candidate->update(['resume_text' => substr($cleanText, 0, 5000)]);

        } catch (\Exception $e) {
            \Log::error('PDF Error: ' . $e->getMessage());
            return; 
        }

        // 2. Siapkan Prompt Khusus Gemini
        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

        $prompt = "
            Anda adalah HR Recruiter Expert. Tugas Anda adalah mengekstrak data dari teks CV berikut menjadi format JSON.
            
            CV TEXT:
            $cleanText

            INSTRUKSI OUTPUT:
            Hanya keluarkan JSON valid. Jangan ada markdown (```json).
            Format JSON wajib seperti ini:
            {
                \"summary\": \"Ringkasan profil kandidat dalam Bahasa Indonesia (maks 2 kalimat)\",
                \"skills\": [\"Skill 1\", \"Skill 2\", \"Skill 3\"],
                \"score\": (Berikan nilai 0-100 berdasarkan relevansi skill),
                \"recommendation\": \"HIRE\" atau \"REJECT\"
            }
        ";

        try {
            // 3. Kirim Request ke Google Gemini (Pakai HTTP Client Laravel)
            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            // 4. Ambil Jawaban
            $result = $response->json();
            
            // Cek apakah ada jawaban valid
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $rawJson = $result['candidates'][0]['content']['parts'][0]['text'];
                
                // Bersihkan format markdown jika Gemini bandel kasih ```json
                $cleanJson = str_replace(['```json', '```', "\n"], '', $rawJson);
                $data = json_decode($cleanJson, true);

                // 5. Simpan ke Database
                if ($data) {
                    $candidate->update([
                        'ai_analysis' => $data,
                        'score' => $data['score'] ?? 0,
                        'ai_summary' => $data['summary'] ?? '-',
                        'status' => 'analyzed'
                    ]);
                }
            } else {
                \Log::error('Gemini Error: ' . json_encode($result));
            }

        } catch (\Exception $e) {
            \Log::error('API Connection Error: ' . $e->getMessage());
        }
    }
}