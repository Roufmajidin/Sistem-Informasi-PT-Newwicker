<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {

            // =========================
            // PAYMENT SOURCE
            // =========================
            $table->string('payment_id')
                ->nullable()
                ->after('spk_id')
                ->index();

            // =========================
            // APPROVAL
            // =========================
            $table->unsignedBigInteger('approved_by')
                ->nullable()
                ->after('created_by');

            $table->timestamp('approved_at')
                ->nullable()
                ->after('approved_by');

            // =========================
            // NOTE
            // =========================
            $table->text('note')
                ->nullable()
                ->after('spk_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {

            $table->dropColumn([
                'payment_id',
                'approved_by',
                'approved_at',
                'note'
            ]);
        });
    }
};
