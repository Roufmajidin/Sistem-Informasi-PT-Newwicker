<?php

namespace App\Http\Controllers;

use App\Models\DetailPo;
use App\Models\ExportIpl;
use App\Models\ExportIplItem;
use App\Models\ExportIplPo;
use App\Models\Po;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ExportDocument;
use App\Models\ExportDocumentFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
class EdController extends Controller
{
    //
    public function index()
    {
        return view('pages.exports.index', [

            'mode' => 'create',

            'ipl' => null,

        ]);
    }

    public function searchPo(Request $request)
    {
        $keyword = $request->keyword;

        $rows = Po::with('detailPos')
            ->where('order_no', 'like', "%{$keyword}%")
            ->orWhere('company_name', 'like', "%{$keyword}%")
            ->limit(10)
            ->get();

        return response()->json($rows);
    }

    public function poItems($id)
    {
        $po = Po::with('detailPos')->findOrFail($id);

        $items = [];

        foreach ($po->detailPos as $detailPo) {

            $detail = $detailPo->detail;

            $items[] = [
                'id' => $detailPo->id,
                'article_nr' => $detail['article_nr_'] ?? '',
                'order_no' => $po->order_no, // tambah ini
                'po_id' => $po->id,

                'description' => $detail['description'] ?? '',
                'photo' => $detail['photo'] ?? '',
                'qty' => $detail['qty'] ?? 0,
                'cbm' => $detail['cbm'] ?? 0,
                'total_cbm' => $detail['total_cbm'] ?? 0,
                'pack_w' => $detail['pack_w'] ?? '',
                'pack_d' => $detail['pack_d'] ?? '',
                'pack_h' => $detail['pack_h'] ?? '',
                // 'value' => $detail['fob_jakarta_in_usd']
                //     ?? $detail['fob_jakarta_price_in_usd_pc']
                //     ?? 0,            ];
                'value' => $this->getPrice($detail),
            ];
        }

        return response()->json($items);
    }

    private function getPrice(array $detail)
    {
        // prioritas 1
        if (!empty($detail['final_fob_price'])) {
            return (float) $detail['final_fob_price'];
        }

        // prioritas 2
        if (!empty($detail['fob_jakarta_in_usd'])) {
            return (float) $detail['fob_jakarta_in_usd'];
        }

        // prioritas 3
        if (!empty($detail['fob_jakarta_price_in_usd_pc'])) {
            return (float) $detail['fob_jakarta_price_in_usd_pc'];
        }

        // prioritas 4
        if (!empty($detail['value_in_usd']) && !empty($detail['qty'])) {
            return round(
                $detail['value_in_usd'] / $detail['qty'],
                2
            );
        }

        return 0;
    }

    public function saveIpl(Request $request)
    {
        DB::beginTransaction();

        try {

            $shipment = $request->shipment ?? [];

            $ipl = ExportIpl::create([

                'invoice_no' => $request->invoice_no,

                'sales_order' => $request->sales_order,

                'buyer' => $request->buyer,

                'buyer_address' => $shipment['buyer_address'] ?? null,

                'customer_code' => $shipment['customer_code'] ?? null,

                'customer_po_no' => $shipment['customer_po_no'] ?? null,

                'container_type' => $shipment['container_type'] ?? null,

                'container_no' => $shipment['container_no'] ?? null,

                'seal_no' => $shipment['seal_no'] ?? null,

                'vessel_name' => $shipment['vessel_name'] ?? null,

                'port_loading' => $shipment['port_loading'] ?? null,

                'port_discharge' => $shipment['port_discharge'] ?? null,

                'commodity' => $shipment['commodity'] ?? null,

                'fumigation' => $shipment['fumigation'] ?? null,

                'etd' => $shipment['etd'] ?? null,

                'eta' => $shipment['eta'] ?? null,

                'created_by' => auth()->id(),

            ]);

            /*
            |--------------------------------------------------------------------------
            | Save PO
            |--------------------------------------------------------------------------
            */

            collect($request->items)
                ->unique('po_id')
                ->each(function ($item) use ($ipl) {

                    ExportIplPo::create([

                        'export_ipl_id' => $ipl->id,

                        'po_id' => $item['po_id'],

                        'po_no' => $item['po_no'],

                    ]);

                });

            /*
            |--------------------------------------------------------------------------
            | Save Items
            |--------------------------------------------------------------------------
            */

            foreach ($request->items as $item) {

                ExportIplItem::create([

                    'export_ipl_id' => $ipl->id,

                    'po_id' => $item['po_id'],

                    'detail_po_id' => $item['detail_po_id'],

                    'po_no' => $item['po_no'],

                    'hs_code' => $item['hs_code'],

                    'article_nr' => $item['article_nr'],

                    'description' => $item['description'],

                    'photo' => $item['photo'],

                    'box_dimension' => $item['box_dimension'],

                    'qty_pcs' => $item['qty_pcs'],

                    'qty_box' => $item['qty_box'],

                    'cbm' => $item['cbm'],

                    'total_cbm' => $item['total_cbm'],

                    'unit_price' => $item['unit_price'],

                    'total_price' => $item['total_price'],

                    'net_weight' => $item['net_weight'],

                    'gross_weight' => $item['gross_weight'],

                    'remark' => $item['remark'],

                ]);

            }

            DB::commit();

            return response()->json([

                'success' => true,

                'message' => 'IPL berhasil disimpan.',

                'id' => $ipl->id,

            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

            ], 500);

        }
    }

