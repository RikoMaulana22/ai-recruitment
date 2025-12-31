<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'chat_history',
        'interview_score',
        'interview_summary',
    ];

    protected $casts = [
        'chat_history' => 'array', // Penting: Agar JSON otomatis jadi Array
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}