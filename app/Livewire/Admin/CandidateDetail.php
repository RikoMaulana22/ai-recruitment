<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Candidate;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CandidateDetail extends Component
{
    public Candidate $candidate;

    // Fungsi mount otomatis jalan saat halaman dibuka
    public function mount(Candidate $candidate)
    {
        $this->candidate = $candidate;
    }

    public function render()
    {
        return view('livewire.admin.candidate-detail');
    }

    public function accept()
    {
        $this->candidate->update(['status' => 'accepted']);
        session()->flash('message', 'Selamat! Kandidat berhasil DITERIMA.');
    }

    public function reject()
    {
        $this->candidate->update(['status' => 'rejected']);
        session()->flash('message', 'Kandidat telah DITOLAK.');
    }
}