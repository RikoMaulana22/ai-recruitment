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
        // --- TAMBAHKAN 3 BARIS INI ---
        'video_answer_1',
        'video_answer_2',
        'video_answer_3',
    ];

    protected $casts = [
        'chat_history' => 'array',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}