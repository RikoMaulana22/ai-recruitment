<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Candidate;
use Livewire\Attributes\Layout;
use Livewire\WithPagination; // Agar tabel tidak kepanjangan
use App\Livewire\Actions\Logout;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    use WithPagination;

    // Properti untuk fitur Search & Filter
    public $search = '';
    public $filterStatus = '';

    // Reset halaman ke 1 setiap kali search berubah
    public function updatedSearch()
    {
        $this->resetPage();
    }
    public function logout(Logout $logout): void
    {
        $logout(); // Lakukan proses logout
        $this->redirect('/', navigate: true); // Redirect ke halaman depan (Welcome)
    }

    public function render()
    {
        // 1. Hitung Statistik untuk Kartu Atas
        $stats = [
            'total' => Candidate::count(),
            'pending' => Candidate::where('status', 'pending')->count(),
            'interview' => Candidate::where('status', 'interviewed')->count(),
            'accepted' => Candidate::where('status', 'accepted')->count(),
        ];

        // 2. Query Data dengan Filter & Search
        $candidates = Candidate::query()
            ->when($this->search, function($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('email', 'like', '%'.$this->search.'%')
                  ->orWhere('ai_summary', 'like', '%'.$this->search.'%');
            })
            ->when($this->filterStatus, function($q) {
                $q->where('status', $this->filterStatus);
            })
            ->latest()
            ->paginate(10); // Tampilkan 10 per halaman

        return view('livewire.admin.dashboard', [
            'candidates' => $candidates,
            'stats' => $stats
        ]);
    }
}