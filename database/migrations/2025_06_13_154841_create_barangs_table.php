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
       Schema::create('barangs', function (Blueprint $table) {
    $table->id();
    $table->integer('buyer_id');
    $table->string('photo')->nullable();
    $table->string('buyer_s_code')->nullable();
    $table->string('description')->nullable();
    $table->string('article_nr')->nullable();
    $table->string('remark')->nullable();
    $table->text('cushion')->nullable();
    $table->string('glass_orMirror')->nullable();
    $table->string('uom')->nullable();
    $table->integer('w')->nullable();
    $table->integer('d')->nullable();
    $table->integer('h')->nullable();
    $table->integer('sw')->nullable();
    $table->integer('sh')->nullable();
    $table->integer('sd')->nullable();
    $table->integer('ah')->nullable();
    $table->integer('weight_capacity')->nullable();
    $table->text('materials')->nullable();
    $table->string('finishes_color')->nullable();
    $table->text('weaving_composition')->nullable();
    $table->integer('usd_selling_price')->nullable();
    $table->string('packing_dimention')->nullable();
    $table->integer('nw')->nullable();
    $table->integer('gw')->nullable();
    $table->integer('cbm')->nullable();
    $table->text('accessories')->nullable();
    $table->string('picture_of_accessories')->nullable();
    $table->string('leather')->nullable();
    $table->string('picture_of_leather')->nullable();
    $table->text('finish_steps')->nullable();
    $table->string('harga_supplier')->nullable();

    // Field tambahan
    $table->string('electricity')->nullable();
    $table->text('comment_visit')->nullable();
    $table->string('loadability')->nullable();

    $table->timestamps();
});

    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
