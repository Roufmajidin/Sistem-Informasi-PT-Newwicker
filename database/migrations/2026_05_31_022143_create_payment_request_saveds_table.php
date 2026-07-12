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
       Schema::create('payment_request_saveds', function (Blueprint $table) {

    $table->id();

    $table->string('request_no')->unique();

    $table->date('request_date')->nullable();

    $table->date('need_date')->nullable();

    $table->json('payment_request_ids');

    $table->decimal('grand_total', 18, 2)
        ->default(0);

    $table->string('status')
        ->default('draft');

    $table->unsignedBigInteger('created_by')
        ->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_request_saveds');
    }
};
