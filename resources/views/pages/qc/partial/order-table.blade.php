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
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('toggleItems');
        if (!toggle) return;

        let expanded = false;

        toggle.addEventListener('click', function() {
            document.querySelectorAll('.extra-row')
                .forEach(row => row.classList.toggle('d-none'));

            expanded = !expanded;
            toggle.textContent = expanded ? 'Show less' : 'Show more';
        });
    });
    // detail
    // DETAIL CLICK ALERT
    document.addEventListener('DOMContentLoaded', function() {

        document.querySelectorAll('.btn-detail').forEach(btn => {
            btn.addEventListener('click', function() {

                const detailPoId = this.dataset.id;
                const kategoriId = document.getElementById('input-jenis')?.value;

                if (!kategoriId) {
                    alert('Pilih jenis terlebih dahulu');
                    return;
                }

                /* ===============================
                   UI: aktifkan row
                =============================== */
                document.querySelectorAll('.item-row')
                    .forEach(row => row.classList.remove('active-row'));

                const row = this.closest('tr');
                if (row) row.classList.add('active-row');

                /* ===============================
                   SET QTY
                =============================== */
                const qty = this.dataset.qty;
                const qtyText = document.getElementById('qtyText');
                const qtyInput = document.getElementById('qtyInput');

                if (qtyText) qtyText.textContent = qty;
                if (qtyInput) qtyInput.value = qty;

                /* ===============================
                   AJAX REQUEST
                =============================== */
                // alert('id : ' + detailPoId + '\n' +
                //       'kategori : ' + kategoriId);
                document.querySelector('.jenis').textContent = `======= ${kategoriId} =====`;

                fetch(`/qc/getData/${kategoriId}/${detailPoId}`)
                    .then(res => {
                        if (!res.ok) throw new Error('Network error');
                        return res.json(); // 🔥 WAJIB
                    })
                    .then(data => {

                        const datas = data.merged_result;

                        if (!datas || Object.keys(datas).length === 0) {
                            console.warn('Tidak ada data QC');
                            return;
                        }

                        const thead = document.querySelector('#qc-table thead');
                        const tbody = document.querySelector('#qc-table tbody');

                        thead.innerHTML = '';
                        tbody.innerHTML = '';

                        /* ===== THEAD ===== */
                        let headRow = '<tr>';
                        Object.keys(datas).forEach(checkpointName => {
                            headRow += `<th>${checkpointName}</th>`;
                        });
                        headRow += '</tr>';
                        thead.innerHTML = headRow;

                        /* ===== TBODY ===== */
                        let bodyRow = '<tr>';
                        Object.values(datas).forEach(item => {
                            bodyRow += `
                <td>
                   ${item?.size ?? ''}
                </td>
            `;
                        });
                        bodyRow += '</tr>';
                        tbody.innerHTML = bodyRow;
                        console.log(data);
                        // images
                        const container = document.getElementById('qc-defect-container');
                        container.innerHTML = ''; // reset

                        Object.entries(datas).forEach(([checkpointName, item], index) => {

                            const statusLabel = item.remark ?
                                `<span class="label info">${item.remark}</span>` :
                                `<span class="label success">OK</span>`;

                            let photosHtml = '';

                            if (item.photos && item.photos.length > 0) {
                                item.photos.forEach(photo => {
                                    photosHtml += `
                <div class="col-sm-3">
                    <p class="text-xs">${photo.keterangan ?? '-'}</p>
                    <img src="/${photo.path}"
                         class="img-responsive img-thumbnail">
                </div>
            `;
                                });
                            } else {
                                photosHtml = `
            <div class="col-sm-12 text-muted text-sm">
                Tidak ada foto
            </div>
        `;
                            }

                            container.innerHTML += `
        <div class="defect-item m-b">

            <div class="defect-header d-flex justify-content-between">
                <div>
                    ${statusLabel}
                    <strong class="m-l-sm">
                        ${index + 1}. ${checkpointName}
                    </strong>

                    <div class="text-sm text-muted m-t-xs">
                        Size: ${item.size ?? '-'}
                    </div>
                </div>
            </div>

            <div class="row m-t-sm">
                ${photosHtml}
            </div>
        </div>
    `;
                        });

                    })
                    .catch(err => {
                        console.error(err);
                        alert('Gagal mengambil data QC');
                    });
            });
        })


    });

    // jenis
    document.querySelectorAll('.item-jenis').forEach(function(item) {
        item.addEventListener('click', function() {

            const jenis = this.dataset.jenis;
            // ubah teks tombol
            document.getElementById('btn-jenis').innerHTML =
                jenis + ' <span class="caret"></span>';

            // simpan ke hidden input (kalau perlu submit)
            document.getElementById('input-jenis').value = jenis;
            document.getElementById('jenis').innerHTML =
                jenis + ' <span class="caret"></span>';

            // alert
        });
    });
</script>
<style>
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
