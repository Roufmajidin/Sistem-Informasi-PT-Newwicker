<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_materials', function (Blueprint $table) {

            $table->id();

            $table->string('kode_barang')->nullable();

            $table->string('nama_barang');

            $table->string('satuan')->nullable();

            $table->decimal('qty', 15, 2)->default(0);

            $table->decimal('harga_qty', 15, 2)->default(0);

            $table->decimal('jumlah', 15, 2)->default(0);

            $table->decimal('in_qty', 15, 2)->default(0);

            $table->decimal('out_qty', 15, 2)->default(0);

            $table->decimal('sisa', 15, 2)->default(0);

            $table->string('gudang')->nullable();

            $table->string('no_po')->nullable();

            $table->date('tanggal')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_materials');
    }
};
