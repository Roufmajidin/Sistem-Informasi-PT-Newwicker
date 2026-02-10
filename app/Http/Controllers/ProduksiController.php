<?php
namespace App\Http\Controllers;

use App\Models\DetailPo;
use App\Models\Spk;

class ProduksiController extends Controller
{
    //
    public function index()
    {
        $detailPo = DetailPo::with('po')->get();
        $spks     = Spk::all();

        $result = [];

        foreach ($detailPo as $dp) {

            $mappedSpk = [];

            foreach ($spks as $spk) {

                $kategori = $spk->data['kategori'] ?? '-';
                $sup      = $spk->data['sup'] ?? '-';

                foreach ($spk->data['items'] ?? [] as $item) {

                    if (($item['detail_po_id'] ?? null) == $dp->id) {

                        // INIT KATEGORI
                        if (! isset($mappedSpk[$kategori])) {
                            $mappedSpk[$kategori] = [];
                        }

                        // INIT SPK (BIAR TIDAK DOBEL)
                        if (! isset($mappedSpk[$kategori][$spk->id])) {
                            $mappedSpk[$kategori][$spk->id] = [
                                'spk_id' => $spk->id,
                                'no_spk' => $spk->data['no_spk'] ?? '-',
                                'sup'    => $sup,
                                'qty'    => 0,
                            ];
                        }

                        // AKUMULASI QTY
                        $mappedSpk[$kategori][$spk->id]['qty'] += $item['qty'] ?? 0;
                    }
                }
            }

            $result[] = [
                'detail_po' => $dp,
                'spk'       => $mappedSpk,
            ];
        }

        // dd($result);
        return view('pages.spk.produksi.index', compact('result'));
    }
}
