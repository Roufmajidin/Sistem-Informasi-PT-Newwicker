@extends('master.master')
@section('title','SPK Editable')
@section('content')

@include('pages.spk.stylespk')

<div class="box">
    <div class="box-header d-flex justify-content-between align-items-center">
        <h3>SPK PRODUKSI</h3>
@if($spk['mode'] === 'edit')
    <span class="warning">EDIT MODE</span>
@else
    <span class="success">CREATE MODE</span>
@endif
        <div style="min-width:180px">
            <label style="font-size:12px; margin-bottom:2px;"><b>Jenis SPK</b></label>
            <select name="spk_type" id="spk_type" class="form-control form-control-sm">
                <option value="">-- Pilih --</option>
                <option value="rangka" {{ ($spk['type'] ?? '')=='rangka' ? 'selected' : '' }}>Rangka</option>
                <option value="anyam" {{ ($spk['type'] ?? '')=='anyam' ? 'selected' : '' }}>Anyam</option>
                <option value="decor" {{ ($spk['type'] ?? '')=='decor' ? 'selected' : '' }}>Decor</option>
                <option value="unfinish" {{ ($spk['type'] ?? '')=='unfinish' ? 'selected' : '' }}>Unfinish</option>
                <option value="unfinish" {{ ($spk['type'] ?? '')=='ikat' ? 'selected' : '' }}>Ikat</option>
                <option value="unfinish" {{ ($spk['type'] ?? '')=='final' ? 'selected' : '' }}>Final</option>
            </select>
        </div>
    </div>
<input type="hidden" id="spk_mode" value="{{ $spk['mode'] }}">

<input type="hidden" id="spk_id" value="{{ $spk['id'] }}">

    <div class="box-body spk-wrapper">
        <table class="table table-bordered spk-table">

            {{-- HEADER --}}
            <!-- @include('pages.spk.header') -->
            <tr>
                <td colspan="6" style="border:none">
                    <img src="/images/newwicker-logo.png" height="60">
                </td>
                <td colspan="6" class="text-right" style="border:none; position:relative">
                    <div class="editable" id="itemSearch" contenteditable
                        style="border:1px solid #ccc; padding:6px">
                        Ketik article / nama item
                    </div>
                    <div id="itemSuggest" class="suggest-box"></div>
                </td>
            </tr>

            <tr>
                <td colspan="12"></td>
            </tr>

            {{-- INFO --}}
            <tr>
                <td><b>NO SPK</b></td>
                <td colspan="1" class="editable no-spk" contenteditable>{{ $spk['no_spk'] }}</td>
                <td colspan="3"></td>
                <td><b>NO PO</b></td>
                <td colspan="3" class="editable no-po" contenteditable>{{ $spk['no_po'] }}</td>
                <td>  <button id="btnSaveSpk" class="btn btn-success btn-sm">
            💾 Save SPK
        </button></td>
            </tr>

            <tr>
                <td><b>Nama</b></td>
                <td colspan="4" style="position:relative">
                    <div contenteditable="true"
                        class="editable"
                        id="supplierInput">
                        {{ $spk['nama'] }}
                    </div>

                    <div id="supplierSuggest">
                    </div>
                </td>

                <td colspan="7"></td>
            </tr>

            <tr>
                <td><b>Tgl Terima</b></td>
                <td colspan="4" class="editable tgl-terima" contenteditable>{{ $spk['tgl_terima'] }}</td>
                <td colspan="7"></td>
            </tr>

            <tr>
                <td><b>Tgl Selesai</b></td>
                <td colspan="4" class="editable tgl-selesai" contenteditable>{{ $spk['tgl_selesai'] }}</td>
                <td colspan="7"></td>
            </tr>

            @include('pages.spk.partial2')

            {{-- ITEMS --}}

            @foreach($spk['items'] as $item)
            <tr class="spk-row" data-detail-id="{{ $item['detail_id'] }}">


                <td style="cursor:pointer" class="editable text-center kode-item delete-row" contenteditable>{{ $item['kode'] }}</td>

                {{-- GAMBAR --}}
                <td>
                    <div class="image-box "
                        contenteditable
                        onpaste="handlePaste(event, this)">
                        @foreach($item['images'] as $img)
                        <img src="{{ $img }}" class="preview-img">
                        @endforeach

                        <input type="file"
                            accept="image/*"
                            multiple
                            capture="environment"
                            onchange="uploadPreview(this)">
                </td>
                <input type="hidden" class="spk-row" data-detail-id="{{ $item['detail_id'] }}">

                <td class="editable nama" contenteditable>{{ $item['nama'] }}</td>
                <td class="editable text-center p" contenteditable>{{ $item['p'] }}</td>
                <td class="editable text-center l" contenteditable>{{ $item['l'] }}</td>
                <td class="editable text-center t" contenteditable>{{ $item['t'] }}</td>
                <td class="editable material" contenteditable>{{ $item['material'] }}</td>
                <td class="editable text-center pcs" contenteditable>{{ $item['pcs'] }}</td>
                <td class="editable text-center set" contenteditable>{{ $item['set'] }}</td>
                <td class="editable text-right harga" contenteditable>{{ $item['harga'] }}</td>
                <td class="text-right total">0</td>

                {{-- CATATAN --}}
               {{-- CATATAN --}}
