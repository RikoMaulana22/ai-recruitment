<?php

namespace App\Livewire\Candidate;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Candidate;
use Livewire\Attributes\Layout;
use App\Jobs\AnalyzeResumeJob;
use Smalot\PdfParser\Parser; // Import Parser

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

        // 1. Baca Teks PDF DULU sebelum simpan ke DB
        $resumeText = '';
        try {
            $parser = new Parser();
            // Baca dari file temporary livewire
            $pdf = $parser->parseFile($this->resume->getRealPath());
            $text = $pdf->getText();
            // Bersihkan teks (hapus spasi berlebih)
            $resumeText = preg_replace('/\s+/', ' ', trim($text));
            // Potong max 5000 karakter agar database tidak penuh
            $resumeText = substr($resumeText, 0, 5000);
        } catch (\Exception $e) {
            // Jika gagal baca, biarkan kosong tapi jangan error
            \Log::error("Gagal baca PDF: " . $e->getMessage());
        }

        // 2. Simpan File Fisik
        $path = $this->resume->store('cv_uploads', 'local');

        // 3. Simpan ke Database (Sekarang 'resume_text' sudah terisi!)
        $candidate = Candidate::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'resume_path' => $path,
            'resume_text' => $resumeText, // <--- INI KUNCINYA
            'status' => 'pending'
        ]);

        // 4. Panggil AI Job (Job sekarang tugasnya hanya analisa, bukan baca PDF lagi)
        AnalyzeResumeJob::dispatch($candidate->id);

        return redirect()->route('interview.start', ['candidate' => $candidate->id]);
    }

    public function render()
    {
        return view('livewire.candidate.upload-cv');
    }
}