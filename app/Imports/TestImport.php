<?php
namespace App\Imports;

use App\Models\Barangs;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TestImport implements ToModel, WithHeadingRow
{public function model(array $row)
    {
    return new Barangs([
        'photo'       => $row['Photo'] ,
        'buyer_code' => $row["Buyer's Code"],
        'item_code'   => $row['Article Nr.'] ,
        'description' => $row['Description'] ,
        // dan seterusnya sesuai kolom yang tersedia...
    ]);
}
    public function headingRowFormatter()
    {
        return function ($heading) {
            // Jadikan huruf kecil, ganti spasi dan karakter spesial
            return strtolower(preg_replace('/[^a-z0-9]/', '_', $heading));
        };
    }}
