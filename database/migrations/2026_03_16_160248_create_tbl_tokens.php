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
        Schema::create('tbl_tokens', function (Blueprint $table) {

            $table->id();
            $table->string('token', 6)->unique();

            $table->string('name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();

            $table->integer('duration')->default(60); // durasi menit
            $table->timestamp('expired_at')->nullable();

            $table->boolean('used')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_tokens');
    }
};
