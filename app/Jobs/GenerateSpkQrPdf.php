<?php

namespace App\Jobs;

use App\Models\Spk;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Throwable;

class GenerateSpkQrPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1200;
    public int $tries = 1;

    public function __construct(public int $spkId, public string $token, public int $userId) {}

    private function status(string $status, int $current, int $total, string $message, array $extra = []): void
    {
        Cache::put("spk_qr_job:{$this->token}", array_merge([
            'user_id' => $this->userId, 'spk_id' => $this->spkId,
            'status' => $status, 'current' => $current, 'total' => $total,
            'message' => $message,
        ], $extra), now()->addHours(6));
    }

    public function handle(): void
    {
        $current = 0;
        $total = 0;
        try {
            $spk = Spk::findOrFail($this->spkId);
            $data = is_array($spk->data) ? $spk->data : json_decode((string) $spk->data, true);
            if (!is_array($data)) throw new \RuntimeException('Data SPK tidak valid.');
            $items = [];
            foreach (($data['items'] ?? []) as $item) {
                if (!is_array($item)) continue;
                $qty = filter_var($item['qty'] ?? null, FILTER_VALIDATE_INT);
                if ($qty === false || $qty < 1) continue;
                $total += $qty;
                if ($total > 2000) throw new \RuntimeException('Jumlah label melebihi 2000.');
                $items[] = ['qty' => $qty, 'detail_po_id' => (int) ($item['detail_po_id'] ?? $spk->detail_po_id ?? 0),
                    'nama' => trim((string) ($item['nama'] ?? '-')), 'kode' => (string) ($item['kode'] ?? ''),
                    'material' => (string) ($item['material'] ?? '')];
            }
            if (!$items) throw new \RuntimeException('Tidak ada item valid.');
            $noSpk = (string) ($data['no_spk'] ?? 'SPK #' . $spk->id);
            $noPo = (string) ($data['no_po'] ?? '');
            $supplier = (string) ($data['sup'] ?? '');
            $this->status('generating', 0, $total, 'Generating QR 0/' . $total);
            $labels = [];
            foreach ($items as $item) {
                for ($sequence = 1; $sequence <= $item['qty']; $sequence++) {
                    $serial = $sequence . '/' . $item['qty'];
                    $value = implode(',', [$spk->id, $spk->po_id, $item['detail_po_id'], $item['nama'], $supplier, $noPo, $serial]);
                    $cacheKey = 'spk_qr_svg_v3_' . hash('sha256', $value);
                    $qr = Cache::remember($cacheKey, now()->addDays(7), function () use ($value) {
                        $svg = (string) QrCode::format('svg')->size(420)->margin(4)->errorCorrection('M')->generate($value);
                        return 'data:image/svg+xml;base64,' . base64_encode($svg);
                    });
                    $labels[] = ['name' => $item['nama'], 'kode' => $item['kode'], 'material' => $item['material'],
                        'number' => $serial, 'qty' => $item['qty'], 'sequence' => $sequence,
                        'no_spk' => $noSpk, 'no_po' => $noPo, 'supplier' => $supplier,
                        'detail_po_id' => $item['detail_po_id'], 'qr' => $qr];
                    $current++;
                    // Reduce cache writes without losing meaningful progress.
                    if ($current % 2 === 0 || $current === $total) {
                        $this->status('generating', $current, $total, "Generating QR {$current}/{$total}");
                    }
                }
            }
            $this->status('rendering', $total, $total, 'QR selesai. Menyusun PDF F4...');
            $pages = array_chunk($labels, 15);
            $pdf = Pdf::loadView('pages.management.pdf.spk-qr-labels', compact('pages', 'spk'))
                ->setPaper([0, 0, 210 * 72 / 25.4, 330 * 72 / 25.4], 'portrait')
                ->setOptions(['isRemoteEnabled' => false, 'defaultFont' => 'DejaVu Sans']);
            $path = 'spk-qr/' . $this->token . '.pdf';
            Storage::disk('local')->put($path, $pdf->output());
            $filename = 'SPK_QR_' . Str::slug($noSpk, '_') . '.pdf';
            $this->status('done', $total, $total, 'PDF siap diunduh.', ['filename' => $filename]);
        } catch (Throwable $e) {
            report($e);
            $this->status('failed', $current, $total, 'Gagal membuat PDF: ' . $e->getMessage());
            throw $e;
        }
    }
}
