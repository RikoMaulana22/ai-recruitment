<?php

namespace App\Jobs;

use App\Models\Interview;
use App\Services\GeminiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GradeInterviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $interviewId;

    public function __construct($interviewId)
    {
        $this->interviewId = $interviewId;
    }

    public function handle(GeminiService $gemini): void
    {
        Log::info("Job Dimulai untuk Interview ID: " . $this->interviewId);

        $interview = Interview::with('candidate')->find($this->interviewId);
        if (!$interview)
            return;

        // 1. Siapkan Data untuk Dikirim ke AI
        $candidate = $interview->candidate;
        $chatHistory = json_encode($interview->chat_history);
        $resumeSummary = $candidate->ai_summary ?? 'Tidak ada ringkasan CV';

        // Cek apakah ada video (hanya info ada/tidak, karena AI text belum bisa nonton video langsung via path lokal)
        $videoStatus = $interview->video_answer_1 ? "Kandidat sudah mengirim video jawaban." : "Belum ada video.";

        $prompt = "
            Bertindaklah sebagai Senior HR Recruiter. 
            Lakukan penilaian ulang (Re-grading) untuk kandidat berikut:
            
            NAMA: {$candidate->name}
            RINGKASAN CV: $resumeSummary
            STATUS VIDEO: $videoStatus
            
            TRANSKRIP CHAT INTERVIEW:
            $chatHistory

            TUGAS:
            Berikan penilaian final (0-100) dan ringkasan performa berdasarkan percakapan chat dan profilnya.
            
            OUTPUT JSON (Strict):
            {
                \"score\": (0-100),
                \"summary\": \"Ringkasan penilaian baru... (Sebutkan kelebihan/kekurangan dari chat)\",
                \"recommendation\": \"HIRE\" atau \"REJECT\"
            }
        ";

        try {
            // 2. Panggil Gemini
            $result = $gemini->generateJsonContent($prompt);

            Log::info("Balasan Gemini: " . json_encode($result));

            if ($result) {
                // 3. Update Database
                $interview->update([
                    'interview_score' => $result['score'] ?? 0,
                    'interview_summary' => $result['summary'] ?? '-',
                ]);

                // Update juga score di tabel candidates (rata-rata atau ambil dari interview)
                $candidate->update([
                    'score' => $result['score'] ?? 0,
                    'status' => ($result['recommendation'] == 'HIRE') ? 'interview_completed' : 'rejected' // Opsional
                ]);
                Log::info("Database berhasil diupdate dengan skor: " . ($result['score'] ?? 0)); // <--- LOG 3

            } else {
                Log::error("Gemini mengembalikan hasil kosong/null");
            }
        } catch (\Exception $e) {
            Log::error("GradeInterviewJob Error: " . $e->getMessage());
        }
    }
}