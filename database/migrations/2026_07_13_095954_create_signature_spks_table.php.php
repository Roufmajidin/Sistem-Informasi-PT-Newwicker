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
      Schema::create('signature_spks', function (Blueprint $table) {

        $table->id();

        $table->unsignedBigInteger('spk_id');

        $table->unsignedBigInteger('supplier_id')
            ->nullable();

        $table->unsignedBigInteger('made_by')
            ->nullable();

        $table->unsignedBigInteger('checked_by')
            ->nullable();

        $table->unsignedBigInteger('approved_by')
            ->nullable();

        /*
        timestamps masing-masing
        */
        $table->timestamp('made_at')
            ->nullable();

        $table->timestamp('checked_at')
            ->nullable();

        $table->timestamp('approved_at')
            ->nullable();

        $table->timestamps();


    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signature_spks');
    }
};
