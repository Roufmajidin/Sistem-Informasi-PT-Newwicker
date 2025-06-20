<?php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class KaryawanImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        return $rows->skip(2); // skip header row
    }
}

