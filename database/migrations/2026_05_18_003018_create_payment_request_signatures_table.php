<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'payment_request_signatures',
            function (Blueprint $table) {

                $table->id();

                $table->unsignedBigInteger(
                    'payment_request_id'
                );

                $table->string('role');

                $table->unsignedBigInteger(
                    'user_id'
                )->nullable();

                $table->enum('status',[

                    'pending',

                    'approved',

                    'rejected'

                ])->default('pending');

                $table->timestamp(
                    'signed_at'
                )->nullable();

                $table->text(
                    'note'
                )->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'payment_request_signatures'
        );
    }
};
