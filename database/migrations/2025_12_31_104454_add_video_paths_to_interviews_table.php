<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('interviews', function (Blueprint $table) {
        // Kita simpan path video untuk 3 pertanyaan
        $table->string('video_answer_1')->nullable();
        $table->string('video_answer_2')->nullable();
        $table->string('video_answer_3')->nullable();
        // Hapus kolom chat history karena tidak dipakai lagi
        $table->dropColumn('chat_history');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            //
        });
    }
};
