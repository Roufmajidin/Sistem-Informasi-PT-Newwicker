<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timeline_qcs', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('po_id');
            $table->unsignedBigInteger('detail_po_id');
            $table->unsignedBigInteger('kategori_id');
            $table->unsignedBigInteger('inspect_schedule_id');

            $table->unsignedBigInteger('user_id')->nullable();

            $table->integer('qty')->default(0);

            $table->date('tanggal');

            // apakah lanjutan dari inspect sebelumnya
            $table->boolean('is_lanjutan')->default(false);

            $table->timestamps();

            /* ===============================
               INDEX
            =============================== */
            $table->index('po_id');
            $table->index('detail_po_id');
            $table->index('kategori_id');
            $table->index('inspect_schedule_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeline_qcs');
    }
};
