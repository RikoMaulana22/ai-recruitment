<?php

namespace App\Jobs;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // Wajib ada
use Illuminate\Support\Facades\Storage;

class GradeInterviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $interviewId;

    protected $companyNeeds = "
        Posisi: Staff IT
        Skill: PHP, Laravel, MySQL.
        Soft Skill: Jujur, Komunikatif.
    ";

    public function __construct($interviewId)
    {
        $this->interviewId = $interviewId;
    }

    public function handle(): void
    {
        Log::info("START: Memulai Job untuk Interview ID: {$this->interviewId}");

        $interview = Interview::with('candidate')->find($this->interviewId);
        
        if (!$interview) {
            Log::error("GAGAL: Data Interview ID {$this->interviewId} tidak ditemukan di database.");
            return;
        }

        $candidate = $interview->candidate;
        Log::info("DATA: Kandidat bernama {$candidate->name}");

        // --- CEK VIDEO ---
        $videoPath = $interview->video_answer_1; 
        $videoData = null;
        $mimeType = 'video/webm';

        if ($videoPath) {
            $fullPath = Storage::disk('public')->path($videoPath);
            Log::info("VIDEO CHECK: Path database -> {$videoPath}");
            
            if (Storage::disk('public')->exists($videoPath)) {
                $size = Storage::disk('public')->size($videoPath);
                Log::info("VIDEO FOUND: Ukuran file -> " . round($size / 1024, 2) . " KB");

                if ($size < 10 * 1024 * 1024) { // < 10MB
                    $fileContent = Storage::disk('public')->get($videoPath);
                    $videoData = base64_encode($fileContent);
                    Log::info("VIDEO PREPPED: Berhasil di-encode ke Base64.");
                    
                    if (str_ends_with($videoPath, '.mp4')) $mimeType = 'video/mp4';
                } else {
                    Log::warning("VIDEO SKIP: File terlalu besar (>10MB), lanjut tanpa video.");
                }
            } else {
                Log::error("VIDEO MISSING: File tidak ada di storage: {$fullPath}");
            }
        } else {
            Log::info("VIDEO SKIP: Tidak ada data video di database.");
        }

        // --- SIAPKAN REQUEST GEMINI ---
        Log::info("GEMINI: Menyiapkan Prompt...");
        
        $prompt = "
            Bertindaklah sebagai JSON Converter.
            Analisis data berikut dan keluarkan HANYA format JSON valid. Jangan ada teks pengantar.
            
            KANDIDAT: {$candidate->name}
            SKILL: " . json_encode($candidate->ai_analysis) . "
            KRITERIA: $this->companyNeeds
            
            Tugas: Beri nilai 0-100 dan alasan singkat.
            
            Format Output Wajib:
            {
                \"score\": 75,
                \"summary\": \"Alasan penilaian...\",
                \"pros\": [\"Kelebihan 1\"],
                \"cons\": [\"Kekurangan 1\"]
            }
        ";

        $apiKey = env('GEMINI_API_KEY');
        if(empty($apiKey)) {
            Log::error("CRITICAL: GEMINI_API_KEY kosong di .env!");
            return;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

        $parts = [['text' => $prompt]];

        if ($videoData) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data' => $videoData
                ]
            ];
        }

        // --- KIRIM REQUEST ---
        try {
            Log::info("GEMINI: Mengirim Request ke Google...");
            
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [['parts' => $parts]],
                    'generationConfig' => ['responseMimeType' => 'application/json']
                ]);

            Log::info("GEMINI: Status Code -> " . $response->status());
            
            if ($response->failed()) {
                Log::error("GEMINI API ERROR: " . $response->body());
                return;
            }

            $result = $response->json();
            
            // --- PROSES RESPON ---
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $rawText = $result['candidates'][0]['content']['parts'][0]['text'];
                Log::info("GEMINI RESPONSE RAW: " . substr($rawText, 0, 100) . "..."); // Log 100 karakter awal

                $data = json_decode($rawText, true);

                if ($data) {
                    $summary = $data['summary'] ?? 'Tanpa ringkasan';
                    if (isset($data['pros'])) $summary .= "\n\n✅ " . implode(', ', $data['pros']);
                    if (isset($data['cons'])) $summary .= "\n❌ " . implode(', ', $data['cons']);

                    $interview->update([
                        'interview_score' => $data['score'] ?? 0,
                        'interview_summary' => $summary,
                    ]);
                    
                    // Opsional: Update status pelamar
                    $candidate->update(['status' => 'interviewed']);

                    Log::info("SUCCESS: Data berhasil disimpan ke database! Score: " . ($data['score'] ?? 0));
                } else {
                    Log::error("JSON PARSE ERROR: Gagal decode JSON dari AI.");
                }
            } else {
                Log::error("GEMINI FORMAT UNKNOWN: Struktur respon tidak sesuai harapan.");
                Log::info(json_encode($result));
            }

        } catch (\Exception $e) {
            Log::error("EXCEPTION: " . $e->getMessage());
        }
    }
}