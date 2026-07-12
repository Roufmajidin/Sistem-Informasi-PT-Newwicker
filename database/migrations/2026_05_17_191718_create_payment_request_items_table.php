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
        Schema::create('payment_request_items', function (Blueprint $table) {

            $table->id();

           $table->integer('payment_request_id')->nullable();
            $table->string('payment_type')->nullable();
            // dp / bahan / kasbon / pelunasan

            $table->bigInteger('amount')->default(0);

            $table->date('payment_date')->nullable();

            $table->text('note')->nullable();

            $table->boolean('is_selected')
                ->default(true);

            $table->enum('status', [
                'draft',
                'waiting',
                'approved',
                'rejected',
                'paid',
            ])->default('draft');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_request_items');
    }
};
