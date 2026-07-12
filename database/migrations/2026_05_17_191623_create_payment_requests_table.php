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
        Schema::create('payment_requests', function (Blueprint $table) {

            $table->id();

            // =========================
            // RELATION
            // =========================
         $table->integer('spk_id');
            // =========================
            // INFO
            // =========================
            $table->string('request_no')
                ->nullable()
                ->index();

            $table->string('no_spk')
                ->nullable()
                ->index();

            $table->string('no_po')
                ->nullable();

            $table->string('supplier')
                ->nullable();

            $table->string('kategori')
                ->nullable();

            // =========================
            // DATE
            // =========================
            $table->date('request_date')
                ->nullable();

            $table->date('need_date')
                ->nullable();

            // =========================
            // CHECKBOX TYPE
            // ex:
            // ["dp","kasbon"]
            // =========================
            $table->json('checked_types')
                ->nullable();

            // =========================
            // TOTAL
            // =========================
            $table->bigInteger('total_amount')
                ->default(0);

            // =========================
            // STATUS
            // =========================
            $table->enum('status', [

                'draft',
                'pending',
                'approved',
                'rejected',
                'paid',

            ])->default('draft');

            // =========================
            // SNAPSHOT SPK
            // =========================
            $table->json('spk_snapshot')
                ->nullable();

            // =========================
            // USER
            // =========================
            $table->unsignedBigInteger('created_by')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_requests');
    }
};
