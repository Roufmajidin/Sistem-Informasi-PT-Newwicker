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
        Schema::create('material_finishings', function (Blueprint $table) {
            $table->id();

            $table->string('nama');

            $table->string('jenis_propan')->nullable();
            $table->string('jenis_diva')->nullable();
            $table->string('jenis_warna_prima')->nullable();
            $table->string('jenis_legenda')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_finishings');
    }
};
