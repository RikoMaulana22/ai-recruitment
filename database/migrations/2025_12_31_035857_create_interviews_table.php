<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('interviews', function (Blueprint $table) {
        $table->id();
        $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
        
        // Menyimpan riwayat chat (User & AI) dalam format JSON
        // Contoh: [{"role": "ai", "content": "Halo..."}, {"role": "user", "content": "Jawabanku..."}]
        $table->json('chat_history')->nullable(); 
        
        $table->integer('interview_score')->nullable(); // Skor hasil wawancara
        $table->text('interview_summary')->nullable();  // Kesimpulan wawancara
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};
