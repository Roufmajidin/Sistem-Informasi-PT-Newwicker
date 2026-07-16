<?php

namespace App\Http\Controllers;

use App\Models\DetailPo;
use App\Models\JenisSupplier;
use App\Models\Karyawan;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestSaved;
use App\Models\PaymentRequestSignature;
use App\Models\Po;
use App\Models\ProductionTimeline;
use App\Models\Spk;
use App\Models\SpkTimeline;
use App\Models\Supplier;
use Carbon\Carbon;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\SignatureSpk;
use App\Models\PaymentRequestApproval;

class SpkController extends Controller
{
    //
    public function delete($id)
    {
        $spk = Spk::find($id);
        if (! $spk) {
            return response()->json([
                'message' => 'SPK tidak ditemukan',
            ], 404);
        }
        $spk->delete();
        return response()->json([
            'message' => 'SPK berhasil dihapus',
        ]);
    }
    private function saveBase64Image($base64, $folder = 'spk')
    {
        if (! str_starts_with($base64, 'data:image')) {
            return $base64;
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
        $viewOnly = $request->is('spk/views/*');
        // dd($viewOnly);

        $mode = (
            $request->routeIs('spk.edit') ||
            $request->routeIs('spk.view')
        )
            ? 'edit'
            : 'create';
        $jenisSuppliers =
            JenisSupplier::orderBy('name')->get();
        // =====================================================
        // EDIT MODE
        // =====================================================
        if ($mode === 'edit') {
            $spkModel = Spk::findOrFail($id);
            $data = $spkModel->data ?? [];
            // siganture approval spk
          $signature = SignatureSpk::with([
                'madeBy.karyawan.divisi',
                'checkedBy.karyawan.divisi',
                'checkedBy2.karyawan.divisi',
                'approvedBy.karyawan.divisi',
                'supplier'
            ])
            ->where('spk_id', $spkModel->id)
            ->first();
            // =========================
            // PAYMENT REQUEST
            // =========================
            $paymentRequest =
                PaymentRequest::where(
                    'spk_id',
                    $spkModel->id
                )->latest()->first();
            // =========================
            // ITEMS
            // =========================
            $items = collect(
                $data['items'] ?? []
            )->map(function ($item) {
                return [
                    'detail_id'      =>
                    $item['detail_po_id'] ?? null,
                    'kode'           =>
                    $item['kode'] ?? '-',
                    'nama'           =>
                    $item['nama'] ?? '-',
                    // 🔥 CUSTOM VALUE
                    'custom_columns' =>
                    $item['custom_columns'] ?? [],
                    'pcs'            => ($item['satuan'] ?? '') === 'pcs'
                        ? $item['qty']
                        : 0,
                    'set'            => ($item['satuan'] ?? '') === 'set'
                        ? $item['qty']
                        : 0,
                    'harga'          =>
                    $item['harga'] ?? 0,
                    'total'          =>
                    $item['total'] ?? 0,
                    'satuan'         =>
                    $item['satuan'] ?? 'pcs',
                    'images'         =>
                    $item['images'] ?? [],
                    'catatan'        =>
                    $item['catatan'] ?? [],
                    'p'              =>
                    $item['p'] ?? '-',
                    'l'              =>
                    $item['l'] ?? '-',
                    't'              =>
                    $item['t'] ?? '-',
                    'material'       =>
                    $item['material'] ?? '-',
                ];
            })->values();
            // =========================
            // FINAL DATA
            // =========================
            $spk = [
                'signature' => $signature,
                'id'             =>
                $spkModel->id,
                'status'         =>
                $spkModel->status ?? 'draft',
                'request_status' =>
                $paymentRequest->status ?? null,
                'no_spk'         =>
                $data['no_spk'] ?? '-',
                'no_po'          =>
                $data['no_po'] ?? '-',
                'nama'           =>
                $data['sup'] ?? '-',
                'tgl_terima'     =>
                $data['tgl_terima'] ?? '-',
                'tgl_selesai'    =>
                $data['tgl_selesai'] ?? '-',
                'type'           =>
                $data['kategori'] ?? '-',
                'items'          =>
                $items,
                'mode'           =>
                'edit',
                'payments'       =>
                $data['payments'] ?? [],
                'checked_types'  =>
                $data['checked_types'] ?? [],
                // 🔥 HEADER DINAMIS
                'custom_headers' =>
                $data['custom_headers'] ?? [],
            ];
        }
        // =====================================================
        // CREATE MODE
        // =====================================================
        else {
            $po = Po::with('details')
                ->findOrFail($id);
            $noSpk =
                $this->generateNoSpk(
                    $po->order_no
                );
            // =========================
            // ITEMS
            // =========================
            $items = $po->details->map(function ($d) {
                $detail = $d->detail;
                $images = [];
                if (! empty($detail['photo'])) {
                    $images[] =
                        $detail['photo'];
                }
                return [
                    'kode'           =>
                    $detail['article_nr_'] ?? '-',
                    'detail_id'      =>
                    $d['id'] ?? '-',
                    'nama'           =>
                    $detail['description'] ?? '-',
                    // 🔥 DEFAULT CUSTOM
                    'custom_columns' => [],
                    'p'              =>
                    $detail['item_w'] ?? '-',
                    'l'              =>
                    $detail['item_d'] ?? '-',
                    't'              =>
                    $detail['item_h'] ?? '-',
                    'material'       =>
                    $detail['composition'] ?? '-',
                    'pcs'            =>
                    $detail['qty'] ?? 0,
                    'set'            =>
                    $detail['set'] ?? 0,
                    'harga'          =>
                    $detail['harga'] ?? 0,
                    'catatan'        =>
                    $d->remark_update ?? '',
                    'images'         =>
                    $images,
                ];
            })->values();
            // =========================
            // FINAL DATA
            // =========================
            $spk = [
                'id'             =>
                $po->id,
                'status'         =>
                'draft',
                'request_status' =>
                null,
                'no_spk'         =>
                $noSpk,
                'no_po'          =>
                $po->order_no,
                'nama'           =>
                $po->supplier_name ?? '-',
                'tgl_terima'     =>
                now()->format('d-M-Y'),
                'tgl_selesai'    =>
                $request->tgl_selesai,
                'type'           =>
                'rangka',
                'items'          =>
                $items,
                'payments'       =>
                [],
                'mode'           =>
                'create',
                'checked_types'  =>
                [],
                // 🔥 HEADER KOSONG
                'custom_headers' => [],
            ];
        }
//         dd([
//     'id' => $id,
//     'spk' => $spkModel
// ]);
        // dd([$spk,$jenis])
        return view(
            'pages.spk.index',
            compact(
                'spk',
                'jenisSuppliers',
                  'viewOnly'
            )
        );
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
        $tanggal = $now->format('m/Y');
        return "{$year}-{$urut}/{$noPo}/{$tanggal}";
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
            if ($mode === 'create' || $mode === 'edit') {
                $qtyPo = (int) ($detailPo->detail['qty'] ?? 0);
                $qtySpkExist = $this->getTotalSpkQtyByDetailPoAndKategori(
                    $detailPo->id,
                    $kategori,
                    $mode === 'edit' ? $spkId : null
                );

                // if (($qtySpkExist + $qty) > $qtyPo) {
                //     return response()->json([
                //         'success' => false,
                //         'message' => "Qty SPK melebihi Qty PO (Sisa: " . ($qtyPo - $qtySpkExist) . ")",
                //     ], 422);
                // }
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
                'detail_po_id'   => $detailPo->id,
                'kode'           => (string) ($item['kode'] ?? ''),
                'nama'           => (string) ($item['nama'] ?? ''),
                'qty'            => $qty,
                'satuan'         => $item['satuan'] ?? '',
                'material'       => (string) ($item['material'] ?? ''),
                'p'              => (string) ($item['p'] ?? ''),
                'l'              => (string) ($item['l'] ?? ''),
                't'              => (string) ($item['t'] ?? ''),
                'harga'          => (float) ($item['harga'] ?? 0),
                'total'          => (float) ($item['total'] ?? 0),
                'images'         => $itemImages,
                'catatan'        => [
                    'remark' => (string) ($item['catatan']['remark'] ?? ''),
                    'images' => $noteImages,
                ],
                'custom_columns' =>
                $item['custom_columns'] ?? [],
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
            'status'         =>
            $request->status ?? 'draft',
            'kategori'       => $kategori,
            'no_spk'         => $request->no_spk,
            'no_po'          => $request->no_po,
            'sup'            => $request->nama,
            'tgl_terima'     => $request->tgl_terima,
            'tgl_selesai'    => $request->tgl_selesai,
            'items'          => $finalItems,
            'payments'       => $request->payments ?? [],
            'checked_types'  =>
            $request->checked_types ?? [],
            // 🔥 HEADER DYNAMIC
            'custom_headers' =>
            $request->custom_headers ?? [],
        ];
//         dd([
//     'before' => $beforeData,
//     'after' => $afterData,
// ]);
        // =========================
        // CREATE / UPDATE
        // =========================
        if ($mode === 'create') {
            $spk = Spk::create([
                'po_id'      => $poId,
                    'status'         =>
            $request->status ?? 'draft',
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
            // $changes = $this->diffRecursive($beforeData, $afterData);
            try {

    $changes = $this->diffRecursive($beforeData, $afterData);

    // dd([
    //     'changes' => $changes
    // ]);

} catch (\Throwable $e) {

    dd([
        'error' => $e->getMessage(),
        'line'  => $e->getLine(),
        'file'  => $e->getFile(),
    ]);

}
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
    public function timeline($id)
    {
        $timelines = SpkTimeline::where('spk_id', $id)
            ->latest()
            ->get();
        return response()->json($timelines);
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
        $spreadsheet = IOFactory::load($templatePath);
        $sheet       = $spreadsheet->getActiveSheet();

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
        $items = $data['items'] ?? [];

        $row = 14;

        foreach ($items as $item) {

            /*
    |--------------------------------------------------------------------------
    | KATEGORI UTAMA
    |--------------------------------------------------------------------------
    */

            $kategoriUtama = '';

            if (
                !empty($item['custom_columns'][0]['kategori'])
            ) {
                $kategoriUtama =
                    $item['custom_columns'][0]['kategori'];
            }

            /*
    |--------------------------------------------------------------------------
    | ITEM UTAMA
    |--------------------------------------------------------------------------
    */

            $sheet->setCellValue(
                "A{$row}",
                $item['kode'] ?? ''
            );

            $sheet->setCellValue(
                "C{$row}",
                $item['nama'] ?? ''
            );

            $sheet->setCellValue(
                "D{$row}",
                $kategoriUtama
            );

            $sheet->setCellValue(
                "E{$row}",
                $item['p'] ?? ''
            );

            $sheet->setCellValue(
                "F{$row}",
                $item['l'] ?? ''
            );

            $sheet->setCellValue(
                "G{$row}",
                $item['t'] ?? ''
            );

            $sheet->setCellValue(
                "H{$row}",
                $item['material'] ?? ''
            );

            if (($item['satuan'] ?? '') === 'pcs') {

                $sheet->setCellValue(
                    "I{$row}",
                    $item['qty'] ?? ''
                );

                $sheet->setCellValue(
                    "J{$row}",
                    ''
                );
            } else {

                $sheet->setCellValue(
                    "I{$row}",
                    ''
                );

                $sheet->setCellValue(
                    "J{$row}",
                    $item['qty'] ?? ''
                );
            }

            $sheet->setCellValue(
                "K{$row}",
                $item['harga'] ?? ''
            );

            $sheet->setCellValue(
                "L{$row}",
                $item['total'] ?? ''
            );

            /*
    |--------------------------------------------------------------------------
    | IMAGE
    |--------------------------------------------------------------------------
    */

            if (!empty($item['images'][0])) {

                $this->insertImage(
                    $sheet,
                    $item['images'][0],
                    "B{$row}",
                    80
                );
            }

            $sheet->getRowDimension($row)
                ->setRowHeight(90);

            /*
    |--------------------------------------------------------------------------
    | DETAIL CUSTOM
    |--------------------------------------------------------------------------
    */

            $details = array_slice(
                $item['custom_columns'] ?? [],
                1
            );

            foreach ($details as $detail) {

                $row++;

                $sheet->setCellValue(
                    "D{$row}",
                    $detail['kategori'] ?? ''
                );

                $sheet->setCellValue(
                    "E{$row}",
                    $detail['p'] ?? ''
                );

                $sheet->setCellValue(
                    "F{$row}",
                    $detail['l'] ?? ''
                );

                $sheet->setCellValue(
                    "G{$row}",
                    $detail['t'] ?? ''
                );

                $sheet->setCellValue(
                    "H{$row}",
                    $detail['material'] ?? ''
                );

                $sheet->setCellValue(
                    "I{$row}",
                    $detail['pcs'] ?? ''
                );

                $sheet->setCellValue(
                    "J{$row}",
                    $detail['set'] ?? ''
                );

                $sheet->setCellValue(
                    "K{$row}",
                    $detail['harga'] ?? ''
                );

                $sheet->setCellValue(
                    "L{$row}",
                    $detail['total'] ?? ''
                );
            }

            $row++;
        }
        $totalRows = 0;

        foreach ($items as $item) {

            $totalRows++;

            $details = array_slice(
                $item['custom_columns'] ?? [],
                1
            );

            $totalRows += count($details);
        }

        /** =====================
         * PAYMENT / RINCIAN
         * ===================== */
        // posisi dinamis setelah item terakhir
        $paymentStartRow = $row + 2;
        $payments = $data['payments'] ?? [];
        // template style payment asli
        $paymentTemplateRow = $paymentStartRow;
        foreach ($payments as $i => $pay) {
            $r = $paymentStartRow + $i;
            // copy style row payment
            if ($i > 0) {
                $sheet->insertNewRowBefore($r, 1);
                $this->copyRowStyle(
                    $sheet,
                    $paymentTemplateRow,
                    $r
                );
            }
            /** =====================
             * ISI PAYMENT
             * ===================== */
            // checkbox req
            $sheet->setCellValue("J{$r}", '✓');
            // amount
            $sheet->setCellValue(
                "K{$r}",
                $pay['amount'] ?? 0
            );
            // date
            $sheet->setCellValue(
                "L{$r}",
                $pay['date'] ?? ''
            );
            // note
            $sheet->setCellValue(
                "M{$r}",
                $pay['note'] ?? ''
            );
            // keterangan
            $sheet->setCellValue(
                "N{$r}",
                $pay['keterangan'] ?? ''
            );
        }
        /** =====================
         * DOWNLOAD
         * ===================== */
        $safeNoSpk = preg_replace(
            '/[\/\\\\]/',
            '-',
            $data['no_spk'] ?? $spk->id
        );
        $filename = "SPK-{$safeNoSpk}.xlsx";
        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))
                ->save('php://output');
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
    public function getTotalSpkQtyByDetailPoAndKategori(
        int $detailPoId,
        string $kategori,
        ?int $excludeSpkId = null
    ) {
        return Spk::where('data->kategori', $kategori)
            ->when($excludeSpkId, function ($q) use ($excludeSpkId) {
                $q->where('id', '!=', $excludeSpkId);
            })
            ->get()
            ->sum(function ($spk) use ($detailPoId) {
                return collect($spk->data['items'] ?? [])
                    ->where('detail_po_id', $detailPoId)
                    ->sum(fn($i) => (int) ($i['qty'] ?? 0));
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
public function spk(Request $request)
    {
         $isRndSpk = $request->spk === 'rnd_spk';
        return view('pages.spk.all', compact('isRndSpk'));
    }
    public function allspk()
    {
        $poList   = Po::all();
        $spks     = Spk::all();
        $detailPo = DetailPo::all();
        $result = $poList->map(function ($po) use ($spks, $detailPo) {
            // =========================
            // SPK per PO
            // =========================
            $spkList = $spks
                ->where('po_id', $po->id)
                ->map(function ($spk) {
                    return [
                        'id'   => $spk->id,
                        'data' => $spk->data,
                    ];
                });
            // =========================
            // DETAIL PO ITEMS
            // =========================
            $items = $detailPo
                ->where('po_id', $po->id)
                ->map(function ($item) use ($spkList) {
                    $detail = is_string($item->detail)
                        ? json_decode($item->detail, true)
                        : $item->detail;
                    /**
                     * STRUKTUR:
                     * kategori → supplier → total_qty
                     */
                    $summary = [];
                    foreach ($spkList as $spk) {
                        $spkId = $spk['id'];         // ✅ AMAN
                        $data  = $spk['data'] ?? []; // 🔥 KUNCI UTAMA
                        $supplier = $data['sup'] ?? '-';
                        $kategori = $data['kategori'] ?? '-';
                        $noSpk    = $data['no_spk'] ?? '-';
                        foreach ($data['items'] ?? [] as $spkItem) {
                            if (
                                isset($spkItem['detail_po_id']) &&
                                $spkItem['detail_po_id'] == $item->id
                            ) {
                                $qty = (int) ($spkItem['qty'] ?? 0);
                                // init kategori
                                if (! isset($summary[$kategori])) {
                                    $summary[$kategori] = [];
                                }
                                // init supplier
                                if (! isset($summary[$kategori][$supplier])) {
                                    $summary[$kategori][$supplier] = [
                                        'total_qty' => 0,
                                        'spks'      => [],
                                    ];
                                }
                                // total qty supplier
                                $summary[$kategori][$supplier]['total_qty'] += $qty;
                                // detail per SPK
                                $summary[$kategori][$supplier]['spks'][] = [
                                    'spk_id'      => $spkId,
                                    'no_spk'      => $noSpk,
                                    'qty'         => $qty,
                                    'tgl_selesai' => $data['tgl_selesai'] ?? null, // 🔥 INI
                                ];
                            }
                        }
                    }
                    return [
                        'id'      => $item->id,
                        'detail'  => $detail,
                        'summary' => $summary,
                    ];
                })
                ->values();
            return [
                'data_po' => [
                    'id'      => $po->id,
                    'no_po'   => $po->order_no,
                    'company' => $po->company_name,
                    'items'   => $items,
                ],
            ];
        });
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
    public function getData(Request $request)
    {
        $poId     = $request->po_id;
        $detailId = $request->detail_po_id;
        $kategori = strtolower($request->kategori);
        // 🔥 ambil semua SPK dalam PO
        $spks = Spk::where('po_id', $poId)->get();
        $supplierIds = [];
        $spkInfo     = [];
        $allSpk      = [];
        foreach ($spks as $spk) {
            $data = is_array($spk->data)
                ? $spk->data
                : json_decode($spk->data, true);
            $items = collect($data['items'] ?? []);
            // 🔥 filter detail_po_id
            $item = $items->firstWhere('detail_po_id', $detailId);
            if (! $item) {
                continue; // ❗ skip kalau bukan item ini
            }
            $supplier = Supplier::where('name', $data['sup'])->first();
            if (! $supplier) {
                continue;
            }
            $kategoriSpk = strtolower($data['kategori']);
            // ================= IN (kategori sekarang)
            if ($kategoriSpk === $kategori) {
                $supplierIds[] = $supplier->id;
                $spkInfo[] = [
                    'sup_id' => $supplier->id,
                    'sup'    => $supplier->name,
                    'no_spk' => $data['no_spk'],
                    'qty'    => $item['qty'] ?? 0,
                    'spk_id' => $spk->id,
                ];
            }
            // ================= OUT (semua kategori tapi tetap detail_po_id sama)
            $allSpk[] = [
                'spk_id'   => $spk->id,
                'sup_id'   => $supplier->id,
                'sup_name' => $supplier->name,
                'kategori' => $kategoriSpk,
                'no_spk'   => $data['no_spk'],
                'qty'      => $item['qty'] ?? 0,
            ];
        }
        // 🔥 supplier dropdown (IN)
        $suppliers = Supplier::whereIn('id', $supplierIds)
            ->select('id', 'name')
            ->get();
        // 🔥 timeline
        $timeline = ProductionTimeline::where('po_id', $poId)
            ->where('detail_po_id', $detailId)
            ->where('process', $kategori)
            ->get();
        return response()->json([
            'items'     => $timeline,
            'suppliers' => $suppliers,
            'spk_info'  => $spkInfo,
            'all_spk'   => $allSpk,
        ]);
    }
    public function saveData(Request $request)
    {
        DB::beginTransaction();
        try {
            $poId     = $request->po_id;
            $detailId = $request->detail_po_id;
            $process  = strtolower($request->process);
            // 🔥 delete dulu biar tidak double
            ProductionTimeline::where('po_id', $poId)
                ->where('detail_po_id', $detailId)
                ->where('process', $process)
                ->delete();
            // ================= IN =================
            foreach ($request->in ?? [] as $row) {
                if (empty($row['qty'])) {
                    continue;
                }
                ProductionTimeline::create([
                    'po_id'        => $poId,
                    'detail_po_id' => $detailId,
                    'process'      => $process,
                    'type'         => 'IN',
                    'sup_id'       => $row['supplier'],
                    'qty'          => $row['qty'],
                    'date'         => $row['tgl'],
                    'remark'       => $row['remark'] ?? '-',
                    'spk_id'       => $row['spk_id'],
                    'next_process' => null,
                ]);
            }
            // ================= OUT =================
            foreach ($request->out ?? [] as $row) {
                if (empty($row['qty'])) {
                    continue;
                }
                ProductionTimeline::create([
                    'po_id'        => $poId,
                    'detail_po_id' => $detailId,
                    'process'      => $process,
                    'type'         => 'OUT',
                    'sup_id'       => $row['supplier'], // 🔥 tujuan supplier
                    'qty'          => $row['qty'],
                    'date'         => now(),
                    'remark'       => $row['remark'] ?? '-',
                    'spk_id'       => $row['spk_id'], // 🔥 tujuan spk
                    'next_process' => $row['next_process'],
                ]);
            }
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function getDetailBarang(Request $request)
    {
        $po_id        = $request->po_id;
        $detail_po_id = $request->detail_po_id;
        $spks = Spk::where('po_id', $po_id)->get();
        $result = [];
        foreach ($spks as $spk) {
            $data = $spk->data;
            foreach ($data['items'] as $item) {
                if ($item['detail_po_id'] == $detail_po_id) {
                    $supplier = Supplier::where('name', $data['sup'])->first();
                    $result[] = [
                        'spk_id'   => $spk->id,
                        'supplier' => [
                            'id'   => $supplier?->id,
                            'name' => $data['sup'],
                        ],
                        'kategori' => $data['kategori'],
                        'item'     => $item,
                    ];
                }
            }
        }
        // =========================
        // 🔥 AMBIL LOG PRODUKSI
        // =========================
        $logs = DB::table('production_timeline as pt')
            ->leftJoin('suppliers as s', 's.id', '=', 'pt.sup_id')
            ->where('pt.po_id', $po_id)
            ->where('pt.detail_po_id', $detail_po_id)
            ->select(
                'pt.date',
                DB::raw("TIME_FORMAT(pt.created_at, '%H:%i') as time"),
                'pt.type',
                'pt.process',
                'pt.next_process',
                'pt.qty',
                's.name as supplier',
                'pt.remark'
            )
            ->orderBy('pt.created_at', 'desc')
            ->get();
        return response()->json([
            'data' => $result,
            'logs' => $logs,
        ]);
    }
    // arsip udah bener
    public function saveProcess(Request $request)
    {
        // =========================
        // VALIDASI BASIC
        // =========================
        $datetime = Carbon::parse($request->date . ' ' . $request->time);
        $request->validate([
            'po_id'        => 'required',
            'detail_po_id' => 'required',
            'qty'          => 'required',
            'type'         => 'required',
            'process'      => 'required',
        ]);
        // =========================
        // CLEAN QTY
        // =========================
        $qty = (int) str_replace(',', '', $request->qty);
        if ($qty <= 0) {
            return response()->json([
                'status'  => false,
                'message' => 'Qty tidak valid',
            ], 422);
        }
        $detail_po_id = $request->detail_po_id;
        $process      = $request->process;
        $type         = $request->type;
        $spk_id       = $request->spk_id;
        // =========================
        // 🔥 VALIDASI MAX SPK (MASUK SUPPLIER + RETURN SERVICE)
        // =========================
        if ($type === 'masuk' && $spk_id) {
            // total masuk
            $totalMasuk = DB::table('production_timeline')
                ->where('detail_po_id', $detail_po_id)
                ->where('spk_id', $spk_id)
                ->where('type', 'masuk')
                ->sum('qty');
            // 🔥 total service (lock sementara)
            $totalService = DB::table('production_timeline')
                ->where('detail_po_id', $detail_po_id)
                ->where('spk_id', $spk_id)
                ->where('type', 'service')
                ->sum('qty');
            // 🔥 effective masuk
            $effectiveMasuk = $totalMasuk - $totalService;
            // ambil data SPK
            $spk = Spk::find($spk_id);
            if (! $spk) {
                return response()->json([
                    'status'  => false,
                    'message' => 'SPK tidak ditemukan',
                ], 422);
            }
            $data = $spk->data;
            $maxQty = collect($data['items'])
                ->where('detail_po_id', $detail_po_id)
                ->first()['qty'] ?? 0;
            if (($effectiveMasuk + $qty) > $maxQty) {
                $sisa = $maxQty - $effectiveMasuk;
                return response()->json([
                    'status'  => false,
                    'message' => "Qty melebihi SPK. Sisa hanya {$sisa}",
                ], 422);
            }
        }
        // =========================
        // 🔥 VALIDASI STOK (KELUAR & SERVICE)
        // =========================
        if (in_array($type, ['keluar', 'service'])) {
            // total masuk
            // $totalIn = DB::table('production_timeline')
            //     ->where('detail_po_id', $detail_po_id)
            //     ->where('process', $process)
            //     ->where('type', 'masuk')
            //     ->sum('qty');
            $totalIn = DB::table('production_timeline')
                ->where('detail_po_id', $detail_po_id)
                ->where(function ($q) use ($process) {
                    // masuk langsung
                    $q->where(function ($q2) use ($process) {
                        $q2->where('process', $process)
                            ->where('type', 'masuk');
                    });
                    // 🔥 dari process lain (next_process)
                    $q->orWhere(function ($q2) use ($process) {
                        $q2->where('next_process', $process)
                            ->where('type', 'keluar');
                    });
                })
                ->sum('qty');
            // total keluar
            $totalOut = DB::table('production_timeline')
                ->where('detail_po_id', $detail_po_id)
                ->where('process', $process)
                ->where('type', 'keluar')
                ->sum('qty');
            // 🔥 total service (lock)
            $totalService = DB::table('production_timeline')
                ->where('detail_po_id', $detail_po_id)
                ->where('process', $process)
                ->where('type', 'service')
                ->sum('qty');
            $available = $totalIn - $totalOut - $totalService;
            if ($qty > $available) {
                return response()->json([
                    'status'  => false,
                    'message' => "Qty melebihi stok tersedia ({$available})",
                ], 422);
            }
        }
        DB::table('production_timeline')->insert([
            'po_id'        => $request->po_id,
            'spk_id'       => $request->spk_id,
            'detail_po_id' => $detail_po_id,
            'qty'          => $qty,
            'sup_id'       => $request->supplier_id,
            'date'         => $request->date,
            'type'         => $type,
            'process'      => $request->process,
            'next_process' => $request->next_process,
            'source_type'  => $request->source_type,
            'remark'       => $request->remark,
            'created_at'   => $datetime,
            'updated_at'   => $datetime,
        ]);
        return response()->json([
            'status'  => true,
            'message' => 'Berhasil disimpan',
        ]);
    }
    public function getTimeline(Request $request)
    {
        $po_id = $request->po_id;
        $timeline = ProductionTimeline::select(
            'detail_po_id',
            'process',
            'type',
            'qty',
            'next_process'
        )
            ->where('po_id', $po_id)
            ->get();
        return response()->json($timeline);
    }
    public function getQc(Request $request)
    {
        $po_id = $request->po_id;
        $qc = \App\Models\InspectSchedule::with('kategori')
            ->where('po_id', $po_id)
            ->get()
            ->map(function ($item) {
                return [
                    'detail_po_id'   => $item->detail_po_id,
                    'jumlah_inspect' => $item->jumlah_inspect,
                    'passed'         => $item->passed,
                    'rejected'       => $item->rejected,
                    'tanggal'        => $item->tanggal_inspect,
                    // 🔥 langsung ambil dari relasi
                    'kategori'       => strtolower(trim($item->kategori?->kategori ?? '')),
                ];
            });
        return response()->json($qc);
    }
    private function getService()
    {
        $client = new Client();
        $client->setAuthConfig(storage_path('app/google-calendar.json'));
        $client->addScope(Calendar::CALENDAR);
        return new Calendar($client);
    }
    /**
     * ===============================
     * TEST DUMMY EVENT
     * ===============================
     */
    public function paymentstore(Request $request)
    {
        DB::beginTransaction();
        try {
            $spk = Spk::findOrFail(
                $request->spk_id
            );
            $pay = $request->payment;
            $data = $spk->data;
            $payments =
                collect($data['payments'] ?? []);
            // =========================
            // FORMAT DATE
            // =========================
            $paymentDate = null;
            if (! empty($pay['date'])) {
                try {
                    $paymentDate =
                        strlen($pay['date']) == 8
                        ? Carbon::createFromFormat(
                            'd/m/y',
                            $pay['date']
                        )
                        : Carbon::createFromFormat(
                            'd/m/Y',
                            $pay['date']
                        );
                } catch (\Exception $e) {
                    $paymentDate = null;
                }
            }
            // =====================================================
            // UNCHECK
            // =====================================================
            if (! $pay['is_request']) {
                // =========================
                // FIND PR BY PAYMENT ID
                // =========================
                $pr = PaymentRequest::where(
                    'payment_id',
                    $pay['payment_id']
                )->first();
                // =========================
                // DELETE PR
                // =========================
                if ($pr) {
                    // sementara bebas delete dulu
                    $pr->delete();
                }
                // =========================
                // UPDATE JSON
                // =========================
                $updatedPayments =
                    $payments->map(function ($item)
                    use ($pay) {
                        if (
                            $item['payment_id']
                            ==
                            $pay['payment_id']
                        ) {
                            $item['is_request'] = false;
                            $item['pr_id'] = null;
                        }
                        return $item;
                    })
                    ->values()
                    ->toArray();
                $data['payments'] =
                    $updatedPayments;
                $spk->update([
                    'data' => $data,
                ]);
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' =>
                    'Request dibatalkan',
                ]);
            }
            // =====================================================
            // CHECKLIST
            // =====================================================
            $currentPayment =
                $payments->firstWhere(
                    'payment_id',
                    $pay['payment_id']
                );
            // =========================
            // SUDAH ADA PR?
            // =========================
            if (
                ! empty($currentPayment['pr_id'])
            ) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' =>
                    'PR sudah ada',
                ]);
            }
            // =========================
            // CREATE PR
            // =========================
            $pr = PaymentRequest::create([
                'spk_id'       =>
                $spk->id,
                'payment_id'   =>
                $pay['payment_id'],
                'request_no'   =>
                $this->generateRequestNo(),
                'no_spk'       =>
                $request->no_spk,
                'no_po'        =>
                $spk->data['no_po'] ?? null,
                'supplier'     =>
                $spk->data['sup'] ?? null,
                'kategori'     =>
                $spk->data['kategori'] ?? null,
                // PAYMENT
                'payment_type' =>
                $pay['note'],
                // 'total_amount' =>
                // (int) $pay['amount'],
                'payment_date' =>
                $paymentDate,
                'note'         =>
                $pay['note_tambahan'] ?? null,
                // REQUEST
                'request_date' =>
                now(),
                'status'       =>
                'draft',
                'created_by'   =>
                auth()->id(),
                'spk_snapshot' =>
                $spk->data,
            ]);
            // =========================
            // UPDATE JSON
            // =========================
            $updatedPayments =
                $payments->map(function ($item)
                use ($pay, $pr) {
                    if (
                        $item['payment_id']
                        ==
                        $pay['payment_id']
                    ) {
                        $item['is_request'] = true;
                        $item['pr_id'] =
                            $pr->id;
                    }
                    return $item;
                })
                ->values()
                ->toArray();
            $data['payments'] =
                $updatedPayments;
            $spk->update([
                'data' => $data,
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' =>
                'PR berhasil dibuat',
                'pr_id'   => $pr->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function saveDraftRequest(
        Request $request
    ) {
        DB::beginTransaction();
        try {
            $ids = collect($request->ids)
                ->unique()
                ->values();
            $requests =
                PaymentRequest::with([
                    'items',
                    'spk',
                ])
                ->whereIn(
                    'id',
                    $ids
                )
                ->get();
            foreach ($requests as $row) {
                // =====================
                // UPDATE REQUEST
                // =====================
                $row->update([
                    'request_date' =>
                    $request->request_date,
                    'need_date'    =>
                    $request->need_date,
                    // DRAFT -> PENDING
                    'status'       =>
                    'pending',
                ]);
                // =====================
                // UPDATE ITEMS
                // =====================
                $row->items()->update([
                    'status' => 'waiting',
                ]);
                // =====================
                // REMOVE CHECKED TYPES
                // =====================
                $spk = $row->spk;
                if ($spk) {
                    $data = $spk->data;
                    $currentChecked =
                        $data['checked_types'] ?? [];
                    $selectedTypes =
                        $row->checked_types ?? [];
                    // hapus yg sudah diproses
                    $data['checked_types'] = array_values(
                        array_diff(
                            $currentChecked,
                            $selectedTypes
                        )
                    );
                    $spk->update([
                        'data' => $data,
                    ]);
                }
                // =====================
                // SIGNATURE DEFAULT
                // =====================
                $roles = [
                    'made_by',
                    'purchasing',
                    'prod_manager',
                    'ceo',
                    'vp_sales',
                    'finance',
                    'hrd',
                    'coo',
                ];
                foreach ($roles as $role) {
                    PaymentRequestSignature::firstOrCreate([
                        'payment_request_id' =>
                        $row->id,
                        'role'               =>
                        $role,
                    ], [
                        'status'    =>
                        $role == 'made_by'
                            ? 'approved'
                            : 'pending',
                        'signed_at' =>
                        $role == 'made_by'
                            ? now()
                            : null,
                        'user_id'   =>
                        auth()->id(),
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' =>
                'Request berhasil diajukan',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
   public function draftr()
{
    $authUser = auth()->user();

    $kepalaPurchasing = Karyawan::whereHas('divisi', function ($q) {
        $q->where('nama', 'KEPALA PURCHASING');
    })->first();

    $prodManager = Karyawan::whereHas('divisi', function ($q) {
        $q->where('nama', 'PROD MANAGER');
    })->first();

    $ceo = Karyawan::whereHas('divisi', function ($q) {
        $q->where('nama', 'CEO');
    })->first();

    $vpSales = Karyawan::whereHas('divisi', function ($q) {
        $q->where('nama', 'VP SALES & MARKETING');
    })->first();

    $finance = Karyawan::whereHas('divisi', function ($q) {
        $q->where('nama', 'FINANCE ACC');
    })->first();

    $hrd = Karyawan::whereHas('divisi', function ($q) {
        $q->where('nama', 'HRD GA & SHE');
    })->first();

    $coo = Karyawan::whereHas('divisi', function ($q) {
        $q->where('nama', 'CO');
    })->first();

    /*
    |--------------------------------------------------------------------------
    | REQUEST DRAFT
    |--------------------------------------------------------------------------
    */
    $requests = PaymentRequest::with('spk')
        ->where('status', 'draft')
        ->latest()
        ->get()
        ->map(function ($request) {

            if (!$request->spk) {

                Log::warning('SPK NOT FOUND', [
                    'payment_request_id' => $request->id,
                    'spk_id' => $request->spk_id
                ]);

                return null;
            }

            $spkData = is_string($request->spk->data)
                ? json_decode($request->spk->data, true)
                : ($request->spk->data ?? []);

            $payment = collect(
                $spkData['payments'] ?? []
            )->firstWhere(
                'payment_id',
                $request->payment_id
            );

            $items = collect(
                $spkData['items'] ?? []
            )->map(function ($item) {

                $mainTotal = (float) ($item['total'] ?? 0);

                $extraTotal = collect(
                    $item['custom_columns'] ?? []
                )->sum(function ($row) {
                    return (float) ($row['total'] ?? 0);
                });

                return [
                    'nama'  => $item['nama'] ?? '-',
                    'kode'  => $item['kode'] ?? '-',
                    'qty'   => $item['qty'] ?? 0,
                    'harga' => $item['harga'] ?? 0,
                    'total' => $mainTotal + $extraTotal,
                ];
            });

            return [
                'id' => $request->id,
                'request_no' => $request->request_no,
                'payment_id' => $request->payment_id,
                'status' => $request->status,
                'request_date' => $request->request_date,
                'need_date' => $request->need_date,

                'spk_id' => $request->spk_id,
                'spk_no' => $spkData['no_spk'] ?? '-',
                'no_po' => $spkData['no_po'] ?? '-',
                'supplier' => $spkData['sup'] ?? '-',
                'kategori' => $spkData['kategori'] ?? '-',
                'tgl_terima' => $spkData['tgl_terima'] ?? '-',
                'tgl_selesai' => $spkData['tgl_selesai'] ?? '-',

                'payment_note' => $payment['note'] ?? '-',
                'payment_amount' => $payment['amount'] ?? 0,
                'payment_date' => $payment['date'] ?? null,
                'payment_is_request' => $payment['is_request'] ?? false,
                'note_tambahan' => $payment['note_tambahan'] ?? null,

                'items' => $items,
                'grand_total_spk' => $items->sum('total'),
            ];
        })
        ->filter()
        ->values();

    /*
    |--------------------------------------------------------------------------
    | SAVED DRAFT
    |--------------------------------------------------------------------------
    */
    $draftRequests = PaymentRequestSaved::latest()
        ->get()
        ->map(function ($draft) {

            $paymentRequests = PaymentRequest::with('spk')
                ->whereIn(
                    'id',
                    $draft->payment_request_ids ?? []
                )
                ->get()
                ->map(function ($request) {

                    if (!$request->spk) {

                    Log::warning('SPK NOT FOUND IN DRAFT', [
                            'payment_request_id' => $request->id,
                            'spk_id' => $request->spk_id
                        ]);

                        return null;
                    }

                    $spkData = is_string($request->spk->data)
                        ? json_decode($request->spk->data, true)
                        : ($request->spk->data ?? []);

                    $payment = collect(
                        $spkData['payments'] ?? []
                    )->firstWhere(
                        'payment_id',
                        $request->payment_id
                    );

                    return [
                        'id' => $request->id,
                        'payment_id' => $request->payment_id,
                        'request_no' => $request->request_no,
                        'spk_no' => $spkData['no_spk'] ?? '-',
                        'no_po' => $spkData['no_po'] ?? '-',
                        'supplier' => $spkData['sup'] ?? '-',
                        'kategori' => $spkData['kategori'] ?? '-',
                        'payment_note' => $payment['note'] ?? '-',
                        'payment_amount' => (float) ($payment['amount'] ?? 0),
                    ];
                })
                ->filter()
                ->values();
        $approval = PaymentRequestApproval::where(
                'payment_request_saved_id',
                $draft->id
            )
            ->where('status', 'Pending')
            ->orderBy('step')
            ->first();
            return [
                'id' => $draft->id,
                'request_no' => $draft->request_no,
                'request_date' => $draft->request_date,
                'need_date' => $draft->need_date,
                'status' => $draft->status,
                'grand_total' => $paymentRequests->sum('payment_amount'),
                'total_items' => $paymentRequests->count(),
                'items' => $paymentRequests,
                'pending_sign'  => $approval->role ?? '-',

            ];
        });

    return view(
        'pages.payment_request.draft',
        compact(
            'requests',
            'draftRequests',
            'authUser',
            'kepalaPurchasing',
            'prodManager',
            'ceo',
            'vpSales',
            'finance',
            'hrd',
            'coo'
        )
    );
}
    public function changeStatus(
        Request $request,
        Spk $spk
    ) {
        try {
            $status = $request->status;
            // =========================
            // VALIDASI
            // =========================
            if (! in_array($status, [
                'draft',
                'progress',
                'finished',
                'closed',
            ])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status tidak valid',
                ], 422);
            }
            // =========================
            // UPDATE STATUS
            // =========================
            $spk->status = $status;
            // FINISH
            if ($status == 'finished') {
                $spk->finished_at = now();
                $spk->finished_by = auth()->id();
            }
            // CLOSED
            if ($status == 'closed') {
                $spk->finished_at =
                    $spk->finished_at ?? now();
                $spk->finished_by =
                    $spk->finished_by ?? auth()->id();
            }
            $spk->save();
            return response()->json([
                'success' => true,
                'message' =>
                'Status berhasil diubah',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function saveDraftGroup(
        Request $request
    ) {

        $request->validate([

            'ids' => 'required|array',

            'request_date' =>
            'required|date',

            'need_date' =>
            'required|date',
        ]);

        $paymentRequests =
            PaymentRequest::whereIn(
                'id',
                $request->ids
            )->get();

        $grandTotal = 0;

        foreach ($paymentRequests as $paymentRequest) {

            $spkData = is_string(
                $paymentRequest->spk->data
            )
                ? json_decode(
                    $paymentRequest->spk->data,
                    true
                )
                : $paymentRequest->spk->data;

            $payment = collect(
                $spkData['payments'] ?? []
            )->firstWhere(
                'payment_id',
                $paymentRequest->payment_id
            );

            $grandTotal += (float)(
                $payment['amount'] ?? 0
            );
        }

        $saved =
            PaymentRequestSaved::create([

                'request_no' =>
                'DR-' .
                    now()->format('ymdHis'),

                'request_date' =>
                $request->request_date,

                'need_date' =>
                $request->need_date,

                'payment_request_ids' =>
                $request->ids,

                'grand_total' =>
                $grandTotal,

                'status' =>
                'Diajukan',

                'created_by' =>
                auth()->id(),
            ]);
            // user tap
        $approvers = [

            [
                'user_id' => 171,
                'step' => 1,
                'role' => 'Head Purchasing'
            ],

            [
                'user_id' => 178,
                'step' => 2,
                'role' => 'Production Manager'
            ],
             [
                'user_id' => 191,
                'step' => 3,
                'role' => 'Director'
            ],
             [
                'user_id' => 141,
                'step' => 4,
                'role' => 'General Manager'
            ],
            [
                'user_id' => 134,
                'step' => 5,
                'role' => 'Finance'
            ],


            [
                'user_id' => 190,
                'step' => 6,
                'role' => 'CEO'
            ],

        ];

        foreach ($approvers as $approval) {

            PaymentRequestApproval::create([

                'payment_request_saved_id' => $saved->id,

                'user_id' => $approval['user_id'],

                'step' => $approval['step'],

                'role' => $approval['role'],

                'status' => 'Pending'

            ]);
        }
        PaymentRequest::whereIn(
            'id',
            $request->ids
        )->update([

            'status' =>
            'saved'
        ]);

        return response()->json([

            'success' => true,

            'message' =>
            'Draft berhasil dibuat',

            'id' =>
            $saved->id,
        ]);
    }
    public function detailDraft($id)
    {
        $draft = PaymentRequestSaved::findOrFail($id);
        $approvals = PaymentRequestApproval::with('user')
            ->where(
                'payment_request_saved_id',
                $draft->id
            )
            ->orderBy('step')
            ->get();
        $items = PaymentRequest::with('spk')
            ->whereIn(
                'id',
                $draft->payment_request_ids ?? []
            )
            ->get()
            ->map(function ($request) {

                $spkData = is_string(
                    $request->spk->data
                )
                    ? json_decode(
                        $request->spk->data,
                        true
                    )
                    : $request->spk->data;

                $payment = collect(
                    $spkData['payments'] ?? []
                )->firstWhere(
                    'payment_id',
                    $request->payment_id
                );

                return [

                    'supplier' =>
                    $spkData['sup'] ?? '-',
                    'spk_id' =>
                    $request->spk_id,

                    'no_po' =>
                    $spkData['no_po'] ?? '-',

                    'spk_no' =>
                    $spkData['no_spk'] ?? '-',

                    'payment_note' =>
                    $payment['note'] ?? '-',
                     'payment_id' => $request->payment_id,
                    'adjustment' =>
                        $payment['adjustment'] ?? 0,
                    'payment_amount' =>
                    (float) (
                        $payment['amount'] ?? 0
                    ),
                    'payment_request_amount' =>
                    !empty($payment['adjustment'])
                        ? (float)$payment['adjustment']
                        : (float)($payment['amount'] ?? 0),
                ];
            });

        return response()->json([

            'id' =>
            $draft->id,

            'request_no' =>
            $draft->request_no,
            'request_date' =>
            $draft->request_date,
            'need_date' =>
            $draft->need_date,
            'grand_total' =>
            $draft->grand_total,

            'items' =>
            $items,
            'is_finance' => auth()->id() == 134,
           'approvals' => $approvals->map(function ($row) {

            return [

                'id' => $row->id,

                'user_id' => $row->user_id,

                'name' => $row->user->name,

                'role' => $row->role,

                'signature' => $row->user->signature,

                'status' => $row->status,

              'approved_at' => $row->approved_at
                ? $row->approved_at->format('d/m/Y H:i')
                : null,

                'can_approve' =>
                    auth()->id() == $row->user_id
                    &&
                    $row->status == 'Pending',

            ];

        }),

        ]);
    }
    // payment request draft
    private function generateRequestNo()
    {
        $now = now();
        $year = $now->format('y'); // 26
        // =========================
        // AMBIL REQUEST TERAKHIR
        // =========================
        $last = PaymentRequest::where(
            'request_no',
            'like',
            "PR/NW/{$year}/%"
        )
            ->latest('id')
            ->first();
        $nextNumber = 1;
        if ($last) {
            preg_match(
                '/PR\/NW\/\d{2}\/(\d{4})/',
                $last->request_no,
                $match
            );
            if (isset($match[1])) {
                $nextNumber =
                    ((int) $match[1]) + 1;
            }
        }
        // =========================
        // FORMAT 0001
        // =========================
        $urut = str_pad(
            $nextNumber,
            4,
            '0',
            STR_PAD_LEFT
        );
        // =========================
        // RESULT
        // =========================
        return "PR/NW/{$year}/{$urut}";
    }
    public function calendar()
    {
        $service = $this->getService();
        // ✅ pakai calendar ID kamu yang sudah benar
        $calendarId = '824e23d84ab88f2e4279aba16457256aca6caddd108e8b1118a6756f3dd0920b@group.calendar.google.com';
        // waktu dummy
        $start = now()->addMinutes(2);
        $end   = now()->addHour();
        $event = new Event([
            'summary'     => '🔥 SPK - Waya',
            'description' => 'Deadline produksi',
            'start'       => [
                'dateTime' => $start->format('Y-m-d\TH:i:s'),
                'timeZone' => 'Asia/Jakarta',
            ],
            'end'         => [
                'dateTime' => $end->format('Y-m-d\TH:i:s'),
                'timeZone' => 'Asia/Jakarta',
            ],
            'reminders'   => [
                'useDefault' => false,
                'overrides'  => [
                    ['method' => 'popup', 'minutes' => 0], // langsung notif
                ],
            ],
        ]);
        $created = $service->events->insert($calendarId, $event);
        return response()->json([
            'status'   => 'success',
            'event_id' => $created->getId(),
            'link'     => $created->htmlLink,
        ]);
    }
    public function addCalendar()
    {
        $service = $this->getService();
        $calendarId = '824e23d84ab88f2e4279aba16457256aca6caddd108e8b1118a6756f3dd0920b@group.calendar.google.com';
        $entry = new \Google\Service\Calendar\CalendarListEntry();
        $entry->setId($calendarId);
        $service->calendarList->insert($entry);
        return 'Calendar berhasil ditambahkan';
    }
    public function preview($id)
{
    $spk = Spk::findOrFail($id);

    return view(
        'pages.spk.preview',
        compact('spk')
    );
}
    public function submitSignature(Request $request, $id)
    {
        $spk = Spk::findOrFail($id);

        $data = is_string($spk->data)
            ? json_decode($spk->data, true)
            : $spk->data;

        $supplier = Supplier::where(
            'name',
            $data['sup'] ?? ''
        )->first();

        SignatureSpk::updateOrCreate(

            [
                'spk_id' => $spk->id
            ],

            [
                'supplier_id' => $supplier?->id,

                'made_by' => auth()->id(),
                'made_at' => now(),
                'made_remark' => $request->remark,

                'checked_by' => 171,
                'checked_by_2' => 178,
                'checked_at' => null,
                'checked_at_2' => null,
                'checked_remark' => null,
                'checked_2_remark' => null,
                'approved_by' => 191,
                'approved_at' => null,
                'approved_remark' => null,
            ]
        );

        $spk->update([
            'status' => 'diajukan'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'SPK berhasil diajukan'
        ]);
    }
    // approve sign in pengajuan spk
  public function approve($id)
{
        $urutan = false;

    $approval =
        PaymentRequestApproval::findOrFail($id);

    // hanya user yang berhak
    if (
        $approval->user_id != auth()->id()
    ) {

        return response()->json([

            'success' => false,

            'message' =>
                'Anda tidak memiliki hak approval'

        ], 403);
    }

    // sudah approve
    if (
        $approval->status == 'Approved'
    ) {

        return response()->json([

            'success' => false,

            'message' =>
                'Data sudah di approve'

        ], 422);
    }
     if ($urutan) {

        $previous = PaymentRequestApproval::where(
                'payment_request_saved_id',
                $approval->payment_request_saved_id
            )
            ->where('step', $approval->step - 1)
            ->first();

        if ($previous && $previous->status != 'Approved') {

            return response()->json([
                'success' => false,
                'message' => 'Menunggu approval sebelumnya'
            ], 422);
        }
    }

    // approve
    $approval->update([

        'status' => 'Approved',

        'approved_at' => now()

    ]);
    // finn
    // FINANCE APPROVAL
if ($approval->user_id == 174) {

    $draft = PaymentRequestSaved::find(
        $approval->payment_request_saved_id
    );

    $paymentRequests = PaymentRequest::whereIn(
        'id',
        $draft->payment_request_ids ?? []
    )->get();

    foreach ($paymentRequests as $pr) {

        $spk = Spk::find($pr->spk_id);

        if (!$spk) {
            continue;
        }

        $data = is_string($spk->data)
            ? json_decode($spk->data, true)
            : $spk->data;

        foreach ($data['payments'] as &$payment) {

            if (
                ($payment['payment_id'] ?? null)
                == $pr->payment_id
            ) {

                $payment['finance_approved'] = true;
                $payment['finance_approved_at'] = now()
                    ->format('Y-m-d H:i:s');
            }
        }

        $spk->update([
            'data' => $data
        ]);
    }
}

    // cek apakah semua sudah approve
    $draft =
        PaymentRequestSaved::find(
            $approval->payment_request_saved_id
        );

    $pendingCount =
        PaymentRequestApproval::where(
            'payment_request_saved_id',
            $draft->id
        )
        ->where(
            'status',
            'Pending'
        )
        ->count();

    if ($pendingCount == 0) {

        $draft->update([

            'status' => 'Approved'

        ]);

        PaymentRequest::whereIn(
            'id',
            $draft->payment_request_ids ?? []
        )->update([

            'status' => 'Approved'

        ]);
    }

    return response()->json([

        'success' => true,

        'message' =>
            'Approval berhasil disimpan'

    ]);
}
// finance adusment
    public function financeAdjustment(
        Request $request
    )
    {
        $spk = Spk::findOrFail(
            $request->spk_id
        );

        $data = is_string(
            $spk->data
        )
            ? json_decode(
                $spk->data,
                true
            )
            : $spk->data;

        foreach (
            $data['payments']
            as &$payment
        ) {

            if (
                $payment['payment_id']
                ==
                $request->payment_id
            ) {

                $payment['adjustment'] =
                    (float)
                    $request->adjustment;

                $payment['adjustment_by'] =
                    auth()->id();

                $payment['adjustment_at'] =
                    now()
                    ->format(
                        'Y-m-d H:i:s'
                    );
            }
        }

        $spk->update([

               'data' => $data


        ]);

        return response()->json([

            'success' => true

        ]);
    }
    // approve
   public function signSignature(Request $request, $id)
{
    $signature = SignatureSpk::findOrFail($id);
    $spk = Spk::findOrFail($signature->spk_id);

    /*
    |--------------------------------------------------------------------------
    | CHECKER 1 (VIVI)
    |--------------------------------------------------------------------------
    */
    if ($request->type === 'checked') {

        if (auth()->id() != $signature->checked_by) {
            return response()->json([
                'success' => false,
                'message' => 'Anda bukan Checker 1'
            ], 403);
        }

        $signature->update([
            'checked_at'     => now(),
            'checked_remark' => $request->remark,
        ]);

        SpkTimeline::create([
            'spk_id' => $spk->id,
            'data' => json_encode([
                'time'   => now()->format('d M Y H:i'),
                'type'   => 'checked',
                'user'   => auth()->user()->name,
                'remark' => $request->remark,
            ])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'SPK berhasil di-check oleh Checker 1'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CHECKER 2 (DIDIN)
    |--------------------------------------------------------------------------
    */
    if ($request->type === 'checked_2') {

        if (auth()->id() != $signature->checked_by_2) {
            return response()->json([
                'success' => false,
                'message' => 'Anda bukan Checker 2'
            ], 403);
        }

        $signature->update([
            'checked_at_2'     => now(),
            'checked_2_remark' => $request->remark,
        ]);

        SpkTimeline::create([
            'spk_id' => $spk->id,
            'data' => json_encode([
                'time'   => now()->format('d M Y H:i'),
                'type'   => 'checked_2',
                'user'   => auth()->user()->name,
                'remark' => $request->remark,
            ])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'SPK berhasil di-check oleh Checker 2'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVED (MR STANLEY)
    |--------------------------------------------------------------------------
    */
    if ($request->type === 'approved') {

        if (auth()->id() != 191) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Mr Stanley yang dapat melakukan approval ini'
            ], 403);
        }

        $signature->update([
            'approved_by'     => auth()->id(),
            'approved_at'     => now(),
            'approved_remark' => $request->remark,
        ]);

        $spk->update([
            'status' => 'approved'
        ]);

        SpkTimeline::create([
            'spk_id' => $spk->id,
            'data' => json_encode([
                'time'   => now()->format('d M Y H:i'),
                'type'   => 'approved',
                'user'   => auth()->user()->name,
                'remark' => $request->remark,
            ])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'SPK berhasil di-approve'
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Invalid request'
    ], 400);
}

public function notifications()
{
    $pfis = Po::query()
        ->whereDate(
            'created_at',
            '>=',
            now()->subDays(7)
        )
        ->latest('created_at')
        ->get()
        ->map(function ($pfi) {

            $shipmentDate =
                $this->parseShipmentDate(
                    $pfi->shipment_date
                );

            return [
                'id' => $pfi->id,
                'order_no' => $pfi->order_no,

                'shipment_date' => $shipmentDate
                    ? $shipmentDate->format('d/m/Y')
                    : ($pfi->shipment_date ?: '-'),

                'created_at' => $pfi->created_at
                    ->format('d/m/Y H:i'),
            ];
        });

    return response()->json($pfis);
}

/*
|--------------------------------------------------------------------------
| FLEXIBLE DATE PARSER
|--------------------------------------------------------------------------
*/
private function parseShipmentDate($value)
{
    if (blank($value)) {
        return null;
    }

    $value = trim($value);

    /*
    |--------------------------------------------------------------------------
    | HAPUS KETERANGAN DALAM KURUNG
    |--------------------------------------------------------------------------
    */

    $value = preg_replace(
        '/\(.*?\)/',
        '',
        $value
    );

    $value = trim($value);

    /*
    |--------------------------------------------------------------------------
    | NORMALISASI BULAN INDONESIA
    |--------------------------------------------------------------------------
    */

    $months = [
        'JANUARI' => 'JANUARY',
        'FEBRUARI' => 'FEBRUARY',
        'MARET' => 'MARCH',
        'APRIL' => 'APRIL',
        'MEI' => 'MAY',
        'JUNI' => 'JUNE',
        'JULI' => 'JULY',
        'AGUSTUS' => 'AUGUST',
        'SEPTEMBER' => 'SEPTEMBER',
        'OKTOBER' => 'OCTOBER',
        'NOVEMBER' => 'NOVEMBER',
        'DESEMBER' => 'DECEMBER',
    ];

    $upper = strtoupper($value);

    foreach ($months as $id => $en) {
        $upper = str_replace(
            $id,
            $en,
            $upper
        );
    }

    $value = $upper;

    /*
    |--------------------------------------------------------------------------
    | COBA PARSE OTOMATIS
    |--------------------------------------------------------------------------
    */

    try {
        return Carbon::parse($value);
    } catch (\Exception $e) {
        //
    }

    /*
    |--------------------------------------------------------------------------
    | FORMAT MANUAL
    |--------------------------------------------------------------------------
    */

    $formats = [
        'd/m/Y',
        'd-m-Y',
        'Y-m-d',
        'd/n/Y',
        'd-n-Y',
        'j/n/Y',
        'j-n-Y',
    ];

    foreach ($formats as $format) {

        try {

            return Carbon::createFromFormat(
                $format,
                $value
            );

        } catch (\Exception $e) {
            //
        }
    }

    return null;
}
public function indexloading(){
    return view('pages.loading.index');
}
public function generateLoading(Request $request)
{
    $containers = [

        '20FT' => [
            'length' => 589,
            'width' => 235,
            'height' => 239,
        ],

        '40HC' => [
            'length' => 1203,
            'width' => 235,
            'height' => 269,
        ],

    ];

    $container =
        $containers[
            $request->container
        ];

    $items3d = [];

    $totalCbm = 0;
    $totalCarton = 0;

    $x = 0;
    $y = 0;
    $z = 0;

    foreach (
        $request->items
        as $item
    )
    {
        $cbm =
            (
                $item['length'] *
                $item['width'] *
                $item['height']
            ) / 1000000;

        $totalCbm +=
            $cbm *
            $item['qty'];

        $totalCarton +=
            $item['qty'];

        for (
            $i = 0;
            $i < $item['qty'];
            $i++
        )
        {
            $items3d[] = [

                'name' =>
                    $item['name'],

                'length' =>
                    $item['length'],

                'width' =>
                    $item['width'],

                'height' =>
                    $item['height'],

                'x' => $x,
                'y' => $y,
                'z' => $z,

                'color' =>
                    sprintf(
                        '0x%06X',
                        mt_rand(
                            0,
                            0xFFFFFF
                        )
                    )
            ];

            $x +=
                $item['length'];

            if (
                $x +
                $item['length']
                >
                $container['length']
            )
            {
                $x = 0;
                $z +=
                    $item['width'];
            }

            if (
                $z +
                $item['width']
                >
                $container['width']
            )
            {
                $z = 0;
                $y +=
                    $item['height'];
            }
        }
    }

    $containerVolume =
        (
            $container['length']
            *
            $container['width']
            *
            $container['height']
        ) / 1000000;

    return response()->json([

        'po_name' =>
            $request->po_name,

        'container' =>
            $container,

        'items' =>
            $items3d,

        'total_cbm' =>
            round(
                $totalCbm,
                2
            ),

        'total_carton' =>
            $totalCarton,

        'utilization' =>
            round(
                (
                    $totalCbm /
                    $containerVolume
                ) * 100,
                2
            )

    ]);
}
}
