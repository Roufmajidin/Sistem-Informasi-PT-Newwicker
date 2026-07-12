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
        Schema::create('stoks', function (Blueprint $table) {
            $table->id();

            $table->string('kode_barang')->unique();
            $table->string('nama_barang');

            $table->enum('jenis', [
                'bahan baku',
                'bahan penolong',
                'bahan penolong alat',
            ]);

            $table->string('satuan')->nullable();
            $table->decimal('harga', 15, 2)->default(0);
            $table->integer('stok_awal')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stoks');
    }
};
