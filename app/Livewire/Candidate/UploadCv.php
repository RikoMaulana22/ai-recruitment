<?php

namespace App\Livewire\Candidate;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Candidate;
use Livewire\Attributes\Layout;
use App\Jobs\AnalyzeResumeJob;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.guest')]
class UploadCv extends Component
{
    use WithFileUploads;

    public $name;
    public $email;
    public $phone;
    public $resume;
    
    // Loading state UI
    public $isAutoFilling = false;

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email|unique:candidates,email',
        'phone' => 'required|numeric',
        'resume' => 'required|file|mimes:pdf|max:2048',
    ];

    public function updatedResume() 
    {
        $this->isAutoFilling = true; 
        $this->validateOnly('resume');

        try {
            // Simulasi loading atau logika parsing awal jika diperlukan
            sleep(1); 
        } catch (\Exception $e) {
            // silent fail
        }

        $this->isAutoFilling = false;
    }

    public function submitForm()
    {
        $this->validate();

        // 1. Ekstrak Teks dari PDF
        $resumeText = '';
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($this->resume->getRealPath());
            $text = $pdf->getText();
            
            // Bersihkan teks (hapus spasi berlebih) & batasi karakter agar tidak overload token AI
            $resumeText = preg_replace('/\s+/', ' ', trim($text));
            $resumeText = substr($resumeText, 0, 5000); 
            
        } catch (\Exception $e) {
            Log::error("Gagal baca PDF: " . $e->getMessage());
            // Lanjut saja, nanti Job akan cek jika resume_text kosong
        }

        // 2. Simpan File Fisik
        $path = $this->resume->store('cv_uploads', 'public');

        // 3. Simpan ke Database
        $candidate = Candidate::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'resume_path' => $path,
            'resume_text' => $resumeText,
            'status' => 'pending'
        ]);

        // 4. Panggil AI Job (FIX: Mengirim Object Candidate, bukan ID)
        AnalyzeResumeJob::dispatch($candidate);

        session()->flash('message', 'CV berhasil diunggah! Sedang diproses AI.');

        // Pastikan route 'interview.start' sudah ada di web.php
        return redirect()->route('interview.start', ['candidate' => $candidate->id]);
    }

    public function render()
    {
        return view('livewire.candidate.upload-cv');
    }
}