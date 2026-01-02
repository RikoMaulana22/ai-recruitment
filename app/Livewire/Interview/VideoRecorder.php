<?php

namespace App\Livewire\Interview;

use Livewire\Component;
use App\Models\Candidate;
use App\Models\Interview;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Jobs\GradeInterviewJob;

#[Layout('layouts.chat')]
class VideoRecorder extends Component
{
    use WithFileUploads;

    public Candidate $candidate;
    public Interview $interview;

    public $currentStep = 1;
    public $videoFile; // File temporary

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

    public function saveVideo()
    {
        // Validasi
        $this->validate([
            'videoFile' => 'required|file|max:51200', // 50MB Max
        ]);

        // Simpan File
        $path = $this->videoFile->store('interview_videos', 'public');

        // Update Database
        $column = 'video_answer_' . $this->currentStep;
        $this->interview->update([$column => $path]);

        // Reset Variable
        $this->reset('videoFile');

        // Logika Pindah Step
        if ($this->currentStep < 3) {
            $this->currentStep++;
            // Kirim sinyal ke JS untuk reset kamera
            $this->dispatch('step-complete'); 
        } else {
            // Jika sudah selesai semua, panggil AI
            GradeInterviewJob::dispatch($this->interview->id);
            return redirect()->route('interview.done');
        }
    }

    public function render()
    {
        return view('livewire.interview.video-recorder');
    }
}