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
    Schema::create('candidates', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('phone')->nullable();
        $table->string('resume_path'); // Lokasi file PDF tersimpan
        
        // Kolom untuk hasil AI
        $table->longText('resume_text')->nullable(); // Teks mentah hasil parsing PDF
        $table->json('ai_analysis')->nullable();     // Hasil breakdown skill (JSON)
        $table->integer('score')->default(0);        // Skor kecocokan (0-100)
        $table->text('ai_summary')->nullable();      // Ringkasan opini AI
        
        $table->string('status')->default('pending'); // pending, interview, rejected, hired
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
