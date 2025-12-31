<?php

namespace App\Livewire\Interview;

use Livewire\Component;
use App\Models\Candidate;
use App\Models\Interview;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;

#[Layout('layouts.chat')] // Tampilan full screen bersih
class ChatBot extends Component
{
    public Candidate $candidate;
    public Interview $interview;
    
    public $userMessage = '';
    public $messages = []; // Menampung chat di layar: [['role' => 'user', 'content' => '...']]

    public function mount(Candidate $candidate)
    {
        $this->candidate = $candidate;

        // Cari atau Buat Sesi Interview Baru
        $this->interview = Interview::firstOrCreate(
            ['candidate_id' => $candidate->id],
            ['chat_history' => []]
        );

        // Load riwayat chat lama jika ada
        $this->messages = $this->interview->chat_history ?? [];

        // Jika chat masih kosong, AI menyapa duluan
        if (empty($this->messages)) {
            $this->addMessage('assistant', "Halo {$candidate->name}, selamat datang! Saya AI Recruiter. Bisakah Anda ceritakan sedikit tentang diri Anda secara singkat?");
        }
    }

    public function sendMessage()
    {
        // 1. Validasi input user
        $this->validate(['userMessage' => 'required|string|max:1000']);

        // 2. Simpan Chat User ke Layar
        $this->addMessage('user', $this->userMessage);
        $tempMessage = $this->userMessage;
        $this->userMessage = ''; // Reset input

        // 3. Panggil AI Gemini (Loading state akan ditangani di frontend)
        $this->askGemini($tempMessage);
    }

    private function addMessage($role, $content)
    {
        $this->messages[] = ['role' => $role, 'content' => $content];
        
        // Simpan ke Database (Update JSON)
        $this->interview->update(['chat_history' => $this->messages]);
    }

    private function askGemini($lastUserMessage)
    {
        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

        // Konteks untuk AI (System Instruction)
        $context = "
            Anda adalah Pewawancara Kerja Profesional. 
            Kandidat: {$this->candidate->name}.
            Ringkasan CV Kandidat: {$this->candidate->ai_summary}.
            
            Tugas: Lakukan wawancara singkat. Ajukan 1 pertanyaan saja dalam satu waktu.
            Gali pengalaman mereka berdasarkan CV. Jangan terlalu kaku.
            Jika sudah cukup (sekitar 5-6 pertukaran pesan), ucapkan terima kasih dan akhiri wawancara.
        ";

        // Format history chat agar AI ingat konteks sebelumnya
        // Gemini butuh format khusus: "parts": [{"text": "..."}]
        $contents = [];
        $contents[] = ['role' => 'user', 'parts' => [['text' => $context]]]; // Instruksi awal sebagai user prompt pertama (trick)

        foreach ($this->messages as $msg) {
            // Mapping role: 'assistant' -> 'model' (Khusus Gemini)
            $role = ($msg['role'] == 'assistant') ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $msg['content']]]
            ];
        }

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($url, ['contents' => $contents]);

            $result = $response->json();

            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $reply = $result['candidates'][0]['content']['parts'][0]['text'];
                $this->addMessage('assistant', $reply);
            }

        } catch (\Exception $e) {
            // Jika error, jangan crash, tapi beri pesan sopan
            $this->addMessage('assistant', "Maaf, koneksi saya terputus sebentar. Bisa ulangi?");
        }
    }

    public function finishInterview()
    {
        // 1. Siapkan data chat untuk dinilai
        $chatString = json_encode($this->messages);
        
        // 2. Siapkan Prompt Penilaian
        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

        $gradingPrompt = "
            Bertindaklah sebagai Senior HR Manager. Berikut adalah transkrip wawancara dengan kandidat bernama {$this->candidate->name}.
            
            TRANSKRIP:
            $chatString

            TUGAS:
            Nilai performa kandidat berdasarkan jawaban mereka.
            
            OUTPUT JSON SAJA:
            {
                \"score\": (0-100),
                \"summary\": \"Ringkasan performa kandidat (maks 3 kalimat). Sebutkan kelebihan dan kekurangannya.\",
                \"recommendation\": \"HIRE\" atau \"REJECT\"
            }
        ";

        try {
            // 3. Kirim ke Gemini
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [['parts' => [['text' => $gradingPrompt]]]]
                ]);
            
            $result = $response->json();
            
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $rawJson = $result['candidates'][0]['content']['parts'][0]['text'];
                $cleanJson = str_replace(['```json', '```', "\n"], '', $rawJson);
                $grading = json_decode($cleanJson, true);

                // 4. Simpan Nilai ke Database
                if ($grading) {
                    $this->interview->update([
                        'interview_score' => $grading['score'] ?? 0,
                        'interview_summary' => $grading['summary'] ?? 'Tidak ada ringkasan.',
                    ]);
                }
            }

        } catch (\Exception $e) {
            // Jika error, beri nilai default dulu
            \Log::error("Grading Error: " . $e->getMessage());
        }

        // 5. Redirect ke halaman 'Terima Kasih'
        return redirect()->route('interview.done');
    }

    public function render()
    {
        return view('livewire.interview.chat-bot');
    }
}