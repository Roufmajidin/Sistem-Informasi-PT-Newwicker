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
    Schema::create('pengajuans', function (Blueprint $table) {
        $table->id();

        $table->string('type_pengajuan');
        $table->string('file')->nullable();

        $table->foreignId('user_id')->constrained()->cascadeOnDelete();

        $table->enum('status', ['pending', 'approved'])->default('pending');

        $table->text('keterangan')->nullable();
        $table->timestamp('approved_date')->nullable();
        $table->text('remark')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuans');
    }
};
