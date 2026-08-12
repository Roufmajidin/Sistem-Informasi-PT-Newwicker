<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AdminReportExport implements WithMultipleSheets
{
    protected $timelines;
    protected $inspection;
    protected $from;
    protected $to;

    public function __construct($timelines,$inspection,$from,$to)
    {
        $this->timelines = $timelines;
        $this->inspection = $inspection;
        $this->from = $from;
        $this->to = $to;
    }

    public function sheets(): array
    {
        return [

            new BarangJadiExport(
                $this->timelines,
                $this->from,
                $this->to
            ),

            new QcExports(
                $this->inspection,
                $this->from,
                $this->to
            ),

        ];
    }
}