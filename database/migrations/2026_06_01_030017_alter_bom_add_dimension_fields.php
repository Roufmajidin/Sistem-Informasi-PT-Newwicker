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
        //
        Schema::table('bom', function (Blueprint $table) {

    $table->decimal(
        'panjang',
        12,
        2
    )->nullable();

    $table->decimal(
        'lebar',
        12,
        2
    )->nullable();

    $table->decimal(
        'tinggi',
        12,
        2
    )->nullable();

    $table->decimal(
        'carton_panjang',
        12,
        2
    )->nullable();

    $table->decimal(
        'carton_lebar',
        12,
        2
    )->nullable();

    $table->decimal(
        'carton_tinggi',
        12,
        2
    )->nullable();

    $table->integer(
        'loadability_pcs'
    )->nullable();

    $table->decimal(
        'loadability_cbm',
        12,
        3
    )->nullable();

    $table->string(
        'image'
    )->nullable();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
