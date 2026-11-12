<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WarehouseOpnameExport implements WithMultipleSheets
{
    public function __construct(private Collection $stoks, private Collection $transactions) {}

    public function sheets(): array
    {
        return [
            new WarehouseOpnameBarangSheet($this->stoks, $this->transactions),
            new WarehouseOpnameDetailSheet($this->stoks, $this->transactions),
        ];
    }
}
