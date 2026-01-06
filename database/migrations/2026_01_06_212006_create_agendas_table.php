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
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();

            $table->enum('jenis_agenda', [
                'photo sample',
                'photo produksi',
                'Maintenance',
            ]);

            $table->string('dibuat_oleh');

            $table->date('tanggal'); // ⬅️ TANPA DEFAULT

            $table->enum('status', [
                'urgent',
                'non urgent',
            ]);

            $table->text('catatan')->nullable();
            $table->text('remark_rouf')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};
