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
       Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->string('jenis');
            $table->text('deskripsi')->nullable();
            $table->string('merk')->nullable();
            $table->foreignId('karyawan_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->text('foto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
