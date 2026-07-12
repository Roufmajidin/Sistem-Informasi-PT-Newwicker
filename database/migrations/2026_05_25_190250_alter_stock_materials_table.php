<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {

        Schema::table(

            'stock_materials',

            function (Blueprint $table) {

                // =====================
                // TAMBAHAN KOLOM
                // =====================

                if (!Schema::hasColumn('stock_materials', 'satuan')) {

                    $table->string('satuan')
                        ->nullable();

                }

                if (!Schema::hasColumn('stock_materials', 'gudang')) {

                    $table->string('gudang')
                        ->nullable();

                }

                if (!Schema::hasColumn('stock_materials', 'no_po')) {

                    $table->string('no_po')
                        ->nullable();

                }

                if (!Schema::hasColumn('stock_materials', 'tanggal')) {

                    $table->date('tanggal')
                        ->nullable();

                }

            }

        );

    }



    public function down(): void
    {

        Schema::table(

            'stock_materials',

            function (Blueprint $table) {

                $table->dropColumn([

                    'satuan',
                    'gudang',
                    'no_po',
                    'tanggal'

                ]);

            }

        );

    }

};
