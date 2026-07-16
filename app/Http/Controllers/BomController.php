<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BomImport;
use App\Models\MaterialFinishing;
use App\Models\MaterialPrice;
use App\Models\Bom;
use App\Models\BomGroup;
use App\Models\BomItem;
use App\Models\BomSummary;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Exports\BomExport;
use Carbon\Carbon;


use Illuminate\Support\Str;
class BomController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new BomImport, $request->file('file'));

        return back()->with('success', 'Import berhasil!');
    }
    public function index()
{
    $materialPrices = MaterialPrice::latest()->get();

    $materialFinishings = MaterialFinishing::latest()->get();
    $boms = Bom::latest()->get();

    $materials =
        MaterialPrice::select(
            'id',
            'nama_material as nama'
        )
        ->get()
        ->map(function($row){

            $row->jenis = 'Material';
            $row->type = 'material_price';

            return $row;
        });

    $finishings =
        MaterialFinishing::select(
            'id',
            'nama'
        )
        ->get()
        ->map(function($row){

            $row->jenis = 'Finishing';
            $row->type = 'material_finishing';

            return $row;
        });

    $masterMaterials =
    collect()
        ->concat($materials)
        ->concat($finishings)
        ->values();
// dd(
//      count($masterMaterials),
//     //  count($masterMaterials)
// );
    return view(
        'pages.bom.index',
        compact(
            'materialPrices',
            'materialFinishings',
            'masterMaterials',
            'boms'
        )
    );
}
    public function bulkStore(Request $request)
    {
        $rows = explode("\n", trim($request->materials));

        foreach ($rows as $row) {

            $row = trim($row);

            if (empty($row)) {
                continue;
            }

            $cols = array_map('trim', explode(',', $row));

            MaterialPrice::create([
                'nama_material' => $cols[0] ?? '',
                'harga'         => $cols[1] ?? 0,
                'satuan'        => $cols[2] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Material berhasil disimpan'
        ]);
    }
    public function destroy($id)
{
    MaterialPrice::findOrFail($id)->delete();

    return response()->json([
        'success' => true
    ]);
}
public function update(Request $request,$id)
{
    $material = MaterialPrice::findOrFail($id);

    $material->update([
        'nama_material' => $request->nama_material,
        'harga'         => $request->harga,
        'satuan'        => $request->satuan,
    ]);

    return response()->json([
        'success' => true
    ]);
}
// finishsing
public function bulkStoreFinishing(Request $request)
{
    $rows = explode("\n", trim($request->materials));

    foreach ($rows as $row) {

        $cols = array_map(
            'trim',
            explode(',', $row)
        );

        MaterialFinishing::create([

            'nama'               => $cols[0] ?? '',

            'jenis_propan'       => $cols[1] ?? 0,

            'jenis_diva'         => $cols[2] ?? 0,

            'jenis_warna_prima'  => $cols[3] ?? 0,

            'jenis_legenda'      => $cols[4] ?? 0,

        ]);
    }

    return response()->json([
        'success'=>true
    ]);
}
public function updateFinishing(Request $request, $id)
{
    $finishing = MaterialFinishing::findOrFail($id);

    $column = $request->column;

    $allowed = [
        'nama',
        'jenis_propan',
        'jenis_diva',
        'jenis_warna_prima',
        'jenis_legenda'
    ];

    if (!in_array($column, $allowed)) {
        return response()->json([
            'success' => false,
            'message' => 'Column tidak valid'
        ], 422);
    }

    $finishing->$column = $request->value;
    $finishing->save();

    return response()->json([
        'success' => true,
        'message' => 'Data berhasil diupdate'
    ]);
}
public function destroyFinishing($id)
{
    $finishing = MaterialFinishing::findOrFail($id);

    $finishing->delete();

    return response()->json([
        'success' => true,
        'message' => 'Data berhasil dihapus'
    ]);
}
// save bom
public function store(Request $request)
{
    DB::beginTransaction();

    try {

       $data = json_decode(
    $request->bom,
    true
);

$image = null;

if ($request->hasFile('image')) {

    $image = $request
        ->file('image')
        ->store(
            'bom',
            'public'
        );
}
        $bom =
            Bom::create([

                'name' =>
                    $data['name'],

                'article_number' =>
                    $data['article_number'],
                'panjang' =>
                        $data['panjang'] ?? null,

                    'lebar' =>
                        $data['lebar'] ?? null,

                    'tinggi' =>
                        $data['tinggi'] ?? null,

                    'carton_panjang' =>
                        $data['carton_panjang'] ?? null,

                    'carton_lebar' =>
                        $data['carton_lebar'] ?? null,

                    'carton_tinggi' =>
                        $data['carton_tinggi'] ?? null,

                    'loadability_pcs' =>
                        $data['loadability_pcs'] ?? null,

                    'loadability_cbm' =>
                        $data['loadability_cbm'] ?? null,
                            'image' => $image

            ]);

        foreach(
            $data['groups']
            as $groupData
        ){
        $sub = $groupData['sub_prices'][0] ?? null;

            $group =
                BomGroup::create([

                    'bom_id' =>
                        $bom->id,

                    'name' =>
                        $groupData['name'],
                     'name_sub' => $sub['name'] ?? null,

                    'harga_sub' => $sub['price'] ?? 0,

                ]);

            foreach(
                $groupData['items']
                as $item
            ){

                BomItem::create([

                    'group_id' =>
                        $group->id,

                    'name' =>
                        $item['name'],

                    'qty' =>
                        $item['qty'],

                    'unit' =>
                        $item['unit'],

                    'notes' =>
                        $item['notes'],
                    'harga' => $item['price'] ??null,

                    'parent_id' => null,

                    'level' => 1

                ]);

            }

        }
        foreach ($data['summaries'] ?? [] as $summary) {

            BomSummary::create([

                'bom_id' => $bom->id,

                'name'   => $summary['name'] ?? '',

                'remark' => $summary['remark'] ?? '',

                'qty'    => $summary['qty'] ?? 0,

                'price'  => $summary['price'] ?? 0,

                'total'  => $summary['total'] ?? 0,

            ]);

        }
        DB::commit();

        return response()->json([
            'success'=>true
        ]);

    } catch(\Exception $e){

        DB::rollBack();

        return response()->json([
            'success'=>false,
            'message'=>$e->getMessage()
        ],500);

    }
}
public function show($id)
{
    $bom = Bom::with([
        'groups.items','summaries'

    ])->findOrFail($id);

    // diperlukan oleh modal material picker
    $materialPrices = MaterialPrice::all();

    $materials = MaterialPrice::select(
        'id',
        'nama_material as nama'
    )
    ->get()
    ->map(function ($row) {

        $row->jenis = 'Material';
        $row->type  = 'material_price';

        return $row;
    });

    $finishings = MaterialFinishing::select(
        'id',
        'nama'
    )
    ->get()
    ->map(function ($row) {

        $row->jenis = 'Finishing';
        $row->type  = 'material_finishing';

        return $row;
    });

    $masterMaterials = $materials
        ->concat($finishings)
        ->values();

    $bomData = [

        'name' => $bom->name,

        'article_number' => $bom->article_number,

        'panjang' => $bom->panjang,
        'lebar' => $bom->lebar,
        'tinggi' => $bom->tinggi,

        'carton_panjang' => $bom->carton_panjang,
        'carton_lebar' => $bom->carton_lebar,
        'carton_tinggi' => $bom->carton_tinggi,

        'loadability_pcs' => $bom->loadability_pcs,
        'loadability_cbm' => $bom->loadability_cbm,

        'image' => $bom->image,

        'groups' => [],
         'summaries' => []

    ];

    foreach ($bom->groups as $group) {

        $groupData = [
            'name' => $group->name,
            'items' => [],
              'sub_prices' => []
        ];

        foreach ($group->items as $item) {

            $groupData['items'][] = [
                'sub_prices' => [],
                'material_id' => $item->material_id,

                'material_type' => $item->material_type,

                // penting
                'price' => $item->harga,

                'name' => $item->name,

                'qty' => $item->qty,

                'unit' => $item->unit,

                'notes' => $item->notes,

               'total' => (float) $item->qty * (float) $item->harga,

            ];
            // dd($item->qty, $item->harga);
        }
  if ($group->name_sub || $group->harga_sub) {

        $groupData['sub_prices'][] = [

            'name' => $group->name_sub,

            'price' => $group->harga_sub

        ];

    }
        $bomData['groups'][] = $groupData;
    }
foreach ($bom->summaries as $summary) {

    $bomData['summaries'][] = [

        'name'   => $summary->name,

        'remark' => $summary->remark,

        'qty'    => $summary->qty,

        'price'  => $summary->price,

        'total'  => $summary->total,

    ];

}
$isEdit = request()->routeIs('bom.edit');
    return view(
        'pages.bom.edit',
        compact(
            'isEdit',
            'bom',
            'bomData',
            'masterMaterials',
            'materialPrices'
        )
    );
}
public function updateBom(
    Request $request,
    $id
)
{
    DB::beginTransaction();

    try {

        $bom =
            Bom::findOrFail($id);
if ($request->hasFile('image')) {

    $image = $request
        ->file('image')
        ->store(
            'bom',
            'public'
        );

    $bom->image = $image;
}
// dd(
//     $request->all(),
//     $request->file('image'),
//     $request->bom
// );

        $data =
        json_decode(  $request->bom,true);


        // UPDATE HEADER BOM

        $bom->update([

            'name' =>
                $data['name'] ?? '',

            'article_number' =>
                $data['article_number'] ?? '',

            'panjang' =>
                $data['panjang'] ?? null,

            'lebar' =>
                $data['lebar'] ?? null,

            'tinggi' =>
                $data['tinggi'] ?? null,

            'carton_panjang' =>
                $data['carton_panjang'] ?? null,

            'carton_lebar' =>
                $data['carton_lebar'] ?? null,

            'carton_tinggi' =>
                $data['carton_tinggi'] ?? null,

            'loadability_pcs' =>
                $data['loadability_pcs'] ?? null,

            'loadability_cbm' =>
                $data['loadability_cbm'] ?? null,

        ]);

        // HAPUS ITEM LAMA

        $groupIds =
            BomGroup::where(
                'bom_id',
                $bom->id
            )
            ->pluck('id');

        BomItem::whereIn(
            'group_id',
            $groupIds
        )->delete();

        BomGroup::where(
            'bom_id',
            $bom->id
        )->delete();
        BomSummary::where(
            'bom_id',
            $bom->id
        )->delete();
        // INSERT ULANG

        foreach(
            $data['groups']
            as $groupData
        ){
        $sub = $groupData['sub_prices'][0] ?? null;

            $group =
                BomGroup::create([

                    'bom_id' =>
                        $bom->id,

                    'name' =>
                        $groupData['name'],

                    'name_sub' => $sub['name'] ?? null,

                    'harga_sub' => $sub['price'] ?? null,

                ]);

            foreach(
                $groupData['items']
                as $item
            ){

                BomItem::create([

                    'group_id' =>
                        $group->id,

                    'material_id' =>
                        $item['material_id']
                        ?? null,
                    'harga' => $item['price'] ?? null,

                    'material_type' =>
                        $item['material_type']
                        ?? null,

                    'name' =>
                        $item['name']
                        ?? '',

                    'qty' =>
                        $item['qty']
                        ?? 0,

                    'unit' =>
                        $item['unit']
                        ?? '',

                    'notes' =>
                        $item['notes']
                        ?? '',

                    'parent_id' =>
                        null,

                    'level' =>
                        1

                ]);

            }

        }
        foreach ($data['summaries'] ?? [] as $summary) {

        BomSummary::create([

            'bom_id' => $bom->id,

            'name' => $summary['name'] ?? '',

            'remark' => $summary['remark'] ?? '',

            'qty' => $summary['qty'] ?? 0,

            'price' => $summary['price'] ?? 0,

            'total' => $summary['total'] ?? 0,

        ]);

    }
        DB::commit();

        return response()->json([

            'success' => true,

            'message' =>
                'BOM berhasil diupdate'

        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([

            'success' => false,

            'message' =>
                $e->getMessage()

        ],500);

    }
}
public function exportExcel($id)
{
    $bom = Bom::with([
        'groups.items',
        'summaries'
    ])->findOrFail($id);

    $bomData = $this->buildBomData($bom);
  // Nama file
    $filename = $bom->article_number
        ? 'BOM_'.$bom->article_number.'.xlsx'
        : 'BOM_'.Str::slug($bom->name, '_').'.xlsx';
    return Excel::download(new BomExport($bomData), $filename);
}
    private function buildBomData(Bom $bom)
{
    $bomData = [

        'name' => $bom->name,

        'article_number' => $bom->article_number,

        'panjang' => $bom->panjang,
        'lebar' => $bom->lebar,
        'tinggi' => $bom->tinggi,

        'carton_panjang' => $bom->carton_panjang,
        'carton_lebar' => $bom->carton_lebar,
        'carton_tinggi' => $bom->carton_tinggi,

        'loadability_pcs' => $bom->loadability_pcs,
        'loadability_cbm' => $bom->loadability_cbm,
        'date' => $bom->created_at,
        'image' => $bom->image,

        'groups' => [],

        'summaries' => []

    ];

    foreach ($bom->groups as $group) {

        $groupData = [

            'name' => $group->name,

            'items' => [],

            'sub_prices' => []

        ];

        foreach ($group->items as $item) {

            $groupData['items'][] = [

                'material_id'   => $item->material_id,

                'material_type' => $item->material_type,

                'price'         => $item->harga,

                'name'          => $item->name,

                'qty'           => $item->qty,

                'unit'          => $item->unit,

                'notes'         => $item->notes,

                'total'         => ($item->qty ?? 0) * ($item->harga ?? 0),

            ];

        }

        if ($group->name_sub || $group->harga_sub) {

            $groupData['sub_prices'][] = [

                'name'  => $group->name_sub,

                'price' => $group->harga_sub

            ];

        }

        $bomData['groups'][] = $groupData;

    }

    foreach ($bom->summaries as $summary) {

        $bomData['summaries'][] = [

            'name'   => $summary->name,

            'remark' => $summary->remark,

            'qty'    => $summary->qty,

            'price'  => $summary->price,

            'total'  => $summary->total,

        ];

    }

    return $bomData;
}
    public function toggleRelease(Request $request, Bom $bom)
{
    $released = $request->boolean('released');

    if ($released) {

        $bom->update([
            'released' => 1,
            'released_date' => now(),
        ]);

        return response()->json([
            'released' => true,
            'released_date' => $bom->released_date->format('d M Y H:i'),
            'message' => 'BOM berhasil direlease.'
        ]);
    }

    $bom->update([
        'released' => 0,
        'released_date' => null,
    ]);

    return response()->json([
        'released' => false,
        'released_date' => null,
        'message' => 'Release berhasil dibatalkan.'
    ]);
}
}

