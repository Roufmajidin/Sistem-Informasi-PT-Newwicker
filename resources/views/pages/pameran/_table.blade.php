{{-- resources/views/pages/pameran/_table.blade.php --}}
@forelse($pm as $i => $p)

    @php
        $a = \App\Models\Exhibition::find( $p->exhibition_id);
        $nm = $a->name;
    @endphp
    <input type="file" id="imageUploader" accept="image/png,image/jpeg" hidden>
<tr class="item-row"
    data-article="{{ trim($p->article_code) }}"
    data-exhibition="{{ $nm }}">
    <td>{{ $i+1 }}</td>

  <td class="sticky-col first-col">
        <img src="{{ asset('storage/pameran/' .$nm. '/' . trim($p->article_code) . '.webp') }}"
             width="80"
             class="product-img"
             loading="lazy">

           <td>
<a href="/pameran/download/{{ $nm }}/{{ trim($p->article_code) }}"
   class="btn btn-sm btn-primary">
   Download
</a>
    <td>{{ $p->article_code }}</td>

    {{-- Name sticky --}}
    <td class="sticky-col second-col">
        {{ $p->name }}
    </td>

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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    let currentArticle = null;
let currentExhibition = null;

$(document).on('dblclick', '.item-row', function () {
    // alert('sdsd')
    currentArticle = $(this).data('article');
    currentExhibition = $(this).data('exhibition');

    $('#imageUploader').click();
});

$('#imageUploader').on('change', function () {

    let file = this.files[0];

    if (!file) return;

    let formData = new FormData();
    formData.append('image', file);
    formData.append('article_code', currentArticle);
    formData.append('exhibition', currentExhibition);

    $('#loadingIndicator').show();

    $.ajax({
        url: "{{ route('pameran.uploadImage') }}",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        success: function (res) {

            $('#loadingIndicator').hide();

            if (res.success) {

                // refresh image
                let img = $('tr[data-article="'+currentArticle+'"] img');

                img.attr('src', res.url + '?t=' + new Date().getTime());

            } else {
                alert(res.message);
            }
        }
    });

});
   </script>
