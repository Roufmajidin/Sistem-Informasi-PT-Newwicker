<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengajuan;
use App\Models\PengajuanDetail;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestSaved;
use App\Models\Spk;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | DRAFT PAYMENT
        |--------------------------------------------------------------------------
        */

        $pengajuans = Pengajuan::with([
            'meta',
            'details',
            'approvalSteps',
            'user',
        ])
            ->where('type_pengajuan', 'Finance')
            ->where('is_draft', 1)
            ->latest('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | ALL DIVISI
        |--------------------------------------------------------------------------
        */

        $allDivisi = Pengajuan::with([
            'user',
            'divisi',
            'divisiItems',
            'approvalSteps',
        ])
            ->whereHas('divisiItems')
            ->latest('id')
            ->get();


        return view(
            'pages.finance.index',
            compact(
                'pengajuans',
                'allDivisi'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DETAIL PENGAJUAN
    |--------------------------------------------------------------------------
    */

    public function detailPengajuan($id)
    {
        $pengajuan = Pengajuan::with([
            'meta',
            'details',
            'user',
            'approvalSteps',
        ])
            ->findOrFail($id);


        return view(
            'pages.finance.partials.detail-pengajuan',
            compact('pengajuan')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE DETAIL FINANCE
    |--------------------------------------------------------------------------
    |
    | Mapping:
    |
    | PengajuanDetail.ids_id
    |        ↓
    | PaymentRequestSaved.id
    |        ↓
    | payment_request_ids[]
    |        ↓
    | PaymentRequest.id
    |        ↓
    | PaymentRequest.payment_id
    |        ↓
    | Spk.data.payments[].payment_id
    |
    |--------------------------------------------------------------------------
    */

    public function updateDetailPengajuan(
        Request $request,
        $detail
    ) {
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | 1. GET DETAIL
            |--------------------------------------------------------------------------
            */

            $detailData =
                PengajuanDetail::findOrFail(
                    $detail
                );


            /*
            |--------------------------------------------------------------------------
            | 2. VALIDASI
            |--------------------------------------------------------------------------
            */

            $validated =
                $request->validate([

                    'date' => [
                        'nullable',
                        'date',
                    ],

                    'no_po' => [
                        'nullable',
                        'string',
                        'max:255',
                    ],

                    'no_inv' => [
                        'nullable',
                        'string',
                        'max:255',
                    ],

                    'type_biaya' => [
                        'nullable',
                        'string',
                        'max:255',
                    ],

                    'nama_barang' => [
                        'nullable',
                        'string',
                        'max:255',
                    ],

                    'qty' => [
                        'required',
                        'numeric',
                        'min:0',
                    ],

                    'harga_satuan' => [
                        'required',
                        'numeric',
                        'min:0',
                    ],

                ]);


            /*
            |--------------------------------------------------------------------------
            | 3. HITUNG TOTAL
            |--------------------------------------------------------------------------
            */

            $qty =
                (float) (
                    $validated['qty']
                    ?? 0
                );


            $hargaSatuan =
                (float) (
                    $validated['harga_satuan']
                    ?? 0
                );


            $totalHarga =
                $qty *
                $hargaSatuan;


            /*
            |--------------------------------------------------------------------------
            | 4. UPDATE DETAIL FINANCE
            |--------------------------------------------------------------------------
            */

            $detailData->update([

                'date' =>
                    $validated['date']
                    ?? null,

                'no_po' =>
                    $validated['no_po']
                    ?? null,

                'no_inv' =>
                    $validated['no_inv']
                    ?? null,

                'type_biaya' =>
                    $validated['type_biaya']
                    ?? null,

                'nama_barang' =>
                    $validated['nama_barang']
                    ?? null,

                'qty' =>
                    $qty,

                'harga_satuan' =>
                    $hargaSatuan,

                'total_harga' =>
                    $totalHarga,

            ]);


            /*
            |--------------------------------------------------------------------------
            | 5. CEK SOURCE ID
            |--------------------------------------------------------------------------
            */

            if (empty($detailData->ids_id)) {

                throw new \Exception(
                    'Detail Finance #' .
                    $detailData->id .
                    ' tidak memiliki ids_id.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | 6. GET PAYMENT REQUEST SAVED
            |--------------------------------------------------------------------------
            |
            | ids_id = payment_request_saveds.id
            |
            */

            $saved =
                PaymentRequestSaved::find(
                    $detailData->ids_id
                );


            if (!$saved) {

                throw new \Exception(

                    'Payment Request Saved #' .
                    $detailData->ids_id .
                    ' tidak ditemukan.'

                );

            }


            /*
            |--------------------------------------------------------------------------
            | 7. AMBIL PAYMENT REQUEST IDS
            |--------------------------------------------------------------------------
            */

            $paymentRequestIds =
                $saved->payment_request_ids
                ?? [];


            /*
            |--------------------------------------------------------------------------
            | Kalau cast array tidak bekerja,
            | handle JSON string juga.
            |--------------------------------------------------------------------------
            */

            if (
                is_string(
                    $paymentRequestIds
                )
            ) {

                $paymentRequestIds =
                    json_decode(
                        $paymentRequestIds,
                        true
                    )
                    ?? [];

            }


            if (
                !is_array(
                    $paymentRequestIds
                )
            ) {

                $paymentRequestIds = [];

            }


            /*
            |--------------------------------------------------------------------------
            | Hilangkan duplicate / kosong
            |--------------------------------------------------------------------------
            */

            $paymentRequestIds =
                collect(
                    $paymentRequestIds
                )
                ->filter(function ($id) {

                    return
                        $id !== null &&
                        $id !== '';

                })
                ->map(function ($id) {

                    return (int) $id;

                })
                ->unique()
                ->values()
                ->all();


            if (
                empty(
                    $paymentRequestIds
                )
            ) {

                throw new \Exception(

                    'Payment Request Saved #' .
                    $saved->id .
                    ' tidak memiliki payment_request_ids.'

                );

            }


            /*
            |--------------------------------------------------------------------------
            | 8. GET PAYMENT REQUEST
            |--------------------------------------------------------------------------
            */

            $paymentRequests =
                PaymentRequest::with('spk')
                    ->whereIn(
                        'id',
                        $paymentRequestIds
                    )
                    ->get();


            if (
                $paymentRequests->isEmpty()
            ) {

                throw new \Exception(

                    'Tidak ada Payment Request yang ditemukan dari ' .
                    'Payment Request Saved #' .
                    $saved->id .
                    '.'

                );

            }


            /*
            |--------------------------------------------------------------------------
            | 9. TENTUKAN PAYMENT REQUEST YANG SESUAI
            |--------------------------------------------------------------------------
            |
            | Karena satu PaymentRequestSaved bisa memiliki banyak
            | Payment Request, kita jangan langsung mengambil
            | payment request pertama.
            |
            | Kita cocokkan dengan NO PO / NO SPK dari detail Finance.
            |
            */

            $detailNoPo =
                trim(
                    (string) (
                        $detailData->no_po
                        ?? ''
                    )
                );


            $detailNoInv =
                trim(
                    (string) (
                        $detailData->no_inv
                        ?? ''
                    )
                );


            $matchedPaymentRequest =
                null;


            /*
            |--------------------------------------------------------------------------
            | PRIORITAS 1
            | Cocokkan SPK no_po + no_spk
            |--------------------------------------------------------------------------
            */

            foreach (
                $paymentRequests
                as $paymentRequest
            ) {

                $spk =
                    $paymentRequest->spk;


                if (!$spk) {
                    continue;
                }


                $spkData =
                    $spk->data;


                if (
                    is_string(
                        $spkData
                    )
                ) {

                    $spkData =
                        json_decode(
                            $spkData,
                            true
                        )
                        ?? [];

                }


                if (
                    !is_array(
                        $spkData
                    )
                ) {

                    $spkData = [];

                }


                $spkNoPo =
                    trim(
                        (string) (
                            $spkData['no_po']
                            ?? ''
                        )
                    );


                $spkNoSpk =
                    trim(
                        (string) (
                            $spkData['no_spk']
                            ?? ''
                        )
                    );


                /*
                |------------------------------------------------------------------
                | NO PO + NO INVOICE / SPK
                |------------------------------------------------------------------
                */

                if (
                    $detailNoPo !== ''
                    &&
                    $detailNoInv !== ''
                    &&
                    $detailNoPo === $spkNoPo
                    &&
                    $detailNoInv === $spkNoSpk
                ) {

                    $matchedPaymentRequest =
                        $paymentRequest;

                    break;

                }

            }


            /*
            |--------------------------------------------------------------------------
            | PRIORITAS 2
            | Kalau belum ketemu, cocokkan NO PO saja
            |--------------------------------------------------------------------------
            */

            if (
                !$matchedPaymentRequest
                &&
                $detailNoPo !== ''
            ) {

                foreach (
                    $paymentRequests
                    as $paymentRequest
                ) {

                    $spk =
                        $paymentRequest->spk;


                    if (!$spk) {
                        continue;
                    }


                    $spkData =
                        $spk->data;


                    if (
                        is_string(
                            $spkData
                        )
                    ) {

                        $spkData =
                            json_decode(
                                $spkData,
                                true
                            )
                            ?? [];

                    }


                    if (
                        !is_array(
                            $spkData
                        )
                    ) {

                        $spkData = [];

                    }


                    $spkNoPo =
                        trim(
                            (string) (
                                $spkData['no_po']
                                ?? ''
                            )
                        );


                    if (
                        $detailNoPo ===
                        $spkNoPo
                    ) {

                        $matchedPaymentRequest =
                            $paymentRequest;

                        break;

                    }

                }

            }


            /*
            |--------------------------------------------------------------------------
            | PRIORITAS 3
            | Kalau hanya ada satu Payment Request,
            | gunakan itu.
            |--------------------------------------------------------------------------
            */

            if (
                !$matchedPaymentRequest
                &&
                $paymentRequests->count() === 1
            ) {

                $matchedPaymentRequest =
                    $paymentRequests->first();

            }


            /*
            |--------------------------------------------------------------------------
            | JIKA MASIH TIDAK DITEMUKAN
            |--------------------------------------------------------------------------
            */

            if (
                !$matchedPaymentRequest
            ) {

                throw new \Exception(

                    'Payment Request yang sesuai tidak ditemukan. ' .

                    'Payment Request Saved #' .
                    $saved->id .

                    '. No PO Finance: ' .
                    ($detailNoPo ?: '-') .

                    ', No Invoice/SPK: ' .
                    ($detailNoInv ?: '-')

                );

            }


            /*
            |--------------------------------------------------------------------------
            | 10. PAYMENT REQUEST
            |--------------------------------------------------------------------------
            */

            $paymentRequest =
                $matchedPaymentRequest;


            /*
            |--------------------------------------------------------------------------
            | 11. PAYMENT ID
            |--------------------------------------------------------------------------
            */

            $paymentId =
                $paymentRequest->payment_id;


            if (
                empty(
                    $paymentId
                )
            ) {

                throw new \Exception(

                    'Payment Request #' .
                    $paymentRequest->id .
                    ' tidak memiliki payment_id.'

                );

            }


            /*
            |--------------------------------------------------------------------------
            | 12. SPK
            |--------------------------------------------------------------------------
            */

            $spk =
                $paymentRequest->spk;


            if (!$spk) {

                throw new \Exception(

                    'SPK untuk Payment Request #' .
                    $paymentRequest->id .
                    ' tidak ditemukan.'

                );

            }


            /*
            |--------------------------------------------------------------------------
            | 13. DATA SPK
            |--------------------------------------------------------------------------
            */

            $spkData =
                $spk->data;


            if (
                is_string(
                    $spkData
                )
            ) {

                $spkData =
                    json_decode(
                        $spkData,
                        true
                    )
                    ?? [];

            }


            if (
                !is_array(
                    $spkData
                )
            ) {

                $spkData = [];

            }


            /*
            |--------------------------------------------------------------------------
            | 14. PAYMENTS
            |--------------------------------------------------------------------------
            */

            $payments =
                $spkData['payments']
                ?? [];


            if (
                !is_array(
                    $payments
                )
            ) {

                $payments = [];

            }


            /*
            |--------------------------------------------------------------------------
            | 15. CARI PAYMENT BERDASARKAN PAYMENT ID
            |--------------------------------------------------------------------------
            */

            $paymentFound =
                false;


            $oldPayment =
                null;


            $newPayment =
                null;


            foreach (
                $payments
                as $index => $payment
            ) {

                $currentPaymentId =
                    (string) (
                        $payment['payment_id']
                        ?? ''
                    );


                if (
                    $currentPaymentId !==
                    (string) $paymentId
                ) {

                    continue;

                }


                /*
                |--------------------------------------------------------------------------
                | PAYMENT DITEMUKAN
                |--------------------------------------------------------------------------
                */

                $paymentFound =
                    true;


                $oldPayment =
                    $payment;


                /*
                |--------------------------------------------------------------------------
                | AMOUNT ASLI
                |--------------------------------------------------------------------------
                */

                $originalAmount =
                    (float) (
                        $payment['amount']
                        ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | ADJUSTMENT BARU
                |--------------------------------------------------------------------------
                */

                $adjustment =
                    $totalHarga;


                /*
                |--------------------------------------------------------------------------
                | REMAINING
                |--------------------------------------------------------------------------
                */

                $remainingAmount =
                    $originalAmount -
                    $adjustment;


                /*
                |--------------------------------------------------------------------------
                | UPDATE
                |--------------------------------------------------------------------------
                */

                $payments[$index][
                    'adjustment'
                ] =
                    $adjustment;


                $payments[$index][
                    'payment_request_amount'
                ] =
                    $adjustment;


                $payments[$index][
                    'remaining_amount'
                ] =
                    $remainingAmount;


                $payments[$index][
                    'adjustment_by'
                ] =
                    auth()->id();


                $payments[$index][
                    'adjustment_at'
                ] =
                    now()->format(
                        'Y-m-d H:i:s'
                    );


                $newPayment =
                    $payments[$index];


                break;

            }


            /*
            |--------------------------------------------------------------------------
            | PAYMENT TIDAK DITEMUKAN
            |--------------------------------------------------------------------------
            */

            if (!$paymentFound) {

                throw new \Exception(

                    'Payment dengan payment_id "' .
                    $paymentId .
                    '" tidak ditemukan di SPK #' .
                    $spk->id .
                    '.'

                );

            }


            /*
            |--------------------------------------------------------------------------
            | 16. SIMPAN SPK
            |--------------------------------------------------------------------------
            */

            $spkData['payments'] =
                array_values(
                    $payments
                );


            $spk->data =
                $spkData;


            $spk->save();


            /*
            |--------------------------------------------------------------------------
            | 17. REFRESH + VERIFIKASI
            |--------------------------------------------------------------------------
            */

            $spk->refresh();


            $verifyData =
                $spk->data;


            if (
                is_string(
                    $verifyData
                )
            ) {

                $verifyData =
                    json_decode(
                        $verifyData,
                        true
                    )
                    ?? [];

            }


            $verifyPayments =
                $verifyData['payments']
                ?? [];


            $verifyPayment =
                null;


            foreach (
                $verifyPayments
                as $payment
            ) {

                if (
                    (string) (
                        $payment['payment_id']
                        ?? ''
                    )
                    ===
                    (string) $paymentId
                ) {

                    $verifyPayment =
                        $payment;

                    break;

                }

            }


            if (!$verifyPayment) {

                throw new \Exception(

                    'Payment berhasil disimpan tetapi gagal diverifikasi dari SPK.'

                );

            }


            /*
            |--------------------------------------------------------------------------
            | PASTIKAN ADJUSTMENT BENAR
            |--------------------------------------------------------------------------
            */

            $savedAdjustment =
                (float) (
                    $verifyPayment['adjustment']
                    ?? 0
                );


            if (
                abs(
                    $savedAdjustment -
                    $totalHarga
                ) > 0.0001
            ) {

                throw new \Exception(

                    'Adjustment SPK tidak sesuai. ' .

                    'Expected: ' .
                    $totalHarga .

                    ', Actual: ' .
                    $savedAdjustment

                );

            }


            /*
            |--------------------------------------------------------------------------
            | 18. GRAND TOTAL
            |--------------------------------------------------------------------------
            */

            $grandTotal =
                PengajuanDetail::where(
                    'pengajuan_id',
                    $detailData->pengajuan_id
                )
                ->sum(
                    'total_harga'
                );


            /*
            |--------------------------------------------------------------------------
            | 19. COMMIT
            |--------------------------------------------------------------------------
            */

            DB::commit();


            /*
            |--------------------------------------------------------------------------
            | 20. RESPONSE
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'success' =>
                    true,

                'message' =>
                    'Detail Finance dan payment SPK berhasil diperbarui.',

                'sync_spk' =>
                    true,

                'payment_request_saved_id' =>
                    $saved->id,

                'payment_request_id' =>
                    $paymentRequest->id,

                'payment_id' =>
                    $paymentId,

                'spk_id' =>
                    $spk->id,

                'old_payment' => [

                    'amount' =>
                        $oldPayment['amount']
                        ?? 0,

                    'adjustment' =>
                        $oldPayment['adjustment']
                        ?? 0,

                ],

                'new_payment' => [

                    'amount' =>
                        $verifyPayment['amount']
                        ?? 0,

                    'adjustment' =>
                        $verifyPayment['adjustment']
                        ?? 0,

                    'payment_request_amount' =>
                        $verifyPayment[
                            'payment_request_amount'
                        ]
                        ?? 0,

                    'remaining_amount' =>
                        $verifyPayment[
                            'remaining_amount'
                        ]
                        ?? 0,

                ],

                'detail' => [

                    'id' =>
                        $detailData->id,

                    'date' =>
                        $detailData->date
                            ? \Carbon\Carbon::parse(
                                $detailData->date
                            )->format(
                                'd/m/Y'
                            )
                            : '-',

                    'no_po' =>
                        $detailData->no_po,

                    'no_inv' =>
                        $detailData->no_inv,

                    'type_biaya' =>
                        $detailData->type_biaya,

                    'nama_barang' =>
                        $detailData->nama_barang,

                    'qty' =>
                        $detailData->qty,

                    'harga_satuan' =>
                        $detailData->harga_satuan,

                    'total_harga' =>
                        $detailData->total_harga,

                ],

                'grand_total' =>
                    $grandTotal,

            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);


            return response()->json([

                'success' =>
                    false,

                'message' =>
                    $e->getMessage(),

                'error' =>
                    config('app.debug')
                        ? $e->getMessage()
                        : null,

            ], 500);

        }
    }
}