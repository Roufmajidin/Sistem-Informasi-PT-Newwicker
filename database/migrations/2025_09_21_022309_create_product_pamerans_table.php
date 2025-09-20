<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductPameransTable extends Migration
{
    public function up()
    {
        Schema::create('product_pameranss', function (Blueprint $table) {
            $table->id();

            // Relasi ke pameran
            $table->foreignId('exhibition_id')->constrained('exhibitions')->onDelete('cascade');

            $table->integer('nr')->nullable();
            $table->string('photo')->nullable();
            $table->string('article_code')->nullable();
            $table->string('name')->nullable();
            $table->string('categories')->nullable();
            $table->string('sub_categories')->nullable();
            $table->text('remark')->nullable();

            // Item dimension
            $table->decimal('item_w', 8, 2)->nullable();
            $table->decimal('item_d', 8, 2)->nullable();
            $table->decimal('item_h', 8, 2)->nullable();

            // Packing dimension
            $table->decimal('packing_w', 8, 2)->nullable();
            $table->decimal('packing_d', 8, 2)->nullable();
            $table->decimal('packing_h', 8, 2)->nullable();

            // Size of set
            $table->integer('set2')->nullable();
            $table->integer('set3')->nullable();
            $table->integer('set4')->nullable();
            $table->integer('set5')->nullable();

            $table->string('composition')->nullable();
            $table->string('finishing')->nullable();
            $table->integer('qty')->nullable();
            $table->decimal('cbm', 10, 6)->nullable();

            // Loadability
            $table->decimal('loadability_20', 12, 2)->nullable();
            $table->decimal('loadability_40', 12, 2)->nullable();
            $table->decimal('loadability_40hc', 12, 2)->nullable();

            $table->decimal('rangka', 12, 2)->nullable();
            $table->decimal('anyam', 12, 2)->nullable();
            $table->decimal('finishing_powder_coating', 12, 2)->nullable();
            $table->decimal('accessories_final', 12, 2)->nullable();
            $table->decimal('electricity', 12, 2)->nullable();
            $table->decimal('packingbox', 12, 2)->nullable();

            $table->string('glass')->nullable();
            $table->decimal('cushion', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->nullable();

            // FOB & USD
            $table->decimal('fob_cost_20', 12, 2)->nullable();
            $table->decimal('fob_cost_40', 12, 2)->nullable();
            $table->decimal('fob_cost_40hc', 12, 2)->nullable();

            $table->decimal('total_production_cost_20', 12, 2)->nullable();
            $table->decimal('total_production_cost_40', 12, 2)->nullable();
            $table->decimal('total_production_cost_40hc', 12, 2)->nullable();

            $table->decimal('cogusd_rate_14500', 12, 4)->nullable();
            $table->decimal('price_cushion', 12, 2)->nullable();
            $table->decimal('price_item', 12, 2)->nullable();
            $table->decimal('fob_jakarta_in_usd', 12, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_pameranss');
    }
}
