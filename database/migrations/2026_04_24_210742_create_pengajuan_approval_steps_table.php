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
    Schema::create('pengajuan_approval_steps', function (Blueprint $table) {
        $table->id();
        $table->foreignId('pengajuan_id')->constrained()->cascadeOnDelete();

        $table->integer('step_order'); // urutan
        $table->string('step_name');   // Made By dll
        $table->string('user_name')->nullable();

        $table->enum('status', ['pending','approved'])->default('pending');
        $table->timestamp('approved_at')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_approval_steps');
    }
};
