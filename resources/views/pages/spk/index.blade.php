@extends('master.master')
@section('title','SPK Editable')
@section('content')

<style>
    .spk-table td,
    .spk-table th {
        vertical-align: middle;
        padding: 6px;
    }

    .editable {
        background: #fff8dc;
        cursor: text;
    }

    .spk-wrapper {
        overflow-x: auto;
    }

    .image-box {
        min-height: 90px;
        border: 1px dashed #ccc;
        padding: 4px;
        display: flex;
        flex-wrap: wrap;
    }

    .preview-img {
        height: 70px;
        margin: 4px;
        border: 1px solid #3c3c3cff;
        border-radius: 4px;
    }

    .editable {
        min-height: 28px;
        border-bottom: 1px solid #999;
        padding: 4px;
        outline: none;
    }

    .editable:empty:before {
        content: attr(data-placeholder);
        color: #aaa;
    }

    .suggest-box {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #282828ff;
        z-index: 999;
        max-height: 160px;
        overflow-y: auto;
        display: none;
    }

    .suggest-item {
        padding: 6px 8px;
        cursor: pointer;
    }

    .suggest-item:hover {
        background: #f6e9c6;
    }

    .note-box img {
        height: 60px;
        margin: 3px;
    }

    .spk-textarea {
        width: 100%;
        border: none;
        resize: none;
        font-size: 11px;
        background: #fff8dc;
        line-height: 1.5;
    }

    .spk-textarea:focus {
        outline: none;
        background: #eef6ff;
    }

    @media print {
        .editable {
            background: none;
        }

        input[type=file] {
            display: none;
        }
    }
    .material {
    max-width:200px;
    width: 200px;

    white-space: normal;        /* AUTO WRAP */
    word-wrap: break-word;      /* word lama */
    word-break: break-word;     /* kata panjang */

    line-height: 1.4;
    vertical-align: top;
}
</style>

<div class="box">
    <div class="box-header d-flex justify-content-between align-items-center">
        <h3>SPK PRODUKSI</h3>

        <div style="min-width:180px">
            <label style="font-size:12px; margin-bottom:2px;"><b>Jenis SPK</b></label>
            <select name="spk_type" id="spk_type" class="form-control form-control-sm">
                <option value="">-- Pilih --</option>
                <option value="rangka" {{ ($spk['type'] ?? '')=='rangka' ? 'selected' : '' }}>Rangka</option>
                <option value="anyam" {{ ($spk['type'] ?? '')=='anyam' ? 'selected' : '' }}>Anyam</option>
                <option value="decor" {{ ($spk['type'] ?? '')=='decor' ? 'selected' : '' }}>Decor</option>
                <option value="unfinish" {{ ($spk['type'] ?? '')=='unfinish' ? 'selected' : '' }}>Unfinish</option>
            </select>
        </div>
    </div>

    <div class="box-body spk-wrapper">
        <table class="table table-bordered spk-table">

            {{-- HEADER --}}
            <tr>
                <td colspan="6" style="border:none">
                    <img src="/images/newwicker-logo.png" height="60">
                </td>
                <td colspan="6" class="text-right" style="border:none">
                    <b>PT. NewWicker Indonesia</b><br>
                    Cirebon – Indonesia<br>
                    factory@newwicker.com
                </td>
            </tr>

            <tr>
                <td colspan="12"></td>
            </tr>

            {{-- INFO --}}
            <tr>
                <td><b>NO SPK</b></td>
                <td colspan="1" class="editable" contenteditable>{{ $spk['no_spk'] }}</td>
                <td colspan="3"></td>
                <td><b>NO PO</b></td>
                <td colspan="3" class="editable" contenteditable>{{ $spk['no_po'] }}</td>
            </tr>

            <tr>
                <td><b>Nama</b></td>
                <td colspan="4" style="position:relative">
                    <div contenteditable="true"
                        class="editable"
                        id="supplierInput">
                        {{ $spk['nama'] }}
                    </div>

                    <div id="supplierSuggest"
                        style="
            position:absolute;
            top:100%;
            left:0;
            right:0;
            background:#fff;
            border:1px solid #ccc;
            z-index:999;
            display:none;
            max-height:150px;
            overflow:auto;
         ">
                    </div>
                </td>

                <td colspan="7"></td>
            </tr>

            <tr>
                <td><b>Tgl Terima</b></td>
                <td colspan="4" class="editable" contenteditable>{{ $spk['tgl_terima'] }}</td>
                <td colspan="7"></td>
            </tr>

            <tr>
                <td><b>Tgl Selesai</b></td>
                <td colspan="4" class="editable" contenteditable>{{ $spk['tgl_selesai'] }}</td>
                <td colspan="7"></td>
            </tr>

            {{-- TABLE HEADER --}}
            <tr class="text-center">
                <th>Kode</th>
                <th>Gambar</th>
                <th>Nama</th>
                <th>P</th>
                <th>L</th>
                <th>T</th>
                <th>Material</th>
                <th>PCS</th>
                <th>SET</th>
                <th>Harga</th>
                <th>Total</th>
                <th>Catatan</th>
            </tr>

            {{-- ITEMS --}}
            @foreach($spk['items'] as $item)
            <tr>
                <td class="editable" contenteditable>{{ $item['kode'] }}</td>

                {{-- GAMBAR --}}
                <td>
                    <div class="image-box"
                        contenteditable
                        onpaste="handlePaste(event, this)">
                        @foreach($item['images'] as $img)
                        <img src="{{ $img }}" class="preview-img">
                        @endforeach
                    </div>

                    <input type="file"
                        accept="image/*"
                        multiple
                        capture="environment"
                        onchange="uploadPreview(this)">
                </td>

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
                <td>
                    <div class="editable note-box"
                        contenteditable
                        onpaste="handlePaste(event, this)">
                        {{ $item['catatan'] }}
                    </div>
                </td>
            </tr>
            @endforeach
          @include('pages.spk.partial1')

        </table>

    </div>
</div>
<!-- search -->
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
function getNumber(el) {
    if (!el) return 0;
    return parseFloat(el.innerText.replace(/[^0-9.]/g,'')) || 0;
}

function format(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

function hitungTotal(row) {
    const pcs   = getNumber(row.querySelector('.pcs'));
    const set   = getNumber(row.querySelector('.set'));
    const harga = getNumber(row.querySelector('.harga'));

    const qty = pcs > 0 ? pcs : set;
    const total = qty * harga;

    const totalCell = row.querySelector('.total');
    if (totalCell) {
        totalCell.innerText = format(total);
    }
}

/* =========================
   EVENT DELEGATION (PENTING)
   ========================= */
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

/* HITUNG SAAT LOAD */
document.querySelectorAll('.spk-table tbody tr').forEach(row => {
    hitungTotal(row);
});
</script>

<script>
document.addEventListener('keydown', function(e){
    if(e.target.isContentEditable && e.key === 'Enter'){
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

    // close when click outside
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

@endsection
