<?php

namespace App\Http\Controllers;

use App\Models\ExportIpl;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

use App\Exports\PackingListExport;
use App\Exports\CommercialInvoiceExport;
use Maatwebsite\Excel\Facades\Excel;

class SofianController extends Controller
{
    //download iovice exports
      public function downloadInvoiceList($id)
    {
        // 1. Fetch invoice data along with relations
        $invoice = ExportIpl::with(['items', 'pos', 'creator'])->findOrFail($id);

        $fileName = 'Commercial_Invoice_' . str_replace(['/', '\\\\', ' '], '_', $invoice->invoice_no) . '.xlsx';

        // 2. Export Excel using Laravel-Excel (Maatwebsite)
        return Excel::download(new CommercialInvoiceExport($invoice), $fileName);
    }
    // download
   public function downloadPackingList($id)
    {
        $ipl = ExportIpl::with([
            'items',
            'pos',
            'creator',
        ])->findOrFail($id);

        $fileName = 'PACKING_LIST_' .
            str_replace('/', '_', $ipl->invoice_no ?? $ipl->id) .
            '.xlsx';

        return Excel::download(
            new PackingListExport($ipl),
            $fileName
        );
    }
}