<td>
    <div class="editable note-box"
        contenteditable
        onpaste="handlePaste(event, this)">

        @foreach($item['catatan']['images'] ?? [] as $img)
            <img src="{{ $img }}" class="preview-img">
        @endforeach

        {!! $item['catatan']['remark'] ?? '' !!}
    </div>
</td>

            </tr>

            @endforeach

            <tr id="spkItemAnchor"></tr>

            @include('pages.spk.partial1')
            <td colspan="3" style="vertical-align: top;margin-left:12px">
                <table class="table table-bordered" style="font-size:12px;">
                    <tr class="text-center">
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Note</th>
                    </tr>


                    <tr>
                        <td class="editable total-amount" contenteditable></td>
                        <td class="editable date-isian" contenteditable></td>
                        <td class="editable note-akhir" contenteditable></td>

                    </tr>

                </table>
            </td>
        </table>

    </div>
</div>
<!-- search -->
<!-- heler -->
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function extractNoteData(noteBox) {
        let remark = '';
        let images = [];

        noteBox.childNodes.forEach(node => {
            if (node.nodeType === Node.TEXT_NODE) {
                remark += node.textContent.trim();
            }

            if (node.nodeType === Node.ELEMENT_NODE && node.tagName === 'IMG') {
                images.push(node.src);
            }
        });

        return {
            remark: remark,
            images: images
        };
    }
</script>

<!-- delete row -->
<script>
   document.addEventListener('click', function (e) {
    if (e.target.classList.contains('delete-row')) {

        const row = e.target.closest('tr');

        Swal.fire({
            title: 'Yakin?',
            text: 'Baris ini akan dihapus',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                row.remove();
                renumberRows();

                Swal.fire({
                    icon: 'success',
                    title: 'Terhapus',
                    text: 'Baris berhasil dihapus',
                    timer: 1200,
                    showConfirmButton: false
                });
            }
        });
    }
});

    function renumberRows() {
        document.querySelectorAll('.spk-row').forEach((row, index) => {
            const noCell = row.querySelector('.row-no');
            if (noCell) {
                noCell.innerText = index + 1;
            }
        });
    }
</script>
<!-- helper wraping text -->
<script>
    document.addEventListener('keydown', function(e) {

        if (!e.target.isContentEditable) return;

        // BOLEH ENTER di material & catatan
        if (e.target.classList.contains('material') ||
            e.target.classList.contains('note-box')) {
            return;
        }

        // BLOK ENTER di field lain
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    });
</script>
<!-- hitung helper -->
<script>
    function getSatuan(row) {
        const pcs = parseFloat(row.querySelector('.pcs')?.innerText) || 0;
        const set = parseFloat(row.querySelector('.set')?.innerText) || 0;

        if (pcs > 0) return 'pcs';
        if (set > 0) return 'set';
        return '';
    }

    function getNumber(el) {
        if (!el) return 0;
        return parseFloat(el.innerText.replace(/[^0-9]/g, '')) || 0;
    }

    function format(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    /* =====================
       HITUNG TOTAL PER ROW
       ===================== */
    function hitungTotal(row) {
        const pcs = getNumber(row.querySelector('.pcs'));
        const set = getNumber(row.querySelector('.set'));
        const harga = getNumber(row.querySelector('.harga'));

        const qty = pcs > 0 ? pcs : set;
        const total = qty * harga;

        const totalCell = row.querySelector('.total');
        if (totalCell) {
            totalCell.innerText = format(total);
        }

        hitungGrandTotal();
    }

    /* =====================
       HITUNG TOTAL AMOUNT
       ===================== */
    function hitungGrandTotal() {
        let grandTotal = 0;

        document.querySelectorAll('.spk-table .total').forEach(td => {
            grandTotal += getNumber(td);
        });

        const amountCell = document.querySelector('.total-amount');
        if (amountCell) {
            amountCell.innerText = format(grandTotal);
        }
    }

    /* =====================
       EVENT LISTENER
       ===================== */
    document.addEventListener('keyup', function(e) {
        if (e.target.closest('.pcs, .set, .harga')) {
            const row = e.target.closest('tr');
            hitungTotal(row);
        }
    });

    document.addEventListener('paste', function(e) {
        if (e.target.closest('.pcs, .set, .harga')) {
            setTimeout(() => {
                const row = e.target.closest('tr');
                hitungTotal(row);
            }, 10);
        }
    });

    /* =====================
       HITUNG SAAT LOAD
       ===================== */
    document.querySelectorAll('.spk-table tr').forEach(row => {
        if (row.querySelector('.pcs') || row.querySelector('.set')) {
            hitungTotal(row);
        }
    });
</script>

<script>
    document.addEventListener('keydown', function(e) {
        if (e.target.isContentEditable && e.key === 'Enter') {
            e.preventDefault();
        }
    });
</script>
<script>
    const input = document.getElementById('supplierInput');
    const suggestBox = document.getElementById('supplierSuggest');
    let typingTimer;

    input.addEventListener('input', function() {
        const keyword = input.innerText.trim();

        clearTimeout(typingTimer);

        if (keyword.length < 2) {
            suggestBox.style.display = 'none';
            return;
        }

        typingTimer = setTimeout(() => {
            fetch(`/supplier/search?q=${encodeURIComponent(keyword)}`)
                .then(res => res.json())
                .then(data => {
                    suggestBox.innerHTML = '';

                    if (data.length === 0) {
                        suggestBox.style.display = 'none';
                        return;
                    }

                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'suggest-item';
                        div.textContent = item.name;
                        div.onclick = () => {
                            input.innerText = item.name;
                            suggestBox.style.display = 'none';
                        };
                        suggestBox.appendChild(div);
                    });

                    suggestBox.style.display = 'block';
                });
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !suggestBox.contains(e.target)) {
            suggestBox.style.display = 'none';
        }
    });
