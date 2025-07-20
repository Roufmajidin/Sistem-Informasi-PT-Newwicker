<?php

namespace App\Jobs;

use App\Imports\ProductImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ImportProdukJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path, $buyerId, $photoColumn, $codeColumn;

    public function __construct($path, $buyerId, $photoColumn = 'A', $codeColumn = 'D')
    {
        $this->path = $path;
        $this->buyerId = $buyerId;
        $this->photoColumn = $photoColumn;
        $this->codeColumn = $codeColumn;
    }

    public function handle()
    {
        Excel::import(
            new ProductImport($this->buyerId, $this->photoColumn, $this->codeColumn),
            $this->path
        );
    }
}
