<div class="box m-b">
    <p class="text-mute d p-2">Details</p>

    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th colspan="6" class="text-center bg-light jenis">
                            <p id="jenis" class="jenis"></p>

                        </th>
                    </tr>

                    <tr>

                        <th>Order No</th>
                        <th>Article no</th>
                        <th>Item Name</th>
                        <th>Qty</th>
                        <th>Supplier</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detailP as $i => $item)
                    <tr class="item-row {{ $i >= 5 ? 'd-none extra-row' : '' }}">
                        <td>{{ $item->detail['no'] ?? '-' }}</td>
                        <td>{{ $item->detail['article_nr_'] ?? '-' }}</td>
                        <td>{{ $item->detail['description'] ?? '-' }}</td>
                        <td>{{ $item->detail['qty'] ?? 0 }}</td>
                        <td>-</td>
                        <td>
                            <a href="javascript:void(0)" class="text-primary btn-detail"
                                data-qty="{{ $item->detail['qty'] ?? 0 }}"
                                data-po-id="{{ $item->po_id }}"
                                data-nama="{{$item->detail['description'] ?? '-' }}"
                                data-article="{{ $item->detail['article_nr_'] ?? '-' }}"
                                data-id="{{ $item->id }}">detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            No items found
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>
            @if($detailP->count() > 5)
            <div class="text-center mt-2">
                <a href="javascript:void(0)" id="toggleItems"
                    class="text-primary" style="cursor:pointer;">
                    Show more
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
<script>
/* =========================================================
   GLOBAL STATE
========================================================= */
let qcBatchData = {};
let currentJenis = null;
let currentPoId = null;
let currentDetailPoId = null;

/* =========================================================
   RESET UI QC (ANTI DATA NYANGKUT)
========================================================= */
function resetQcUI() {

    qcBatchData = {};
    currentDetailPoId = null;

    const batch = document.getElementById('batch-container');
    if (batch) {
        batch.innerHTML = '<span class="text-muted">Pilih batch</span>';
    }

    const thead = document.querySelector('#qc-table thead');
    const tbody = document.querySelector('#qc-table tbody');
    if (thead) thead.innerHTML = '';
    if (tbody) tbody.innerHTML = '';

    const defect = document.getElementById('qc-defect-container');
    if (defect) defect.innerHTML = '';

    const progress = document.getElementById('progressQty');
    if (progress) progress.textContent = '-';
}

/* =========================================================
   DOM READY
========================================================= */
document.addEventListener('DOMContentLoaded', function () {

    /* ===============================
       TOGGLE ITEM (SHOW MORE)
    =============================== */
    const toggle = document.getElementById('toggleItems');
    if (toggle) {
        let expanded = false;
        toggle.addEventListener('click', function () {
            document.querySelectorAll('.extra-row')
                .forEach(r => r.classList.toggle('d-none'));
            expanded = !expanded;
            toggle.textContent = expanded ? 'Show less' : 'Show more';
        });
    }

    /* ===============================
       PILIH JENIS (EVENT DELEGATION)
    =============================== */
    document.body.addEventListener('click', function (e) {

        const el = e.target.closest('.item-jenis');
        if (!el) return;

        e.preventDefault();

        const jenis = el.dataset.jenis;
        const poId  = document.getElementById('po-id')?.value;

        if (!jenis || !poId) {
            alert('Jenis / PO tidak valid');
            return;
        }

        // set state
        currentJenis = jenis;
        currentPoId  = poId;

        // dropdown text
        const btnJenis = document.getElementById('btn-jenis');
        if (btnJenis) {
            btnJenis.innerHTML = jenis.toUpperCase() + ' <span class="caret"></span>';
        }

        // hidden input
        document.getElementById('input-jenis').value = jenis;
        document.getElementById('po-id').value = poId;

        // judul tabel
        const jenisEl = document.getElementById('jenis');
        if (jenisEl) {
            jenisEl.textContent = ` ${jenis.toUpperCase()} `;
        }

        // reset semua data QC lama
        resetQcUI();

        console.log('[SET JENIS]', { jenis, poId });
    });

    /* ===============================
       CLICK DETAIL ITEM
    =============================== */
  function openJenisDropdown() {
    const btn = document.getElementById('btn-jenis');
    if (!btn) return;

    // force click (cara manusia)
    btn.dispatchEvent(
        new MouseEvent('click', {
            bubbles: true,
            cancelable: true,
            view: window
        })
    );

    // highlight biar user notice
    btn.classList.add('btn-warning');
    setTimeout(() => btn.classList.remove('btn-warning'), 1200);
}


    document.querySelectorAll('.btn-detail').forEach(btn => {
        btn.addEventListener('click', function () {

            if (!currentJenis || !currentPoId) {
                  openJenisDropdown();


                return;
            }
            const nama = this.dataset.nama;
            // alert(nama);
            const detailPoId = this.dataset.id;
            const qty = this.dataset.qty;
            console.log('[DETAIL ITEM]', { detailPoId, qty,currentDetailPoId });
            // pindah item → reset UI
            if (currentDetailPoId !== detailPoId) {
                resetQcUI();
            }

            currentDetailPoId = detailPoId;
           document.getElementById('nama').textContent = nama;

            // aktifkan row
            document.querySelectorAll('.item-row')
                .forEach(r => r.classList.remove('active-row'));
            this.closest('tr')?.classList.add('active-row');

            // set qty
            const qtyText = document.getElementById('qtyText');
            if (qtyText) qtyText.textContent = qty;

            // fetch QC
            fetch(`/qc/getData/${currentJenis}/${detailPoId}/${currentPoId}`)
                .then(res => res.json())
                .then(res => {

                    qcBatchData = res.batches || {};

                    renderBatchButtons(qcBatchData);
                    renderProgressQty(qcBatchData);

                })
                .catch(() => alert('Gagal mengambil data QC'));
        });
    });

});

