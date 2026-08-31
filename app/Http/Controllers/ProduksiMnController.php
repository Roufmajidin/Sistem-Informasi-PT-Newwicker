<?php
namespace App\Http\Controllers;

use App\Models\InspectSchedule;
use App\Models\Kategori;
use App\Models\Po;
use App\Models\ProductionTimeline;
use App\Models\QcReport;
use App\Models\Spk;
use App\Models\Supplier;
use App\Models\TransaksiStok;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SignatureSpk;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestSaved;
use App\Models\PaymentRequestApproval;
use Illuminate\Support\Facades\Log;
use App\Exports\BarangJadiExport;
use App\Exports\AdminReportExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\SpkLama;
use App\Models\InvLama;
use App\Models\MonitoringInvoice;
use App\Models\DetailPo;

use App\Helpers\ProductionMonitoringHelper;


class ProduksiMnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function qcReport($inspectScheduleId)
    {
        /*
    |--------------------------------------------------------------------------
    | INSPECT
    |--------------------------------------------------------------------------
    */
        $inspect = InspectSchedule::with([
            'kategori',
        ])->findOrFail($inspectScheduleId);
        /*
    |--------------------------------------------------------------------------
    | GET QC REPORT
    |--------------------------------------------------------------------------
    */
        $reports = DB::table('qc_report')
            ->where(
                'inspect_schedule_id',
                $inspect->id
            )
            ->orderBy('check_point_id')
            ->get();
        /*
    |--------------------------------------------------------------------------
    | GET PHOTOS
    |--------------------------------------------------------------------------
    */
        $photos = DB::table('report_photo')
            ->where(
                'inspect_schedule_id',
                $inspect->id
            )
            ->get();
        /*
    |--------------------------------------------------------------------------
    | GROUPED
    |--------------------------------------------------------------------------
    */
        $grouped = [];
        foreach ($reports as $report) {
            $dateKey = \Carbon\Carbon::parse(
                $report->created_at
            )->format('Y-m-d');
            /*
        |--------------------------------------------------------------------------
        | REMARK
        |--------------------------------------------------------------------------
        */
            $remark = [];
            if ($report->remark) {
                $decoded = json_decode(
                    $report->remark,
                    true
                );
                if (
                    json_last_error()
                    === JSON_ERROR_NONE
                ) {
                    $remark = $decoded;
                } else {
                    $remark = [
                        'text' => $report->remark,
                    ];
                }
            }
            /*
        |--------------------------------------------------------------------------
        | PUSH DATA
        |--------------------------------------------------------------------------
        */
            $grouped[$dateKey][] = [
                'check_point_id' =>
                $report->check_point_id,
                'remark'         =>
                $remark,
                'created_at'     =>
                $report->created_at,
            ];
        }
        /*
    |--------------------------------------------------------------------------
    | DETAIL PO
    |--------------------------------------------------------------------------
    */
        $detailPo = DB::table('detail_po')
            ->where(
                'id',
                $inspect->detail_po_id
            )
            ->first();
        $detailData  = [];
        $itemName    = '-';
        $articleCode = '-';
        $qty         = '-';
        $itemImage   = null;
        if ($detailPo && $detailPo->detail) {
            $detailData = json_decode(
                $detailPo->detail,
                true
            );
            /*
        |--------------------------------------------------------------------------
        | ITEM INFO
        |--------------------------------------------------------------------------
        */
            $itemName =
            $detailData['description'] ?? $detailData['nama'] ?? $detailData['item'] ?? '-';
            $articleCode =
            $detailData['article'] ?? $detailData['article_code'] ?? $detailData['article_no'] ?? $detailData['code'] ?? '-';
            $qty =
            $detailData['qty'] ?? '-';
            $itemImage =
            $detailData['photo'] ?? null;
        }
        /*
    |--------------------------------------------------------------------------
    | PFI SIZE
    |--------------------------------------------------------------------------
    */
        $pfi = [
            'w'  =>
            $detailData['w'] ?? '-',
            'd'  =>
            $detailData['d'] ?? '-',
            'h'  =>
            $detailData['h'] ?? '-',
            'sw' =>
            $detailData['sw'] ?? '-',
            'sd' =>
            $detailData['sd'] ?? '-',
            'sh' =>
            $detailData['sh'] ?? '-',
        ];
        /*
    |--------------------------------------------------------------------------
    | RETURN VIEW
    |--------------------------------------------------------------------------
    */
        return view(
            'pages.management.qc_report',
            [
                'inspect'     => $inspect,
                'grouped'     => $grouped,
                'photos'      => $photos,
                'detailData'  => $detailData,
                'itemName'    => $itemName,
                'articleCode' => $articleCode,
                'qty'         => $qty,
                'itemImage'   => $itemImage,
                'pfi'         => $pfi,
            ]
        );
    }
 
   
    // public function index(Request $request)
    // {


    //     $searchPo = $request->search_po;
    //     $selectedDate = $request->tanggal;




    //     $categories = [
    //         'rangka' => 'rangka',
    //         'anyam' => 'anyam',
    //         'unfinish' => 'unfinish',
    //         'final' => 'final',
    //         'decor' => 'decor',
    //         'accessories' => 'accessories',

    //         'packaging' => 'box',
    //         'box' => 'box',
    //     ];




    //     $inspectionCategoryMap = [
    //         4 => 'rangka',
    //         5 => 'anyam',
    //         6 => 'unfinish',
    //         7 => 'final',
    //     ];




    //     $dates = InspectSchedule::query()
    //         ->when($searchPo, function ($q) use ($searchPo) {

    //             $q->whereHas('po', function ($qq) use ($searchPo) {

    //                 $qq->where(
    //                     'order_no',
    //                     'like',
    //                     '%' . $searchPo . '%'
    //                 );
    //             });
    //         })
    //         ->select('tanggal_inspect')
    //         ->distinct()
    //         ->orderBy('tanggal_inspect')
    //         ->pluck('tanggal_inspect');



    //     $poQuery = Po::with([
    //         'detailPos',
    //         'spks',
    //     ]);




    //     $sort = strtolower($request->input('sort', 'desc'));

    //     if (!in_array($sort, ['asc', 'desc'])) {
    //         $sort = 'desc';
    //     }

    //     $poQuery->orderBy('release_date', $sort);




    //     if ($searchPo) {

    //         $poQuery->where(function ($q) use ($searchPo) {

    //             $q->where(
    //                 'order_no',
    //                 'like',
    //                 '%' . $searchPo . '%'
    //             )

    //                 ->orWhere(
    //                     'company_name',
    //                     'like',
    //                     '%' . $searchPo . '%'
    //                 );

    //         });

    //     }



    //     $brand = strtolower(
    //         trim($request->input('brand', 'all'))
    //     );

    //     if ($brand === 'nw') {

    //         $poQuery
    //             ->where('order_no', 'like', 'NW%')
    //             ->where('order_no', 'not like', 'NWS%');

    //     } elseif ($brand === 'nws') {

    //         $poQuery
    //             ->where('order_no', 'like', 'NWS%');

    //     }




    //     $pos = $poQuery->get();




    //     $detailPoIds = [];

    //     foreach ($pos as $po) {

    //         foreach ($po->detailPos as $detailPo) {

    //             $detailPoIds[] = $detailPo->id;
    //         }
    //     }




    //     $inspectQuery = InspectSchedule::query();




    //     if (!empty($detailPoIds)) {

    //         $inspectQuery->whereIn(
    //             'detail_po_id',
    //             $detailPoIds
    //         );

    //     } else {



    //         $inspectQuery->whereRaw('1 = 0');
    //     }



    //     if ($selectedDate) {

    //         $inspectQuery->whereDate(
    //             'tanggal_inspect',
    //             $selectedDate
    //         );
    //     }




    //     $allInspects = $inspectQuery
    //         ->get()
    //         ->groupBy(function ($item) {

    //             return (string) $item->detail_po_id;
    //         });




    //     $allInventories = ProductionTimeline::query()
    //         ->whereIn(
    //             'detail_po_id',
    //             $detailPoIds
    //         )
    //         ->get()
    //         ->groupBy('detail_po_id');




    //     $allSpks = Spk::query()
    //         ->get()
    //         ->keyBy('id');




    //     $inspectTotals = InspectSchedule::query()
    //         ->selectRaw('
    //         spk_id,
    //         detail_po_id,
    //         SUM(passed) as total_passed,
    //         SUM(rejected) as total_rejected
    //     ')
    //         ->whereNotNull('spk_id')
    //         ->groupBy(
    //             'spk_id',
    //             'detail_po_id'
    //         )
    //         ->get()
    //         ->keyBy(function ($item) {

    //             return
    //                 $item->spk_id
    //                 . '_'
    //                 . $item->detail_po_id;
    //         });




    //     $datas = [];




    //     foreach ($pos as $po) {

    //         $poId = $po->id;


    //         $datas[$poId] = [

    //             'po_number' =>
    //                 $po->order_no,

    //             'buyer_name' =>
    //                 $po->company_name
    //                 ?? $po->company_name
    //                 ?? $po->buyer
    //                 ?? '',

    //             'items' => [],
    //         ];




    //         foreach ($po->detailPos as $detailPo) {




    //             $detail =
    //                 $detailPo->detail ?? [];


    //             if (is_string($detail)) {

    //                 $detail = json_decode(
    //                     $detail,
    //                     true
    //                 );
    //             }



    //             $qty =
    //                 $detail['qty']
    //                 ?? 0;


    //             $itemName =
    //                 $detail['description']
    //                 ?? $detail['nama']
    //                 ?? $detail['item']
    //                 ?? '-';


    //             $image =
    //                 $detail['photo']
    //                 ?? null;




    //             $itemData = [

    //                 'item_name' =>
    //                     $itemName,

    //                 'item_image' =>
    //                     $image,

    //                 'qty' =>
    //                     $qty,

    //                 'spks' =>
    //                     [],
    //             ];




    //             foreach ($categories as $category) {

    //                 $itemData[
    //                     $category . '_pass'
    //                 ] = 0;

    //                 $itemData[
    //                     $category . '_reject'
    //                 ] = 0;

    //                 $itemData[
    //                     $category . '_in'
    //                 ] = 0;

    //                 $itemData[
    //                     $category . '_out'
    //                 ] = 0;
    //             }



    //             $inspects =
    //                 $allInspects[
    //                     (string) $detailPo->id
    //                 ] ?? collect();


    //             /*
    //             |--------------------------------------------------------------------------
    //             | HITUNG TOTAL SPK PER KATEGORI
    //             |--------------------------------------------------------------------------
    //             |
    //             | Rangka kaki kayu -> Accessories
    //             |
    //             */

    //             $spkCategoryQty = [];

    //             /*
    //             |--------------------------------------------------------------------------
    //             | KOMPONEN ITEM PER KATEGORI
    //             |--------------------------------------------------------------------------
    //             | Dipakai hanya untuk menghitung Qty IN produk jadi/monitoring.
    //             | Jika sebuah item mempunyai beberapa komponen, Qty IN efektif
    //             | mengikuti komponen yang paling sedikit (minimum balance),
    //             | sehingga kelebihan komponen tidak dihitung sebagai produk.
    //             |
    //             | Contoh:
    //             | Anyam Rangka    72
    //             | Anyam Dudukan   70
    //             | Anyam Sandaran  72
    //             | Maka Anyam IN = 70, bukan 214 dan bukan 71.33.
    //             |--------------------------------------------------------------------------
    //             */
    //             $componentGroups = [];

    //             foreach ($po->spks as $spk) {

    //                 $spkData = $spk->data;

    //                 if (is_string($spkData)) {
    //                     $spkData = json_decode(
    //                         $spkData,
    //                         true
    //                     );
    //                 }

    //                 $kategoriSpk = strtolower(
    //                     trim(
    //                         $spkData['kategori'] ?? ''
    //                     )
    //                 );

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | NORMALISASI KATEGORI SPK
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $spkPrefix = null;

    //                 /*
    //                 | KAKI KAYU = ACCESSORIES
    //                 | Harus dicek SEBELUM rangka.
    //                 */

    //                 if (
    //                     str_contains(
    //                         $kategoriSpk,
    //                         'kaki kayu'
    //                     )
    //                     ||
    //                     str_contains(
    //                         $kategoriSpk,
    //                         'kayu kaki'
    //                     )
    //                 ) {

    //                     $spkPrefix = 'accessories';

    //                 } elseif (
    //                     str_contains($kategoriSpk, 'aksesori')
    //                     || str_contains($kategoriSpk, 'aksesor')
    //                     || str_contains($kategoriSpk, 'accessor')
    //                 ) {

    //                     $spkPrefix = 'accessories';

    //                 } elseif (
    //                     str_contains(
    //                         $kategoriSpk,
    //                         'rangka'
    //                     )
    //                 ) {

    //                     $spkPrefix = 'rangka';

    //                 } elseif (
    //                     str_contains(
    //                         $kategoriSpk,
    //                         'anyam'
    //                     )
    //                 ) {

    //                     $spkPrefix = 'anyam';

    //                 } elseif (
    //                     str_contains(
    //                         $kategoriSpk,
    //                         'unfinish'
    //                     )
    //                 ) {

    //                     $spkPrefix = 'unfinish';

    //                 } elseif (
    //                     str_contains(
    //                         $kategoriSpk,
    //                         'final'
    //                     )
    //                 ) {

    //                     $spkPrefix = 'final';

    //                 } elseif (
    //                     str_contains(
    //                         $kategoriSpk,
    //                         'decor'
    //                     )
    //                     ||
    //                     str_contains(
    //                         $kategoriSpk,
    //                         'dekor'
    //                     )
    //                 ) {

    //                     $spkPrefix = 'decor';

    //                 } elseif (
    //                     str_contains(
    //                         $kategoriSpk,
    //                         'box'
    //                     )
    //                     ||
    //                     str_contains(
    //                         $kategoriSpk,
    //                         'packaging'
    //                     )
    //                 ) {

    //                     $spkPrefix = 'box';
    //                 }


    //                 if (!$spkPrefix) {
    //                     continue;
    //                 }


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | TOTAL QTY SPK PER DETAIL PO
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 foreach (
    //                     ($spkData['items'] ?? [])
    //                     as $spkItem
    //                 ) {

    //                     if (
    //                         ($spkItem['detail_po_id'] ?? null)
    //                         != $detailPo->id
    //                     ) {
    //                         continue;
    //                     }

    //                     $qtySpk = (float) (
    //                         $spkItem['qty'] ?? 0
    //                     );

    //                     /* Simpan definisi komponen dari custom_columns. */
    //                     $customColumns = $spkItem['custom_columns'] ?? [];

    //                     if (is_string($customColumns)) {
    //                         $decodedColumns = json_decode($customColumns, true);
    //                         $customColumns = is_array($decodedColumns)
    //                             ? $decodedColumns
    //                             : [];
    //                     }

    //                     if (is_array($customColumns) && !empty($customColumns)) {
    //                         foreach ($customColumns as $component) {
    //                             if (!is_array($component)) {
    //                                 continue;
    //                             }

    //                             $componentName = '';

    //                             foreach ([
    //                                 'nama',
    //                                 'name',
    //                                 'nama_material',
    //                                 'nama_bahan',
    //                                 'bahan',
    //                                 'triplek',
    //                                 'finishing',
    //                                 'komponen',
    //                                 'component',
    //                                 'description',
    //                                 'proses',
    //                                 'process',
    //                                 'nama_proses',
    //                                 'jenis_proses',
    //                             ] as $key) {
    //                                 $value = $component[$key] ?? null;

    //                                 if (is_string($value) && trim($value) !== '') {
    //                                     $clean = strtolower(trim($value));
    //                                     if (!in_array($clean, ['-', 'null', 'undefined', 'n/a', 'na'], true)) {
    //                                         $componentName = trim($value);
    //                                         break;
    //                                     }
    //                                 }
    //                             }

    //                             if ($componentName === '') {
    //                                 continue;
    //                             }

    //                             $componentQty =
    //                                 isset($component['pcs']) &&
    //                                 $component['pcs'] !== '' &&
    //                                 is_numeric($component['pcs'])
    //                                     ? (float) $component['pcs']
    //                                     : $qtySpk;

    //                             $componentGroups[$spkPrefix][] = [
    //                                 'name' => $componentName,
    //                                 'qty_spk' => $componentQty,
    //                             ];
    //                         }
    //                     }

    //                     $spkCategoryQty[
    //                         $spkPrefix
    //                     ] =
    //                         ($spkCategoryQty[$spkPrefix] ?? 0)
    //                         + $qtySpk;
    //                 }
    //             }

    //             /*
    //             |--------------------------------------------------------------------------
    //             | KOMPONEN ANYAM UNTUK TAMPILAN
    //             |--------------------------------------------------------------------------
    //             | Jangan mengubah anyam_in asli. Simpan daftar komponen unik agar
    //             | Blade dapat menampilkan indikator dan membagi nilai display.
    //             | Nama proses dinormalisasi hanya untuk deduplikasi; nama asli tetap
    //             | dikirim untuk ditampilkan.
    //             |--------------------------------------------------------------------------
    //             */
    //             $anyamComponents = [];

    //             foreach (($componentGroups['anyam'] ?? []) as $component) {
    //                 $name = trim((string) ($component['name'] ?? ''));

    //                 if ($name === '') {
    //                     continue;
    //                 }

    //                 $normalized = strtolower(
    //                     preg_replace('/\s+/', ' ', $name)
    //                 );

    //                 if ($normalized === '') {
    //                     continue;
    //                 }

    //                 $anyamComponents[$normalized] = $name;
    //             }

    //             $itemData['anyam_components'] = array_values($anyamComponents);
    //             $itemData['anyam_component_count'] = count($anyamComponents);


    //             /*
    //             |--------------------------------------------------------------------------
    //             | INSPECTION PASS / REJECT
    //             |--------------------------------------------------------------------------
    //             |
    //             | Pass dari inspection harus dialokasikan ke kategori SPK.
    //             |
    //             | Contoh:
    //             |
    //             | Rangka       = 50
    //             | Kaki Kayu    = 200 -> Accessories
    //             |
    //             | Inspection Pass = 250
    //             |
    //             | Maka:
    //             |
    //             | Rangka Pass      = 50
    //             | Accessories Pass = 200
    //             |
    //             |--------------------------------------------------------------------------
    //             */

    //             foreach ($inspects as $inspect) {

    //                 $kategoriId =
    //                     (int) $inspect->kategori_id;

    //                 $inspectionPrefix =
    //                     $inspectionCategoryMap[
    //                         $kategoriId
    //                     ] ?? null;

    //                 if (!$inspectionPrefix) {
    //                     continue;
    //                 }


    //                 $passed = (float) (
    //                     $inspect->passed ?? 0
    //                 );

    //                 $rejected = (float) (
    //                     $inspect->rejected ?? 0
    //                 );


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | KATEGORI YANG BOLEH MENERIMA PASS
    //                 |--------------------------------------------------------------------------
    //                 |
    //                 | Untuk inspection kategori Rangka (ID 4),
    //                 | kita distribusikan ke:
    //                 |
    //                 | 1. Rangka
    //                 | 2. Accessories (khusus kaki kayu)
    //                 |
    //                 */

    //                 $allocationCategories = [
    //                     $inspectionPrefix
    //                 ];


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | KHUSUS INSPECTION RANGKA
    //                 |--------------------------------------------------------------------------
    //                 |
    //                 | Kalau ada SPK kaki kayu pada item yang sama,
    //                 | kaki kayu dianggap Accessories.
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 if (
    //                     $inspectionPrefix === 'rangka'
    //                     &&
    //                     !empty(
    //                     $spkCategoryQty['accessories']
    //                 )
    //                 ) {

    //                     $allocationCategories[] =
    //                         'accessories';
    //                 }


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | ALOKASI PASS
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $remainingPass =
    //                     $passed;

    //                 foreach (
    //                     $allocationCategories
    //                     as $allocationCategory
    //                 ) {

    //                     if (
    //                         $remainingPass <= 0
    //                     ) {
    //                         break;
    //                     }


    //                     $capacity =
    //                         (float) (
    //                             $spkCategoryQty[
    //                                 $allocationCategory
    //                             ] ?? 0
    //                         );


    //                     if (
    //                         $capacity <= 0
    //                     ) {
    //                         continue;
    //                     }


    //                     $alreadyPassed =
    //                         (float) (
    //                             $itemData[
    //                                 $allocationCategory
    //                                 . '_pass'
    //                             ] ?? 0
    //                         );


    //                     $available =
    //                         max(
    //                             0,
    //                             $capacity
    //                             - $alreadyPassed
    //                         );


    //                     if (
    //                         $available <= 0
    //                     ) {
    //                         continue;
    //                     }


    //                     $allocated =
    //                         min(
    //                             $remainingPass,
    //                             $available
    //                         );


    //                     $itemData[
    //                         $allocationCategory
    //                         . '_pass'
    //                     ] += $allocated;


    //                     $remainingPass -=
    //                         $allocated;
    //                 }


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | FALLBACK
    //                 |--------------------------------------------------------------------------
    //                 |
    //                 | Kalau tidak ada SPK yang cocok,
    //                 | pertahankan perilaku lama.
    //                 |
    //                 */

    //                 if (
    //                     $remainingPass > 0
    //                     &&
    //                     isset(
    //                     $itemData[
    //                         $inspectionPrefix
    //                         . '_pass'
    //                     ]
    //                 )
    //                 ) {

    //                     $itemData[
    //                         $inspectionPrefix
    //                         . '_pass'
    //                     ] += $remainingPass;
    //                 }


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | REJECT
    //                 |--------------------------------------------------------------------------
    //                 |
    //                 | Untuk sementara tetap mengikuti kategori
    //                 | inspection asli agar fungsi lama tidak rusak.
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $itemData[
    //                     $inspectionPrefix
    //                     . '_reject'
    //                 ] += $rejected;
    //             }


    //             foreach ($po->spks as $spk) {



    //                 $spkData =
    //                     $spk->data;


    //                 if (is_string($spkData)) {

    //                     $spkData = json_decode(
    //                         $spkData,
    //                         true
    //                     );
    //                 }




    //                 $spkItems =
    //                     $spkData['items']
    //                     ?? [];




    //                 foreach ($spkItems as $spkItem) {




    //                     if (
    //                         ($spkItem['detail_po_id'] ?? null)
    //                         != $detailPo->id
    //                     ) {

    //                         continue;
    //                     }



    //                     $inspectTotalKey =
    //                         $spk->id
    //                         . '_'
    //                         . $detailPo->id;




    //                     $inspectTotal =
    //                         $inspectTotals[
    //                             $inspectTotalKey
    //                         ] ?? null;



    //                     $itemData['spks'][] = [

    //                         'id' =>
    //                             $spk->id,

    //                         'supplier' =>
    //                             $spkData['sup']
    //                             ?? '-',

    //                         'kategori' =>
    //                             $spkData['kategori']
    //                             ?? '-',

    //                         'jenis_asli' =>
    //                             $spkData['kategori']
    //                             ?? '-',

    //                         'no_spk' =>
    //                             $spkData['no_spk']
    //                             ?? '-',

    //                         'status' =>
    //                             $spk->status
    //                             ?? '-',

    //                         'harga' =>
    //                             $spkItem['harga']
    //                             ?? 0,

    //                         'qty' =>
    //                             $spkItem['qty']
    //                             ?? 0,

    //                         'detail_po_id' =>
    //                             $detailPo->id,

    //                         'inspect_schedule_id' =>
    //                             $inspectTotal
    //                             ? true
    //                             : false,

    //                         'passed' =>
    //                             $inspectTotal
    //                                 ->total_passed
    //                             ?? 0,

    //                         'rejected' =>
    //                             $inspectTotal
    //                                 ->total_rejected
    //                             ?? 0,
    //                     ];
    //                 }
    //             }




    //             $inventories =
    //                 $allInventories[
    //                     $detailPo->id
    //                 ] ?? collect();



    //             foreach ($inventories as $inventory) {




    //                 $spkInv =
    //                     $allSpks[
    //                         $inventory->spk_id
    //                     ] ?? null;


    //                 if (!$spkInv) {

    //                     continue;
    //                 }




    //                 $spkInvData =
    //                     $spkInv->data;


    //                 if (is_string($spkInvData)) {

    //                     $spkInvData =
    //                         json_decode(
    //                             $spkInvData,
    //                             true
    //                         );
    //                 }


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | CATEGORY INVENTORY
    //                 |--------------------------------------------------------------------------
    //                 |
    //                 | INVENTORY tetap berdasarkan kategori SPK.
    //                 |
    //                 | Jangan menggunakan kategori inspection di sini.
    //                 |
    //                 */

    //                 $kategoriInv =
    //                     strtolower(
    //                         trim(
    //                             $spkInvData['kategori']
    //                             ?? ''
    //                         )
    //                     );


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | NORMALIZE CATEGORY
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $prefix = null;

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | ACCESSORIES — KHUSUS KAKI KAYU
    //                 |--------------------------------------------------------------------------
    //                 |
    //                 | Contoh:
    //                 | RANGKA KAKI KAYU
    //                 | RANGKA KAYU KAKI
    //                 | KAKI KAYU
    //                 | RANGKA + KAKI KAYU
    //                 |
    //                 | Semua dianggap ACCESSORIES.
    //                 |
    //                 | HARUS dicek sebelum "rangka", karena
    //                 | "RANGKA KAKI KAYU" juga mengandung kata "rangka".
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 if (
    //                     str_contains($kategoriInv, 'kaki kayu')
    //                     ||
    //                     str_contains($kategoriInv, 'kayu kaki')
    //                 ) {

    //                     $prefix = 'accessories';

    //                 } elseif (
    //                     str_contains($kategoriInv, 'aksesori')
    //                     || str_contains($kategoriInv, 'aksesor')
    //                     || str_contains($kategoriInv, 'accessor')
    //                 ) {

    //                     $prefix = 'accessories';

    //                 } elseif (
    //                     str_contains(
    //                         $kategoriInv,
    //                         'rangka'
    //                     )
    //                 ) {

    //                     $prefix = 'rangka';

    //                 } elseif (
    //                     str_contains(
    //                         $kategoriInv,
    //                         'anyam'
    //                     )
    //                 ) {

    //                     $prefix = 'anyam';

    //                 } elseif (
    //                     str_contains(
    //                         $kategoriInv,
    //                         'unfinish'
    //                     )
    //                 ) {

    //                     $prefix = 'unfinish';

    //                 } elseif (
    //                     str_contains(
    //                         $kategoriInv,
    //                         'final'
    //                     )
    //                 ) {

    //                     $prefix = 'final';

    //                 } elseif (
    //                     str_contains(
    //                         $kategoriInv,
    //                         'decor'
    //                     )
    //                     ||
    //                     str_contains(
    //                         $kategoriInv,
    //                         'dekor'
    //                     )
    //                 ) {

    //                     $prefix = 'decor';

    //                 } elseif (
    //                     str_contains(
    //                         $kategoriInv,
    //                         'box'
    //                     )
    //                     ||
    //                     str_contains(
    //                         $kategoriInv,
    //                         'packaging'
    //                     )
    //                 ) {

    //                     $prefix = 'box';
    //                 }

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | UNKNOWN CATEGORY
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 if (!$prefix) {

    //                     continue;
    //                 }


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | INVENTORY TYPE
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $type =
    //                     strtolower(
    //                         trim(
    //                             $inventory->type
    //                             ?? ''
    //                         )
    //                     );


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | QTY
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $qtyInventory =
    //                     (float) (
    //                         $inventory->qty
    //                         ?? 0
    //                     );


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | IN
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 if ($type === 'in') {

    //                     $itemData[
    //                         $prefix . '_in'
    //                     ] +=
    //                         $qtyInventory;

    //                 }


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | OUT
    //                 |--------------------------------------------------------------------------
    //                 */ else {

    //                     $itemData[
    //                         $prefix . '_out'
    //                     ] +=
    //                         $qtyInventory;
    //                 }
    //             }


    //             /*
    //             |--------------------------------------------------------------------------
    //             | ELIMINASI KELEBIHAN KOMPONEN
    //             |--------------------------------------------------------------------------
    //             | Untuk item multi-komponen, satu produk hanya boleh dihitung
    //             | sebanyak komponen yang paling sedikit sudah masuk/balance.
    //             |
    //             | Contoh: 72 + 70 + 72 -> efektif 70.
    //             | Jika semua komponen balance 32 + 32 + 32 -> 32.
    //             |
    //             | Logic ini hanya dijalankan jika custom_columns benar-benar ada
    //             | dan minimal 2 komponen terdeteksi. Item tanpa komponen tetap
    //             | memakai logic inventory lama.
    //             |--------------------------------------------------------------------------
    //             */
    //             foreach ($componentGroups as $componentCategory => $components) {

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | ANYAM IN DITAMPILKAN DARI TOTAL INVENTORY
    //                 |--------------------------------------------------------------------------
    //                 | Untuk Anyam, nilai raw anyam_in dipertahankan. Pembagian berdasarkan
    //                 | jumlah komponen dilakukan di Blade agar 214 / 3 = 71 untuk display.
    //                 | Kategori lain tetap menggunakan eliminasi lama.
    //                 |--------------------------------------------------------------------------
    //                 */
    //                 if ($componentCategory === 'anyam') {
    //                     continue;
    //                 }

    //                 // Hindari duplicate component dari beberapa SPK yang sama.
    //                 $uniqueComponents = [];

    //                 foreach ($components as $component) {
    //                     $normalizedName = strtolower(trim(
    //                         preg_replace('/\s+/', ' ', $component['name'] ?? '')
    //                     ));

    //                     if ($normalizedName === '') {
    //                         continue;
    //                     }

    //                     $uniqueComponents[$normalizedName] = $component;
    //                 }

    //                 $components = array_values($uniqueComponents);

    //                 if (count($components) < 2) {
    //                     continue;
    //                 }

    //                 $componentIn = [];

    //                 foreach ($components as $index => $component) {
    //                     $target = strtolower(trim(
    //                         preg_replace('/[^a-z0-9]+/i', ' ', $component['name'] ?? '')
    //                     ));
    //                     $target = preg_replace('/\s+/', ' ', $target);

    //                     if ($target === '') {
    //                         continue;
    //                     }

    //                     $qtyInComponent = 0;

    //                     foreach ($inventories as $inventory) {
    //                         $type = strtolower(trim($inventory->type ?? ''));

    //                         if (!in_array($type, ['in', 'service_masuk'], true)) {
    //                             continue;
    //                         }

    //                         $qty = (float) ($inventory->qty ?? 0);
    //                         if ($qty <= 0) {
    //                             continue;
    //                         }

    //                         $remark = strtolower(trim(
    //                             preg_replace('/[^a-z0-9]+/i', ' ', (string) ($inventory->remark ?? ''))
    //                         ));
    //                         $remark = preg_replace('/\s+/', ' ', $remark);

    //                         // Remark kosong tetap dianggap sebagai item utama;
    //                         // hanya komponen pertama yang menerima fallback tersebut.
    //                         if ($remark === '') {
    //                             if ($index === 0) {
    //                                 $qtyInComponent += $qty;
    //                             }
    //                             continue;
    //                         }

    //                         if (
    //                             $remark === $target
    //                             || str_contains($remark, $target)
    //                             || str_contains($target, $remark)
    //                         ) {
    //                             $qtyInComponent += $qty;
    //                         }
    //                     }

    //                     // Jangan boleh melebihi Qty SPK komponen.
    //                     $qtySpkComponent = (float) ($component['qty_spk'] ?? 0);

    //                     if ($qtySpkComponent > 0) {
    //                         $qtyInComponent = min(
    //                             $qtyInComponent,
    //                             $qtySpkComponent
    //                         );
    //                     }

    //                     $componentIn[] = $qtyInComponent;
    //                 }

    //                 if (count($componentIn) >= 2) {
    //                     // Produk yang benar-benar bisa dihitung adalah komponen
    //                     // yang paling sedikit sudah tersedia. Kelebihan komponen
    //                     // dieliminasi agar tidak menghasilkan qty produk palsu.
    //                     $effectiveIn = min($componentIn);

    //                     $itemData[$componentCategory . '_in'] =
    //                         min(
    //                             (float) ($itemData[$componentCategory . '_in'] ?? 0)
    //                                 ?: $effectiveIn,
    //                             $effectiveIn
    //                         );
    //                 }
    //             }

    //             /*
    //             |--------------------------------------------------------------------------
    //             | PUSH ITEM
    //             |--------------------------------------------------------------------------
    //             */

    //             $datas[
    //                 $poId
    //             ]['items'][] =
    //                 $itemData;
    //         }
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | RETURN
    //     |--------------------------------------------------------------------------
    //     */

    //     return view(
    //         'pages.management.index',
    //         [
    //             'datas' =>
    //                 $datas,

    //             'searchPo' =>
    //                 $searchPo,

    //             'selectedDate' =>
    //                 $selectedDate,

    //             'dates' =>
    //                 $dates,
    //         ]
    //     );
    // }
 
    // fix on 31/08/26
    
        public function index(Request $request)
    {
        $datas = $this->buildMonitoringData($request);

        return view(
            'pages.management.index',
            compact('datas')
        );
    }

        private function buildMonitoringData(Request $request)
    {
        $start = microtime(true);

        /*
        |--------------------------------------------------------------------------
        | LOAD PO
        |--------------------------------------------------------------------------
        */
        $pos = Po::with([
            'detailPos',
            'spks',
        ])->get();


        /*
        |--------------------------------------------------------------------------
        | INSPECTION PER SPK
        |--------------------------------------------------------------------------
        |
        | Inspection yang mempunyai spk_id.
        |
        | Key:
        | spk_id_detail_po_id
        |
        */
        $inspectionTotals = InspectSchedule::query()
            ->selectRaw('
            spk_id,
            detail_po_id,
            SUM(passed) as total_passed,
            SUM(rejected) as total_rejected
        ')
            ->whereNotNull('spk_id')
            ->groupBy(
                'spk_id',
                'detail_po_id'
            )
            ->get()
            ->keyBy(function ($row) {
                return $row->spk_id
                    . '_'
                    . $row->detail_po_id;
            });


        /*
        |--------------------------------------------------------------------------
        | INSPECTION TANPA SPK
        |--------------------------------------------------------------------------
        |
        | Unfinish dan Final dilakukan QC tanpa SPK.
        |
        | kategori_id:
        | 6 = Unfinish
        | 7 = Final
        |
        */
        $inspectionWithoutSpk = InspectSchedule::query()
            ->selectRaw('
            detail_po_id,
            kategori_id,
            SUM(passed) as total_passed,
            SUM(rejected) as total_rejected
        ')
            ->whereNull('spk_id')
            ->whereIn('kategori_id', [6, 7])
            ->groupBy(
                'detail_po_id',
                'kategori_id'
            )
            ->get()
            ->groupBy('detail_po_id');


        /*
        |--------------------------------------------------------------------------
        | PRODUCTION TIMELINE
        |--------------------------------------------------------------------------
        |
        | Total IN per SPK.
        |
        */
        $inventoryTotals = ProductionTimeline::query()
            ->selectRaw('
            spk_id,
            detail_po_id,
            SUM(
                CASE
                    WHEN LOWER(TRIM(type)) = "in"
                    THEN COALESCE(qty, 0)
                    ELSE 0
                END
            ) as total_in
        ')
            ->whereNotNull('spk_id')
            ->groupBy(
                'spk_id',
                'detail_po_id'
            )
            ->get()
            ->keyBy(function ($row) {
                return $row->spk_id
                    . '_'
                    . $row->detail_po_id;
            });

        /*
        |--------------------------------------------------------------------------
        | PRODUCTION TIMELINE PER PROCESS
        |--------------------------------------------------------------------------
        |
        | KHUSUS untuk SPK composite seperti:
        |
        | RANGKA + ANYAM
        |
        | Jangan bergantung pada spk_id karena RANGKA dan ANYAM
        | dapat berasal dari SPK yang berbeda.
        |
        | process kosong -> fallback ke jenis.
        |
        */
        $inventoryByDetailComponent = ProductionTimeline::query()
            ->selectRaw('
            spk_id,
            detail_po_id,
            type,
            remark,
            SUM(
                CASE
                    WHEN LOWER(TRIM(type)) = "in"
                    THEN COALESCE(qty, 0)
                    ELSE 0
                END
            ) as total_in
        ')
            ->whereNotNull('spk_id')
            ->whereNotNull('detail_po_id')
            ->groupBy(
                'spk_id',
                'detail_po_id',
                'type',
                'remark'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | RESULT
        |--------------------------------------------------------------------------
        */
        $result = [];


        /*
        |--------------------------------------------------------------------------
        | HELPER LOCAL
        |--------------------------------------------------------------------------
        */
        $normalizeArray = function ($value) {

            if (is_string($value)) {

                $decoded = json_decode(
                    $value,
                    true
                );

                if (
                    json_last_error() === JSON_ERROR_NONE
                    && is_array($decoded)
                ) {
                    return $decoded;
                }

                return [];
            }

            return is_array($value)
                ? $value
                : [];
        };


        /*
        |--------------------------------------------------------------------------
        | LOOP PO
        |--------------------------------------------------------------------------
        */
        foreach ($pos as $po) {

            $poData = [
                'po_id' =>
                    $po->id,

                'po_number' =>
                    $po->order_no,

                'buyer' =>
                    $po->company_name,

                'items' =>
                    [],
            ];


            /*
            |--------------------------------------------------------------------------
            | LOOP DETAIL PO
            |--------------------------------------------------------------------------
            */
            foreach (
                $po->detailPos
                as $detailPo
            ) {

                /*
                |--------------------------------------------------------------------------
                | DETAIL PO
                |--------------------------------------------------------------------------
                */
                $detail =
                    $normalizeArray(
                        $detailPo->detail
                    );


                /*
                |--------------------------------------------------------------------------
                | ITEM NAME
                |--------------------------------------------------------------------------
                */
                $itemName =
                    $detail['description']
                    ?? $detail['nama']
                    ?? $detail['item']
                    ?? '-';


                /*
                |--------------------------------------------------------------------------
                | ITEM IMAGE
                |--------------------------------------------------------------------------
                */
                $itemImage =
                    $detail['item_image']
                    ?? $detail['image']
                    ?? $detail['gambar']
                    ?? $detail['photo']
                    ?? null;


                /*
                |--------------------------------------------------------------------------
                | UNFINISH
                |--------------------------------------------------------------------------
                */
                $unfinishPassed = 0;
                $unfinishRejected = 0;


                /*
                |--------------------------------------------------------------------------
                | FINAL
                |--------------------------------------------------------------------------
                */
                $finalPassed = 0;
                $finalRejected = 0;


                /*
                |--------------------------------------------------------------------------
                | INSPECTION TANPA SPK
                |--------------------------------------------------------------------------
                */
                $itemInspections =
                    $inspectionWithoutSpk[
                        $detailPo->id
                    ]
                    ?? collect();


                foreach (
                    $itemInspections
                    as $inspection
                ) {

                    $kategoriId =
                        (int) $inspection->kategori_id;


                    /*
                    |--------------------------------------------------------------------------
                    | UNFINISH
                    |--------------------------------------------------------------------------
                    */
                    if (
                        $kategoriId === 6
                    ) {

                        $unfinishPassed +=
                            (float) (
                                $inspection->total_passed
                                ?? 0
                            );

                        $unfinishRejected +=
                            (float) (
                                $inspection->total_rejected
                                ?? 0
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | FINAL
                    |--------------------------------------------------------------------------
                    */ elseif (
                        $kategoriId === 7
                    ) {

                        $finalPassed +=
                            (float) (
                                $inspection->total_passed
                                ?? 0
                            );

                        $finalRejected +=
                            (float) (
                                $inspection->total_rejected
                                ?? 0
                            );
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | ITEM DATA
                |--------------------------------------------------------------------------
                */
                $itemData = [

                    'detail_po_id' =>
                        $detailPo->id,

                    'item_name' =>
                        $itemName,

                    'item_image' =>
                        $itemImage,

                    'qty' =>
                        (float) (
                            $detail['qty']
                            ?? 0
                        ),

                    'spks' =>
                        [],

                    'unfinish' => [

                        'passed' =>
                            $unfinishPassed,

                        'rejected' =>
                            $unfinishRejected,
                    ],

                    'final' => [

                        'passed' =>
                            $finalPassed,

                        'rejected' =>
                            $finalRejected,
                    ],
                ];


                /*
                |--------------------------------------------------------------------------
                | LOOP SPK
                |--------------------------------------------------------------------------
                */
                foreach (
                    $po->spks
                    as $spk
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | SPK DATA
                    |--------------------------------------------------------------------------
                    */
                    $spkData =
                        $normalizeArray(
                            $spk->data
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | LOOP ITEM SPK
                    |--------------------------------------------------------------------------
                    */
                    foreach (
                        ($spkData['items'] ?? [])
                        as $spkItem
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | FILTER DETAIL PO
                        |--------------------------------------------------------------------------
                        */
                        if (
                            ($spkItem['detail_po_id'] ?? null)
                            != $detailPo->id
                        ) {
                            continue;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | CLASSIFICATION
                        |--------------------------------------------------------------------------
                        */
                        $classification =
                            ProductionMonitoringHelper::classifySpkCategory(
                                $spkData['kategori']
                                ?? ''
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | KEY MONITORING
                        |--------------------------------------------------------------------------
                        */
                        $key =
                            $spk->id
                            . '_'
                            . $detailPo->id;


                        /*
                        |--------------------------------------------------------------------------
                        | INSPECTION
                        |--------------------------------------------------------------------------
                        */
                        $inspection =
                            $inspectionTotals[$key]
                            ?? null;


                        /*
                        |--------------------------------------------------------------------------
                        | INVENTORY
                        |--------------------------------------------------------------------------
                        */
                        $inventory =
                            $inventoryTotals[$key]
                            ?? null;


                        /*
                        |--------------------------------------------------------------------------
                        | TOTAL IN ASLI
                        |--------------------------------------------------------------------------
                        */
                        $totalIn =
                            (float) (
                                $inventory->total_in
                                ?? 0
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | PASSED
                        |--------------------------------------------------------------------------
                        */
                        $totalPassed =
                            (float) (
                                $inspection->total_passed
                                ?? 0
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | REJECTED
                        |--------------------------------------------------------------------------
                        */
                        $totalRejected =
                            (float) (
                                $inspection->total_rejected
                                ?? 0
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | COMPONENT
                        |--------------------------------------------------------------------------
                        |
                        | PENTING:
                        |
                        | Sumber component BUKAN custom_headers.
                        |
                        | Sumber yang benar:
                        |
                        | $spkItem['custom_columns'][*]['proses']
                        |
                        | Contoh:
                        |
                        | ANYAM RANGKA
                        | ANYAM DUDUKAN
                        | ANYAM SANDARAN
                        |
                        */
                        $components = [];

                        $customColumns =
                            $normalizeArray(
                                $spkItem['custom_columns']
                                ?? []
                            );


                        foreach (
                            $customColumns
                            as $customColumn
                        ) {

                            if (
                                !is_array($customColumn)
                            ) {
                                continue;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | NAMA PROSES / COMPONENT
                            |--------------------------------------------------------------------------
                            */
                          $processName =
    trim(
        (string) (
            $customColumn['proses']
            ?? $customColumn['deskripsi']
            ?? $customColumn['name']
            ?? ''
        )
    );


                            /*
                            |--------------------------------------------------------------------------
                            | SKIP KOSONG
                            |--------------------------------------------------------------------------
                            */
                            if (
                                $processName === ''
                            ) {
                                continue;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | QTY COMPONENT
                            |--------------------------------------------------------------------------
                            */
                            $componentQty =
                                $customColumn['pcs']
                                ?? $customColumn['qty']
                                ?? $customColumn['quantity']
                                ?? $spkItem['qty']
                                ?? 0;


                            /*
                            |--------------------------------------------------------------------------
                            | COMPONENT
                            |--------------------------------------------------------------------------
                            */
                            $components[] = [

                                'name' =>
                                    $processName,

                                'qty_spk' =>
                                    (float) 
                                    $componentQty,

                                'qty_in' =>
                                    0,

                                'passed' =>
                                    0,

                                'rejected' =>
                                    0,
                            ];
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | REMOVE DUPLICATE COMPONENT
                        |--------------------------------------------------------------------------
                        */
                        $uniqueComponents = [];

                        foreach (
                            $components
                            as $component
                        ) {

                            $componentKey =
                                strtoupper(
                                    preg_replace(
                                        '/\s+/',
                                        ' ',
                                        trim(
                                            $component['name']
                                        )
                                    )
                                );


                            if (
                                isset(
                                $uniqueComponents[
                                    $componentKey
                                ]
                            )
                            ) {
                                continue;
                            }


                            $uniqueComponents[
                                $componentKey
                            ] =
                                $component;
                        }


                        $components =
                            array_values(
                                $uniqueComponents
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | JUMLAH COMPONENT
                        |--------------------------------------------------------------------------
                        */
                        $componentCount =
                            count($components);


                        /*
                        |--------------------------------------------------------------------------
                        | DETEKSI COMPOSITE RANGKA + ANYAM
                        |--------------------------------------------------------------------------
                        */
                        $kategoriSpk = strtoupper(
                            preg_replace(
                                '/\s+/',
                                ' ',
                                trim(
                                    (string) (
                                        $spkData['kategori'] ?? ''
                                    )
                                )
                            )
                        );

                        $hasRangka = false;
                        $hasAnyam = false;
                        $hasDudukan = false;
                        $hasSandaran = false;

                        foreach ($components as $componentCheck) {

                            $componentNameCheck = strtoupper(
                                trim(
                                    (string) (
                                        $componentCheck['name'] ?? ''
                                    )
                                )
                            );

                            if (str_contains($componentNameCheck, 'RANGKA')) {
                                $hasRangka = true;
                            }

                            if (str_contains($componentNameCheck, 'ANYAM')) {
                                $hasAnyam = true;
                            }

                            if (
                                str_contains($componentNameCheck, 'DUDUKAN')
                                || str_contains($componentNameCheck, 'DUDUK')
                            ) {
                                $hasDudukan = true;
                            }

                            if (
                                str_contains($componentNameCheck, 'SANDARAN')
                                || str_contains($componentNameCheck, 'SANDAR')
                            ) {
                                $hasSandaran = true;
                            }
                        }

                        $isAssemblingComposite =
                            $hasDudukan
                            && $hasSandaran;
                        $isRangkaAnyamComposite =
                            $kategoriSpk === 'RANGKA + ANYAM'
                            || ($hasRangka && $hasAnyam);
                        /*
                        |--------------------------------------------------------------------------
                        | DETEKSI PACKAGING / BOX COMPOSITE
                        |--------------------------------------------------------------------------
                        |
                        | Contoh custom_columns:
                        |
                        | BOX
                        | LAYER
                        | EMPTY
                        |
                        | IN masing-masing component diambil dari
                        | ProductionTimeline.remark:
                        |
                        | box   -> BOX
                        | layer -> LAYER
                        | empty -> EMPTY
                        |
                        | Jangan membagi total IN.
                        */
                        $kategoriSpkLower = strtolower($kategoriSpk);

                        $classificationCategory =
                            strtolower(
                                trim(
                                    (string) (
                                        $classification['category'] ?? ''
                                    )
                                )
                            );

                        $isPackagingComposite =
                            $classificationCategory === 'box'
                            || $classificationCategory === 'packaging'
                            || str_contains(
                                $kategoriSpkLower,
                                'box'
                            )
                            || str_contains(
                                $kategoriSpkLower,
                                'packaging'
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | QTY IN MONITORING
                        |--------------------------------------------------------------------------
                        |
                        | PRIORITAS:
                        |
                        | 1. Assembling
                        | 2. Rangka + Anyam
                        | 3. Packaging / Box
                        | 4. SPK biasa
                        |
                        */
                        if ($isAssemblingComposite) {

                            /*
                            |--------------------------------------------------------------------------
                            | ASSEMBLING: DUDUKAN + SANDARAN
                            |--------------------------------------------------------------------------
                            | 1 set utuh membutuhkan 1 dudukan + 1 sandaran.
                            | Qty assembling = MIN(qty dudukan, qty sandaran).
                            */
                            $qtyDudukan = 0;
                            $qtySandaran = 0;

                            foreach ($components as &$component) {

                                $componentName = strtoupper(
                                    trim(
                                        (string) (
                                            $component['name'] ?? ''
                                        )
                                    )
                                );

                                $processRows = $inventoryByDetailComponent
                                    ->filter(function ($row) use (
                                        $spk,
                                        $detailPo,
                                        $componentName
                                    ) {

                                        if (
                                            (int) ($row->spk_id ?? 0)
                                            !== (int) $spk->id
                                        ) {
                                            return false;
                                        }

                                        if (
                                            (int) ($row->detail_po_id ?? 0)
                                            !== (int) $detailPo->id
                                        ) {
                                            return false;
                                        }

                                        $remarkKey = strtoupper(
                                            trim(
                                                (string) (
                                                    $row->remark ?? ''
                                                )
                                            )
                                        );

                                        if ($remarkKey === '') {
                                            return false;
                                        }

                                        if (
                                            str_contains($componentName, 'DUDUKAN')
                                            || str_contains($componentName, 'DUDUK')
                                        ) {
                                            return
                                                str_contains($remarkKey, 'DUDUKAN')
                                                || str_contains($remarkKey, 'DUDUK');
                                        }

                                        if (
                                            str_contains($componentName, 'SANDARAN')
                                            || str_contains($componentName, 'SANDAR')
                                        ) {
                                            return
                                                str_contains($remarkKey, 'SANDARAN')
                                                || str_contains($remarkKey, 'SANDAR');
                                        }

                                        return false;
                                    });

                                $component['qty_in'] =
                                    (float) $processRows->sum('total_in');

                                $component['passed'] =
                                    $totalPassed;

                                $component['rejected'] =
                                    $totalRejected;

                                if (
                                    str_contains($componentName, 'DUDUKAN')
                                    || str_contains($componentName, 'DUDUK')
                                ) {
                                    $qtyDudukan +=
                                        (float) $component['qty_in'];
                                }

                                if (
                                    str_contains($componentName, 'SANDARAN')
                                    || str_contains($componentName, 'SANDAR')
                                ) {
                                    $qtySandaran +=
                                        (float) $component['qty_in'];
                                }
                            }

                            unset($component);

                            $qtyAssembling = min(
                                $qtyDudukan,
                                $qtySandaran
                            );

                            foreach ($components as &$component) {
                                $component['qty_assembling'] =
                                    $qtyAssembling;
                            }

                            unset($component);

                        } elseif ($isRangkaAnyamComposite) {

                            foreach (
                                $components
                                as &$component
                            ) {

                                $componentName = strtoupper(
                                    trim(
                                        (string) (
                                            $component['name'] ?? ''
                                        )
                                    )
                                );

                                if (str_contains($componentName, 'RANGKA')) {
                                    $targetProcess = 'RANGKA';
                                } elseif (str_contains($componentName, 'ANYAM')) {
                                    $targetProcess = 'ANYAM';
                                } else {
                                    $targetProcess = $componentName;
                                }

                                /*
                                | Cari semua Timeline pada detail PO ini.
                                | ANYAM/RANGKA menggunakan contains supaya variasi
                                | seperti ANYAM DUDUKAN / RANGKA ROTAN ikut masuk.
                                */
                                $processRows = $inventoryByDetailComponent
                                    ->filter(function ($row) use (
                                        $spk,
                                        $detailPo,
                                        $targetProcess
                                    ) {

                                        /*
                                        |--------------------------------------------------------------------------
                                        | SPK DAN DETAIL PO HARUS SAMA
                                        |--------------------------------------------------------------------------
                                        */
                                        if (
                                            (int) ($row->spk_id ?? 0)
                                            !== (int) $spk->id
                                        ) {
                                            return false;
                                        }

                                        if (
                                            (int) ($row->detail_po_id ?? 0)
                                            !== (int) $detailPo->id
                                        ) {
                                            return false;
                                        }

                                        /*
                                        |--------------------------------------------------------------------------
                                        | COMPONENT DIAMBIL DARI REMARK
                                        |--------------------------------------------------------------------------
                                        */
                                        $remarkKey = strtoupper(
                                            trim(
                                                (string) (
                                                    $row->remark ?? ''
                                                )
                                            )
                                        );

                                        if ($remarkKey === '') {
                                            return false;
                                        }

                                        if ($targetProcess === 'ANYAM') {
                                            return str_contains(
                                                $remarkKey,
                                                'ANYAM'
                                            );
                                        }

                                        if ($targetProcess === 'RANGKA') {
                                            return str_contains(
                                                $remarkKey,
                                                'RANGKA'
                                            );
                                        }

                                        return $remarkKey === strtoupper(
                                            trim($targetProcess)
                                        );
                                    });

                                $component['qty_in'] =
                                    (float) $processRows->sum('total_in');

                                $component['passed'] =
                                    $totalPassed;

                                $component['rejected'] =
                                    $totalRejected;
                            }

                            unset($component);

                        } elseif ($isPackagingComposite) {

                            /*
                            |--------------------------------------------------------------------------
                            | PACKAGING / BOX
                            |--------------------------------------------------------------------------
                            |
                            | Setiap component membaca IN dari remark.
                            |
                            | BOX   -> remark box
                            | LAYER -> remark layer
                            | EMPTY -> remark empty
                            |
                            | Matching dibuat case-insensitive dan toleran
                            | terhadap tambahan teks pada remark.
                            */
                            foreach (
                                $components
                                as &$component
                            ) {

                                $componentName = strtoupper(
                                    trim(
                                        (string) (
                                            $component['name'] ?? ''
                                        )
                                    )
                                );

                                $processRows =
                                    $inventoryByDetailComponent
                                        ->filter(function ($row) use (
                                            $spk,
                                            $detailPo,
                                            $componentName
                                        ) {

                                            if (
                                                (int) ($row->spk_id ?? 0)
                                                !== (int) $spk->id
                                            ) {
                                                return false;
                                            }

                                            if (
                                                (int) ($row->detail_po_id ?? 0)
                                                !== (int) $detailPo->id
                                            ) {
                                                return false;
                                            }

                                            /*
                                            |--------------------------------------------------------------------------
                                            | HANYA TRANSAKSI IN
                                            |--------------------------------------------------------------------------
                                            |
                                            | Query sudah menghitung total_in,
                                            | tetapi tetap aman apabila ada row
                                            | dengan type selain IN.
                                            */
                                            if (
                                                strtolower(
                                                    trim(
                                                        (string) (
                                                            $row->type ?? ''
                                                        )
                                                    )
                                                ) !== 'in'
                                            ) {
                                                return false;
                                            }

                                            $remarkKey = strtoupper(
                                                trim(
                                                    (string) (
                                                        $row->remark ?? ''
                                                    )
                                                )
                                            );

                                            if ($remarkKey === '') {
                                                return false;
                                            }

                                            /*
                                            |--------------------------------------------------------------------------
                                            | NORMALISASI REMARK
                                            |--------------------------------------------------------------------------
                                            |
                                            | BOX      cocok BOX
                                            | BOX 1    cocok BOX
                                            | layer    cocok LAYER
                                            | EMPTY    cocok EMPTY
                                            */
                                            $componentKey = preg_replace(
                                                '/[^A-Z0-9]+/',
                                                ' ',
                                                $componentName
                                            );

                                            $remarkNormalized = preg_replace(
                                                '/[^A-Z0-9]+/',
                                                ' ',
                                                $remarkKey
                                            );

                                            $componentKey =
                                                trim($componentKey);

                                            $remarkNormalized =
                                                trim($remarkNormalized);

                                            if (
                                                $componentKey === ''
                                                || $remarkNormalized === ''
                                            ) {
                                                return false;
                                            }

                                            /*
                                            | Component BOX tidak boleh mengambil
                                            | LAYER / EMPTY.
                                            */
                                            return
                                                $remarkNormalized === $componentKey
                                                || str_contains(
                                                    ' ' . $remarkNormalized . ' ',
                                                    ' ' . $componentKey . ' '
                                                )
                                                || str_contains(
                                                    $remarkNormalized,
                                                    $componentKey
                                                );
                                        });

                                $component['qty_in'] =
                                    (float) $processRows->sum(
                                        'total_in'
                                    );

                                $component['passed'] =
                                    $totalPassed;

                                $component['rejected'] =
                                    $totalRejected;
                            }

                            unset($component);

                            /*
                            |--------------------------------------------------------------------------
                            | QTY IN UTAMA PACKAGING
                            |--------------------------------------------------------------------------
                            |
                            | Kolom IN utama menggunakan component BOX.
                            |
                            | Contoh:
                            |
                            | BOX   = 30
                            | LAYER = 60
                            | EMPTY = 30
                            |
                            | Maka IN Packaging = 30.
                            */
                            $packagingQtyIn = 0;

                            foreach ($components as $component) {

                                $componentName = strtoupper(
                                    trim(
                                        (string) (
                                            $component['name'] ?? ''
                                        )
                                    )
                                );

                                if (
                                    $componentName === 'BOX'
                                    || str_contains(
                                        $componentName,
                                        'BOX'
                                    )
                                ) {
                                    $packagingQtyIn =
                                        (float) (
                                            $component['qty_in'] ?? 0
                                        );

                                    break;
                                }
                            }

                            /*
                            | Jika tidak ada component BOX, fallback ke
                            | component pertama agar tidak menghasilkan
                            | nilai kosong secara salah.
                            */
                            if (
                                $packagingQtyIn <= 0
                                && !empty($components)
                            ) {
                                $packagingQtyIn =
                                    (float) (
                                        $components[0]['qty_in'] ?? 0
                                    );
                            }

                            $componentQtyIn =
                                $packagingQtyIn;

                        } else {

                            /*
                            |--------------------------------------------------------------------------
                            | SPK BIASA - LOGIC LAMA
                            |--------------------------------------------------------------------------
                            */
                            if ($componentCount > 1) {

                                $componentQtyIn =
                                    floor(
                                        $totalIn
                                        /
                                        $componentCount
                                    );

                            } else {

                                $componentQtyIn =
                                    $totalIn;
                            }

                            foreach (
                                $components
                                as &$component
                            ) {

                                $component['qty_in'] =
                                    $componentQtyIn;

                                $component['passed'] =
                                    $totalPassed;

                                $component['rejected'] =
                                    $totalRejected;
                            }

                            unset($component);
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | SUB NAME
                        |--------------------------------------------------------------------------
                        |
                        | Sesuai kebutuhan:
                        |
                        | SUB NAME = supplier SPK.
                        |
                        */
                        $subName =
                            $spkData['sup']
                            ?? '-';


                        /*
                        |--------------------------------------------------------------------------
                        | PUSH SPK
                        |--------------------------------------------------------------------------
                        */
                        /*
                        |--------------------------------------------------------------------------
                        | PUSH SPK
                        |--------------------------------------------------------------------------
                        |
                        | Composite RANGKA + ANYAM dibuat menjadi record terpisah
                        | supaya Blade dapat menjumlahkan IN ke kolom yang benar.
                        */
                        if ($isAssemblingComposite) {

                            $qtyAssembling =
                                (float) (
                                    $components[0]['qty_assembling']
                                    ?? 0
                                );

                            $assemblingNames = collect($components)
                                ->pluck('name')
                                ->filter()
                                ->values()
                                ->implode(' + ');

                            $itemData['spks'][] = [

                                'spk_id' =>
                                    $spk->id,

                                'no_spk' =>
                                    $spkData['no_spk']
                                    ?? '-',

                                'sub_name' =>
                                    $subName,

                                'supplier' =>
                                    $spkData['sup']
                                    ?? '-',

                                'kategori' =>
                                    $spkData['kategori']
                                    ?? '-',

                                'kategori_monitoring' =>
                                    'assembling',

                                'classification' =>
                                    $classification['classification']
                                    ?? null,

                                'is_exception' =>
                                    $classification['is_exception']
                                    ?? false,

                                'exception_rule' =>
                                    $classification['exception_rule']
                                    ?? false,

                                'qty' =>
                                    $qtyAssembling,

                                'harga' =>
                                    (float) (
                                        $spkItem['harga']
                                        ?? 0
                                    ),

                                'total_in' =>
                                    $qtyAssembling,

                                'qty_in' =>
                                    $qtyAssembling,

                                'passed' =>
                                    (float) $totalPassed,

                                'rejected' =>
                                    (float) $totalRejected,

                                'component_count' =>
                                    1,

                                'component_name' =>
                                    $assemblingNames,

                                'components' =>
                                    $components,
                            ];

                        } elseif ($isRangkaAnyamComposite) {

                            foreach ($components as $component) {

                                $componentName = trim(
                                    (string) (
                                        $component['name'] ?? ''
                                    )
                                );

                                $componentNameUpper = strtoupper(
                                    $componentName
                                );

                                if (str_contains($componentNameUpper, 'RANGKA')) {
                                    $componentCategory = 'rangka';
                                } elseif (str_contains($componentNameUpper, 'ANYAM')) {
                                    $componentCategory = 'anyam';
                                } else {
                                    $componentCategory =
                                        $classification['category'] ?? null;
                                }

                                $itemData['spks'][] = [

                                    'spk_id' =>
                                        $spk->id,

                                    'no_spk' =>
                                        $spkData['no_spk']
                                        ?? '-',

                                    'sub_name' =>
                                        $subName,

                                    'supplier' =>
                                        $spkData['sup']
                                        ?? '-',

                                    'kategori' =>
                                        $spkData['kategori']
                                        ?? '-',

                                    'kategori_monitoring' =>
                                        $componentCategory,

                                    'classification' =>
                                        $classification['classification']
                                        ?? null,

                                    'is_exception' =>
                                        $classification['is_exception']
                                        ?? false,

                                    'exception_rule' =>
                                        $classification['exception_rule']
                                        ?? false,

                                    'qty' =>
                                        (float) (
                                            $spkItem['qty']
                                            ?? 0
                                        ),

                                    'harga' =>
                                        (float) (
                                            $spkItem['harga']
                                            ?? 0
                                        ),

                                    'total_in' =>
                                        (float) (
                                            $component['qty_in']
                                            ?? 0
                                        ),

                                    'qty_in' =>
                                        (float) (
                                            $component['qty_in']
                                            ?? 0
                                        ),

                                    'passed' =>
                                        (float) (
                                            $component['passed']
                                            ?? 0
                                        ),

                                    'rejected' =>
                                        (float) (
                                            $component['rejected']
                                            ?? 0
                                        ),

                                    'component_count' =>
                                        1,

                                    'component_name' =>
                                        $componentName,

                                    'components' => [
                                        $component,
                                    ],
                                ];
                            }

                        } elseif ($isPackagingComposite) {

                            /*
                            |--------------------------------------------------------------------------
                            | PUSH PACKAGING / BOX
                            |--------------------------------------------------------------------------
                            |
                            | Tetap satu record SPK.
                            | qty_in utama = IN component BOX.
                            | Detail component tetap dikirim ke Blade.
                            */
                            $itemData['spks'][] = [

                                'spk_id' =>
                                    $spk->id,

                                'no_spk' =>
                                    $spkData['no_spk']
                                    ?? '-',

                                'sub_name' =>
                                    $subName,

                                'supplier' =>
                                    $spkData['sup']
                                    ?? '-',

                                'kategori' =>
                                    $spkData['kategori']
                                    ?? '-',

                                'kategori_monitoring' =>
                                    'packaging',

                                'classification' =>
                                    $classification['classification']
                                    ?? null,

                                'is_exception' =>
                                    $classification['is_exception']
                                    ?? false,

                                'exception_rule' =>
                                    $classification['exception_rule']
                                    ?? null,

                                'qty' =>
                                    (float) (
                                        $spkItem['qty']
                                        ?? 0
                                    ),

                                'harga' =>
                                    (float) (
                                        $spkItem['harga']
                                        ?? 0
                                    ),

                                /*
                                | Total IN asli seluruh component.
                                | Dipertahankan untuk kebutuhan detail/debug.
                                */
                                'total_in' =>
                                    $totalIn,

                                /*
                                | IN utama = BOX.
                                */
                                'qty_in' =>
                                    (float) $componentQtyIn,

                                'passed' =>
                                    $totalPassed,

                                'rejected' =>
                                    $totalRejected,

                                'component_count' =>
                                    $componentCount,

                                'component_name' =>
                                    collect($components)
                                        ->pluck('name')
                                        ->filter()
                                        ->values()
                                        ->implode(' + '),

                                'components' =>
                                    $components,
                            ];

                        } else {

                            /*
                            |--------------------------------------------------------------------------
                            | SPK BIASA - TETAP SEPERTI SEBELUMNYA
                            |--------------------------------------------------------------------------
                            */
                            $itemData['spks'][] = [

                                'spk_id' =>
                                    $spk->id,

                                'no_spk' =>
                                    $spkData['no_spk']
                                    ?? '-',

                                'sub_name' =>
                                    $subName,

                                'supplier' =>
                                    $spkData['sup']
                                    ?? '-',

                                'kategori' =>
                                    $spkData['kategori']
                                    ?? '-',

                                'kategori_monitoring' =>
                                    $classification['category']
                                    ?? null,

                                'classification' =>
                                    $classification['classification']
                                    ?? null,

                                'is_exception' =>
                                    $classification['is_exception']
                                    ?? false,

                                'exception_rule' =>
                                    $classification['exception_rule']
                                    ?? null,

                                'qty' =>
                                    (float) (
                                        $spkItem['qty']
                                        ?? 0
                                    ),

                                'harga' =>
                                    (float) (
                                        $spkItem['harga']
                                        ?? 0
                                    ),

                                'total_in' =>
                                    $totalIn,

                                'qty_in' =>
                                    $componentQtyIn,

                                'passed' =>
                                    $totalPassed,

                                'rejected' =>
                                    $totalRejected,

                                'component_count' =>
                                    $componentCount,

                                'components' =>
                                    $components,
                            ];
                        }
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | PUSH ITEM
                |--------------------------------------------------------------------------
                */
                $poData['items'][] =
                    $itemData;
            }


            /*
            |--------------------------------------------------------------------------
            | PUSH PO
            |--------------------------------------------------------------------------
            */
            $result[] = $poData;
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER REQUEST
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) $request->input('search_po', '')
        );

        $brand = strtolower(
            trim(
                (string) $request->input('brand', 'all')
            )
        );

        $sort = strtolower(
            trim(
                (string) $request->input('sort', 'desc')
            )
        );


        /*
        |--------------------------------------------------------------------------
        | VALIDATE BRAND
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $brand,
                ['all', 'nw', 'nws', 'nwr', 'nwd'],
                true
            )
        ) {
            $brand = 'all';
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATE SORT
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $sort,
                ['asc', 'desc'],
                true
            )
        ) {
            $sort = 'desc';
        }


        /*
        |--------------------------------------------------------------------------
        | COLLECTION
        |--------------------------------------------------------------------------
        */

        $result = collect($result);


        /*
        |--------------------------------------------------------------------------
        | SEARCH NO PO / BUYER
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {

            $searchLower = strtolower($search);

            $result = $result->filter(
                function ($po) use ($searchLower) {

                    $poNumber = strtolower(
                        (string) (
                            $po['po_number'] ?? ''
                        )
                    );

                    $buyer = strtolower(
                        (string) (
                            $po['buyer'] ?? ''
                        )
                    );

                    return
                        str_contains(
                            $poNumber,
                            $searchLower
                        )
                        ||
                        str_contains(
                            $buyer,
                            $searchLower
                        );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER COMPANY
        |--------------------------------------------------------------------------
        |
        | NW  -> hanya NW
        | NWS -> hanya NWS
        | NWR -> hanya NWR
        | NWD -> hanya NWD
        |
        */

        if ($brand !== 'all') {

            $result = $result->filter(
                function ($po) use ($brand) {

                    $poNumber = strtoupper(
                        trim(
                            (string) (
                                $po['po_number'] ?? ''
                            )
                        )
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Ambil prefix sebelum spasi
                    |--------------------------------------------------------------------------
                    |
                    | NW 26 - 36  => NW
                    | NWS 26 - 01 => NWS
                    | NWR 26 - 01 => NWR
                    | NWD 26 - 01 => NWD
                    |
                    */

                    $prefix = strtoupper(
                        trim(
                            explode(' ', $poNumber)[0]
                        )
                    );

                    return $prefix === strtoupper($brand);
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SORT PO
        |--------------------------------------------------------------------------
        */

        $result = $result->sortBy(
            function ($po) {

                $poNumber =
                    (string) (
                        $po['po_number'] ?? ''
                    );

                preg_match(
                    '/(\d+)\s*$/',
                    $poNumber,
                    $matches
                );

                return isset($matches[1])
                    ? (int) $matches[1]
                    : PHP_INT_MAX;
            },
            SORT_NUMERIC,
            $sort === 'desc'
        );


        /*
        |--------------------------------------------------------------------------
        | RESET INDEX
        |--------------------------------------------------------------------------
        */

        $result = $result
            ->values()
            ->toArray();
        Log::info('PRODUCTION MONITORING TIME', [
            'time' => round(
                microtime(true) - $start,
                3
            ),

            'search' =>
                $request->input('search_po'),

            'brand' =>
                $request->input('brand'),

            'sort' =>
                $request->input('sort'),

            'total_po' =>
                count($result),
        ]);
        return $result;
    }

    public function data(Request $request)
    {
        $datas = $this->buildMonitoringData($request);

        return response()->json(
            $datas,
            200,
            [],
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE
        );
    }



    // pew
    private function getMonitoringCategory($jenis)
    {
        $jenis = strtoupper(trim($jenis));

        $rangka = [
            'RANGKA',
            'RANGKA BESI',
            'RANGKA KAYU',
            'RANGKA ROTAN',
            'RANGKA ALUMUNIUN',
            'RANGKA TRIPLEK',
            // 'PLAT BESI',
        ];

        $anyam = [
            'ANYAM',
            'ANYAM SINTETIS',
            'ANYAM KARAKTER',
        ];

        $unfinish = [
            'RANGKA + ANYAM',
            'ANYAM + DEKOR',
            'BASKET JOGJA',
            'BASKE JOGJA',
            'BASKET LOMBOKAN',
            'BASKET TASIK',
        ];

        if (in_array($jenis, $rangka)) {
            return 'rangka';
        }

        if (in_array($jenis, $anyam)) {
            return 'anyam';
        }

        if (in_array($jenis, $unfinish)) {
            return 'unfinish';
        }

        return strtolower($jenis);
    }

   public function inventor()
    {
        $processes = [
            'rangka' => 'Rangka',
            'anyam' => 'Anyam',
            'unfinish' => 'Unfinish',
            'accessories' => 'Accessories',
            'decor' => 'Decor',
            'ikat' => 'Ikat',
            'final' => 'Final',
            'packaging' => 'Packaging',
        ];
        $signatures = SignatureSpk::with([
            'madeBy',
            'checkedBy',
            'checkedBy2',
            'approvedBy',
        ])
            ->get()
            ->keyBy('spk_id');
        $spks = \App\Models\Spk::latest()
            ->get()
            ->map(function ($spk) use ($signatures) {
                $data = $spk->data;
                if (is_string($data)) {
                    $data = json_decode($data, true);
                }
                $signature = $signatures->get($spk->id);

                $deadlinePercent = 0;
                $deadlineColor = 'secondary';
                $deadlineText = 'No Deadline';

                $tglTerima = $this->parseDate(
                    $data['tgl_terima'] ?? null
                );

                $tglSelesai = $this->parseDate(
                    $data['tgl_selesai'] ?? null
                );
                Log::info([
    'raw_tgl_terima' => $data['tgl_terima'] ?? null,
    'raw_tgl_selesai' => $data['tgl_selesai'] ?? null,
]);

                if ($tglTerima && $tglSelesai) {

                    $today = now();

                    $totalHari = max(
                        $tglTerima->diffInDays($tglSelesai),
                        1
                    );

                    $hariBerjalan = max(
                        $tglTerima->diffInDays(
                            $today,
                            false
                        ),
                        0
                    );

                    $deadlinePercent = min(
                        round(
                            ($hariBerjalan / $totalHari) * 100
                        ),
                        100
                    );

                    $sisaHari = (int) $today->diffInDays(
                        $tglSelesai,
                        false
                    );

                    if ($sisaHari < 0) {

                        $deadlineColor = 'danger';
                        $deadlineText =
                            'Overdue '.
                            abs($sisaHari).
                            ' Hari';

                        $deadlinePercent = 100;

                    } elseif ($sisaHari <= 3) {

                        $deadlineColor = 'danger';
                        $deadlineText =
                            'Critical ('.
                            $sisaHari.
                            ' hari)';

                    } elseif ($sisaHari <= 7) {

                        $deadlineColor = 'warning';
                        $deadlineText =
                            'Warning ('.
                            $sisaHari.
                            ' hari)';

                    } elseif ($sisaHari <= 14) {

                        $deadlineColor = 'info';
                        $deadlineText =
                            'Normal ('.
                            $sisaHari.
                            ' hari)';

                    } else {

                        $deadlineColor = 'success';
                        $deadlineText =
                            'Safe ('.
                            $sisaHari.
                            ' hari)';
                    }
                }

                $items = collect($data['items'] ?? [])
                    ->map(function ($item) {
                        return [
                            'nama' => $item['nama'] ?? '-',
                            'kode' => $item['kode'] ?? '-',
                            'qty' => $item['qty'] ?? 0,
                            'satuan' => $item['satuan'] ?? 'pcs',

                            'l' => $item['l'] ?? '-',
                            'p' => $item['p'] ?? '-',
                            't' => $item['t'] ?? '-',
                            'material' => $item['material'] ?? '-',
                            'images' => $item['images'] ?? [],
                            'detail_po_id' => $item['detail_po_id'] ?? null,
                        ];
                    })
                    ->values()
                    ->toArray();
                // payment blm selesai
                $totalSpk = collect($data['items'] ?? [])->sum(function ($item) {

                    $total = floatval($item['total'] ?? 0);

                    foreach ($item['custom_columns'] ?? [] as $col) {
                        $total += floatval($col['total'] ?? 0);
                    }

                    return $total;
                });

                $totalPayment = collect($data['payments'] ?? [])->sum(function ($pay) {
                    return floatval($pay['amount'] ?? 0);
                });

                $isFinished = $totalPayment >= $totalSpk;

                return [
                    'is_finished' => $isFinished,
                    'id' => $spk->id,
                    'no_spk' => $data['no_spk'] ?? '-',
                    'supplier' => $data['sup'] ?? '-',
                    'supplier_id' => $data['sup_id'] ?? null,
                    'kategori' => $data['kategori'] ?? '-',
                    'no_po' => $data['no_po'] ?? '-',
                    'status' => $spk->status ?? '-',
                    'tgl_terima' => $tglTerima
                         ? $tglTerima->format('d/m/y')
                         : '-',

                    'tgl_selesai' => $tglSelesai
                        ? $tglSelesai->format('d/m/y')
                        : '-',
                    'items' => $items,
                    'deadline_percent' => $deadlinePercent,
                    'deadline_color' => $deadlineColor,
                    'deadline_text' => $deadlineText,
                    // signature

                    'signature' => [
                        'made_at' => $signature?->made_at,
                        'checked_at' => $signature?->checked_at,
                        'checked_at_2' => $signature?->checked_at_2,
                        'approved_at' => $signature?->approved_at,

                        'made_by' => $signature?->madeBy?->name,
                        'checked_by' => $signature?->checkedBy?->name,
                        'checked_by_2' => $signature?->checkedBy2?->name,
                        'approved_by' => $signature?->approvedBy?->name,
                    ],

                ];

            })
            ->values()
            ->toArray();
        $spks = collect($spks)
            ->where('is_finished', false)
            ->values()
            ->toArray();

        // dd($spks);
        return view(
            'pages.management.inventor',
            compact(
                'spks',
                'processes'
            )
        );
    }

    // arsip
    public function inventorArsip()
    {
        $processes = [
            'rangka' => 'Rangka',
            'anyam' => 'Anyam',
            'unfinish' => 'Unfinish',
            'accessories' => 'Accessories',
            'decor' => 'Decor',
            'ikat' => 'Ikat',
            'final' => 'Final',
            'packaging' => 'Packaging',
        ];
        $signatures = SignatureSpk::with([
            'madeBy',
            'checkedBy',
            'checkedBy2',
            'approvedBy',
        ])
            ->get()
            ->keyBy('spk_id');
        $spks = \App\Models\Spk::latest()
            ->get()
            ->map(function ($spk) use ($signatures) {
                $data = $spk->data;
                if (is_string($data)) {
                    $data = json_decode($data, true);
                }
                $signature = $signatures->get($spk->id);

                $deadlinePercent = 0;
                $deadlineColor = 'secondary';
                $deadlineText = 'No Deadline';

                $tglTerima = $this->parseDate(
                    $data['tgl_terima'] ?? null
                );

                $tglSelesai = $this->parseDate(
                    $data['tgl_selesai'] ?? null
                );

                if ($tglTerima && $tglSelesai) {

                    $today = now();

                    $totalHari = max(
                        $tglTerima->diffInDays($tglSelesai),
                        1
                    );

                    $hariBerjalan = max(
                        $tglTerima->diffInDays(
                            $today,
                            false
                        ),
                        0
                    );

                    $deadlinePercent = min(
                        round(
                            ($hariBerjalan / $totalHari) * 100
                        ),
                        100
                    );

                    $sisaHari = (int) $today->diffInDays(
                        $tglSelesai,
                        false
                    );

                    if ($sisaHari < 0) {

                        $deadlineColor = 'danger';
                        $deadlineText =
                            'Overdue '.
                            abs($sisaHari).
                            ' Hari';

                        $deadlinePercent = 100;

                    } elseif ($sisaHari <= 3) {

                        $deadlineColor = 'danger';
                        $deadlineText =
                            'Critical ('.
                            $sisaHari.
                            ' hari)';

                    } elseif ($sisaHari <= 7) {

                        $deadlineColor = 'warning';
                        $deadlineText =
                            'Warning ('.
                            $sisaHari.
                            ' hari)';

                    } elseif ($sisaHari <= 14) {

                        $deadlineColor = 'info';
                        $deadlineText =
                            'Normal ('.
                            $sisaHari.
                            ' hari)';

                    } else {

                        $deadlineColor = 'success';
                        $deadlineText =
                            'Safe ('.
                            $sisaHari.
                            ' hari)';
                    }
                }

                $items = collect($data['items'] ?? [])
                    ->map(function ($item) {
                        return [
                            'nama' => $item['nama'] ?? '-',
                            'kode' => $item['kode'] ?? '-',
                            'qty' => $item['qty'] ?? 0,
                            'satuan' => $item['satuan'] ?? 'pcs',

                            'l' => $item['l'] ?? '-',
                            'p' => $item['p'] ?? '-',
                            't' => $item['t'] ?? '-',
                            'material' => $item['material'] ?? '-',
                            'images' => $item['images'] ?? [],
                            'detail_po_id' => $item['detail_po_id'] ?? null,
                        ];
                    })
                    ->values()
                    ->toArray();
                // payment blm selesai
                $totalSpk = collect($data['items'] ?? [])->sum(function ($item) {

                    $total = floatval($item['total'] ?? 0);

                    foreach ($item['custom_columns'] ?? [] as $col) {
                        $total += floatval($col['total'] ?? 0);
                    }

                    return $total;
                });

                $totalPayment = collect($data['payments'] ?? [])->sum(function ($pay) {
                    return floatval($pay['amount'] ?? 0);
                });

                $isFinished = $totalPayment >= $totalSpk;

                return [
                    'is_finished' => $isFinished,
                    'id' => $spk->id,
                    'no_spk' => $data['no_spk'] ?? '-',
                    'supplier' => $data['sup'] ?? '-',
                    'supplier_id' => $data['sup_id'] ?? null,
                    'kategori' => $data['kategori'] ?? '-',
                    'no_po' => $data['no_po'] ?? '-',
                    'status' => $spk->status ?? '-',
                    'tgl_terima' => $tglTerima
                         ? $tglTerima->format('d/m/y')
                         : '-',

                    'tgl_selesai' => $tglSelesai
                        ? $tglSelesai->format('d/m/y')
                        : '-',
                    'items' => $items,
                    'deadline_percent' => $deadlinePercent,
                    'deadline_color' => $deadlineColor,
                    'deadline_text' => $deadlineText,
                    // signature

                    'signature' => [
                        'made_at' => $signature?->made_at,
                        'checked_at' => $signature?->checked_at,
                        'checked_at_2' => $signature?->checked_at_2,
                        'approved_at' => $signature?->approved_at,

                        'made_by' => $signature?->madeBy?->name,
                        'checked_by' => $signature?->checkedBy?->name,
                        'checked_by_2' => $signature?->checkedBy2?->name,
                        'approved_by' => $signature?->approvedBy?->name,
                    ],

                ];

            })
            ->values()
            ->toArray();
        $spks = collect($spks)
            ->where('is_finished', true)
            ->values()
            ->toArray();

        // dd($spks);
        return view(
            'pages.management.inventor_arsip',
            compact(
                'spks',
                'processes'
            )
        );
    }
   public function inventorDetail($id)
    {
        $spk = Spk::findOrFail($id);

        $data = is_string($spk->data)
            ? json_decode($spk->data, true)
            : $spk->data;

        $itemMap = collect(
            $data['items'] ?? []
        )->keyBy('detail_po_id');

        /*

    |--------------------------------------------------------------------------
    | ITEMS + CUSTOM COLUMN TOTAL
    |--------------------------------------------------------------------------
    */
    // inspection result
    $detailPoIds = collect(
        $data['items'] ?? []
    )->pluck('detail_po_id')->filter();
    $kategoriId = Kategori::where(
        'kategori',
        $data['kategori']
    )->value('id');
    $inspectSummary = InspectSchedule::where(
            'spk_id',
            $spk->id
        )
        ->selectRaw("
            detail_po_id,
            SUM(passed) as passed,
            SUM(rejected) as rejected
        ")
        ->groupBy('detail_po_id')
        ->get()
        ->keyBy('detail_po_id');
$items = collect(
    $data['items'] ?? []
)->map(function ($item) use ($inspectSummary) {

    $inspect = $inspectSummary->get(
        $item['detail_po_id'] ?? null
    );

    $item['passed'] = (int) (
        $inspect->passed ?? 0
    );

    $item['rejected'] = (int) (
        $inspect->rejected ?? 0
    );

    return $item;

});

$items = collect(
    $data['items'] ?? []
)->map(function ($item) use ($inspectSummary) {

    $inspect = $inspectSummary->get(
        $item['detail_po_id'] ?? null
    );

    $item['passed'] = (int) (
        $inspect->passed ?? 0
    );

    $item['rejected'] = (int) (
        $inspect->rejected ?? 0
    );

    return $item;

});

/*
    |--------------------------------------------------------------------------
    | GRAND TOTAL SPK
    |--------------------------------------------------------------------------
    */
       $grandTotal = $items->sum(function ($item) {

        $total = (float) ($item['total'] ?? 0);

        $customTotal = collect(
            $item['custom_columns'] ?? []
        )->sum(function ($custom) {

            return (float) (
                $custom['total'] ?? 0
            );

        });

        return $total + $customTotal;
    });

        /*
    |--------------------------------------------------------------------------
    | SUPPLIER
    |--------------------------------------------------------------------------
    */
        $supplier = Supplier::where(
            'name',
            $data['sup'] ?? ''
        )->first();

        /*
    |--------------------------------------------------------------------------
    | TIMELINE
    |--------------------------------------------------------------------------
    */
        $timelines = ProductionTimeline::where(
            'spk_id',
            $spk->id
        )
            ->orderBy('id')
            ->get()
            ->map(function ($row) use ($itemMap) {

                return [
                    'id'           => $row->id,

                    'detail_po_id' => $row->detail_po_id,

                    'item_name'    =>
                    $itemMap[$row->detail_po_id]['nama'] ?? '-',

                    'item_code'    =>
                    $itemMap[$row->detail_po_id]['kode'] ?? '-',

                    'qty'          => $row->qty,

                    'type'         => $row->type,

                    'process'      => $row->process,

                    'next_process' => $row->next_process,

                    'remark'       => $row->remark ?? '-',

                    'date'         => \Carbon\Carbon::parse(
                        $row->date
                    )->format('Y-m-d'),

                    'time'         => \Carbon\Carbon::parse(
                        $row->date
                    )->format('H:i'),
                ];
            });

        /*
    |--------------------------------------------------------------------------
    | BAHAN BAKU
    |--------------------------------------------------------------------------
    */
        $bahanBaku = TransaksiStok::with('stok')
            ->where('spk_id', $spk->id)
            ->orderBy('tanggal')
            ->get()
            ->map(function ($row) {

                return [
                    'id'          => $row->id,

                    'tanggal'     => $row->tanggal,

                    'tipe'        => $row->tipe,

                    'qty'         => $row->qty,

                    'po'          => $row->po,

                    'keterangan'  => $row->keterangan,

                    'stok_id'     => $row->stok_id,

                    'kode_barang' =>
                    $row->stok->kode_barang ?? '-',

                    'nama_barang' =>
                    $row->stok->nama_barang ?? '-',

                    'satuan'      =>
                    $row->stok->satuan ?? '-',
                    'harga_vivi' => $row->harga_vivi ?? null,
                    'harga'       =>
                    $row->stok->harga ?? 0,
'sst'  =>
                    $row->stok->qty ?? 0,
                    'stok_akhir'  =>
                    $row->stok->stok_akhir ?? 0,
                ];
            });
//             dd(
//     collect($data['items'])->pluck('detail_po_id')
// );
// dd($items);
// dd(
//     $detailPoIds->toArray(),
//     InspectSchedule::where('spk_id', $spk->id)->get()->toArray()
// );
//   $financeApproved = false;

   $financeApproved = false;

$paymentRequestIds = PaymentRequest::where(
    'spk_id',
    $spk->id
)->pluck('id')->toArray();

if (!empty($paymentRequestIds)) {

    $draft = PaymentRequestSaved::all()
        ->first(function ($row) use ($paymentRequestIds) {

            return count(
                array_intersect(
                    $row->payment_request_ids ?? [],
                    $paymentRequestIds
                )
            ) > 0;

        });

    if ($draft) {

        $financeApproved =
            PaymentRequestApproval::where(
                'payment_request_saved_id',
                $draft->id
            )
            ->where('status', 'Approved')
            ->where(function ($q) {

                $q->where('user_id', 174)
                  ->orWhere('role', 'Finance');

            })
            ->exists();
    }


        }

        return response()->json([
            'can_edit_harga' => auth()->id() == 171,

            'grand_total' => $grandTotal,

            'bahan_baku'  => $bahanBaku,

            'kategori'    =>
            $data['kategori'] ?? '-',

            'status'      =>
            $spk->status ?? '-',

            'spk'         => $spk,

            'items'       => $items,

            'spk_no'      =>
            $data['no_spk'] ?? '-',

            'payments'    =>
            $data['payments'] ?? [],

            'supplier'    => [
                'id'   =>
                $supplier->id ?? null,

                'name' =>
                $supplier->name ?? '-',
            ],

            'timelines'   => $timelines,

    'payments' => collect(
        $data['payments'] ?? []
)->map(function ($payment) {
        $amount = (float)(
            $payment['amount'] ?? 0
        );

        $adjustment = (float)(
            $payment['adjustment'] ?? 0
        );

        return [

            'date' =>
                $payment['date'] ?? null,

            'note' =>
                $payment['note'] ?? '-',
  'finance_approved' =>
            $payment['finance_approved'] ?? false,
            'amount' =>
                $amount,
                    'is_request' =>
                $payment['is_request'] ?? null,
            'payment_id' =>
                $payment['payment_id'] ?? null,

            'note_tambahan' =>
                $payment['note_tambahan'] ?? null,

            'adjustment' =>
                $adjustment,

            'payment_request_amount' =>
                $adjustment > 0
                    ? $adjustment
                    : $amount,

            'remaining_amount' =>
                $adjustment > 0
                    ? ($amount - $adjustment)
                    : 0,

            'adjustment_by' =>
                $payment['adjustment_by'] ?? null,

            'adjustment_at' =>
                $payment['adjustment_at'] ?? null,
           'finance_approved' =>
            $payment['finance_approved'] ?? false,
            // vivi
        ];

    })->values(),

        ]);
    }
    // vivi update
    public function updateHargaVivi(Request $request)
    {
        abort_unless(auth()->id() == 171, 403);

        $transaksi = TransaksiStok::findOrFail(
            $request->id
        );

        $transaksi->update([
            'harga_vivi' => $request->harga
        ]);

        return response()->json([
            'success' => true
        ]);
    }
    public function inventorStore(Request $request)
    {
        /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */
        $request->validate([
            'spk_id'       => 'required',
            'detail_po_id' => 'required|array',
            'qty'          => 'required|array',
        ]);
        /*
    |--------------------------------------------------------------------------
    | GET SPK
    |--------------------------------------------------------------------------
    */
        $spk = Spk::findOrFail(
            $request->spk_id
        );
        $spkData = is_string($spk->data)
            ? json_decode($spk->data, true)
            : $spk->data;
        /*
    |--------------------------------------------------------------------------
    | GET PO ID
    |--------------------------------------------------------------------------
    */
        $poId =
        $spkData['po_id'] ?? $spk->po_id ?? null;
        /*
    |--------------------------------------------------------------------------
    | DELETE OLD
    |--------------------------------------------------------------------------
    */
        ProductionTimeline::where(
            'spk_id',
            $request->spk_id
        )->delete();
        /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */
        foreach ($request->detail_po_id as $i => $detailPoId) {
            /*
        |--------------------------------------------------------------------------
        | DATE TIME
        |--------------------------------------------------------------------------
        */
            $dateTime = now();
            if (
                ! empty($request->date[$i]) &&
                ! empty($request->time[$i])
            ) {
                $dateTime =
                $request->date[$i]
                . ' ' .
                $request->time[$i];
            }
            /*
        |--------------------------------------------------------------------------
        | CREATE
        |--------------------------------------------------------------------------
        */
            ProductionTimeline::create([
                'po_id'        =>
                $poId,
                'spk_id'       =>
                $request->spk_id,
                'detail_po_id' =>
                $detailPoId,
                'qty'          =>
                $request->qty[$i] ?? 0,
                'sup_id'       =>
                $request->sup_id[$i] ?? null,
                'process'      =>
                $request->process[$i] ?? null,
                'next_process' =>
                $request->next_process[$i] ?? null,
                'date'         =>
                $dateTime,
                'type'         =>
                $request->type[$i] ?? 'in',
                'remark'       =>
                $request->remark[$i] ?? null,
                'source_type'  =>
                'inventor',
            ]);
        }
        /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */
        return response()->json([
            'success' => true,
            'message' => 'Inventory berhasil disimpan',
        ]);
    }
    public function delete($id)
    {
        $timeline = ProductionTimeline::find($id);
        if (! $timeline) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }
        $timeline->delete();
        return response()->json([
            'message' => 'Data berhasil dihapus',
        ]);
    }
//     private function parseDate($date)
// {
//     if (empty($date) || $date == '-') {
//         return null;
//     }

//     try {
//         return \Carbon\Carbon::parse($date);
//     } catch (\Exception $e) {

//         try {
//             return \Carbon\Carbon::createFromFormat(
//                 'd/m/Y',
//                 $date
//             );
//         } catch (\Exception $e) {

//             try {
//                 return \Carbon\Carbon::createFromFormat(
//                     'd-M-Y',
//                     $date
//                 );
//             } catch (\Exception $e) {

//                 return null;
//             }
//         }
//     }
// }
   public function monitoringFinishing(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. MONITORING INVOICE
        |--------------------------------------------------------------------------
        */

        $monitoringInvoices = MonitoringInvoice::query()
            ->orderBy('tanggal_invoice')
            ->orderBy('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 2. INVOICE LAMA
        |--------------------------------------------------------------------------
        */

        $invLamas = InvLama::query()
            ->orderBy('tanggal_invoice')
            ->orderBy('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 3. BUAT MAP INVOICE LAMA
        |--------------------------------------------------------------------------
        */

        $invLamaMap = [];

        foreach ($invLamas as $invLama) {

            $key = $this->normalizeInvoice(
                $invLama->nomor_invoice
            );

            if ($key === '') {
                continue;
            }

            /*
             * Kalau ada duplikat, gunakan record terakhir.
             */
            $invLamaMap[$key] = $invLama;
        }


        /*
        |--------------------------------------------------------------------------
        | 4. BUAT MAP MONITORING INVOICE
        |--------------------------------------------------------------------------
        */

        $monitoringMap = [];

        foreach ($monitoringInvoices as $invoice) {

            $key = $this->normalizeInvoice(
                $invoice->nomor_invoice
            );

            if ($key === '') {
                continue;
            }

            $monitoringMap[$key] = $invoice;
        }


        /*
        |--------------------------------------------------------------------------
        | 5. GABUNG MONITORING INVOICE + INVOICE LAMA
        |--------------------------------------------------------------------------
        |
        | Jika invoice ada di monitoring:
        |     gunakan monitoring.
        |
        | Jika hanya ada di inv_lama:
        |     tetap masukkan.
        |
        */

        $allInvoices = collect();

        foreach ($monitoringInvoices as $invoice) {

            $key = $this->normalizeInvoice(
                $invoice->nomor_invoice
            );

            $allInvoices->push([
                'key' => $key,
                'model' => $invoice,
                'source' => 'monitoring_invoice',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Tambahkan invoice yang hanya ada di INV LAMA
        |--------------------------------------------------------------------------
        */

        foreach ($invLamas as $invLama) {

            $key = $this->normalizeInvoice(
                $invLama->nomor_invoice
            );

            if ($key === '') {
                continue;
            }

            /*
             * Kalau sudah ada di MonitoringInvoice,
             * jangan dibuat dua kali.
             */
            if (isset($monitoringMap[$key])) {
                continue;
            }

            $allInvoices->push([
                'key' => $key,
                'model' => $invLama,
                'source' => 'inv_lama',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 6. SORT SEMUA INVOICE BERDASARKAN TANGGAL
        |--------------------------------------------------------------------------
        */

        $allInvoices = $allInvoices
            ->sortBy(function ($item) {

                return $item['model']->tanggal_invoice
                    ? Carbon::parse(
                        $item['model']->tanggal_invoice
                    )->timestamp
                    : PHP_INT_MAX;
            })
            ->values();


        /*
        |--------------------------------------------------------------------------
        | 7. AMBIL SPK LAMA
        |--------------------------------------------------------------------------
        |
        | SPK lama digunakan untuk PO lama.
        |
        */

        $spkLamas = SpkLama::query()
            ->orderBy('tanggal_potong')
            ->orderBy('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 8. AMBIL SPK BARU
        |--------------------------------------------------------------------------
        */

        $spks = Spk::query()
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 9. LEDGER
        |--------------------------------------------------------------------------
        */

        $ledger = [];

        $groupNumber = 0;


        /*
        |--------------------------------------------------------------------------
        | RUNNING SALDO GLOBAL
        |--------------------------------------------------------------------------
        |
        | Saldo ini TIDAK di-reset ketika masuk invoice baru.
        |
        | Contoh:
        |
        | Invoice A       + 1.000.000
        | Pemotongan        - 600.000
        | Saldo               400.000
        |
        | Invoice B       + 2.000.000
        | Saldo             2.400.000
        |
        */

        $runningSaldo = 0;


        /*
        |--------------------------------------------------------------------------
        | 10. PROSES SETIAP INVOICE
        |--------------------------------------------------------------------------
        */

        foreach ($allInvoices as $invoiceItem) {

            $invoice = $invoiceItem['model'];

            $invoiceKey = $invoiceItem['key'];

            $invoiceSource = $invoiceItem['source'];


            /*
            |--------------------------------------------------------------------------
            | NOMOR INVOICE
            |--------------------------------------------------------------------------
            */

            $invoiceNumber = trim(
                $invoice->nomor_invoice ?? ''
            );

            if ($invoiceNumber === '') {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | DETAIL BAHAN
            |--------------------------------------------------------------------------
            */

            $detailBahan = $invoice->detail_bahan ?? [];

            if (!is_array($detailBahan)) {
                $detailBahan = [];
            }


            /*
            |--------------------------------------------------------------------------
            | GRAND TOTAL
            |--------------------------------------------------------------------------
            */

            $invoiceTotal = $this->calculateDetailTotal(
                $detailBahan
            );


            /*
            |--------------------------------------------------------------------------
            | TANGGAL INVOICE
            |--------------------------------------------------------------------------
            */

            $invoiceDate = $invoice->tanggal_invoice;


            /*
            |--------------------------------------------------------------------------
            | BARIS INVOICE
            |--------------------------------------------------------------------------
            */

            $rows = [];

            /*
  |--------------------------------------------------------------------------
  | TO SUB INVOICE
  |--------------------------------------------------------------------------
  |
  | Invoice sekarang mempunyai tujuan SUB:
  |
  | TOMO
  | DARTO
  |
  | Ini harus menjadi sumber kategori utama invoice.
  | Dengan begitu invoice tetap masuk tab masing-masing
  | walaupun belum mempunyai pemotongan SPK.
  |
  */

            $invoiceToSub = trim(
                (string) (
                    $invoice->to_sub
                    ?? ''
                )
            );


            /*
            |--------------------------------------------------------------------------
            | NORMALISASI TO SUB
            |--------------------------------------------------------------------------
            */

            $invoiceSubNormalized =
                $this->normalizeSupplier(
                    $invoiceToSub
                );


            /*
            |--------------------------------------------------------------------------
            | KATEGORI INVOICE
            |--------------------------------------------------------------------------
            |
            | Jika to_sub tersedia:
            |
            | TOMO  -> finishing
            | DARTO -> darto
            |
            | Jika kosong, nanti fallback ke kategori lama.
            |
            */

            $invoiceKategori =
                $invoiceSubNormalized
                ?: strtolower(
                    trim(
                        (string) (
                            $invoice->kategori
                            ?? $invoice->kategori_invoice
                            ?? ''
                        )
                    )
                );


            $rows[] = [

                'id' =>
                    'INV-' .
                    $invoiceSource .
                    '-' .
                    $invoice->id,

                'source_id' =>
                    $invoice->id,

                'source' =>
                    $invoiceSource,

                'type' =>
                    'invoice',

                'tanggal' =>
                    $invoiceDate,

                'description' =>
                    'Invoice',

                'sub' =>
                    $invoiceNumber,

                /*
                |--------------------------------------------------------------------------
                | TO SUB
                |--------------------------------------------------------------------------
                */
                'to_sub' =>
                    $invoiceToSub,

                /*
                |--------------------------------------------------------------------------
                | SUPPLIER
                |--------------------------------------------------------------------------
                */
                'supplier' =>
                    $invoiceSubNormalized
                    ?: '',

                /*
                |--------------------------------------------------------------------------
                | KATEGORI
                |--------------------------------------------------------------------------
                */
                'kategori' =>
                    $invoiceKategori,

                'debet' =>
                    $invoiceTotal,

                'kredit' =>
                    0,

                'saldo' =>
                    0,

                'invoice' =>
                    $invoiceNumber,

                'no_inv' =>
                    $invoiceNumber,

                'po' =>
                    null,

                'no_spk' =>
                    null,

                'note_tambahan' =>
                    null,

                'detail_bahan' =>
                    $detailBahan,

                'sort_date' =>
                    $invoiceDate,
            ];


            /*
            |--------------------------------------------------------------------------
            | 11. CARI PEMOTONGAN SPK LAMA
            |--------------------------------------------------------------------------
            |
            | Untuk SPK lama kita cari berdasarkan no_inv.
            |
            */

            foreach ($spkLamas as $spkLama) {

                $spkInvoiceKey = $this->normalizeInvoice(
                    $spkLama->no_inv
                );


                /*
                |--------------------------------------------------------------------------
                | Invoice harus sama
                |--------------------------------------------------------------------------
                */

                if (
                    $spkInvoiceKey === '' ||
                    $spkInvoiceKey !== $invoiceKey
                ) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | Supplier / sub
                |--------------------------------------------------------------------------
                */

                $supplierLabel = $spkLama->name_sub;


                /*
                |--------------------------------------------------------------------------
                | Hanya DARTO dan TOMO
                |--------------------------------------------------------------------------
                */

                $supplierNormalized = $this->normalizeSupplier(
                    $supplierLabel
                );


                /*
                |--------------------------------------------------------------------------
                | Kalau name_sub bukan TOMO / DARTO,
                | tetap boleh tampil kalau data lama Anda
                | memang menggunakan nama seperti PRODUKSI.
                |--------------------------------------------------------------------------
                */

                if (
                    $supplierNormalized
                ) {
                    $supplierLabel = $supplierNormalized;
                }


                /*
                |--------------------------------------------------------------------------
                | NOMINAL PEMOTONGAN
                |--------------------------------------------------------------------------
                */

                $amount = (float) (
                    $spkLama->pemotongan_bahan ?? 0
                );


                if ($amount <= 0) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | JANGAN KURANGI SALDO DI SINI
                |--------------------------------------------------------------------------
                |
                | Saldo akan dihitung ulang setelah semua
                | transaksi invoice selesai dikumpulkan dan di-sort.
                |
                */


                /*
                |--------------------------------------------------------------------------
                | TAMBAHKAN PEMOTONGAN
                |--------------------------------------------------------------------------
                */

                $rows[] = [

                    /*
                     * ID UNIK
                     */
                    'id' => 'LAMA-' . $spkLama->id,

                    /*
                     * ID ASLI SPK LAMA
                     */
                    'source_id' => $spkLama->id,

                    'spk_lama_id' => $spkLama->id,

                    'source' => 'spk_lama',

                    'type' => 'pemotongan',

                    'tanggal' => $spkLama->tanggal_potong,

                    'description' => 'Pemotongan bahan',

                    'sub' => $spkLama->no_spk
                        ?: $spkLama->po,

                    'supplier' => $supplierLabel,
                    'kategori' => strtolower(trim($supplierLabel)),
                    'debet' => 0,

                    'kredit' => $amount,

                    'saldo' => 0,

                    'invoice' => $invoiceNumber,

                    'no_inv' => $spkLama->no_inv,

                    'po' => $spkLama->po,

                    'no_spk' => $spkLama->no_spk,

                    'note_tambahan' => null,

                    'detail_bahan' => [],

                    'sort_date' => $spkLama->tanggal_potong,
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | 12. CARI SPK BARU
            |--------------------------------------------------------------------------
            */

            foreach ($spks as $spk) {

                $data = $spk->data;

                if (!is_array($data)) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | SUPPLIER
                |--------------------------------------------------------------------------
                */

                $supplier = $data['sup']
                    ?? $spk->supplier->name
                    ?? null;


                $supplierNormalized =
                    $this->normalizeSupplier($supplier);


                /*
                |--------------------------------------------------------------------------
                | HANYA TOMO / DARTO
                |--------------------------------------------------------------------------
                */

                if (!$supplierNormalized) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | PAYMENTS
                |--------------------------------------------------------------------------
                */

                $payments = $data['payments'] ?? [];

                if (!is_array($payments)) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | LOOP PAYMENT
                |--------------------------------------------------------------------------
                */

                foreach ($payments as $paymentIndex => $payment) {

                    if (!is_array($payment)) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | HANYA PAYMENT BAHAN
                    |--------------------------------------------------------------------------
                    */

                    $note = strtolower(
                        trim(
                            $payment['note'] ?? ''
                        )
                    );


                    if ($note !== 'bahan') {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | NOTE TAMBAHAN
                    |--------------------------------------------------------------------------
                    */

                    $noteTambahan =
                        trim(
                            $payment['note_tambahan']
                            ?? ''
                        );


                    if ($noteTambahan === '') {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | AMBIL NOMOR INVOICE DARI NOTE
                    |--------------------------------------------------------------------------
                    |
                    | Contoh:
                    |
                    | INVOICE LEGENDA KAJ2290626 (29/6/2026)
                    |
                    | INVC LEGENDA KAJ180526 (23/6/2026)
                    |
                    */

                    /*
|--------------------------------------------------------------------------
| COCOKKAN PAYMENT DENGAN INVOICE
|--------------------------------------------------------------------------
|
| Jangan menggunakan regex karena format invoice bisa bermacam-macam:
|
| KAJ0450826
| DUCATY 003230.08.26
| DUCATY-003230.08.26
| INVC LEGENDA KAJ180526
|
| Kita normalisasi keduanya:
|
| DUCATY 003230.08.26
|        ↓
| DUCATY0032300826
|
*/

$noteInvoiceNormalized =
    $this->normalizeInvoice(
        $noteTambahan
    );


/*
|--------------------------------------------------------------------------
| PAYMENT HARUS MENGANDUNG NOMOR INVOICE
|--------------------------------------------------------------------------
*/

if (
    $invoiceKey === '' ||
    $noteInvoiceNormalized === '' ||
    !str_contains(
        $noteInvoiceNormalized,
        $invoiceKey
    )
) {
    continue;
}


/*
|--------------------------------------------------------------------------
| NOMOR INVOICE PAYMENT
|--------------------------------------------------------------------------
|
| Untuk kebutuhan data row, gunakan invoice
| yang sedang diproses.
|
*/

$paymentInvoice = $invoiceKey;


                    /*
                    |--------------------------------------------------------------------------
                    | NOMINAL
                    |--------------------------------------------------------------------------
                    */

                    $amount = (float) (
                        $payment['amount'] ?? 0
                    );


                    if ($amount <= 0) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | TANGGAL
                    |--------------------------------------------------------------------------
                    */

                    $paymentDate = null;

                    if (!empty($payment['date'])) {

                        try {

                            $paymentDate =
                                Carbon::parse(
                                    $payment['date']
                                );

                        } catch (\Throwable $e) {

                            $paymentDate = $invoiceDate;
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | JANGAN KURANGI SALDO DI SINI
                    |--------------------------------------------------------------------------
                    |
                    | Saldo akan dihitung setelah semua row
                    | selesai di-sort.
                    |
                    */


                    /*
                    |--------------------------------------------------------------------------
                    | NOMOR SPK
                    |--------------------------------------------------------------------------
                    */

                    $noSpk = $data['no_spk']
                        ?? $spk->no_spk
                        ?? null;


                    /*
                    |--------------------------------------------------------------------------
                    | PO
                    |--------------------------------------------------------------------------
                    */

                    $noPo = $data['no_po']
                        ?? null;


                    /*
                    |--------------------------------------------------------------------------
                    | ROW PEMOTONGAN
                    |--------------------------------------------------------------------------
                    */

                    $rows[] = [

                        /*
                         * ID UNIK
                         */
                        'id' =>
                            'SPK-' .
                            $spk->id .
                            '-PAY-' .
                            $paymentIndex,

                        'source_id' => $spk->id,

                        'spk_id' => $spk->id,

                        'source' => 'spk',

                        'type' => 'pemotongan',

                        'tanggal' => $paymentDate,

                        'description' =>
                            'Pemotongan bahan',

                        'sub' => $noSpk,

                        'supplier' =>
                            $supplierNormalized,
                        'kategori' => strtolower(trim($supplierNormalized)),
                        'debet' => 0,

                        'kredit' => $amount,

                        'saldo' => 0,

                        'invoice' => $invoiceNumber,

                        'no_inv' => $paymentInvoice,

                        'po' => $noPo,

                        'no_spk' => $noSpk,

                        'note_tambahan' =>
                            $noteTambahan,

                        'detail_bahan' => [],

                        'sort_date' => $paymentDate,
                    ];
                }
            }


            /*
            |--------------------------------------------------------------------------
            | 13. SORT BARIS BERDASARKAN TANGGAL
            |--------------------------------------------------------------------------
            |
            | Invoice harus selalu menjadi baris pertama.
            |
            */

            $invoiceRow = $rows[0];

            $otherRows = array_slice(
                $rows,
                1
            );


            usort(
                $otherRows,
                function ($a, $b) {

                    $dateA = !empty($a['sort_date'])
                        ? Carbon::parse(
                            $a['sort_date']
                        )->timestamp
                        : PHP_INT_MAX;

                    $dateB = !empty($b['sort_date'])
                        ? Carbon::parse(
                            $b['sort_date']
                        )->timestamp
                        : PHP_INT_MAX;

                    return $dateA <=> $dateB;
                }
            );


            /*
            |--------------------------------------------------------------------------
            | 14. RUNNING SALDO
            |--------------------------------------------------------------------------
            |
            | INVOICE = DEBET
            | PEMOTONGAN = KREDIT
            |
            | Rumus:
            |
            | saldo = saldo sebelumnya + debet - kredit
            |
            */

            /*
            |--------------------------------------------------------------------------
            | INVOICE MENAMBAH SALDO GLOBAL
            |--------------------------------------------------------------------------
            */

            $runningSaldo += (float) $invoiceTotal;


            /*
            |--------------------------------------------------------------------------
            | SALDO INVOICE
            |--------------------------------------------------------------------------
            */

            $invoiceRow['saldo'] = $runningSaldo;

            $finalRows = [
                $invoiceRow
            ];


            /*
            |--------------------------------------------------------------------------
            | PROSES PEMOTONGAN
            |--------------------------------------------------------------------------
            */

            foreach ($otherRows as $row) {

                $debet = (float) (
                    $row['debet'] ?? 0
                );

                $kredit = (float) (
                    $row['kredit'] ?? 0
                );


                /*
                |--------------------------------------------------------------------------
                | RUNNING BALANCE
                |--------------------------------------------------------------------------
                */

                $runningSaldo += $debet;

                $runningSaldo -= $kredit;


                /*
                |--------------------------------------------------------------------------
                | SIMPAN SALDO ROW
                |--------------------------------------------------------------------------
                */

                $row['saldo'] = $runningSaldo;


                /*
                |--------------------------------------------------------------------------
                | TAMBAHKAN KE FINAL ROWS
                |--------------------------------------------------------------------------
                */

                $finalRows[] = $row;
            }


            /*
            |--------------------------------------------------------------------------
            | 15. GROUP
            |--------------------------------------------------------------------------
            */

            $groupNumber++;

            /*
|--------------------------------------------------------------------------
| KATEGORI INVOICE
|--------------------------------------------------------------------------
|
| Ambil kategori dari invoice jika field tersebut tersedia.
|
*/

            /*
|--------------------------------------------------------------------------
| KATEGORI INVOICE
|--------------------------------------------------------------------------
|
| PRIORITAS:
|
| 1. to_sub
| 2. kategori
| 3. kategori_invoice
| 4. field kategori lama lainnya
|
| Karena to_sub sengaja dibuat untuk menentukan
| invoice tersebut masuk ke tab TOMO / DARTO.
|
*/

            $kategoriInvoice = '';

            $invoiceToSub =
                trim(
                    (string) (
                        $invoice->to_sub
                        ?? ''
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | TO SUB ADALAH PRIORITAS UTAMA
            |--------------------------------------------------------------------------
            */

            if ($invoiceToSub !== '') {

                $normalizedToSub =
                    $this->normalizeSupplier(
                        $invoiceToSub
                    );

                if ($normalizedToSub) {

                    $kategoriInvoice =
                        strtolower(
                            $normalizedToSub
                        );

                } else {

                    $kategoriInvoice =
                        strtolower(
                            trim(
                                $invoiceToSub
                            )
                        );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | FALLBACK KATEGORI LAMA
            |--------------------------------------------------------------------------
            */

            if ($kategoriInvoice === '') {

                foreach ([
                    'kategori',
                    'kategori_invoice',
                    'jenis_invoice',
                    'tipe_invoice',
                    'category',
                    'type_invoice',
                ] as $field) {

                    if (
                        isset($invoice->{$field}) &&
                        trim(
                            (string) $invoice->{$field}
                        ) !== ''
                    ) {

                        $kategoriInvoice =
                            strtolower(
                                trim(
                                    (string) $invoice->{$field}
                                )
                            );

                        break;
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | GROUP
            |--------------------------------------------------------------------------
            */

            $ledger[] = [
                'group' => $groupNumber,

                'invoice' =>
                    $invoiceNumber,

                'tanggal' =>
                    $invoiceDate,

                'source' =>
                    $invoiceSource,

                /*
                |--------------------------------------------------------------------------
                | TO SUB
                |--------------------------------------------------------------------------
                */
                'to_sub' =>
                    $invoiceToSub,

                /*
                |--------------------------------------------------------------------------
                | KATEGORI
                |--------------------------------------------------------------------------
                */
                'kategori' =>
                    $kategoriInvoice,

                'rows' =>
                    $finalRows,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | 16. KIRIM KE VIEW
        |--------------------------------------------------------------------------
        */
        // dd([
        //     'allInvoices' => $allInvoices->count(),

        //     'ledger_group' => count($ledger),

        //     'ledger_invoices' => collect($ledger)
        //         ->map(function ($group) {
        //             return [
        //                 'group' => $group['group'],
        //                 'invoice' => $group['invoice'],
        //                 'source' => $group['source'],
        //                 'rows' => count($group['rows']),
        //             ];
        //         })
        //         ->values(),
        // ]);
        return view(
            'pages.finishing.monitoring',
            compact(
                'ledger'
            )
        );
    }
private function parseDate($date)
{
    if (blank($date) || $date == '-') {
        return null;
    }

    $date = trim($date);

    // 10/07/2026
    try {
        return Carbon::createFromFormat('d/m/Y', $date);
    } catch (\Exception $e) {
    }

    // 10-Jul-2026
    try {
        return Carbon::createFromFormat('d-M-Y', $date);
    } catch (\Exception $e) {
    }

    // 2026-07-10 atau format lain
    try {
        return Carbon::parse($date);
    } catch (\Exception $e) {
    }

    return null;
}
  public function barangJadi(Request $request)
    {
        $query = ProductionTimeline::with([
            'po',
            'spk',
            'detailPo',
        ]);

        // Filter tanggal
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $timelines = $query
            ->orderByDesc('date')
            ->get();

        return view('pages.admin.laporan_admin', [
            'timelines' => $timelines,
            'from' => $request->from,
            'to' => $request->to,
        ]);
    }
  // export 
    public function exportBarangJadi(Request $request)
    {
        // ==========================
        // Barang Jadi
        // ==========================
        $query = ProductionTimeline::with([
            'po',
            'spk',
            'detailPo',
        ]);

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $timelines = $query
            ->orderByDesc('date')
            ->get();

        // ==========================
        // QC PASS
        // ==========================
        $inspection = InspectSchedule::with([
            'kategori',
            'user',
            'spk',
            'detailPo',
            'po',
        ]);

        if ($request->filled('from')) {
            $inspection->whereDate('tanggal_inspect', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $inspection->whereDate('tanggal_inspect', '<=', $request->to);
        }

        $inspection = $inspection
            ->orderByDesc('tanggal_inspect')
            ->get();

        return Excel::download(
            new AdminReportExport(
                $timelines,
                $inspection,
                $request->from,
                $request->to
            ),
            'LAPORAN_BARANG_JADI_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
     public function barangJadiRekap(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Barang Masuk
        |--------------------------------------------------------------------------
        */

        $barangMasuk = ProductionTimeline::with([
            'spk',
            'detailPo'
        ])
            ->selectRaw("
        spk_id,
        detail_po_id,
        SUM(qty) as qty_in
    ")
            ->where('type', 'in')

            ->whereBetween('date', [
                $request->from,
                $request->to
            ])
            ->groupBy(
                'spk_id',
                'detail_po_id'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | QC PASS
        |--------------------------------------------------------------------------
        */

        $qcPass = InspectSchedule::with([
            'spk',
            'detailPo'
        ])
            ->selectRaw("
        spk_id,
        detail_po_id,
        SUM(passed) as qty_pass
    ")
            ->whereBetween('tanggal_inspect', [
                $request->from,
                $request->to
            ])
            ->groupBy(
                'spk_id',
                'detail_po_id'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Merge
        |--------------------------------------------------------------------------
        */

        $rows = [];

        foreach ($barangMasuk as $item) {

            $qc = $qcPass->first(function ($q) use ($item) {
                return $q->spk_id == $item->spk_id
                    && $q->detail_po_id == $item->detail_po_id;
            });

            $detail = optional($item->detailPo)->detail;

            if (is_string($detail)) {
                $detail = json_decode($detail, true);
            }

            $rows[] = [
                'description' => $detail['description'] ?? '-',
                'spk' => optional($item->spk)->data['no_spk'] ?? '-',
                'sub' => optional($item->spk)->data['sup'] ?? '-',
                'kategori' => optional($item->spk)->data['kategori'] ?? '-',
                'qty_in' => $item->qty_in,
                'qty_pass' => $qc->qty_pass ?? 0,
                'selisih' => $item->qty_in - ($qc->qty_pass ?? 0),
            ];
        }

        return response()->json($rows);
    }
      private function normalizeInvoice($value)
    {
        if (!$value) {
            return '';
        }

        return strtoupper(
            preg_replace(
                '/[^A-Z0-9]/',
                '',
                trim($value)
            )
        );
    }
     private function calculateDetailTotal($detailBahan)
    {
        $grandTotal = 0;

        if (!is_array($detailBahan)) {
            return 0;
        }

        foreach ($detailBahan as $item) {

            $total = $item['total'] ?? null;

            if ($total !== null && $total !== '') {

                if (is_string($total)) {

                    $total = str_replace(
                        ['Rp', 'rp', '.', ',', ' '],
                        ['', '', '', '', ''],
                        $total
                    );
                }

                $grandTotal += (float) $total;

                continue;
            }


            $qty = (float) (
                $item['qty'] ?? 0
            );

            $harga = (float) (
                $item['harga'] ?? 0
            );

            $grandTotal += $qty * $harga;
        }

        return $grandTotal;
    }
    private function normalizeSupplier($value)
    {
        $value = strtoupper(trim((string) $value));

        if (str_contains($value, 'TOMO')) {
            return 'TOMO';
        }

        if (str_contains($value, 'DARTO')) {
            return 'DARTO';
        }

        return null;
    }
     public function test()
    {
        /*
        |--------------------------------------------------------------------------
        | 1. PERIODE
        |--------------------------------------------------------------------------
        |
        | Hari ini sampai 7 hari ke belakang.
        |
        */

        $now = now();

        $startDate = $now->copy()
            ->subDays(7)
            ->startOfDay();

        $endDate = $now->copy()
            ->endOfDay();


        /*
        |--------------------------------------------------------------------------
        | 2. PRODUCTION TIMELINE
        |--------------------------------------------------------------------------
        |
        | ProductionTimeline menjadi sumber utama.
        |
        | Hanya:
        | - type = in
        | - tanggal 7 hari terakhir
        |
        */

        $timelines = ProductionTimeline::query()
            ->whereRaw(
                'LOWER(TRIM(type)) = ?',
                ['in']
            )
            ->whereBetween(
                DB::raw('DATE(date)'),
                [
                    $startDate->format('Y-m-d'),
                    $endDate->format('Y-m-d'),
                ]
            )
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 3. JIKA TIDAK ADA DATA
        |--------------------------------------------------------------------------
        */

        if ($timelines->isEmpty()) {
            return view('pages.spk.test', [
                'rows' => collect(),
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 4. AMBIL SPK
        |--------------------------------------------------------------------------
        */

        $spkIds = $timelines
            ->pluck('spk_id')
            ->filter()
            ->unique()
            ->values();

        $spks = Spk::query()
            ->with('po')
            ->whereIn('id', $spkIds)
            ->get()
            ->keyBy('id');


        /*
        |--------------------------------------------------------------------------
        | 5. DETAIL PO
        |--------------------------------------------------------------------------
        */

        $detailPoIds = $timelines
            ->pluck('detail_po_id')
            ->filter()
            ->unique()
            ->values();

        $detailPos = DetailPo::query()
            ->whereIn('id', $detailPoIds)
            ->get()
            ->keyBy('id');


        /*
        |--------------------------------------------------------------------------
        | 6. ALL IN
        |--------------------------------------------------------------------------
        |
        | Total seluruh pemasukan item di ProductionTimeline
        | sepanjang waktu.
        |
        | TIDAK dibatasi periode 7 hari.
        |
        | Kunci:
        | spk_id + detail_po_id
        |
        */

        $allIn = ProductionTimeline::query()
            ->whereRaw(
                'LOWER(TRIM(type)) = ?',
                ['in']
            )
            ->whereIn('spk_id', $spkIds)
            ->whereIn('detail_po_id', $detailPoIds)
            ->select(
                'spk_id',
                'detail_po_id',
                DB::raw(
                    'SUM(COALESCE(qty, 0)) AS total_in'
                )
            )
            ->groupBy(
                'spk_id',
                'detail_po_id'
            )
            ->get()
            ->keyBy(function ($item) {
                return $item->spk_id
                    . '-'
                    . $item->detail_po_id;
            });


        /*
        |--------------------------------------------------------------------------
        | 7. INSPECTION SCHEDULE
        |--------------------------------------------------------------------------
        |
        | PASS dan REJECT dicocokkan dengan:
        | spk_id + detail_po_id
        |
        */

        $inspections = InspectSchedule::query()
            ->whereIn('spk_id', $spkIds)
            ->whereIn('detail_po_id', $detailPoIds)
            ->select(
                'spk_id',
                'detail_po_id',
                DB::raw(
                    'SUM(COALESCE(passed, 0)) AS total_passed'
                ),
                DB::raw(
                    'SUM(COALESCE(rejected, 0)) AS total_rejected'
                )
            )
            ->groupBy(
                'spk_id',
                'detail_po_id'
            )
            ->get()
            ->keyBy(function ($item) {
                return $item->spk_id
                    . '-' .
                    $item->detail_po_id;
            });


        /*
        |--------------------------------------------------------------------------
        | 8. BENTUK ROW
        |--------------------------------------------------------------------------
        */

        $rows = collect();

        foreach ($timelines as $timeline) {

            $spk = $spks->get($timeline->spk_id);

            if (!$spk) {
                continue;
            }

            $detail = $detailPos->get($timeline->detail_po_id);

            if (!$detail) {
                continue;
            }

            $detailData = $detail->detail;

            if (!is_array($detailData)) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | DETAIL ITEM
            |--------------------------------------------------------------------------
            */

            $articleNr = trim(
                (string) (
                    $detailData['article_nr_']
                    ?? ''
                )
            );

            if ($articleNr === '') {
                continue;
            }

            $description = trim(
                (string) (
                    $detailData['description']
                    ?? '-'
                )
            );

            $sub = trim(
                (string) (
                    $detailData['sub_category']
                    ?? '-'
                )
            );

            $qty = (float) (
                $detailData['qty']
                ?? 0
            );


            /*
            |--------------------------------------------------------------------------
            | DATA SPK
            |--------------------------------------------------------------------------
            */

            $spkData = $spk->data ?? [];

            if (!is_array($spkData)) {
                $spkData = [];
            }

            $noSpk = trim(
                (string) (
                    $spkData['no_spk']
                    ?? '-'
                )
            );

            $supplier = trim(
                (string) (
                    $spkData['sup']
                    ?? '-'
                )
            );

            $kategori = trim(
                (string) (
                    $spkData['kategori']
                    ?? '-'
                )
            );


            /*
            |--------------------------------------------------------------------------
            | NO PFI
            |--------------------------------------------------------------------------
            */

            $noPfi = optional(
                $spk->po
            )->order_no ?? '-';


            /*
            |--------------------------------------------------------------------------
            | TANGGAL TIMELINE
            |--------------------------------------------------------------------------
            */

            try {
                $timelineDate = Carbon::parse(
                    $timeline->date
                );
            } catch (\Throwable $e) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | QTY IN
            |--------------------------------------------------------------------------
            */

            $inQty = (float) (
                $timeline->qty
                ?? 0
            );


            /*
            |--------------------------------------------------------------------------
            | INSPECTION
            |--------------------------------------------------------------------------
            */

            $inspectionKey =
                $timeline->spk_id
                . '-'
                . $timeline->detail_po_id;

            $inspection = $inspections->get(
                $inspectionKey
            );

            $passQty = $inspection
                ? (float) (
                    $inspection->total_passed
                    ?? 0
                )
                : 0;

            $rejectQty = $inspection
                ? (float) (
                    $inspection->total_rejected
                    ?? 0
                )
                : 0;


            /*
            |--------------------------------------------------------------------------
            | ALL IN
            |--------------------------------------------------------------------------
            |
            | Total pemasukan item tersebut di seluruh
            | ProductionTimeline, tanpa batas tanggal.
            |
            */

            $allInKey =
                $timeline->spk_id
                . '-'
                . $timeline->detail_po_id;

            $allInRecord = $allIn->get(
                $allInKey
            );

            $allInQty = $allInRecord
                ? (float) (
                    $allInRecord->total_in
                    ?? 0
                )
                : 0;


            /*
            |--------------------------------------------------------------------------
            | SALDO ITEM
            |--------------------------------------------------------------------------
            */

            $saldoItem = max(
                0,
                $qty - $passQty
            );


            /*
            |--------------------------------------------------------------------------
            | SALDO PAYMENT SPK
            |--------------------------------------------------------------------------
            |
            | Untuk setiap SPK:
            |
            | TOTAL SPK
            | - seluruh payment yang benar-benar mengurangi saldo
            |
            | Data lama bisa memiliki:
            | amount > 0
            | adjustment = 0
            | payment_request_amount = 0
            |
            | Jadi sumber nilai payment:
            |
            | adjustment jika > 0
            | ELSE amount
            |
            */

            $totalSpk = 0;

            $items = $spkData['items'] ?? [];

            if (is_array($items)) {
                foreach ($items as $spkItem) {
                    $totalSpk += (float) (
                        $spkItem['total']
                        ?? 0
                    );
                }
            }


            $totalPayment = 0;

            $payments = $spkData['payments'] ?? [];

            if (is_array($payments)) {

                foreach ($payments as $payment) {

                    $amount = (float) (
                        $payment['amount']
                        ?? 0
                    );

                    $adjustment = (float) (
                        $payment['adjustment']
                        ?? 0
                    );

                    $paymentValue =
                        $adjustment > 0
                            ? $adjustment
                            : $amount;

                    if ($paymentValue <= 0) {
                        continue;
                    }

                    $note = strtolower(
                        trim(
                            (string) (
                                $payment['note']
                                ?? ''
                            )
                        )
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | RETURN BAHAN
                    |--------------------------------------------------------------------------
                    |
                    | Return bahan mengembalikan saldo.
                    |
                    */

                    if ($note === 'return_bahan') {
                        $totalPayment -= $paymentValue;
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | UPAH
                    |--------------------------------------------------------------------------
                    |
                    | Payment upah tidak mengurangi saldo SPK.
                    |
                    */

                    if (
                        $note === 'upah'
                        ||
                        str_contains($note, 'upah')
                    ) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PAYMENT NORMAL
                    |--------------------------------------------------------------------------
                    */

                    $totalPayment += $paymentValue;
                }
            }


            $saldoPayment = max(
                0,
                $totalSpk - $totalPayment
            );


            /*
            |--------------------------------------------------------------------------
            | PUSH ROW
            |--------------------------------------------------------------------------
            */

            $rows->push([

                'tanggal' =>
                    $timelineDate,

                'article_nr' =>
                    $articleNr,

                'description' =>
                    $description,

                'no_pfi' =>
                    $noPfi,

                'no_spk' =>
                    $noSpk,

                'supplier' =>
                    $supplier,

                'kategori' =>
                    $kategori,

                'sub' =>
                    $sub,

                'qty' =>
                    $qty,

                'in' =>
                    $inQty,

                'all_in' =>
                    $allInQty,

                'pass' =>
                    $passQty,

                'rejected' =>
                    $rejectQty,

                'saldo' =>
                    $saldoItem,

                'saldo_payment' =>
                    $saldoPayment,

                'spk_id' =>
                    $timeline->spk_id,

                'detail_po_id' =>
                    $timeline->detail_po_id,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 9. GROUP
        |--------------------------------------------------------------------------
        |
        | Satu item dapat memiliki beberapa production timeline.
        |
        | Group:
        | article + spk + detail_po
        |
        */

        $rows = $rows
            ->groupBy(function ($row) {

                return implode('|', [
                    $row['article_nr'],
                    $row['spk_id'],
                    $row['detail_po_id'],
                ]);
            })
            ->map(function ($group) {

                $first = $group
                    ->sortBy('tanggal')
                    ->first();


                /*
                |--------------------------------------------------------------------------
                | TOTAL IN
                |--------------------------------------------------------------------------
                */

                $first['in'] =
                    $group->sum('in');


                /*
                |--------------------------------------------------------------------------
                | ALL IN
                |--------------------------------------------------------------------------
                |
                | Jangan dijumlahkan lagi karena setiap row timeline
                | sudah membawa total lifetime.
                |
                */

                $first['all_in'] =
                    $group->max('all_in');


                /*
                |--------------------------------------------------------------------------
                | PASS
                |--------------------------------------------------------------------------
                */

                $first['pass'] =
                    $group->max('pass');


                /*
                |--------------------------------------------------------------------------
                | REJECT
                |--------------------------------------------------------------------------
                */

                $first['rejected'] =
                    $group->max('rejected');


                /*
                |--------------------------------------------------------------------------
                | SALDO ITEM
                |--------------------------------------------------------------------------
                */

                $first['saldo'] = max(
                    0,
                    $first['qty']
                    - $first['pass']
                );


                /*
                |--------------------------------------------------------------------------
                | SALDO PAYMENT
                |--------------------------------------------------------------------------
                |
                | Sama untuk seluruh item dalam SPK.
                |
                */

                $first['saldo_payment'] =
                    $group->sortByDesc('tanggal')
                        ->first()['saldo_payment']
                    ?? 0;


                /*
                |--------------------------------------------------------------------------
                | TANGGAL
                |--------------------------------------------------------------------------
                */

                $first['tanggal'] =
                    $group
                        ->pluck('tanggal')
                        ->sort()
                        ->first();

                return $first;
            })
            ->values();


        /*
        |--------------------------------------------------------------------------
        | 10. SORT
        |--------------------------------------------------------------------------
        */

        $rows = $rows
            ->sortBy([
                ['tanggal', 'asc'],
                ['article_nr', 'asc'],
            ])
            ->values();


        /*
        |--------------------------------------------------------------------------
        | 11. RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view('pages.spk.test', [
            'rows' => $rows,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}