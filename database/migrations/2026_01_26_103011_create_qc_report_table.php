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
        Schema::create('qc_report', function (Blueprint $table) {
            $table->id();
            $table->integer('check_point_id');
            $table->String('remark')->nullable();
            $table->string('size')->nullable();
            $table->integer('po_id');
            $table->integer('detail_po_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qc_report');
    }
};
