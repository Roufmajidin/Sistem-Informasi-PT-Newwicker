<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labelings', function (Blueprint $table) {
            $table->id();
            $table->string('description')->nullable();
            $table->json('labels')->nullable(); // simpan array list label
            $table->date('jadwal_container')->nullable();
            $table->string('status_rouf')->nullable();
            $table->string('status_yogi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labelings');
    }
};
