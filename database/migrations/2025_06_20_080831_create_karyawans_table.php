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
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('nik')->unique();
            $table->string('tempat');
            $table->date('tanggal_lahir');
            $table->text('alamat');
            $table->enum('status_perkawinan', ['Lajang', 'Menikah', 'Duda', 'Janda']);
            $table->unsignedBigInteger('divisi_id')->nullable();
            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            $table->string('lokasi');
            $table->date('tanggal_join');
            $table->timestamps();

            // Foreign key ke tabel divisis (jika ada)
            // $table->foreign('divisi_id')->references('id')->on('divisis')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
