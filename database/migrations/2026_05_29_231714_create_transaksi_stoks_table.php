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
        Schema::create('transaksi_stoks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stok_id')
                ->constrained('stoks')
                ->cascadeOnDelete();

            $table->date('tanggal');

            $table->enum('tipe', [
                'in',
                'out',
            ]);

            $table->integer('qty');

            $table->string('po')->nullable();
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_stoks');
    }
};
