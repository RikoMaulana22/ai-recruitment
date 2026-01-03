<?php

namespace App\Livewire\Interview;

use Livewire\Component;
use App\Models\Candidate;
use App\Models\Interview;
use App\Services\GeminiService;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.chat')]
class ChatBot extends Component
{
    public Candidate $candidate;
    public Interview $interview;
    
    public $userMessage = '';
    public $messages = []; 

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
            $initialGreeting = "Halo {$candidate->name}, selamat datang! Saya AI Recruiter. Bisakah Anda ceritakan sedikit tentang diri Anda secara singkat?";
            $this->addMessage('assistant', $initialGreeting);
        }
    }

    /**
     * Mengirim pesan user ke AI dan mendapatkan balasan.
     */
    public function sendMessage(GeminiService $gemini)
    {
        // 1. Validasi input user
        $this->validate(['userMessage' => 'required|string|max:1000']);

        // 2. Simpan pesan user ke UI & DB
        $this->addMessage('user', $this->userMessage);
        
        // 3. Siapkan Prompt / Persona AI
        $systemInstruction = "
            Anda adalah HR Manager yang ramah tapi kritis.
            Kandidat: {$this->candidate->name}.
            Ringkasan CV: {$this->candidate->ai_summary}.
            
            Tugas: Lakukan wawancara kerja.
            1. Ajukan HANYA SATU pertanyaan dalam satu waktu.
            2. Pertanyaan harus mengalir berdasarkan jawaban kandidat sebelumnya.
            3. Fokus menggali pengalaman kerja dan skill teknis kandidat.
            4. Jangan memberikan jawaban yang terlalu panjang.
            5. Jika kandidat bertanya balik, jawab singkat lalu kembali ke topik wawancara.
        ";

        // 4. Panggil Gemini Service (Kirim seluruh history chat untuk konteks)
        // Service akan mengembalikan string teks balasan
        $aiReply = $gemini->generateChatResponse($this->messages, $systemInstruction);

        if (is_string($aiReply) && !empty($aiReply)) {
             $this->addMessage('assistant', $aiReply);
        } else {
             // Fallback jika terjadi error koneksi atau format
             $this->addMessage('assistant', "Maaf, koneksi saya terputus sebentar. Bisakah Anda mengulangi jawaban Anda?");
        }

        // 5. Reset input user
        $this->userMessage = '';
    }

    /**
     * Mengakhiri interview dan melakukan penilaian otomatis.
     */
    public function finishInterview(GeminiService $gemini)
    {
        // 1. Siapkan transkrip chat
        $chatString = json_encode($this->messages);
        
        // 2. Siapkan Prompt Penilaian (Scoring)
        $prompt = "
            Bertindaklah sebagai Senior HR Manager. Berikut adalah transkrip wawancara dengan kandidat bernama {$this->candidate->name}.
            
            TRANSKRIP WAWANCARA:
            $chatString

            TUGAS:
            Nilai performa kandidat berdasarkan kualitas jawaban, kecocokan skill, dan sikap mereka selama wawancara.
            
            OUTPUT HARUS JSON VALID (Tanpa Markdown):
            {
                \"score\": (Isi dengan angka 0-100),
                \"summary\": \"Ringkasan penilaian (maksimal 3 kalimat). Sebutkan kekuatan utama dan kelemahan (jika ada).\",
                \"recommendation\": \"HIRE\" atau \"REJECT\"
            }
        ";

        try {
            // 3. Panggil Gemini Service (Mode JSON)
            // Kita tidak butuh 'systemInstruction' khusus di sini, cukup prompt di atas.
            $result = $gemini->generateJsonContent($prompt);
            
            // 4. Simpan hasil ke Database
            if ($result) {
                $this->interview->update([
                    'interview_score' => $result['score'] ?? 0,
                    'interview_summary' => $result['summary'] ?? 'Tidak ada ringkasan.',
                ]);
                
                // Opsional: Update status kandidat di tabel candidates
                $this->candidate->update(['status' => 'interview_completed']);
            } else {
                Log::error("Grading Failed: Kosong atau format salah.");
            }

        } catch (\Exception $e) {
            Log::error("Grading Exception: " . $e->getMessage());
        }

        // 5. Redirect ke halaman selesai
        return redirect()->route('interview.done');
    }

    /**
     * Helper untuk update array pesan dan simpan ke DB.
     */
    private function addMessage($role, $content)
    {
        $this->messages[] = ['role' => $role, 'content' => $content];
        
        // Simpan ke kolom JSON di database agar state terjaga jika di-refresh
        $this->interview->update(['chat_history' => $this->messages]);
    }

    public function render()
    {
        return view('livewire.interview.chat-bot');
    }
}