<?php
namespace App\Console\Commands;

use App\Imports\ProductImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ImportProdukCommand extends Command
{
    // ✅ Nama command di terminal
      protected $signature = 'import:produk
        {--file= : Path ke file Excel}
        {--buyer= : ID buyer}
        {--photo=A : Kolom gambar (default A)}
        {--code=D : Kolom kode produk (default D)}';

    protected $description = 'Import data produk dari file Excel';


    public function handle()
    {
        $filePath    = $this->option('file');
        $buyerId     = $this->option('buyer');
        $photoColumn = $this->option('photo') ?? 'A';
        $codeColumn  = $this->option('code') ?? 'D';

        if (! file_exists($filePath)) {
            $this->error("File tidak ditemukan: $filePath");
            return Command::FAILURE;
        }

        $this->info("Import dimulai dari: $filePath");
        $this->info("Buyer ID: $buyerId | Photo: $photoColumn | Code: $codeColumn");

        Excel::import(new ProductImport($buyerId, $photoColumn, $codeColumn), $filePath);

        $this->info("✅ Import selesai.");

        return Command::SUCCESS;
    }
}
