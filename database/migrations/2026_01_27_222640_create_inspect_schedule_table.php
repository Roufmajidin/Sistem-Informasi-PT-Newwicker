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
        Schema::create('inspect_schedule', function (Blueprint $table) {
            $table->id();
            $table->integer('po_id');
            $table->integer('detail_po_id');
            $table->integer('batch');
            $table->integer('jumlah_inspect');
            $table->String('tanggal_inspect')->nullable();
            $table->String('user_id')->nullable();
            $table->unsignedBigInteger('kategori_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspect_schedule');
    }
};