</script>

<script>
    function handlePaste(e, container) {
        let items = (e.clipboardData || window.clipboardData).items;

        for (let i = 0; i < items.length; i++) {
            let item = items[i];

            if (item.type.indexOf("image") !== -1) {
                e.preventDefault();

                let blob = item.getAsFile();
                let reader = new FileReader();

                reader.onload = function(event) {
                    let img = document.createElement('img');
                    img.src = event.target.result;
                    img.className = 'preview-img';
                    container.appendChild(img);
                };

                reader.readAsDataURL(blob);
            }
        }
    }

    function uploadPreview(input) {
        let container = input.previousElementSibling;

        Array.from(input.files).forEach(file => {
            let reader = new FileReader();
            reader.onload = e => {
                let img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'preview-img';
                container.appendChild(img);
            };
            reader.readAsDataURL(file);
        });

        input.value = '';
    }
</script>
<!-- save data -->
<script>
document.getElementById('btnSaveSpk').addEventListener('click', function () {

    let items = [];

    document.querySelectorAll('.spk-row').forEach(row => {
        const detailId = row.dataset.detailId;
        if (!detailId) return;

        let images = [];
        row.querySelectorAll('.image-box img').forEach(img => {
            images.push(img.src);
        });

        const noteBox = row.querySelector('.note-box');

        items.push({
            detail_id: detailId,
            kode: row.querySelector('.kode-item')?.innerText.trim() || '',
            nama: row.querySelector('.nama')?.innerText.trim() || '',
            p: row.querySelector('.p')?.innerText.trim() || '',
            l: row.querySelector('.l')?.innerText.trim() || '',
            t: row.querySelector('.t')?.innerText.trim() || '',
            material: row.querySelector('.material')?.innerText.trim() || '',
            pcs: row.querySelector('.pcs')?.innerText.trim() || '',
            set: row.querySelector('.set')?.innerText.trim() || '',
            satuan: getSatuan(row),
            harga: row.querySelector('.harga')?.innerText.trim() || '',
            total: row.querySelector('.total')?.innerText.trim() || '',
            images: images,
            catatan: noteBox ? extractNoteData(noteBox) : {
                remark: '',
                images: []
            }
        });
    });

    const mode  = document.getElementById('spk_mode')?.value;
    const spkId = document.getElementById('spk_id')?.value;
const noSpkEl = document.querySelector('.no-spk');
const noPoEl  = document.querySelector('.no-po');



    const payload = {
        spk_id: mode === 'edit' ? spkId : null, // 🔥 KUNCI
        spk_type: document.getElementById('spk_type').value,
       no_spk: noSpkEl ? noSpkEl.innerText.trim() : '',
    no_po:  noPoEl ? noPoEl.innerText.trim() : '',
        nama: document.getElementById('supplierInput')?.innerText || '',
        tgl_terima: document.querySelector('.tgl-terima')?.innerText || '',
        tgl_selesai: document.querySelector('.tgl-selesai')?.innerText || '',
        items: items
    };
//     console.log('NO SPK:', payload.no_spk);
// console.log('NO PO:', payload.no_po);

    // 🔥 URL DINAMIS
    let url = '';
    if (mode === 'edit') {
        url = "{{ url('/spk/update') }}/" + spkId;
    } else {
        url = "{{ url('/spk/create') }}/" + spkId; // spkId = PO ID
    }

    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(res => {
      if (res.success) {

        Swal.fire({
            icon: 'success',
            title: 'SPK Berhasil',
            html: `
                <div style="font-size:14px">
                    ${res.message}<br><br>
                    <b>No SPK:</b><br>
                    <span style="font-size:18px;color:#198754">
                        ${res.no_spk}
                    </span>
                </div>
            `,
            confirmButtonText: 'OK'
        });

    } else {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: res.message || 'Gagal menyimpan SPK'
        });
    }
    })
    .catch(err => {
        console.error(err);

    Swal.fire({
        icon: 'error',
        title: 'Error Server',
        text: 'Terjadi kesalahan pada server'
    });
    });
});
</script>


