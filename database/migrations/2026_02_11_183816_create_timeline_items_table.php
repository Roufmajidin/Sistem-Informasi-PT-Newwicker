<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('timeline_items', function (Blueprint $table) {
            $table->id();

            /* ===== ACTOR ===== */
            $table->unsignedBigInteger('user_id')->nullable()
                  ->comment('Admin yang melakukan aksi');

            /* ===== AKSI ===== */
            $table->string('action')
                  ->comment('in | out | service | back | edit | delete');

            /* ===== REFERENSI DATA ===== */
            $table->string('ref_table')->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();

            /* ===== RELASI PRODUKSI ===== */
            $table->unsignedBigInteger('detail_po_id')->nullable();
            $table->unsignedBigInteger('po_id')->nullable();
            $table->unsignedBigInteger('spk_id')->nullable();

            /* ===== SUB / SERVICE ===== */
            $table->string('sup')->nullable();

            /* ===== QTY ===== */
            $table->integer('qty')->default(0);

            /* ===== DATA TAMBAHAN ===== */
            $table->json('payload')->nullable()
                  ->comment('snapshot before/after atau keterangan tambahan');

            $table->timestamps();

            /* ===== INDEX ===== */
            $table->index(['user_id', 'action']);
            $table->index(['detail_po_id']);
            $table->index(['ref_table', 'ref_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeline_items');
    }
};
