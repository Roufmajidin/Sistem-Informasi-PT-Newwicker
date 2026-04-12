<?php
namespace Database\Seeders;

use App\Models\IzinType;
use Illuminate\Database\Seeder;

class IzinTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run()
    {
        IzinType::insert([
            ['name' => 'sakit', 'code' => 'SKT'],
            ['name' => 'izin', 'code' => 'IZN'],
            ['name' => 'cuti', 'code' => 'CTI'],
            ['name' => 'alpha', 'code' => 'ALP'],
        ]);
    }
}