<!-- search and add row -->
<script>
    const itemInput = document.getElementById('itemSearch');
    const itemSuggest = document.getElementById('itemSuggest');
    let itemTimer;

    itemInput.addEventListener('input', function() {

        const keyword = itemInput.innerText.trim();

        clearTimeout(itemTimer);

        if (keyword.length < 2) {
            itemSuggest.style.display = 'none';
            return;
        }

        itemTimer = setTimeout(() => {

            fetch("{{ route('detailpo.search') }}?q=" + encodeURIComponent(keyword))

                .then(res => res.json())
                .then(data => {

                    itemSuggest.innerHTML = '';

                    if (!data.length) {
                        itemSuggest.style.display = 'none';
                        return;
                    }

                    data.forEach(item => {

                        const div = document.createElement('div');
                        div.className = 'suggest-item';

                        div.innerHTML = `
                    <b>${item.kode}</b><br>
                    <small>${item.nama}</small>
                `;

                        div.onclick = () => {
                            addItemRow(item);
                            itemInput.innerText = '';
                            itemSuggest.style.display = 'none';
                        };

                        itemSuggest.appendChild(div);
                    });

                    itemSuggest.style.display = 'block';
                });

        }, 300);
    });
</script>
<!-- add rows -->
<script>
    function addItemRow(item) {

        const exist = document.querySelector(
            `.spk-row[data-detail-id="${item.detail_id}"]`
        );

        if (exist) {
            alert('Item sudah ada');
            return;
        }

        // ===== BUILD IMAGE HTML =====
        let imagesHtml = '';

        if (item.images && item.images.length) {
            item.images.forEach(img => {
                imagesHtml += `<img src="${img}" class="preview-img">`;
            });
        }

        // fallback kalau backend cuma kirim photo
        if (!imagesHtml && item.photo) {
            imagesHtml = `<img src="${item.photo}" class="preview-img">`;
        }

        const tr = document.createElement('tr');
        tr.classList.add('spk-row');
        tr.dataset.detailId = item.detail_id;

        tr.innerHTML = `
        <td class="editable text-center kode-item delete-row" contenteditable>${item.kode}</td>

        <td>
            <div class="image-box" contenteditable onpaste="handlePaste(event,this)">
                ${imagesHtml}
            </div>

            <input type="file"
                accept="image/*"
                multiple
                capture="environment"
                onchange="uploadPreview(this)">
        </td>

        <td class="editable nama" contenteditable>${item.nama ?? ''}</td>
        <td class="editable text-center p" contenteditable>${item.p ?? ''}</td>
        <td class="editable text-center l" contenteditable>${item.l ?? ''}</td>
        <td class="editable text-center t" contenteditable>${item.t ?? ''}</td>
        <td class="editable material" contenteditable>${item.material ?? ''}</td>
        <td class="editable text-center pcs" contenteditable>${item.qty ?? 0}</td>
        <td class="editable text-center set" contenteditable>0</td>
        <td class="editable text-right harga" contenteditable>0</td>
        <td class="text-right total">0</td>

        <td>
            <div class="editable note-box"
                contenteditable
                onpaste="handlePaste(event,this)">
            </div>
        </td>
    `;

        const anchor = document.getElementById('spkItemAnchor');

        if (anchor) {
            anchor.before(tr);
        } else {
            document.querySelector('.spk-table tbody').appendChild(tr);
        }

        hitungTotal(tr);
    }
</script>


@endsection
