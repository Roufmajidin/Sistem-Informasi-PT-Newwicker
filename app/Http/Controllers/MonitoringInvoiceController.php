<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MonitoringInvoice;
use App\Models\InvLama;
use App\Models\SpkLama;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MonitoringInvoiceController extends Controller
{
    /**
     * LIST
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Default:
        | Invoice terbaru -> terlama
        |--------------------------------------------------------------------------
        */

        $invoices = MonitoringInvoice::orderByDesc('tanggal_invoice')
            ->orderByDesc('id')
            ->get();

        return view(
            'pages.finishing.index',
            compact('invoices')
        );
    }


    /**
     * STORE
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'nomor_invoice' => [
                'required',
                'string',
                'max:100',
            ],

            'tanggal_invoice' => [
                'required',
                'date',
            ],

            /*
            |--------------------------------------------------------------------------
            | TO SUB
            |--------------------------------------------------------------------------
            */

            'to_sub' => [
                'required',
                'in:tomo,darto',
            ],

            /*
            |--------------------------------------------------------------------------
            | DETAIL BAHAN
            |--------------------------------------------------------------------------
            */

            'detail_bahan' => [
                'required',
                'array',
                'min:1',
            ],

            'detail_bahan.*.harga' => [
                'required',
                'numeric',
                'min:0',
            ],

            'detail_bahan.*.jenis' => [
                'required',
                'string',
                'max:255',
            ],

            'detail_bahan.*.qty' => [
                'required',
                'numeric',
                'min:0',
            ],

            'detail_bahan.*.satuan' => [
                'required',
                'string',
                'max:50',
            ],

            'detail_bahan.*.total' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);


        MonitoringInvoice::create([

            'nomor_invoice' =>
                $validated['nomor_invoice'],

            'tanggal_invoice' =>
                $validated['tanggal_invoice'],

            /*
            |--------------------------------------------------------------------------
            | TO SUB
            |--------------------------------------------------------------------------
            */

            'to_sub' =>
                $validated['to_sub'],

            'detail_bahan' =>
                $validated['detail_bahan'],
        ]);


        return redirect()
            ->route('monitoring-invoice.index')
            ->with(
                'success',
                'Invoice berhasil ditambahkan.'
            );
    }


    /**
     * UPDATE
     */
    public function update(Request $request, $id)
    {
        $invoice =
            MonitoringInvoice::findOrFail($id);


        $validated = $request->validate([

            'nomor_invoice' => [
                'required',
                'string',
                'max:100',
            ],

            'tanggal_invoice' => [
                'required',
                'date',
            ],

            /*
            |--------------------------------------------------------------------------
            | TO SUB
            |--------------------------------------------------------------------------
            */

            'to_sub' => [
                'required',
                'in:tomo,darto',
            ],

            /*
            |--------------------------------------------------------------------------
            | DETAIL BAHAN
            |--------------------------------------------------------------------------
            */

            'detail_bahan' => [
                'required',
                'array',
                'min:1',
            ],

            'detail_bahan.*.harga' => [
                'required',
                'numeric',
                'min:0',
            ],

            'detail_bahan.*.jenis' => [
                'required',
                'string',
                'max:255',
            ],

            'detail_bahan.*.qty' => [
                'required',
                'numeric',
                'min:0',
            ],

            'detail_bahan.*.satuan' => [
                'required',
                'string',
                'max:50',
            ],

            'detail_bahan.*.total' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);


        $invoice->update([

            'nomor_invoice' =>
                $validated['nomor_invoice'],

            'tanggal_invoice' =>
                $validated['tanggal_invoice'],

            /*
            |--------------------------------------------------------------------------
            | TO SUB
            |--------------------------------------------------------------------------
            */

            'to_sub' =>
                $validated['to_sub'],

            'detail_bahan' =>
                $validated['detail_bahan'],
        ]);


        return redirect()
            ->route('monitoring-invoice.index')
            ->with(
                'success',
                'Invoice berhasil diperbarui.'
            );
    }


    /**
     * DELETE
     */
    public function destroy($id)
    {
        $invoice =
            MonitoringInvoice::findOrFail($id);

        $invoice->delete();

        return redirect()
            ->route('monitoring-invoice.index')
            ->with(
                'success',
                'Invoice berhasil dihapus.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE INVOICE LAMA
    |--------------------------------------------------------------------------
    */

    public function storeInvoiceLama(Request $request)
    {
        $request->validate([

            'nomor_invoice' => [
                'required',
                'string',
                'max:255',
            ],

            'tanggal_invoice' => [
                'nullable',
                'date',
            ],

            'detail_bahan' => [
                'required',
                'array',
                'min:1',
            ],

            'detail_bahan.*.qty' => [
                'nullable',
            ],

            'detail_bahan.*.harga' => [
                'nullable',
            ],

            'detail_bahan.*.jenis' => [
                'nullable',
                'string',
            ],

            'detail_bahan.*.total' => [
                'nullable',
            ],

            'detail_bahan.*.satuan' => [
                'nullable',
                'string',
            ],

            'detail_bahan.*.spk' => [
                'nullable',
                'string',
            ],

            'detail_bahan.*.supplier' => [
                'nullable',
                'string',
            ],
        ]);


        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | NOMOR INVOICE
            |--------------------------------------------------------------------------
            */

            $nomorInvoice =
                trim(
                    (string) $request->nomor_invoice
                );


            /*
            |--------------------------------------------------------------------------
            | TANGGAL INVOICE
            |--------------------------------------------------------------------------
            */

            $tanggalInvoice = null;

            if (
                $request->filled(
                    'tanggal_invoice'
                )
            ) {

                $tanggalInvoice =
                    Carbon::parse(
                        $request->tanggal_invoice
                    )->format('Y-m-d');
            }


            /*
            |--------------------------------------------------------------------------
            | DETAIL BAHAN
            |--------------------------------------------------------------------------
            */

            $detailBahan = [];


            foreach (
                $request->detail_bahan
                as $item
            ) {

                $detailBahan[] = [

                    'qty' =>
                        (string) (
                            $item['qty'] ?? '0'
                        ),

                    'harga' =>
                        (string) (
                            $this->parseNumber(
                                $item['harga'] ?? 0
                            )
                        ),

                    'jenis' =>
                        trim(
                            (string) (
                                $item['jenis'] ?? ''
                            )
                        ),

                    'total' =>
                        (string) (
                            $this->parseNumber(
                                $item['total'] ?? 0
                            )
                        ),

                    'satuan' =>
                        trim(
                            (string) (
                                $item['satuan'] ?? ''
                            )
                        ),
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | SIMPAN INV LAMA
            |--------------------------------------------------------------------------
            */

            $invoice =
                InvLama::updateOrCreate(

                    [
                        'nomor_invoice' =>
                            $nomorInvoice,
                    ],

                    [
                        'tanggal_invoice' =>
                            $tanggalInvoice,

                        'detail_bahan' =>
                            $detailBahan,
                    ]
                );


            /*
            |--------------------------------------------------------------------------
            | HAPUS SPK LAMA
            |--------------------------------------------------------------------------
            */

            SpkLama::where(
                'no_inv',
                $nomorInvoice
            )->delete();


            $spkInserted = 0;


            /*
            |--------------------------------------------------------------------------
            | PROSES SETIAP BARIS
            |--------------------------------------------------------------------------
            */

            foreach (
                $request->detail_bahan
                as $index => $item
            ) {

                $spkRaw =
                    trim(
                        (string) (
                            $item['spk'] ?? ''
                        )
                    );


                if ($spkRaw === '') {
                    continue;
                }


                $spk =
                    $this->formatSpk(
                        $spkRaw
                    );


                if ($spk === '') {
                    continue;
                }


                $po =
                    $this->formatPo(
                        $spk
                    );


                $supplier =
                    trim(
                        (string) (
                            $item['supplier'] ?? ''
                        )
                    );


                $pemotongan =
                    $this->parseNumber(
                        $item['total'] ?? 0
                    );


                $tanggalPotong =
                    $tanggalInvoice;


                SpkLama::create([

                    'name_sub' =>
                        $supplier !== ''
                            ? $supplier
                            : null,

                    'po' =>
                        $po !== ''
                            ? $po
                            : null,

                    'no_spk' =>
                        $spk,

                    'no_inv' =>
                        $nomorInvoice,

                    'pemotongan_bahan' =>
                        $pemotongan,

                    'tanggal_potong' =>
                        $tanggalPotong,
                ]);


                $spkInserted++;
            }


            DB::commit();


            return response()->json([

                'success' => true,

                'message' =>
                    'Invoice lama berhasil disimpan.',

                'invoice_id' =>
                    $invoice->id,

                'spk_inserted' =>
                    $spkInserted,
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();


            return response()->json([

                'success' => false,

                'message' =>
                    $e->getMessage(),

                'line' =>
                    $e->getLine(),

                'file' =>
                    $e->getFile(),

            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | FORMAT SPK
    |--------------------------------------------------------------------------
    */

    private function formatSpk($spk)
    {
        $spk =
            trim(
                (string) $spk
            );


        if ($spk === '') {
            return '';
        }


        $spk =
            str_replace(
                [
                    "\r",
                    "\n",
                    "\t",
                ],
                ' ',
                $spk
            );


        $spk =
            preg_replace(
                '/\s+/u',
                ' ',
                $spk
            );


        $spk =
            preg_replace(
                '/\/\s*NW\s*/i',
                '/NW ',
                $spk
            );


        $spk =
            preg_replace(
                '/NW\s*(\d{2})\s*-\s*(\d{1,3})/i',
                'NW $1 - $2',
                $spk
            );


        return trim($spk);
    }


    /*
    |--------------------------------------------------------------------------
    | FORMAT PO
    |--------------------------------------------------------------------------
    */

    private function formatPo($spk)
    {
        $spk =
            trim(
                (string) $spk
            );


        if ($spk === '') {
            return '';
        }


        if (
            preg_match(
                '/NW\s*(\d{2})\s*-\s*(\d{1,3})/i',
                $spk,
                $match
            )
        ) {

            return
                $match[1]
                . ' - '
                . $match[2];
        }


        return '';
    }


    /*
    |--------------------------------------------------------------------------
    | PARSE NUMBER
    |--------------------------------------------------------------------------
    */

    private function parseNumber($value)
    {
        if (
            $value === null ||
            $value === ''
        ) {

            return 0;
        }


        if (
            is_int($value) ||
            is_float($value)
        ) {

            return $value;
        }


        $text =
            trim(
                (string) $value
            );


        if ($text === '') {
            return 0;
        }


        $text =
            str_ireplace(
                'Rp',
                '',
                $text
            );


        $text =
            str_replace(
                [
                    ' ',
                    "\xc2\xa0",
                ],
                '',
                $text
            );


        $text =
            preg_replace(
                '/[^0-9.,\-]/',
                '',
                $text
            );


        if ($text === '') {
            return 0;
        }


        $text =
            str_replace(
                [
                    '.',
                    ',',
                ],
                '',
                $text
            );


        return (float) $text;
    }
}