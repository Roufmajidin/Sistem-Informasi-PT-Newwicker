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
    Schema::create('pengajuan_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('pengajuan_id')->constrained()->cascadeOnDelete();

        $table->integer('no')->nullable();
        $table->date('date')->nullable();

        $table->string('no_po')->nullable();
        $table->string('no_inv')->nullable();

        $table->string('type_biaya')->nullable();
        $table->string('nama_barang')->nullable();

        $table->integer('qty')->nullable();
        $table->decimal('harga_satuan', 15, 2)->nullable();
        $table->decimal('total_harga', 15, 2)->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_details');
    }
};
