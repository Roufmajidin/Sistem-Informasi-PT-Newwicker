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
       Schema::create('export_ipls', function (Blueprint $table) {

        $table->id();

        $table->string('invoice_no')->unique();
        $table->string('sales_order')->nullable();

        $table->string('buyer')->nullable();
        $table->text('buyer_address')->nullable();

        $table->string('customer_code')->nullable();
        $table->string('customer_po_no')->nullable();

        $table->string('container_type')->nullable();
        $table->string('container_no')->nullable();
        $table->string('seal_no')->nullable();

        $table->string('vessel_name')->nullable();

        $table->string('port_loading')->nullable();
        $table->string('port_discharge')->nullable();

        $table->string('commodity')->nullable();
        $table->string('fumigation')->nullable();

        $table->date('etd')->nullable();
        $table->date('eta')->nullable();

        $table->foreignId('created_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_ipls');
    }
};
