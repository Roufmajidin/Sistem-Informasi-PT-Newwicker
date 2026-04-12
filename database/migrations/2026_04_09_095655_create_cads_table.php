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
        Schema::create('cads', function (Blueprint $table) {
            $table->id();

            $table->string('article_code')->index(); // bisa untuk search
            $table->string('file_path');             // path file CAD
            $table->integer('version')->default(1);

            $table->unsignedBigInteger('uploaded_by')->nullable(); // user id

            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cads');
    }
};
