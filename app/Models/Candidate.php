<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'resume_path',
        'status',
        // Kolom hasil AI (pastikan Anda sudah menjalankan migration sebelumnya)
        'resume_text',
        'ai_analysis',
        'score',
        'ai_summary',
    ];

    // Casting agar kolom JSON otomatis jadi array saat dipanggil
    protected $casts = [
        'ai_analysis' => 'array',
    ];

    // Tambahkan di dalam class Candidate
    public function interview()
    {
        return $this->hasOne(Interview::class);
    }
}