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
    Schema::table('spk', function ($table) {

        $table->enum('status',[
            'draft',
            'progress',
            'finished',
            'closed'
        ])
        ->default('draft')
        ->after('data');

        $table->timestamp('finished_at')
            ->nullable();

        $table->unsignedBigInteger('finished_by')
            ->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
