<?php

namespace Database\Seeders;

use App\Models\Divisi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DivisiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  public function run(): void
    {
        $divisis = [
            'IT',
            'Finance',
            'Sales & Marketing',
            'QC PRODUKSI',
            'EXPORT HRDGA',
            'FINANCE ACC',
            'RND',
            'DRIVER',
            'PRODUKSI',
            'FINISHING PRODUKSI',
            'GENERAL MANAGER',
            'FIN ACC MANAGER',
            'DRAFTER RND',
            'FIN ACC',
            'SALES MARKETING',
            'EXPORT',
            'KEPALA PROD',
            'KEPALA RND',
            'ADM PROD',
            'PRODUKSI NW PLUS',
            'DRIVER NW PLUS',
            'PROD NW PLUS',
            'FINISHING PROD NW PLUS',
        ];

        // Insert unique only
        foreach (array_unique($divisis) as $nama) {
            Divisi::firstOrCreate(['nama' => $nama]);
        }
    }
}
