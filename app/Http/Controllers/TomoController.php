<?php

namespace App\Http\Controllers;

use App\Models\Po;
use Illuminate\Http\Request;

class TomoController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $pos = Po::query()
            ->with(['detailPos', 'spks'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_no', 'like', '%' . $search . '%')
                        ->orWhere('company_name', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $cards = $pos->getCollection()->map(function ($po) {
            $spkItems = [];
            foreach ($po->spks as $spk) {
                $data = is_array($spk->data) ? $spk->data : json_decode((string) $spk->data, true);
                if (!is_array($data))
                    continue;
                // Hanya SPK milik supplier TOMO FINISHING.
                $supplier = preg_replace('/\s+/u', ' ', trim((string) ($data['sup'] ?? '')));
                if (mb_strtoupper($supplier) !== 'TOMO FINISHING')
                    continue;
                foreach (($data['items'] ?? []) as $row) {
                    if (!is_array($row))
                        continue;
                    $detailId = (string) ($row['detail_po_id'] ?? '');
                    if ($detailId === '')
                        continue;
                    $spkItems[$detailId][] = [
                        'number' => $data['no_spk'] ?? $spk->spk_no ?? $spk->no_spk ?? $spk->nomor_spk ?? ('SPK #' . $spk->id),
                        'qty' => $row['qty'] ?? null,
                        'harga' => isset($row['harga']) && is_numeric($row['harga']) ? (float) $row['harga'] : null,
                    ];
                }
            }

            return [
                'po' => $po->order_no,
                'buyer' => $po->company_name ?: '-',
                'items' => $po->detailPos->map(function ($detailPo) use ($spkItems) {
                    $data = is_array($detailPo->detail) ? $detailPo->detail : json_decode((string) $detailPo->detail, true);
                    $data = is_array($data) ? $data : [];
                    $photo = $data['photo'] ?? null;
                    if (is_array($photo))
                        $photo = $photo['url'] ?? $photo['path'] ?? null;
                    if (is_string($photo) && $photo !== '') {
                        if (!preg_match('~^(https?://|/)~i', $photo)) {
                            $photo = asset('storage/' . ltrim($photo, '/'));
                        }
                    } else
                        $photo = null;
                    return [
                        'id' => $detailPo->id,
                        'name' => $data['description'] ?? $data['nama'] ?? $data['item'] ?? '-',
                        'article' => $data['article_nr_'] ?? $data['article_nr'] ?? $data['article_code'] ?? $data['article'] ?? $data['code'] ?? '-',
                        'qty' => $data['qty'] ?? 0,
                        'image' => $photo,
                        'spks' => $spkItems[(string) $detailPo->id] ?? [],
                    ];
                })->values()->all(),
            ];
        })->values();

        return view('pages.tomo.index', compact('cards', 'pos', 'search'));
    }
}