    public function ipl()
    {
        $datas = ExportIpl::withCount([
            'items',
            'pos',
        ])
            ->latest()
            ->paginate(20);

        return view(
            'pages.exports.ipl',
            compact('datas')
        );
    }

    public function edit($id)
    {
        $ipl = ExportIpl::with([

            'pos',

            'items',

        ])->findOrFail($id);
        // dd($ipl->items->toArray());

        return view('pages.exports.index', [

            'mode' => 'edit',

            'ipl' => $ipl,

        ]);
    }

    public function updateIpl(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $ipl = ExportIpl::findOrFail($id);

            $shipment = $request->shipment ?? [];

            /*
            |--------------------------------------------------------------------------
            | Header
            |--------------------------------------------------------------------------
            */

            $ipl->update([

                'invoice_no' => $request->invoice_no,

                'sales_order' => $request->sales_order,

                'buyer' => $request->buyer,

                'buyer_address' => $shipment['buyer_address'] ?? null,

                'customer_code' => $shipment['customer_code'] ?? null,

                'customer_po_no' => $shipment['customer_po_no'] ?? null,

                'container_type' => $shipment['container_type'] ?? null,

                'container_no' => $shipment['container_no'] ?? null,

                'seal_no' => $shipment['seal_no'] ?? null,

                'vessel_name' => $shipment['vessel_name'] ?? null,

                'port_loading' => $shipment['port_loading'] ?? null,

                'port_discharge' => $shipment['port_discharge'] ?? null,

                'commodity' => $shipment['commodity'] ?? null,

                'fumigation' => $shipment['fumigation'] ?? null,

                'etd' => $shipment['etd'] ?? null,

                'eta' => $shipment['eta'] ?? null,

            ]);

            /*
            |--------------------------------------------------------------------------
            | Delete Old
            |--------------------------------------------------------------------------
            */
            // dd($request->items);
            $ipl->pos()->delete();

            $ipl->items()->delete();

            /*
            |--------------------------------------------------------------------------
            | Save PO
            |--------------------------------------------------------------------------
            */

            collect($request->items)
                ->unique('po_id')
                ->each(function ($item) use ($ipl) {

                    if (empty($item['po_id'])) {
                        return;
                    }

                    $ipl->pos()->create([

                        'po_id' => $item['po_id'] ?? null,

                        'po_no' => $item['po_no'],

                    ]);

                });

            /*
            |--------------------------------------------------------------------------
            | Save Items
            |--------------------------------------------------------------------------
            */

            foreach ($request->items as $item) {

                if (!isset($item['po_id'])) {
                    continue;
                }

                $ipl->items()->create([
                    'po_id' => $item['po_id'],
                    'detail_po_id' => $item['detail_po_id'] ?? null,
                    'po_no' => $item['po_no'] ?? null,
                    'hs_code' => $item['hs_code'] ?? null,
                    'article_nr' => $item['article_nr'] ?? null,
                    'description' => $item['description'] ?? null,
                    'photo' => $item['photo'] ?? null,
                    'box_dimension' => $item['box_dimension'] ?? null,
                    'qty_pcs' => $item['qty_pcs'] ?? 0,
                    'qty_box' => $item['qty_box'] ?? 0,
                    'cbm' => $item['cbm'] ?? 0,
                    'total_cbm' => $item['total_cbm'] ?? 0,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'total_price' => $item['total_price'] ?? 0,
                    'net_weight' => $item['net_weight'] ?? 0,
                    'gross_weight' => $item['gross_weight'] ?? 0,
                    'remark' => $item['remark'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([

                'success' => true,

                'message' => 'IPL berhasil diperbarui.',

            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

            ], 500);

        }
    }

    public function deleteItem($id)
    {
        try {

            $item = ExportIplItem::findOrFail($id);

            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil dihapus.',
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);

        }
    }

    public function check(Request $request, $detailPoId)
    {
        $detailPo = DetailPo::findOrFail($detailPoId);

        $detail = is_array($detailPo->detail)
            ? $detailPo->detail
            : json_decode($detailPo->detail, true);

        $qtyPo = (float) ($detail['qty'] ?? 0);

        $usedQty = ExportIplItem::where('detail_po_id', $detailPoId)
            ->when($request->item_id, function ($q) use ($request) {
                $q->where('id', '!=', $request->item_id);
            })
            ->sum('qty_pcs');

        return response()->json([
            'qty_po' => $qtyPo,
            'used_qty' => $usedQty,
            'available_qty' => max(0, $qtyPo - $usedQty),
            'is_full' => $usedQty >= $qtyPo,
        ]);
    }

    public function stock()
    {
        $po = Po::with('detailPos')
            ->orderBy('order_no')
            ->get();

        foreach ($po as $itemPo) {

            foreach ($itemPo->detailPos as $detail) {

                $detail->item = is_array($detail->detail)
                    ? $detail->detail
                    : json_decode($detail->detail, true);

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Qty Loaded per Export
        |--------------------------------------------------------------------------
        */

        $loadedItems = ExportIplItem::with('exportIpl')
            ->select(
                'id',
                'export_ipl_id',
                'detail_po_id',
                'qty_pcs'
            )
            ->orderBy('export_ipl_id')
            ->get()
            ->groupBy('detail_po_id');

        return view('pages.exports.so', compact(
            'po',
            'loadedItems'
        ));
    }
    public function docExports()
    {
            $document = null;
        return view('pages.exports.doc_form', compact('document'));
    }
    public function documentList(Request $request)
    {
        $keyword = trim($request->keyword);

        $query = ExportIpl::withCount('items')
            ->with('creator');

        if ($keyword) {

            $query->where(function ($q) use ($keyword) {

                $q->where('invoice_no', 'like', "%{$keyword}%")
                    ->orWhere('sales_order', 'like', "%{$keyword}%")
                    ->orWhere('buyer', 'like', "%{$keyword}%");

            });

        }

        $datas = $query
            ->latest()
            ->get()
            ->map(function ($row) {

                return [

                    'id' => $row->id,

                    'invoice_no' => $row->invoice_no,

                    'sales_order' => $row->sales_order,

                    'buyer' => $row->buyer,

                    'container' => $row->container_type,

                    'etd' => optional($row->etd)->format('d M Y'),

                    'items' => $row->items_count,

                    'created_by' => optional($row->creator)->name,

                ];

            });

        return response()->json($datas);
    }
    public function documentDetail($id)
    {
        $ipl = ExportIpl::with([
            'items',
            'pos',
            'creator',
        ])->findOrFail($id);

        return response()->json([
            'header' => [

                'id' => $ipl->id,

                'invoice_no' => $ipl->invoice_no,

                'sales_order' => $ipl->sales_order,

                'buyer' => $ipl->buyer,

                'buyer_address' => $ipl->buyer_address,

                'container_type' => $ipl->container_type,

                'container_no' => $ipl->container_no,

                'seal_no' => $ipl->seal_no,

                'vessel_name' => $ipl->vessel_name,

                'etd' => optional($ipl->etd)->format('d M Y'),

                'eta' => optional($ipl->eta)->format('d M Y'),

                'commodity' => $ipl->commodity,

            ],

            'items' => $ipl->items->map(function ($item) {

                return [

                    'article' => $item->article_nr,

                    'description' => $item->description,

                    'qty' => $item->qty_pcs,

                    'box' => $item->qty_box,

                    'price' => $item->unit_price,

                    'total' => $item->total_price,

                    'cbm' => $item->total_cbm,

                    'photo' => $item->photo,

                    'po' => $item->po_no,

                ];

            }),

            'totals' => [

                'qty' => $ipl->items->sum('qty_pcs'),

                'box' => $ipl->items->sum('qty_box'),

                'total_price' => $ipl->items->sum('total_price'),

                'total_cbm' => $ipl->items->sum('total_cbm'),

            ]

        ]);
    }

    // list po
    public function poList(Request $request)
    {
        $query = Po::query();

        if ($request->filled('q')) {

            $keyword = trim($request->q);

            $query->where(function ($q) use ($keyword) {

                $q->where('order_no', 'like', "%{$keyword}%")
                    ->orWhere('company_name', 'like', "%{$keyword}%")
                    ->orWhere('country', 'like', "%{$keyword}%");

            });

        }

        return response()->json(
            $query->orderByDesc('id')->get([
                'id',
                'order_no',
                'company_name',
                'country',
                'shipment_date'
            ])
        );
    }
    public function poDetail($id)
    {
        $po = Po::with('details')->findOrFail($id);

        return response()->json($po);
    }
    public function storeDocument(Request $request)
    {
        DB::beginTransaction();

        try {

            $document = ExportDocument::create([

                'po_id' => $request->po_id,

                'buyer_name' => $request->buyer_name,

                'invoice_id' => $request->invoice,

                'packing_list_id' => $request->packing_list,

                'peb_no' => $request->peb_no,

                'created_by' => Auth::id(),

            ]);

            $singleFiles = [

                'shipping_instruction',

                'delivery_order',

                'bill_of_lading',

                'certificate_origin',

                'certificate_fumigation',

                'v_legal',

                'phyto',

                'isf',

                'lacey_plant',

                'lacey_animal',

            ];

            foreach ($singleFiles as $type) {

                if ($request->hasFile($type)) {

                    $file = $request->file($type);

                    $path = $file->store(
                        'export_documents/' . $document->id,
                        'public'
                    );

                    ExportDocumentFile::create([

                        'export_document_id' => $document->id,

                        'document_type' => $type,

                        'original_name' => $file->getClientOriginalName(),

                        'file_path' => $path,

                        'file_size' => $file->getSize(),

                        'mime_type' => $file->getMimeType(),

                    ]);

                }

            }

            // Declaration Multiple File
            if ($request->hasFile('declarations')) {

                foreach ($request->file('declarations') as $file) {

                    $path = $file->store(
                        'export_documents/' . $document->id . '/declarations',
                        'public'
                    );

                    ExportDocumentFile::create([

                        'export_document_id' => $document->id,

                        'document_type' => 'declaration',

                        'original_name' => $file->getClientOriginalName(),

                        'file_path' => $path,

                        'file_size' => $file->getSize(),

                        'mime_type' => $file->getMimeType(),

                    ]);

                }

            }

            DB::commit();

            return response()->json([

                'success' => true,

                'message' => 'Export Document berhasil disimpan.',

                'id' => $document->id,

            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

            ], 500);

        }
    }
    public function history()
    {
        $datas = ExportDocument::with([
            'po',
            'creator',
            'invoice',
            'packingList',
            'files',
        ])
            ->latest()
            ->paginate(20);

        return view(
            'pages.exports.doc_history',
            compact('datas')
        );
    }
    public function editDoc($id)
    {
        $document = ExportDocument::with([
            'files',
            'invoice',
            'packingList',
            'po',
        ])->findOrFail($id);
            // dd($document);
        return view(
            'pages.exports.doc_form',
            compact('document')
        );
    }
    // update 


public function update(Request $request, $id)
{
    DB::beginTransaction();

    try {

        $doc = ExportDocument::findOrFail($id);

        $doc->update([
            'po_id'            => $request->po_id,
            'buyer_name'       => $request->buyer_name,
            'invoice_id'       => $request->invoice,
            'packing_list_id'  => $request->packing_list,
            'peb_no'           => $request->peb_no,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Hapus file yang dihapus user
        |--------------------------------------------------------------------------
        */

        $deleted = json_decode($request->deleted_files, true);

        if (!empty($deleted)) {

            $files = ExportDocumentFile::whereIn('id', $deleted)->get();

            foreach ($files as $file) {

                if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                    Storage::disk('public')->delete($file->file_path);
                }

                $file->delete();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Single File
        |--------------------------------------------------------------------------
        */

        $singleFiles = [

            'shipping_instruction',
            'delivery_order',
            'bl',
            'coo',
            'fumigation',
            'v_legal',
            'phyto',
            'isf',
            'lacey_plant',
            'lacey_animal',

        ];

        foreach ($singleFiles as $type) {

            if ($request->hasFile($type)) {

                $old = ExportDocumentFile::where([
                    'export_document_id' => $doc->id,
                    'document_type'      => $type,
                ])->first();

                if ($old) {

                    if ($old->file_path && Storage::disk('public')->exists($old->file_path)) {
                        Storage::disk('public')->delete($old->file_path);
                    }

                    $old->delete();
                }

                $file = $request->file($type);

                $path = $file->store('export_documents', 'public');

                ExportDocumentFile::create([

                    'export_document_id' => $doc->id,

                    'document_type'      => $type,

                    'original_name'      => $file->getClientOriginalName(),

                    'file_path'          => $path,

                    'mime_type'          => $file->getMimeType(),

                    'file_size'          => $file->getSize(),

                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Declaration (Multiple)
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('declarations')) {

            foreach ($request->file('declarations') as $file) {

                $path = $file->store('export_documents', 'public');

                ExportDocumentFile::create([

                    'export_document_id' => $doc->id,

                    'document_type'      => 'declaration',

                    'original_name'      => $file->getClientOriginalName(),

                    'file_path'          => $path,

                    'mime_type'          => $file->getMimeType(),

                    'file_size'          => $file->getSize(),

                ]);
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Document berhasil diupdate.'
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
    // EXPORT DOWNLOAD
    // ada di helpers

}