/* =========================================================
   RENDER BATCH BUTTON
========================================================= */
function renderBatchButtons(batches) {

    const container = document.getElementById('batch-container');
    const inspectorEl = document.getElementById('inspector');
    const qcdateEl = document.getElementById('qc_date');

    if (!container) return;

    container.innerHTML = '';

    if (!Object.keys(batches).length) {
        container.innerHTML = '<span class="text-muted">Belum ada batch</span>';
        if (inspectorEl) inspectorEl.textContent = 'N/A';
        if (qcdateEl) inspectorEl.textContent = 'N/A';
        return;
    }

    Object.entries(batches).forEach(([key, batch], index) => {

        const btn = document.createElement('button');
        btn.className = 'btn btn-sm btn-outline-primary m-r-xs';

        btn.innerHTML = `
            Batch ${batch.batch_ke}<br>
            <small>${batch.tanggal}</small>
        `;

        btn.addEventListener('click', () => {
            renderBatch(key);

            // set inspector sesuai batch yang diklik
            if (inspectorEl) {
                inspectorEl.textContent = batch.inspector || 'N/A';
            }
               if (qcdateEl) {
                qcdateEl.textContent = batch.tanggal || 'N/A';
            }
        });

        container.appendChild(btn);

        // auto select batch pertama
        if (index === 0) {
            renderBatch(key);
            if (inspectorEl) {
                inspectorEl.textContent = batch.inspector || 'N/A';
            }
              if (qcdateEl) {
                qcdateEl.textContent = batch.tanggal || 'N/A';
            }
        }
    });
}


/* =========================================================
   PROGRESS QTY
========================================================= */
function renderProgressQty(batches) {

    const qtyText = document.getElementById('qtyText');
    const progress = document.getElementById('progressQty');

    if (!qtyText || !progress) return;

    const qtyAsli = parseInt(qtyText.textContent || 0);
    let totalInspect = 0;

    Object.values(batches).forEach(b => {
        totalInspect += parseInt(b.jumlah_inspect || 0);
    });

    const percent = qtyAsli
        ? ((totalInspect / qtyAsli) * 100).toFixed(1)
        : 0;

    progress.textContent =
        `${totalInspect} / ${qtyAsli} (${percent}%)`;
}

/* =========================================================
   RENDER BATCH DETAIL
========================================================= */
function renderBatch(batchKey) {

    const batch = qcBatchData[batchKey];
    if (!batch) return;

    // table
    const thead = document.querySelector('#qc-table thead');
    const tbody = document.querySelector('#qc-table tbody');

    if (!thead || !tbody) return;

    thead.innerHTML = '';
    tbody.innerHTML = '';

    let head = '<tr>';
    let body = '<tr>';

    Object.entries(batch.checkpoints).forEach(([name, cp]) => {
        head += `<th>${name}</th>`;
        body += `<td>${cp.size ?? '-'}</td>`;
    });

    thead.innerHTML = head + '</tr>';
    tbody.innerHTML = body + '</tr>';

    // defect / photo
    const container = document.getElementById('qc-defect-container');
    if (!container) return;

    container.innerHTML = '';

    Object.entries(batch.checkpoints).forEach(([name, cp], i) => {

        let photosHtml = '';

        if (cp.photos && cp.photos.length) {
            cp.photos.forEach(p => {
                  const imgUrl = `/storage/${p.path}`;

        photosHtml += `
            <div class="col-sm-3">
                <img src="${imgUrl}" class="img-thumbnail">
                <p class="text-xs">${p.keterangan ?? '-'}</p>
            </div>`;
            });
        } else {
            photosHtml = '<div class="col-sm-12 text-muted">Tidak ada foto</div>';
        }

        container.innerHTML += `
            <div class="m-b">
                <strong>${i + 1}. ${name}</strong>
                <span class="label m-l-sm">${cp.remark ?? 'OK'}</span>
                <div class="row m-t-sm">${photosHtml}</div>
            </div>`;
    });
}
</script>
<style>
    .sticky-right {
    position: sticky;
    top: 60px;          /* jarak dari atas */
}
    .dropdown-menu {
    z-index: 9999;
}
    /* efek hover biar keliatan clickable */
    .table tbody tr {
        cursor: pointer;
    }

    /* row aktif */
    .table tbody tr.active-row {
        background-color: #e7f1ff;
        /* biru lembut */
    }

    /* animasi halus */
    .table tbody tr {
        transition: background-color .2s ease;
    }

    .table thead tr:first-child th {
        font-weight: bold;
        letter-spacing: 1px;
        /* background: #f5f5f5; */
        /* color:black */
    }
</style>
