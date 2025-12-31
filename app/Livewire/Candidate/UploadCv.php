<?php

namespace App\Livewire\Candidate;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Candidate;
use Livewire\Attributes\Layout; // Import class Layout
use App\Jobs\AnalyzeResumeJob;

// PERBAIKAN: Tambahkan atribut ini agar Livewire tahu harus pakai layout yang mana
#[Layout('layouts.guest')]
class UploadCv extends Component
{
    use WithFileUploads;

    public $name;
    public $email;
    public $phone;
    public $resume;

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email|unique:candidates,email',
        'phone' => 'required|numeric',
        'resume' => 'required|file|mimes:pdf|max:2048',
    ];

    public function save()
    {
        $this->validate();

        // 1. Simpan File
        $path = $this->resume->store('cv_uploads', 'local');

        // 2. Simpan ke Database
        $candidate = Candidate::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'resume_path' => $path,
            'status' => 'pending'
        ]);

        // 3. Panggil AI Job (Biarkan dia kerja di background)
        AnalyzeResumeJob::dispatch($candidate->id);

        // 4. HAPUS BAGIAN INI:
        // $this->reset();
        // session()->flash('message', '...');

        // 5. GANTI DENGAN INI (Redirect langsung ke ruang Interview):
        return redirect()->route('interview.start', ['candidate' => $candidate->id]);
    }

    public function render()
    {
        return view('livewire.candidate.upload-cv');
    }
}