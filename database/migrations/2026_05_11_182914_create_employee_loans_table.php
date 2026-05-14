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
        Schema::create('employee_loans', function (Blueprint $table) {

            $table->id();

            $table->string('nama_karyawan');
            $table->integer('user_id');
            $table->string('approver')
                ->nullable();
            $table->string('status')->nullable();
            // department/divisi
            $table->foreignId('divisi_id')
                ->nullable()
                ->constrained('divisis')
                ->nullOnDelete();

            $table->decimal('nominal_pengajuan', 15, 2);

            $table->text('alasan_pengajuan');

            // pemotongan_gaji / tunai
            $table->enum('cara_pengembalian', [
                'pemotongan_gaji',
                'tunai',
            ]);

            // nullable jika tunai
            $table->decimal('nominal_potongan_gaji', 15, 2)
                ->nullable();

            // 2-12 bulan
            $table->integer('periode_pembayaran');

            // contoh: Januari 2025
            $table->string('pelunasan_terakhir')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_loans');
    }
};
