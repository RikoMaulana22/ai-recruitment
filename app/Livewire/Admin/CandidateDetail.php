<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Candidate;
use App\Jobs\GradeInterviewJob;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CandidateDetail extends Component
{
    public Candidate $candidate;

    public function mount(Candidate $candidate)
    {
        $this->candidate = $candidate;
    }

    public function regrade($interviewId)
    {
        // Panggil Job
        GradeInterviewJob::dispatch($interviewId);
        
        // Beri feedback visual (opsional, karena poll akan handle sisanya)
        session()->flash('message', 'Perintah penilaian ulang dikirim ke AI.');
    }

    // TERIMA ATAU TOLAK
    public function accept()
    {
        $this->candidate->update(['status' => 'accepted']);
        session()->flash('message', 'Kandidat berhasil diterima!');
    }

    public function reject()
    {
        $this->candidate->update(['status' => 'rejected']);
        session()->flash('message', 'Kandidat telah ditolak.');
    }

    public function render()
    {
        // [FIX] Paksa ambil data terbaru dari database setiap kali render (polling)
        $this->candidate->refresh(); 
        
        
        return view('livewire.admin.candidate-detail');
    }
}