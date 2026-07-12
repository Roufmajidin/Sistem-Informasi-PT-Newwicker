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
        Schema::create('bom_summaries', function (Blueprint $table) {

                $table->id();

               $table->unsignedBigInteger('bom_id');

                $table->foreign('bom_id')
                    ->references('id')
                    ->on('bom')
                    ->onDelete('cascade');

                $table->string('name')->nullable();
                $table->string('remark')->nullable();

                $table->decimal('qty', 15, 4)->default(0);
                $table->decimal('price', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);

                $table->timestamps();

            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_summaries');
    }
};
