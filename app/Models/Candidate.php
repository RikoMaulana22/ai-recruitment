<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'resume_path',
        'resume_text',
        'status',      // pending, analyzed, interviewing, rejected, hired
        'ai_analysis', // Menyimpan JSON lengkap dari Gemini
        'score',       // Opsional: Jika ingin menyimpan skor di kolom terpisah
        'ai_summary',  // Opsional: Jika ingin menyimpan ringkasan terpisah
    ];

    /**
     * Casting otomatis kolom JSON menjadi Array PHP.
     * Jadi $candidate->ai_analysis akan langsung berupa array, bukan string.
     */
    protected $casts = [
        'ai_analysis' => 'array',
        'score' => 'integer', // Pastikan score dianggap angka
    ];

    /**
     * Relasi ke Interview
     */
    public function interview(): HasOne
    {
        return $this->hasOne(Interview::class);
    }

    /**
     * --- HELPER / ACCESSOR ---
     * Fungsi ini memudahkanmu mengambil data spesifik dari JSON ai_analysis.
     * Cara pakainya di Blade: {{ $candidate->getAnalysis('match_score') }}
     */
    public function getAnalysis($key, $default = '-')
    {
        // Cek apakah ai_analysis ada isinya dan berupa array
        if ($this->ai_analysis && is_array($this->ai_analysis)) {
            return $this->ai_analysis[$key] ?? $default;
        }
        return $default;
    }
}