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
        Schema::create('absens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('tanggal');                  // tanggal absen
            $table->time('jam_masuk')->nullable();    // jam datang
            $table->time('jam_keluar')->nullable();   // jam pulang
            $table->string('keterangan')->nullable(); // hadir, izin, sakit, dll

            $table->timestamps();

            $table->unique(['user_id', 'tanggal']); // 1 user hanya 1 absen per hari
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absens');
    }
};
