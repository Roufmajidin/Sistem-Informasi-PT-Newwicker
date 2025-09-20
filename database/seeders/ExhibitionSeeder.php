<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exhibition;

class ExhibitionSeeder extends Seeder
{
    public function run(): void
    {
        $exhibitions = [
            ['name' => 'Pameran 2020', 'year' => 2020],
            ['name' => 'Pameran 2021', 'year' => 2021],
            ['name' => 'Pameran 2022', 'year' => 2022],
            ['name' => 'Pameran 2022', 'year' => 2023],
            ['name' => 'Pameran 2022', 'year' => 2024],
            ['name' => 'Pameran 2022', 'year' => 2025],
        ];

        foreach ($exhibitions as $ex) {
            Exhibition::create($ex);
        }
    }
}
