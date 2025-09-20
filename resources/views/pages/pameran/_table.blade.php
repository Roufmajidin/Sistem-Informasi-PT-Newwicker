{{-- resources/views/pages/pameran/_table.blade.php --}}
@forelse($pm as $i => $p)
<tr>
    <td>{{ $i+1 }}</td>
    <td>
        @if($p->photo)
            <img src="{{ asset($p->photo) }}" alt="photo" width="80">
        @endif
    </td>
    <td>{{ $p->article_code }}</td>
    <td>{{ $p->name }}</td>
    <td>{{ $p->categories }}</td>

    <td>{{ $p->item_w }}</td>
    <td>{{ $p->item_d }}</td>
    <td>{{ $p->item_h }}</td>

    <td>{{ $p->pack_w }}</td>
    <td>{{ $p->pack_d }}</td>
    <td>{{ $p->pack_h }}</td>

    <td>{{ $p->set2 }}</td>
    <td>{{ $p->set3 }}</td>
    <td>{{ $p->set4 }}</td>
    <td>{{ $p->set5 }}</td>

    <td>{{ $p->composition }}</td>
    <td>{{ $p->finishing }}</td>
    <td>{{ $p->qty }}</td>
    <td>{{ $p->cbm }}</td>

    <td>{{ $p->load_20 }}</td>
    <td>{{ $p->load_40 }}</td>
    <td>{{ $p->load_40hc }}</td>

    <td>{{ $p->rangka }}</td>
    <td>{{ $p->anyam }}</td>
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
