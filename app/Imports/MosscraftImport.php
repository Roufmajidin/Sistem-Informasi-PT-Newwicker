<?php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class MosscraftImport implements ToArray, WithCalculatedFormulas
{
    public array $data = [];

    public function array(array $array)
    {
        $this->data = $array;
    }
}
