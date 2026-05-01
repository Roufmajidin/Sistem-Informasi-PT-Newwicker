<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('bom_groups')->cascadeOnDelete();

            $table->string('name');
            $table->decimal('qty', 10, 3)->nullable();
            $table->string('unit')->nullable();
            $table->text('notes')->nullable();

            // 🔥 support nested (opsional tapi disarankan)
            $table->foreignId('parent_id')->nullable()->constrained('bom_items')->cascadeOnDelete();
            $table->integer('level')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_items');
    }
};
