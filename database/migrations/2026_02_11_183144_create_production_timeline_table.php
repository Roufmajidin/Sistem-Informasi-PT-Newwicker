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
        Schema::create('production_timeline', function (Blueprint $table) {
            $table->id();
            $table->integer('detail_po_id');
            $table->integer('po_id');
            $table->integer('spk_id');
            $table->string('sup');
            $table->string('jenis');
            $table->string('qty');
            $table->string('date');
            $table->string('remark')->default('-');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_timeline');
    }
};
