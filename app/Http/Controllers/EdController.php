<?php

namespace App\Http\Controllers;

use App\Models\ExportIpl;
use App\Models\ExportIplItem;
use App\Models\ExportIplPo;
use App\Models\Po;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                'value' => $detail['fob_jakarta_in_usd']
                    ?? $detail['fob_jakarta_price_in_usd_pc']
                    ?? 0,            ];
        }

        return response()->json($items);
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

                $ipl->items()->create([

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
}
