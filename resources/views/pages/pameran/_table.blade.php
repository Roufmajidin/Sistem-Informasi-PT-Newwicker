{{-- resources/views/pages/pameran/_table.blade.php --}}
@forelse($pm as $i => $p)
<tr>
    <td >{{ $i+1 }}</td>
    <td>
    <img src="{{ asset('storage/pameran/' . $p->article_code . '.jpg') }}" width="80"  loading="lazy">

    </td>
    <td>{{ $p->article_code }}</td>
    <td class="sticky">{{ $p->name }}</td>
    <td>{{ $p->categories }}</td>

    <td>{{ $p->item_w }}</td>
    <td>{{ $p->item_d }}</td>
    <td>{{ $p->item_h }}</td>

    <td>{{ $p->packing_w }}</td>
    <td>{{ $p->packing_d }}</td>
    <td>{{ $p->packing_h }}</td>

    <td>{{ $p->set2 }}</td>
    <td>{{ $p->set3 }}</td>
    <td>{{ $p->set4 }}</td>
    <td>{{ $p->set5 }}</td>

    <td>{{ $p->composition }}</td>
    <td>{{ $p->finishing }}</td>
    <td>{{ $p->qty }}</td>
    <td>{{ $p->cbm }}</td>

  <td>{{ round($p->loadability_20) }}</td>
<td>{{ round($p->loadability_40) }}</td>
<td>{{ round($p->loadability_40hc) }}</td>

    <td>{{ $p->rangka }}</td>
    <td>{{ $p->anyam }}</td>
    <td>{{ $p->fob_jakarta_in_usd }}</td>
    <td>{{ $p->finishing_powder }}</td>
    <td>{{ $p->accessories }}</td>
    <td>{{ $p->electricity }}</td>

    <td>{{ $p->remark }}</td>
</tr>
@empty
<tr>
    <td colspan="30" class="text-center">Tidak ada data</td>
</tr>
@endforelse
<div id="importResult" class="mt-3"></div>

<div id="loadingIndicator" class="alert alert-warning text-center" style="display:none;">
    Memuat data, mohon tunggu...
</div>
