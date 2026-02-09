<?php
namespace App\Http\Controllers;

use App\Models\DetailPo;
use App\Models\Po;
use App\Models\Spk;
use App\Models\SpkTimeline;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SpkController extends Controller
{
    //
    private function saveBase64Image($base64, $folder = 'spk')
    {
        if (! str_starts_with($base64, 'data:image')) {
            return $base64; // sudah URL
        }

        preg_match('/data:image\/(.*?);base64,/', $base64, $match);
        $extension = $match[1] ?? 'png';

        $image = substr($base64, strpos($base64, ',') + 1);
        $image = base64_decode($image);

        $filename = $folder . '/' . Str::uuid() . '.' . $extension;

        Storage::disk('public')->put($filename, $image);

        return Storage::url($filename);
    }

    public function show($id)
    {
        $spk = [
            'no_spk'      => '25-1254/NW 25-81/12/2025',
            'no_po'       => 'NW 25-81',
            'nama'        => 'PAK HERI',
            'tgl_terima'  => '22-Dec-25',
            'tgl_selesai' => '',
            'items'       => [
                [
                    'kode'     => '17744',
                    'gambar'   => '/storage/spk/chair.jpg',
                    'nama'     => 'ELEGANT SLIMIT CHAIR',
                    'ukuran'   => [62, 75, 97],
                    'material' => 'Rattan Frame',
                    'qty_pcs'  => 70,
                    'qty_set'  => '',
                    'harga'    => 'Rp',
                    'total'    => '-',
                    'catatan'  => '',
                ],
            ],
        ];

        return view('spk.show', compact('spk'));
    }
    public function index(Request $request, $id)
    {
        // =========================
        // MODE DETECTION
        // =========================
        $mode = request()->routeIs('spk.edit') ? 'edit' : 'create';

        if ($mode === 'edit') {

            $spkModel = Spk::findOrFail($id);
            $data     = $spkModel->data;

            $items = collect($data['items'] ?? [])->map(function ($item) {

                return [
                    'detail_id' => $item['detail_po_id'] ?? null, // 🔥 INI KUNCI
                    'kode'      => $item['kode'] ?? '-',
                    'nama'      => $item['nama'] ?? '-',
                    'pcs'       => ($item['satuan'] ?? '') === 'pcs' ? $item['qty'] : 0,
                    'set'       => ($item['satuan'] ?? '') === 'set' ? $item['qty'] : 0,
                    'harga'     => $item['harga'] ?? 0,
                    'total'     => $item['total'] ?? 0,
                    'satuan'    => $item['satuan'] ?? 'pcs',
                    'images'    => $item['images'] ?? [],
                    'catatan'   => $item['catatan'],
                    // 'note_img'  => $item['catatan']['images'] ?? [],
                    'p'         => $item['p'] ?? '-',
                    'l'         => $item['l'] ?? '-',
                    't'         => $item['t'] ?? '-',
                    'material'  => $item['material'] ?? '-',
                ];
            })->values();

            $spk = [
                'id'          => $spkModel->id,
                'no_spk'      => $data['no_spk'] ?? '-',
                'no_po'       => $data['no_po'] ?? '-',
                'nama'        => $data['sup'] ?? '-',
                'tgl_terima'  => $data['tgl_terima'] ?? '-',
                'tgl_selesai' => $data['tgl_selesai'] ?? '-',
                'type'        => $data['kategori'] ?? '-',
                'items'       => $items,
                'mode'        => 'edit',
            ];
        } else {

            // =========================
            // CREATE MODE → DATA DARI PO
            // =========================
            $po = Po::with('details')->findOrFail($id);

            $now      = Carbon::now();
            $year     = $now->format('y');
            $month    = $now->format('m');
            $yearFull = $now->format('Y');

            $noSpkUrut = str_pad($po->id, 4, '0', STR_PAD_LEFT);
            $noSpk     = "{$year}-{$noSpkUrut}/{$po->order_no}/{$month}/{$yearFull}";

            $items = $po->details->map(function ($d) {
                $detail = $d->detail;
                $images = [];

                if (! empty($detail['photo'])) {
                    $images[] = $detail['photo'];
                }

                return [
                    'kode'      => $detail['article_nr_'] ?? '-',
                    'detail_id' => $d['id'] ?? '-',
                    'nama'      => $detail['description'] ?? '-',
                    'p'         => $detail['item_w'] ?? '-',
                    'l'         => $detail['item_d'] ?? '-',
                    't'         => $detail['item_h'] ?? '-',
                    'material'  => $detail['composition'] ?? '-',
                    'pcs'       => $detail['qty'] ?? 0,
                    'set'       => $detail['set'] ?? 0,
                    'harga'     => $detail['harga'] ?? 0,
                    'catatan'   => $d->remark_update ?? '',
                    'images'    => $images, // multi image ready
                ];
            })->values();
            $noSpk = $this->generateNoSpk($po->order_no);

            $spk = [
                'id'          => $po->id,
                'no_spk'      => $noSpk,
                'no_po'       => $po->order_no,
                'nama'        => $po->supplier_name ?? '-',
                'tgl_terima'  => now()->format('d-M-Y'),
                'tgl_selesai' => $request->tgl_selesai,
                'type'        => 'rangka',
                'items'       => $items,
                'mode'        => 'create',
            ];
        }
        // dd($spk);
        return view('pages.spk.index', compact('spk'));
    }
// helper
    private function generateNoSpk($noPo)
    {
        $now      = now();
        $year     = $now->format('y'); // 26
        $month    = $now->format('m'); // 02
        $yearFull = $now->format('Y'); // 2026

        // 🔥 ambil SPK terakhir di tahun yg sama
        $lastSpk = Spk::where('data->no_spk', 'like', "{$year}-%")
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastSpk) {
            // contoh: 26-0029/NW NW 25 - 02/02/2026
            $noSpk = $lastSpk->data['no_spk'] ?? '';

            if (preg_match('/^\d{2}-(\d{4})/', $noSpk, $match)) {
                $nextNumber = (int) $match[1] + 1;
            }
        }

        $urut = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return "{$year}-{$urut}/{$noPo}/{$month}/{$yearFull}";
    }
    public function save(Request $request, $poId)
    {
        $kategori = $request->spk_type;
        $items    = $request->items ?? [];
        $spkId    = $request->spk_id; // edit mode jika ada

        if (! $kategori || empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak lengkap',
            ], 422);
        }

        // =========================
        // MODE
        // =========================
        $mode = $spkId ? 'edit' : 'create';

        $beforeData = null;
        $spkModel   = null;

        if ($mode === 'edit') {
            $spkModel   = Spk::findOrFail($spkId);
            $beforeData = $spkModel->data ?? [];
        }

        // =========================
        // OLAH ITEMS
        // =========================
        $finalItems = [];

        foreach ($items as $item) {

            if (empty($item['detail_id'])) {
                continue;
            }

            // ===== HITUNG QTY
            $qty = 0;
            if (($item['satuan'] ?? '') === 'pcs') {
                $qty = (int) ($item['pcs'] ?? 0);
            } elseif (($item['satuan'] ?? '') === 'set') {
                $qty = (int) ($item['set'] ?? 0);
            }

            if ($qty <= 0) {
                continue;
            }

            // ===== DETAIL PO
            $detailPo = DetailPo::find($item['detail_id']);
            if (! $detailPo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detail PO tidak ditemukan',
                ], 422);
            }

            // ===== VALIDASI QTY (CREATE SAJA)
            if ($mode === 'create') {
                $qtyPo = (int) ($detailPo->detail['qty'] ?? 0);

                $qtySpkExist = $this->getTotalSpkQtyByDetailPoAndKategori(
                    $detailPo->id,
                    $kategori
                );

                if (($qtySpkExist + $qty) > $qtyPo) {
                    return response()->json([
                        'success' => false,
                        'message' => "Qty SPK melebihi qty PO (Detail PO ID {$detailPo->id})",
                    ], 422);
                }
            }

            // ===== IMAGE ITEM
            $itemImages = [];
            foreach ($item['images'] ?? [] as $img) {
                $itemImages[] = is_string($img) && str_starts_with($img, 'data:image')
                    ? $this->saveBase64Image($img, 'spk/items')
                    : $img;
            }

            // ===== IMAGE CATATAN
            $noteImages = [];
            foreach ($item['catatan']['images'] ?? [] as $img) {
                $noteImages[] = is_string($img) && str_starts_with($img, 'data:image')
                    ? $this->saveBase64Image($img, 'spk/notes')
                    : $img;
            }

            $finalItems[] = [
                'detail_po_id' => $detailPo->id,
                'kode'         => (string) ($item['kode'] ?? ''),
                'nama'         => (string) ($item['nama'] ?? ''),
                'qty'          => $qty,
                'satuan'       => $item['satuan'] ?? '',
                'material'     => (string) ($item['material'] ?? ''),
                'p'            => (string) ($item['p'] ?? ''),
                'l'            => (string) ($item['l'] ?? ''),
                't'            => (string) ($item['t'] ?? ''),
                'harga'        => (float) ($item['harga'] ?? 0),
                'total'        => (float) ($item['total'] ?? 0),
                'images'       => $itemImages,
                'catatan'      => [
                    'remark' => (string) ($item['catatan']['remark'] ?? ''),
                    'images' => $noteImages,
                ],
            ];
        }

        if (empty($finalItems)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada item valid',
            ], 422);
        }

        // =========================
        // DATA FINAL
        // =========================
        $afterData = [
            'kategori'    => $kategori,
            'no_spk'      => $request->no_spk,
            'no_po'       => $request->no_po,
            'sup'         => $request->nama,
            'tgl_terima'  => $request->tgl_terima,
            'tgl_selesai' => $request->tgl_selesai,
            'items'       => $finalItems,
        ];

        // =========================
        // CREATE / UPDATE
        // =========================
        if ($mode === 'create') {

            $spk = Spk::create([
                'po_id'      => $poId,
                'data'       => $afterData,
                'created_by' => auth()->id(),
            ]);

            SpkTimeline::create([
                'spk_id' => $spk->id,
                'data'   => [
                    'type'  => 'create',
                    'user'  => auth()->user()->name,
                    'time'  => now(),
                    'after' => $afterData,
                ],
            ]);

        } else {

            $changes = $this->diffRecursive($beforeData, $afterData);

            $spkModel->update([
                'data'       => $afterData,
                'updated_by' => auth()->id(),
            ]);

            SpkTimeline::create([
                'spk_id' => $spkModel->id,
                'data'   => [
                    'type'    => 'update',
                    'user'    => auth()->user()->name,
                    'time'    => now(),
                    'before'  => $beforeData,
                    'after'   => $afterData,
                    'changes' => $changes,
                ],
            ]);

            $spk = $spkModel;
        }

        return response()->json([
            'success' => true,
            'message' => $mode === 'edit'
                ? 'SPK berhasil diperbarui'
                : 'SPK berhasil dibuat',
            'spk_id'  => $spk->id,
            'no_spk'  => $mode === 'edit' ? $spkModel->data['no_spk'] : $spk->no_spk,
        ]);
    }
