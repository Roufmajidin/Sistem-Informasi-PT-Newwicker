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
        Schema::create('buyers', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->nullable();
            $table->string('company_name')->nullable();
            $table->string('shipment_date')->nullable();
            $table->string('country')->nullable();
            $table->string('packing')->nullable();
            $table->string('contact_person')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyyers');
    }
};
