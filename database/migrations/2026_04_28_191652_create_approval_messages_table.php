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
        Schema::create('approval_messages', function (Blueprint $table) {
            $table->id();



            $table->text('message');
            $table->integer('pengajuan_id');
            $table->integer('user_id');
            $table->integer('approval_step_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_messages');
    }
};
