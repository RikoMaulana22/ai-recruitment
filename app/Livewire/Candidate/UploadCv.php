<?php

namespace App\Livewire\Candidate;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Candidate;
use Livewire\Attributes\Layout;
use App\Services\GeminiService;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.guest')]
class UploadCv extends Component
{
    use WithFileUploads;

    // Data Form
    public $name;
    public $email;
    public $phone;
    public $summary;
    public $skills;
    
    // File Upload
    public $resume;
    
    // State UI
    public $isAutoFilling = false; // Untuk loading indicator

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email|unique:candidates,email',
        'phone' => 'required|numeric',
        'resume' => 'required|file|mimes:pdf|max:2048',
        'summary' => 'required|string',
    ];

    // Magic Method: Jalan otomatis saat user memilih file 'resume'
    public function updatedResume()
    {
        $this->validateOnly('resume');
        $this->isAutoFilling = true; // Nyalakan loading

        try {
            // 1. Baca Teks PDF
            $parser = new Parser();
            $pdf = $parser->parseFile($this->resume->getRealPath());
            $text = substr($pdf->getText(), 0, 4000); // Ambil 4000 karakter pertama

            // 2. Panggil AI untuk Ekstraksi
            $gemini = new GeminiService();
            $data = $gemini->extractDataFromCvText($text);

            // 3. Auto-fill Form
            if ($data) {
                $this->name = $data['name'] ?? '';
                $this->email = $data['email'] ?? '';
                $this->phone = $data['phone'] ?? '';
                $this->summary = $data['summary'] ?? '';
                // Jika skills array, gabungkan jadi string koma
                $this->skills = is_array($data['skills'] ?? '') 
                    ? implode(', ', $data['skills']) 
                    : ($data['skills'] ?? '');
            }

        } catch (\Exception $e) {
            Log::error("Gagal Auto-fill: " . $e->getMessage());
        }

        $this->isAutoFilling = false; // Matikan loading
    }

    public function submitForm()
    {
        $this->validate();

        // 1. Simpan File Fisik
        $path = $this->resume->store('cv_uploads', 'public'); // Simpan di storage/app/public

        // 2. Simpan Data ke Database
        $candidate = Candidate::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'resume_path' => $path,
            'ai_summary' => $this->summary, // Simpan ringkasan hasil edit user
            // Simpan skills di kolom json atau text (sesuaikan struktur DB Anda)
             'ai_analysis' => ['skills' => explode(',', $this->skills)], 
            'status' => 'pending'
        ]);

        // 3. Redirect ke Halaman Interview (Video/Chat)
        // Kita kirim ID kandidat agar halaman selanjutnya tahu siapa yg sedang interview
        return redirect()->route('interview.start', ['candidate' => $candidate->id]);
    }

    public function render()
    {
        return view('livewire.candidate.upload-cv');
    }
}