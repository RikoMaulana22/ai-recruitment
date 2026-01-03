<?php

namespace App\Jobs;

use App\Models\Candidate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Smalot\PdfParser\Parser;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AnalyzeResumeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $candidateId;

    public function __construct($candidateId)
    {
        $this->candidateId = $candidateId;
    }

    public function handle(GeminiService $gemini): void
    {
        $candidate = Candidate::find($this->candidateId);
        if (!$candidate) return;

        // Gunakan Storage facade agar path benar (local/s3/public)
        $filePath = Storage::disk('local')->path($candidate->resume_path);
        
        // 1. Parse PDF
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $cleanText = preg_replace('/\s+/', ' ', trim($pdf->getText()));
            
            // Simpan teks mentah untuk keperluan debugging/history
            $candidate->update(['resume_text' => substr($cleanText, 0, 5000)]);
        } catch (\Exception $e) {
            Log::error("PDF Parser Error: " . $e->getMessage());
            return;
        }

        // 2. Analisis Mendalam oleh AI
        $prompt = "
            Analisis teks CV berikut.
            CV TEXT: " . substr($cleanText, 0, 4000) . "
            
            OUTPUT JSON (Strict):
            {
                \"summary\": \"Ringkasan profesional kandidat (Bahasa Indonesia)\",
                \"skills\": [\"List\", \"Teknis\", \"Skill\"],
                \"score\": (0-100 relevansi untuk posisi Staff IT),
                \"recommendation\": \"HIRE\" atau \"REJECT\"
            }
        ";

        $data = $gemini->generateJsonContent($prompt, "Anda adalah Senior HR Recruiter.");

        if ($data) {
            $candidate->update([
                'ai_analysis' => $data,
                'score' => $data['score'] ?? 0,
                'ai_summary' => $data['summary'] ?? '-',
                'status' => 'analyzed'
            ]);
        }
    }
}