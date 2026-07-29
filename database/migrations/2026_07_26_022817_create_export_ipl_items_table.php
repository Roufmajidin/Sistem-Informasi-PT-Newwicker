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
    Schema::create('export_ipl_items', function (Blueprint $table) {

        $table->id();

        $table->foreignId('export_ipl_id')
            ->constrained('export_ipls')
            ->cascadeOnDelete();

        $table->foreignId('po_id')
            ->nullable()
            ->constrained('po')
            ->nullOnDelete();

        $table->foreignId('detail_po_id')
            ->nullable()
            ->constrained('detail_po')
            ->nullOnDelete();

        $table->string('po_no')->nullable();

        $table->string('hs_code')->nullable();

        $table->string('article_nr')->nullable();

        $table->longText('description')->nullable();

        $table->text('photo')->nullable();

        $table->string('box_dimension')->nullable();

        $table->integer('qty_pcs')->default(0);
        $table->integer('qty_box')->default(0);

        $table->decimal('cbm', 12, 3)->default(0);

        $table->decimal('total_cbm', 12, 3)->default(0);

        $table->decimal('unit_price', 15, 2)->default(0);

        $table->decimal('total_price', 15, 2)->default(0);

        $table->decimal('net_weight', 12, 2)->default(0);

        $table->decimal('gross_weight', 12, 2)->default(0);

        $table->text('remark')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_ipl_items');
    }
};
