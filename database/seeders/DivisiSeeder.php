<?php
namespace Database\Seeders;

use App\Models\Divisi;
use Illuminate\Database\Seeder;

class DivisiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisis = [
            'QC',
            'HRD GA & SHE',
            'FINANCE ACC',
            'RND',
            'DRIVER',
            'PRODUKSI',
            'FINISHING PRODUKSI',
            'DRIVER',
            'Drafter',
            'VP SALES & MARKETING',
            'PRODUKSI',
            'GA',
            'IT',
            'QC RND',
            'PROD',
            'RND',
            'DRIVER',
            'DRIVER',
            'COO',
            'PROD',
            'RND',
            'PROD',
            'RND',
            'PROD',
            'FIN',
            'PPIC',
            'RND',
            'SALES',
            'FIN',
            'QC',
            'EXPORT',
            'QC',
            'QC',
            'PROD',
            'QC',
            'PROD',
            'PROD',
            'PURCHASING',
            'RND',
            'PURCHASING',
            'PURCHASING',
            'PROD',
            'FIN',
        ];

        // Insert unique only
        foreach (array_unique($divisis) as $nama) {
            Divisi::firstOrCreate(['nama' => $nama]);
        }
    }
}
