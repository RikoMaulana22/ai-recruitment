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
    
    // Variabel ini digunakan untuk loading state di UI
    public $isAutoFilling = false;

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email|unique:candidates,email',
        'phone' => 'required|numeric',
        'resume' => 'required|file|mimes:pdf|max:2048',
    ];

    // --- FITUR TAMBAHAN: Auto Fill saat file dipilih (Opsional) ---
    // Fungsi ini akan jalan otomatis saat user memilih file PDF
    public function updatedResume() 
    {
        $this->isAutoFilling = true; // Nyalakan loading di UI

        // Validasi file dulu agar tidak error saat parsing
        $this->validateOnly('resume');

        try {
            // Jika kamu ingin fitur auto-fill nama/email dari PDF di sini
            // Kamu bisa pindahkan logika Parser ke sini.
            // Untuk sekarang, kita hanya simulasi loading sebentar.
            sleep(1); 
        } catch (\Exception $e) {
            // handle error
        }

        $this->isAutoFilling = false; // Matikan loading
    }
    // -------------------------------------------------------------

    // UBAH NAMA DARI 'save' MENJADI 'submitForm'
    public function submitForm()
    {
        $this->validate();

        // 1. Baca Teks PDF
        $resumeText = '';
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($this->resume->getRealPath());
            $text = $pdf->getText();
            
            // Bersihkan teks
            $resumeText = preg_replace('/\s+/', ' ', trim($text));
            $resumeText = substr($resumeText, 0, 5000);
            
        } catch (\Exception $e) {
            Log::error("Gagal baca PDF: " . $e->getMessage());
        }

        // 2. Simpan File Fisik
        $path = $this->resume->store('cv_uploads', 'public'); // Gunakan 'public' agar bisa diakses (opsional)

        // 3. Simpan ke Database
        $candidate = Candidate::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'resume_path' => $path,
            'resume_text' => $resumeText,
            'status' => 'pending'
        ]);

        // 4. Panggil AI Job
        AnalyzeResumeJob::dispatch($candidate->id);

        session()->flash('message', 'CV berhasil diunggah! Sedang diproses AI.');

        return redirect()->route('interview.start', ['candidate' => $candidate->id]);
    }

    public function render()
    {
        return view('livewire.candidate.upload-cv');
    }
}