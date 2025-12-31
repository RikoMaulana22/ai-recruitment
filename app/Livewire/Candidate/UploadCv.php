<?php

namespace App\Livewire\Candidate;

use Livewire\Component;
use Livewire\WithFileUploads; // Wajib untuk upload file
use App\Models\Candidate;
use Livewire\Attributes\Layout;


class UploadCv extends Component
{
    use WithFileUploads;

    public $name;
    public $email;
    public $phone;
    public $resume; // Variable penampung file sementara

    // Aturan Validasi
    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email|unique:candidates,email',
        'phone' => 'required|numeric',
        'resume' => 'required|file|mimes:pdf|max:2048', // Maksimal 2MB, wajib PDF
    ];

    public function save()
    {
        $this->validate();

        // 1. Simpan File PDF ke folder 'cv_uploads'
        // Hasilnya path seperti: cv_uploads/randomname.pdf
        $path = $this->resume->store('cv_uploads', 'local');

        // 2. Simpan Data ke Database
        Candidate::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'resume_path' => $path,
            'status' => 'pending'
        ]);

        // 3. Reset Form & Beri Pesan Sukses
        $this->reset();
        session()->flash('message', 'CV berhasil dikirim! AI kami sedang memprosesnya.');
    }

    public function render()
    {
        return view('livewire.candidate.upload-cv');
    }
}