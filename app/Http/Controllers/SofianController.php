<?php

namespace App\Http\Controllers;

use App\Models\ExportIpl;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

use App\Exports\PackingListExport;
use Maatwebsite\Excel\Facades\Excel;

class SofianController extends Controller
{
    //
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
