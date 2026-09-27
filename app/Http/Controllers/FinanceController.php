<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengajuan;

class FinanceController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | DRAFT PAYMENT
        |--------------------------------------------------------------------------
        | LOGIC LAMA - TIDAK DIUBAH
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
        |
        | Ambil Pengajuan sebagai parent.
        |
        | Detail barang divisi:
        |   $pengajuan->divisiItems
        |
        | Approval:
        |   $pengajuan->approvalSteps
        |
        | Pembuat:
        |   $pengajuan->user
        |
        | Divisi:
        |   $pengajuan->divisi
        |
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
}