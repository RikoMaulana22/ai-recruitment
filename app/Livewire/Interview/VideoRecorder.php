<?php

namespace App\Livewire\Interview;

use Livewire\Component;
use App\Models\Candidate;
use App\Models\Interview;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('layouts.chat')] // Kita pakai layout full screen yang tadi
class VideoRecorder extends Component
{
    use WithFileUploads;

    public Candidate $candidate;
    public Interview $interview;

    public $currentStep = 1; // 1, 2, atau 3
    public $videoFile; // File video sementara

    // Daftar Pertanyaan
    public $questions = [
        1 => 'Silakan perkenalkan diri Anda dan ceritakan pengalaman kerja paling relevan.',
        2 => 'Sebutkan satu pencapaian terbesar Anda dan satu kegagalan yang pernah Anda alami.',
        3 => 'Mengapa kami harus menerima Anda di posisi ini dibandingkan kandidat lain?'
    ];

    public function mount(Candidate $candidate)
    {
        $this->candidate = $candidate;
        $this->interview = Interview::firstOrCreate(['candidate_id' => $candidate->id]);
    }

    // Fungsi dipanggil saat user upload video selesai
    public function saveVideo()
    {
        $this->validate([
            'videoFile' => 'required|mimes:mp4,webm,mov|max:51200', // Max 50MB
        ]);

        // Simpan file ke storage
        $path = $this->videoFile->store('interview_videos', 'public');

        // Update database sesuai step
        $column = 'video_answer_' . $this->currentStep;
        $this->interview->update([$column => $path]);

        // Reset file dan lanjut ke pertanyaan berikutnya
        $this->videoFile = null;
        
        if ($this->currentStep < 3) {
            $this->currentStep++;
        } else {
            // Jika sudah pertanyaan ke-3, tandai selesai & panggil AI
            // $this->dispatch('analyzeVideo', $this->interview->id); // Nanti kita buat Job-nya
            return redirect()->route('interview.done');
        }
    }

    public function render()
    {
        return view('livewire.interview.video-recorder');
    }
}