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
    Schema::create('export_ipl_pos', function (Blueprint $table) {

        $table->id();

        $table->foreignId('export_ipl_id')
            ->constrained('export_ipls')
            ->cascadeOnDelete();

        $table->foreignId('po_id')
            ->constrained('po')
            ->cascadeOnDelete();

        $table->string('po_no');

        $table->timestamps();

        $table->unique([
            'export_ipl_id',
            'po_id'
        ]);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_ipl_pos');
    }
};
