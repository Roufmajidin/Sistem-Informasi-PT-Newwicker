<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MonitoringInvoice;
use App\Models\InvLama;
use App\Models\SpkLama;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; // Import Carbon here

class MonitoringInvoiceController extends Controller
{
    /**
     * LIST
     */
    public function index()
    {
        $invoices = MonitoringInvoice::latest('id')->get();

        return view('pages.finishing.index', compact('invoices'));
    }

    /**
     * STORE
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_invoice' => 'required|string|max:100',
            'tanggal_invoice' => 'required|date',

            'detail_bahan' => 'required|array|min:1',
            'detail_bahan.*.harga' => 'required|numeric|min:0',
            'detail_bahan.*.jenis' => 'required|string|max:255',
            'detail_bahan.*.qty' => 'required|numeric|min:0',
            'detail_bahan.*.satuan' => 'required|string|max:50',
            'detail_bahan.*.total' => 'required|numeric|min:0',
        ]);

        MonitoringInvoice::create([
            'nomor_invoice' => $validated['nomor_invoice'],
            'tanggal_invoice' => $validated['tanggal_invoice'],
            'detail_bahan' => $validated['detail_bahan'],
        ]);

        return redirect()
            ->route('monitoring-invoice.index')
            ->with('success', 'Invoice berhasil ditambahkan.');
    }

    /**
     * UPDATE
     */
    public function update(Request $request, $id)
    {
        $invoice = MonitoringInvoice::findOrFail($id);

        $validated = $request->validate([
            'nomor_invoice' => 'required|string|max:100',
            'tanggal_invoice' => 'required|date',
            'detail_bahan.*.harga' => 'required|numeric|min:0',

            'detail_bahan' => 'required|array|min:1',

            'detail_bahan.*.jenis' => 'required|string|max:255',
            'detail_bahan.*.qty' => 'required|numeric|min:0',
            'detail_bahan.*.satuan' => 'required|string|max:50',
            'detail_bahan.*.total' => 'required|numeric|min:0',
        ]);

        $invoice->update([
            'nomor_invoice' => $validated['nomor_invoice'],
            'tanggal_invoice' => $validated['tanggal_invoice'],
            'detail_bahan' => $validated['detail_bahan'],
        ]);

        return redirect()
            ->route('monitoring-invoice.index')
            ->with('success', 'Invoice berhasil diperbarui.');
    }

