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
    Schema::create('pengajuan_meta', function (Blueprint $table) {
        $table->id();
        $table->foreignId('pengajuan_id')->constrained()->cascadeOnDelete();

        $table->date('tanggal')->nullable();
        $table->string('nomor')->nullable();
        $table->string('type_pembayaran')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_metas');
    }
};