// helper
    private function diffRecursive($before, $after, $path = '')
    {
        $changes = [];

        foreach ($after as $key => $value) {
            $currentPath = $path ? "$path.$key" : $key;

            if (! array_key_exists($key, $before)) {
                $changes[$currentPath] = [
                    'before' => null,
                    'after'  => $value,
                ];
                continue;
            }

            if (is_array($value) && is_array($before[$key])) {
                $nested  = $this->diffRecursive($before[$key], $value, $currentPath);
                $changes = array_merge($changes, $nested);
            } elseif ($before[$key] != $value) {
                $changes[$currentPath] = [
                    'before' => $before[$key],
                    'after'  => $value,
                ];
            }
        }

        return $changes;
    }
// export to excel
    public function export($spkId)
    {
        $spk  = Spk::findOrFail($spkId);
        $data = $spk->data;

        $templatePath = storage_path('app/templates/SPK-TEMPLATE.xlsx');
        $spreadsheet  = IOFactory::load($templatePath);
        $sheet        = $spreadsheet->getActiveSheet();

        /** =====================
         * HEADER
         * ===================== */
        $sheet->setCellValue('B7', $data['no_spk'] ?? '');
        $sheet->setCellValue('B8', $data['sup'] ?? '');
        $sheet->setCellValue('B9', $data['tgl_terima'] ?? '');
        $sheet->setCellValue('B10', $data['tgl_selesai'] ?? '-');
        $sheet->setCellValue('K7', $data['no_po'] ?? '');

        /** =====================
         * ITEM SETUP
         * ===================== */
        $templateRow = 14;
        $startRow    = 14;
        $items       = $data['items'] ?? [];
        $itemCount   = count($items);

        if ($itemCount > 1) {
            // 🔥 Geser row di bawah table (termasuk perjanjian & signature)
            $sheet->insertNewRowBefore(
                $templateRow + 1,
                $itemCount - 1
            );

            // 🔥 Copy style row template ke row baru
            for ($i = 1; $i < $itemCount; $i++) {
                $this->copyRowStyle($sheet, $templateRow, $templateRow + $i);
            }
        }

        /** =====================
         * ISI ITEM
         * ===================== */
        $row = $startRow;

        foreach ($items as $item) {

            $sheet->setCellValue("A{$row}", $item['kode'] ?? '');
            $sheet->setCellValue("C{$row}", $item['nama'] ?? '');
            $sheet->setCellValue("D{$row}", $item['p'] ?? '');
            $sheet->setCellValue("E{$row}", $item['l'] ?? '');
            $sheet->setCellValue("F{$row}", $item['t'] ?? '');
            $sheet->setCellValue("G{$row}", $item['material'] ?? '');
            // 🔥 QTY LOGIC
            if (($item['satuan'] ?? '') === 'pcs') {
                $sheet->setCellValue("H{$row}", $item['qty'] ?? '');
                $sheet->setCellValue("I{$row}", ''); // kosongkan set
            } elseif (($item['satuan'] ?? '') === 'set') {
                $sheet->setCellValue("H{$row}", ''); // kosongkan pcs
                $sheet->setCellValue("I{$row}", $item['qty'] ?? '');
            } else {
                // fallback kalau satuan aneh / kosong
                $sheet->setCellValue("H{$row}", '');
                $sheet->setCellValue("I{$row}", '');
            }

            $sheet->setCellValue("J{$row}", $item['harga'] ?? '');
            $sheet->setCellValue("K{$row}", $item['total']* $item['qty'] ?? '');


            // AUTO HEIGHT
            $sheet->getRowDimension($row)->setRowHeight(90);

            /** ITEM IMAGE */
            if (! empty($item['images'][0])) {
                $this->insertImage($sheet, $item['images'][0], "B{$row}", 80);
            }

            /** CATATAN IMAGE */
            if (! empty($item['catatan']['images'][0])) {
                $this->insertImage($sheet, $item['catatan']['images'][0], "L{$row}", 80);
            }

            $row++;
        }

        /** =====================
         * DOWNLOAD
         * ===================== */
        $safeNoSpk = preg_replace('/[\/\\\\]/', '-', $data['no_spk'] ?? $spk->id);
        $filename  = "SPK-{$safeNoSpk}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename);
    }
    private function copyRowStyle($sheet, $srcRow, $dstRow)
    {
        foreach (range('A', 'K') as $col) {
            $sheet->duplicateStyle(
                $sheet->getStyle($col . $srcRow),
                $col . $dstRow
            );
        }

        // Copy merge
        foreach ($sheet->getMergeCells() as $merge) {
            if (preg_match("/{$srcRow}/", $merge)) {
                $newMerge = preg_replace(
                    "/{$srcRow}/",
                    $dstRow,
                    $merge
                );
                $sheet->mergeCells($newMerge);
            }
        }
    }
    private function insertImage($sheet, $path, $cell, $height = 80)
    {
        $realPath = public_path(str_replace(url('/'), '', $path));
        if (! file_exists($realPath)) {
            return;
        }

        $drawing = new Drawing();
        $drawing->setPath($realPath);
        $drawing->setCoordinates($cell);
        $drawing->setHeight($height);
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(5);
        $drawing->setWorksheet($sheet);
    }

    private function addImage($sheet, $path, $cell, $height = 80)
    {
        if (! $path) {
            return;
        }

        // kalau path masih URL
        $realPath = public_path(str_replace(url('/'), '', $path));

        if (! file_exists($realPath)) {
            return;
        }

        $drawing = new Drawing();
        $drawing->setPath($realPath);
        $drawing->setCoordinates($cell);
        $drawing->setHeight($height);
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(5);
        $drawing->setWorksheet($sheet);
    }

    private function diffData(array $before, array $after)
    {
        $changes = [];

        foreach ($after as $key => $value) {
            if (! array_key_exists($key, $before)) {
                $changes[$key] = [
                    'before' => null,
                    'after'  => $value,
                ];
                continue;
            }

            if ($before[$key] != $value) {
                $changes[$key] = [
                    'before' => $before[$key],
                    'after'  => $value,
                ];
            }
        }

        return $changes;
    }

    private function getTotalSpkQtyByDetailPoAndKategori($detailPoId, $kategori)
    {
        return Spk::whereJsonContains('data->items', [
            'detail_po_id' => $detailPoId,
        ])
            ->where('data->kategori', $kategori)
            ->get()
            ->sum(function ($spk) use ($detailPoId) {
                return collect($spk->data['items'])
                    ->where('detail_po_id', $detailPoId)
                    ->sum('qty');
            });
    }

    // ItemController.php
    public function search(Request $request)
    {
        $q = trim($request->q);

        if (! $q) {
            return [];
        }

        return DetailPo::where(function ($query) use ($q) {
            $query->where('detail->article_nr_', 'like', "%{$q}%")
                ->orWhere('detail->description', 'like', "%{$q}%");
        })
            ->limit(10)
            ->get()
            ->map(function ($row) {

                $detail = $row->detail ?? [];
                $images = [];
                if (! empty($detail['photo'])) {
                    $images[] = $detail['photo'];
                }
                return [
                    'detail_id' => $row->id,
                    'kode'      => data_get($detail, 'article_nr_'),
                    'nama'      => data_get($detail, 'description'),
                    'p'         => (float) data_get($detail, 'item_w'),
                    'l'         => (float) data_get($detail, 'item_d'),
                    't'         => (float) data_get($detail, 'item_h'),
                    'material'  => data_get($detail, 'composition'),
                    'qty'       => (int) data_get($detail, 'qty'),
                    'photo'     => data_get($detail, 'photo'),
                    'images'    => $images, // multi image ready

                ];
            });
    }

    // timeline spk
    public function tima()
    {
        $timeline = SpkTimeline::latest()
            ->limit(50)
            ->get()
            ->map(function ($row) {

                $data = $row->data ?? [];

                return [
                    'id'      => $row->id,
                    'spk_id'  => $row->spk_id,
                    'type'    => $data['type'] ?? 'info',
                    'user'    => $data['user'] ?? optional($row->user)->name,
                    'time'    => $data['time'] ?? $row->created_at,
                    'before'  => $data['before'] ?? null,
                    'after'   => $data['after'] ?? null,
                    'changes' => $data['changes'] ?? null,
                ];
            });

        return response()->json($timeline);
    }

    public function spk()
    {
        return view('pages.spk.all');
    }
    public function allspk()
    {
        $poList = Po::all();
        $spks   = Spk::all();

        $result = $poList->map(function ($po) use ($spks) {

            // ambil spk berdasarkan po_id saja
            $spkList = $spks->where('po_id', $po->id)->values();

            return [
                'data_po' => [
                    'id'    => $po->id,
                    'no_po' => $po->order_no,
                ],

                'spks'    => $spkList->map(function ($spk) {

                    return [
                        'id'   => $spk->id,
                        'data' => $spk->data,
                    ];
                }),
            ];
        });
        // dd($result);
        return response()->json($result);
    }

    // get spk
    public function spkEdit($id)
    {
        $spk = Spk::findOrFail($id);

        $data = $spk->data; // otomatis array kalau column json cast

        // =========================
        // Mapping ke format blade
        // =========================
        $spkView = [
            'id'          => $spk->id,
            'type'        => $data['kategori'] ?? '',

            'no_spk'      => $data['no_spk'] ?? '',
            'no_po'       => $data['no_po'] ?? '',

            'nama'        => $data['sup'] ?? '',

            'tgl_terima'  => $data['tgl_terima'] ?? '',
            'tgl_selesai' => $data['tgl_selesai'] ?? '',

            'items'       => [],
        ];

        // =========================
        // Build item dari JSON
        // =========================
        if (isset($data['item'])) {
            $item = $data['item'];

            $spkView['items'][] = [
                'detail_id' => $item['detail_id'] ?? '',

                'kode'      => $item['kode'] ?? '',
                'nama'      => $item['nama'] ?? '',

                'p'         => $item['p'] ?? '',
                'l'         => $item['l'] ?? '',
                't'         => $item['t'] ?? '',

                'material'  => $item['material'] ?? '',

                'pcs'       => $item['pcs'] ?? 0,
                'set'       => $item['set'] ?? 0,

                'harga'     => $item['harga'] ?? 0,
                'total'     => $item['total'] ?? 0,

                'images'    => $item['images'] ?? [],

                'catatan'   => $item['catatan']['remark'] ?? '',
            ];
        }
        // dd($spkView);
        return view('pages.spk.edit', [
            'spk' => $spkView,
        ]);
    }
    // saipamn edit

}
