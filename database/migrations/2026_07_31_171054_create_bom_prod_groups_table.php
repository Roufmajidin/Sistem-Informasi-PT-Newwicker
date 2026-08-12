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
       Schema::create('bom_prod_groups', function (Blueprint $table) {
           $table->id();

           $table->foreignId('bom_prod_id')
               ->constrained('bom_prod')
               ->cascadeOnDelete();

           $table->string('name');

           $table->timestamps();
       });
   }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_prod_groups');
    }
};
