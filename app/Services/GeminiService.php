<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        // REVISI: Gunakan 1.5-flash (2.5 belum ada)
        $this->baseUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent";
    }

    public function generateJsonContent($prompt, $systemInstruction = null)
    {
        $payload = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json'
            ]
        ];

        if ($systemInstruction) {
            $payload['system_instruction'] = [
                'parts' => [['text' => $systemInstruction]]
            ];
        }

        return $this->sendRequest($payload);
    }

    public function generateChatResponse($history, $systemInstruction)
    {
        $contents = [];

        foreach ($history as $msg) {
            $role = ($msg['role'] == 'assistant') ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $msg['content']]]
            ];
        }

        $payload = [
            'contents' => $contents,
            'system_instruction' => [
                'parts' => [['text' => $systemInstruction]]
            ]
        ];

        return $this->sendRequest($payload);
    }

    private function sendRequest($payload)
    {
        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->baseUrl}?key={$this->apiKey}", $payload);

            if ($response->failed()) {
                Log::error("Gemini API Error: " . $response->body());
                return null; // Return null agar kode pemanggil tahu ada error
            }

            $result = $response->json();

            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $rawText = $result['candidates'][0]['content']['parts'][0]['text'];

                // REVISI: Hanya hapus markdown block, JANGAN hapus \n (newline)
                // agar jika nanti dipaksa decode JSON, tetap valid, tapi tidak merusak text biasa.
                $cleanText = str_replace(['```json', '```'], '', $rawText);

                $decoded = json_decode($cleanText, true);

                // Kembalikan array jika sukses decode (JSON), atau text asli jika gagal (Chat)
                return $decoded ?? $rawText;
            }
        } catch (\Exception $e) {
            Log::error("Gemini Connection Exception: " . $e->getMessage());
        }

        return null;
    }

    public function extractDataFromCvText($text)
{
    $prompt = "
        Anda adalah Data Entry Assistant. Tugas Anda adalah mengekstrak informasi dari teks CV berikut ke dalam format JSON untuk mengisi formulir pendaftaran.
        
        TEKS CV:
        $text
        
        INSTRUKSI:
        1. Ambil Nama Lengkap, Email, Nomor HP (format angka), dan Ringkasan Profil (maks 3 kalimat).
        2. Ambil Skill Utama (maksimal 5, pisahkan dengan koma).
        3. Jika tidak ditemukan, isi dengan string kosong atau null.
        
        OUTPUT JSON:
        {
            \"name\": \"Nama Kandidat\",
            \"email\": \"email@domain.com\",
            \"phone\": \"08123456789\",
            \"summary\": \"Ringkasan profil kandidat...\",
            \"skills\": \"PHP, Laravel, React\"
        }
    ";

    // Panggil method generateJsonContent yang sudah kita buat sebelumnya
    return $this->generateJsonContent($prompt);
}
}