<?php

namespace App\Jobs;

use App\Models\Candidate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Log;

class AnalyzeResumeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $candidate;

    public function __construct(Candidate $candidate)
    {
        $this->candidate = $candidate;
    }

    public function handle(): void
    {
        if (!$this->candidate->resume_text) {
            return;
        }

        try {
            // --- PROMPT ENGINEERING KHUSUS HRD ---
            $prompt = "
            Bertindaklah sebagai Senior HR Recruiter. Tugasmu adalah menganalisis teks CV pelamar di bawah ini untuk posisi Developer/General (sesuaikan konteks).
            
            TEKS CV:
            {$this->candidate->resume_text}

            Tolong berikan analisis mendalam dalam format JSON yang valid (tanpa markdown ```json).
            Analisis harus mencakup:
            1. 'summary': Ringkasan profesional pelamar (Bahasa Indonesia, max 2 kalimat).
            2. 'pros': Array berisi 3-5 poin kelebihan/kekuatan utama pelamar.
            3. 'cons': Array berisi 3-5 poin kekurangan/hal yang perlu diklarifikasi saat interview.
            4. 'recommendation': Saran untuk HR (Sangat Disarankan / Dipertimbangkan / Tidak Disarankan) beserta alasannya singkat.
            5. 'score': Nilai kecocokan dari 0-100 (integer).

            Struktur JSON wajib seperti ini:
            {
                \"summary\": \"...\",
                \"pros\": [\"...\", \"...\"],
                \"cons\": [\"...\", \"...\"],
                \"recommendation\": \"...\",
                \"score\": 85
            }
            ";

            // Eksekusi Gemini
            $result = Gemini::geminiPro()->generateContent($prompt);
            $rawText = $result->text();

            // Membersihkan format jika Gemini memberikan Markdown block
            $cleanJson = str_replace(['```json', '```'], '', $rawText);
            $analysisData = json_decode($cleanJson, true);

            // Jika gagal decode JSON, simpan raw text sebagai fallback
            if (!$analysisData) {
                Log::error('Gagal decode JSON dari Gemini', ['response' => $rawText]);
                return;
            }

            // Update Database
            $this->candidate->update([
                'status' => 'analyzed',
                'score' => $analysisData['score'] ?? 0,
                'ai_summary' => $analysisData['summary'] ?? '-', // Ringkasan singkat
                'ai_analysis' => $analysisData, // Simpan JSON lengkap (untuk pros/cons)
            ]);

        } catch (\Exception $e) {
            Log::error("Error analyzing resume: " . $e->getMessage());
        }
    }
}