    /**
     * DELETE
     */
    public function destroy($id)
    {
        $invoice = MonitoringInvoice::findOrFail($id);

        $invoice->delete();

        return redirect()
            ->route('pages.finishing.index')
            ->with('success', 'Invoice berhasil dihapus.');
    }
    /* |-------------------------------------------------------------------------- | STORE INVOICE LAMA |-------------------------------------------------------------------------- | | Menyimpan: | | 1. inv_lama | 2. spk_lama | | Setiap BARIS bahan menjadi 1 record spk_lama. | */
    public function storeInvoiceLama(Request $request)
    { /* |-------------------------------------------------------------------------- | VALIDASI |-------------------------------------------------------------------------- */
        $request->validate(['nomor_invoice' => ['required', 'string', 'max:255',], 'tanggal_invoice' => ['nullable', 'date',], 'detail_bahan' => ['required', 'array', 'min:1',], 'detail_bahan.*.qty' => ['nullable',], 'detail_bahan.*.harga' => ['nullable',], 'detail_bahan.*.jenis' => ['nullable', 'string',], 'detail_bahan.*.total' => ['nullable',], 'detail_bahan.*.satuan' => ['nullable', 'string',], 'detail_bahan.*.spk' => ['nullable', 'string',], 'detail_bahan.*.supplier' => ['nullable', 'string',],]); /* |-------------------------------------------------------------------------- | TRANSACTION |-------------------------------------------------------------------------- */
        DB::beginTransaction();
        try { /* |-------------------------------------------------------------------------- | NOMOR INVOICE |-------------------------------------------------------------------------- */
            $nomorInvoice = trim((string) $request->nomor_invoice); /* |-------------------------------------------------------------------------- | TANGGAL INVOICE |-------------------------------------------------------------------------- */
            $tanggalInvoice = null;
            if ($request->filled('tanggal_invoice')) {
                $tanggalInvoice = Carbon::parse($request->tanggal_invoice)->format('Y-m-d');
            } /* |-------------------------------------------------------------------------- | SIAPKAN DETAIL BAHAN |-------------------------------------------------------------------------- */
            $detailBahan = [];
            foreach ($request->detail_bahan as $item) {
                $detailBahan[] = ['qty' => (string) ($item['qty'] ?? '0'), 'harga' => (string) ($this->parseNumber($item['harga'] ?? 0)), 'jenis' => trim((string) ($item['jenis'] ?? '')), 'total' => (string) ($this->parseNumber($item['total'] ?? 0)), 'satuan' => trim((string) ($item['satuan'] ?? '')),];
            } /* |-------------------------------------------------------------------------- | SIMPAN INV LAMA |-------------------------------------------------------------------------- */
            $invoice = InvLama::updateOrCreate(['nomor_invoice' => $nomorInvoice,], ['tanggal_invoice' => $tanggalInvoice, 'detail_bahan' => $detailBahan,]); /* |-------------------------------------------------------------------------- | HAPUS SPK LAMA MILIK INVOICE INI |-------------------------------------------------------------------------- | | Supaya ketika invoice diedit dan disimpan lagi: | | - tidak duplicate | - semua baris dibuat ulang | */
            SpkLama::where('no_inv', $nomorInvoice)->delete(); /* |-------------------------------------------------------------------------- | COUNTER |-------------------------------------------------------------------------- */
            $spkInserted = 0; /* |-------------------------------------------------------------------------- | PROSES SETIAP BARIS BAHAN |-------------------------------------------------------------------------- */
            foreach ($request->detail_bahan as $index => $item) { /* |-------------------------------------------------------------------------- | AMBIL SPK |-------------------------------------------------------------------------- */
                $spkRaw = trim((string) ($item['spk'] ?? '')); /* |-------------------------------------------------------------------------- | SPK KOSONG |-------------------------------------------------------------------------- */
                if ($spkRaw === '') {
                    continue;
                } /* |-------------------------------------------------------------------------- | FORMAT SPK |-------------------------------------------------------------------------- */
                $spk = $this->formatSpk($spkRaw);
                if ($spk === '') {
                    continue;
                } /* |-------------------------------------------------------------------------- | FORMAT PO |-------------------------------------------------------------------------- */
                $po = $this->formatPo($spk); /* |-------------------------------------------------------------------------- | SUPPLIER |-------------------------------------------------------------------------- */
                $supplier = trim((string) ($item['supplier'] ?? '')); /* |-------------------------------------------------------------------------- | NILAI POTONGAN |-------------------------------------------------------------------------- | | AMBIL DARI TOTAL PADA BARIS TERSEBUT. | | Contoh: | | THINNER -> 884000 | NCT -> 988000 | ANTI CIPPING -> 360000 | HARDINER -> 155000 | */
                $pemotongan = $this->parseNumber($item['total'] ?? 0); /* |-------------------------------------------------------------------------- | TANGGAL POTONG |-------------------------------------------------------------------------- */
                $tanggalPotong = $tanggalInvoice; /* |-------------------------------------------------------------------------- | INSERT SPK LAMA |-------------------------------------------------------------------------- | | JANGAN menggunakan: | | updateOrCreate() | | Karena beberapa bahan dapat mempunyai: | | no_inv yang sama | no_spk yang sama | | Tetapi harus menjadi record berbeda. | */
                SpkLama::create(['name_sub' => $supplier !== '' ? $supplier : null, 'po' => $po !== '' ? $po : null, 'no_spk' => $spk, 'no_inv' => $nomorInvoice, 'pemotongan_bahan' => $pemotongan, 'tanggal_potong' => $tanggalPotong,]);
                $spkInserted++;
            } /* |-------------------------------------------------------------------------- | COMMIT |-------------------------------------------------------------------------- */
            DB::commit(); /* |-------------------------------------------------------------------------- | RESPONSE BERHASIL |-------------------------------------------------------------------------- */
            return response()->json(['success' => true, 'message' => 'Invoice lama berhasil disimpan.', 'invoice_id' => $invoice->id, 'spk_inserted' => $spkInserted,]);
        } catch (\Throwable $e) { /* |-------------------------------------------------------------------------- | ROLLBACK |-------------------------------------------------------------------------- */
            DB::rollBack(); /* |-------------------------------------------------------------------------- | RESPONSE ERROR |-------------------------------------------------------------------------- */
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile(),], 500);
        }
    } /* |-------------------------------------------------------------------------- | FORMAT SPK |-------------------------------------------------------------------------- | | Input: | | 26-1123/NW 26-32/7/2026 | | Output: | | 26-1123/NW 26 - 32/7/2026 | |-------------------------------------------------------------------------- */
    private function formatSpk($spk)
    {
        $spk = trim((string) $spk);
        if ($spk === '') {
            return '';
        } /* |-------------------------------------------------------------------------- | HILANGKAN ENTER |-------------------------------------------------------------------------- */
        $spk = str_replace(["\r", "\n", "\t",], ' ', $spk); /* |-------------------------------------------------------------------------- | HILANGKAN SPASI BERLEBIH |-------------------------------------------------------------------------- */
        $spk = preg_replace('/\s+/u', ' ', $spk); /* |-------------------------------------------------------------------------- | / NW |-------------------------------------------------------------------------- | | / NW | /NW | / NW | | menjadi: | | /NW | */
        $spk = preg_replace('/\/\s*NW\s*/i', '/NW ', $spk); /* |-------------------------------------------------------------------------- | FORMAT NOMOR NW |-------------------------------------------------------------------------- | | NW26-32 | NW 26-32 | NW 26 - 32 | | menjadi: | | NW 26 - 32 | */
        $spk = preg_replace('/NW\s*(\d{2})\s*-\s*(\d{1,3})/i', 'NW $1 - $2', $spk);
        return trim($spk);
    } /* |-------------------------------------------------------------------------- | FORMAT PO |-------------------------------------------------------------------------- | | Input: | | 26-1123/NW 26 - 32/7/2026 | | Output: | | 26 - 32 | |-------------------------------------------------------------------------- */
    private function formatPo($spk)
    {
        $spk = trim((string) $spk);
        if ($spk === '') {
            return '';
        } /* |-------------------------------------------------------------------------- | CARI NW XX-XXX |-------------------------------------------------------------------------- */
        if (preg_match('/NW\s*(\d{2})\s*-\s*(\d{1,3})/i', $spk, $match)) {
            return $match[1] . ' - ' . $match[2];
        }
        return '';
    } /* |-------------------------------------------------------------------------- | PARSE NUMBER |-------------------------------------------------------------------------- | | Mendukung: | | Rp1,326,000 | Rp 1,326,000 | Rp22,100 | 1.326.000 | 1,326,000 | 1326000 | |-------------------------------------------------------------------------- */
    private function parseNumber($value)
    { /* |-------------------------------------------------------------------------- | NULL / KOSONG |-------------------------------------------------------------------------- */
        if ($value === null || $value === '') {
            return 0;
        } /* |-------------------------------------------------------------------------- | SUDAH NUMERIC |-------------------------------------------------------------------------- */
        if (is_int($value) || is_float($value)) {
            return $value;
        } /* |-------------------------------------------------------------------------- | STRING |-------------------------------------------------------------------------- */
        $text = trim((string) $value);
        if ($text === '') {
            return 0;
        } /* |-------------------------------------------------------------------------- | HAPUS RP |-------------------------------------------------------------------------- */
        $text = str_ireplace('Rp', '', $text); /* |-------------------------------------------------------------------------- | HAPUS SPASI NORMAL + NBSP EXCEL |-------------------------------------------------------------------------- */
        $text = str_replace([' ', "\xc2\xa0",], '', $text); /* |-------------------------------------------------------------------------- | SISAKAN ANGKA, TITIK, KOMA, MINUS |-------------------------------------------------------------------------- */
        $text = preg_replace('/[^0-9.,\-]/', '', $text);
        if ($text === '') {
            return 0;
        } /* |-------------------------------------------------------------------------- | FORMAT RUPIAH |-------------------------------------------------------------------------- | | 1.326.000 | 1,326,000 | | menjadi: | | 1326000 | */
        $text = str_replace(['.', ',',], '', $text); /* |-------------------------------------------------------------------------- | RETURN |-------------------------------------------------------------------------- */
        return (float) $text;
    }
}