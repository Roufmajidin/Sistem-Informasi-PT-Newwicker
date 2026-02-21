<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    /* ================= GLOBAL ================= */
    let currentSpk = {};
    let maxQtyIn = 0;
    let currentPoId = null; // ✅ WAJIB

    /* ================= ELEMENT ================= */
    const tableBody = document.querySelector('#editTable tbody');
    const addRowBtn = document.getElementById('addRowBtn');
    const saveBtn = document.getElementById('saveBtn');
    const kategoriSelect = document.getElementById('kategoriFilter');

    /* ================= OPEN MODAL ================= */
    document.addEventListener('click', async e => {
        const btn = e.target.closest('.btn-qty-in');
        if (!btn) return;

        currentSpk = JSON.parse(btn.dataset.spk || '{}');
        maxQtyIn = Number(btn.dataset.qty || 0);

        const detailId = btn.dataset.detail;
        const poId = btn.dataset.po;
        currentPoId = poId;

        document.getElementById('detailId').value = detailId;
        document.getElementById('qtyModalTitle').innerHTML =
            `${btn.dataset.item} <small class="text-muted">(Qty PO: ${maxQtyIn})</small>`;

        tableBody.innerHTML = '';

        /* ===== ISI SELECT KATEGORI DINAMIS ===== */
        const kategoriSet = new Set(Object.keys(currentSpk)); // ambil semua kategori
        kategoriSelect.innerHTML =
            `<option value="">-- Semua Kategori --</option>` + [...kategoriSet].map(k =>
                `<option value="${k}">${k}</option>`
            ).join('');
        kategoriSelect.value = ''; // default kosong → semua

        /* ===== LOAD EXISTING DATA ===== */
        await loadTimeline();
        new bootstrap.Modal(document.getElementById('qtyModal')).show();
    });

    /* ================= LOAD TIMELINE ================= */
    async function loadTimeline() {
        const detailId = document.getElementById('detailId').value;
        const kategori = kategoriSelect.value;
        const spkParam = JSON.stringify(currentSpk);

        const url = kategori ?
            `/production-timeline/${kategori}?po_id=${currentPoId}&detail_po_id=${detailId}&spk_id=${encodeURIComponent(spkParam)}` :
            `/production-timeline?po_id=${currentPoId}&detail_po_id=${detailId}&spk_id=${encodeURIComponent(spkParam)}`;

        const res = await fetch(url);
        const response = await res.json(); // ambil seluruh JSON
        const data = response.data || []; // ambil key 'data'
        const kesimpulan = response.kesimpulan || {}; // ambil key 'kesimpulan' jika perlu

        // ===== POPULATE TABLE =====
        tableBody.innerHTML = '';
        if (data.length) data.forEach(d => addRow(d));
        else addRow();

        // ===== RENDER KESIMPULAN =====
        const container = document.getElementById('summaryContainer');
 container.innerHTML = '';

// Loop per kategori
Object.entries(kesimpulan).forEach(([kategori, val]) => {
    const inQty = val.in || 0;
    const outQty = val.out || 0;
    const totalSpk = val.total_spk || 0;
    const belumMasuk = val.belum_masuk || 0;
    const serviceOut = val.service_out || 0;
    const netIn = inQty ;
    const prosesLain = val.proses_lain || 0;

    const html = `
        <div class="mb-3">
            <strong>${kategori.toUpperCase()}</strong>
            <table class="table table-sm table-bordered mt-1">
                <thead class="table-light text-center">
                    <tr>
                        <th>In</th>
                        <th>Net</th>
                        ${serviceOut ? '<th>Service</th>' : ''}
                        <th>Out</th>
                        <th>Total SPK</th>
                        <th>Belum Masuk</th>
                        ${prosesLain ? '<th>Proses Lain</th>' : ''}
                    </tr>
                </thead>
                <tbody>
                    <tr class="text-center">
                        <td>${inQty}</td>
                        <td>${netIn}</td>
                        ${serviceOut ? `<td>${serviceOut}</td>` : ''}
                        <td>${outQty}</td>
                        <td>${totalSpk}</td>
                        <td>${belumMasuk}</td>
                        ${prosesLain ? `<td>${prosesLain}</td>` : ''}
                    </tr>
                </tbody>
            </table>
            <small class="text-muted">Note: NET adalah barang faktual di pabrik</small>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
});
    }


    /* ================= ADD ROW ================= */
    addRowBtn.addEventListener('click', () => addRow());

    function addRow(data = null) {
        const row = document.createElement('tr');
        if (data?.id) row.dataset.id = data.id;

        row.innerHTML = `
        <td><input type="number" class="table-input inQty" style="width:80px"></td>

        <td>
            <input type="number" class="table-input outQty" style="width:80px">
            <div class="form-check mt-1 d-none serviceWrap">
                <input class="form-check-input table-checkbox serviceCheck" type="checkbox">
                <label class="form-check-label small">Service</label>
            </div>
        </td>

        <td>
            <select class="table-select subSelect">
                <option value="">-- Sub --</option>
                ${Object.keys(currentSpk).map(
                    s => `<option value="${s}">${s}</option>`
                ).join('')}
            </select>
        </td>

        <td>
            <select class="table-select spkSelect">
                <option value="">-- SPK --</option>
            </select>
        </td>

        <td>
            <input type="date" class="table-input dateInput">
        </td>

        <td>
            <input type="text" class="table-input remarkInput">
        </td>

      <td class="text-center">
    <button class="btn btn-sm btn-success btnSaveRow d-none">Save Edit</button>
    <button class="btn btn-sm btn-danger btnDel">✕</button>
</td>
    `;

        tableBody.appendChild(row);

        /* ===== FILL DATA (EDIT MODE) ===== */
        if (data) {
            if (data.type === 'in') {
                row.querySelector('.inQty').value = data.qty;
                row.querySelector('.outQty').disabled = true;
            } else {
                row.querySelector('.outQty').value = data.qty;
                row.querySelector('.inQty').disabled = true;
                row.querySelector('.serviceWrap').classList.remove('d-none');
            }

            row.querySelector('.subSelect').value = data.sup;

            /* populate spk */
            const spkSelect = row.querySelector('.spkSelect');
            const raw = currentSpk[data.sup] || {};
            Object.values(raw).forEach(spk => {
                spkSelect.insertAdjacentHTML(
                    'beforeend',
                    `<option value="${spk.spk_id}">
                    ${spk.sup} (Qty: ${spk.qty})
                </option>`
                );
            });

            spkSelect.value = data.spk_id;
            row.querySelector('.dateInput').value = data.date;
            row.querySelector('.remarkInput').value = data.remark || '';
            row.querySelector('.serviceCheck').checked = data.is_service == 1;
        } else {
            row.querySelector('.dateInput').value = new Date().toISOString().slice(0, 10);
        }
    }

    /* ================= DELETE ROW ================= */
    tableBody.addEventListener('click', e => {
        if (e.target.classList.contains('btnDel')) {
            e.target.closest('tr').remove();
        }
    });

    /* ================= IN / OUT LOGIC ================= */
    tableBody.addEventListener('input', e => {
        const row = e.target.closest('tr');
        if (!row) return;
// kalau row lama → tampilkan tombol save edit
    if (row.dataset.id) {

        row.querySelector('.btnSaveRow').classList.remove('d-none');

        // 🔥 highlight row berubah
        row.classList.add('table-warning');
    }
        const inQty = row.querySelector('.inQty');
        const outQty = row.querySelector('.outQty');
        const serviceWrap = row.querySelector('.serviceWrap');
        const serviceCheck = row.querySelector('.serviceCheck');

        if (e.target === inQty && inQty.value > 0) {
            outQty.value = '';
            outQty.disabled = true;
            serviceWrap.classList.add('d-none');
            serviceCheck.checked = false;
            if (+inQty.value > maxQtyIn) inQty.value = maxQtyIn;
        }

        if (e.target === outQty && outQty.value > 0) {
            inQty.value = '';
            inQty.disabled = true;
            serviceWrap.classList.remove('d-none');
        }

        if (!inQty.value) outQty.disabled = false;
        if (!outQty.value) {
            inQty.disabled = false;
            serviceWrap.classList.add('d-none');
            serviceCheck.checked = false;
        }
    });

    /* ================= SUB → SPK ================= */
    tableBody.addEventListener('change', e => {
        if (!e.target.classList.contains('subSelect')) return;

        const row = e.target.closest('tr');
        const spkSelect = row.querySelector('.spkSelect');

        spkSelect.innerHTML = `<option value="">-- SPK --</option>`;

        const raw = getFilteredSpkBySub(e.target.value);
        Object.values(raw).forEach(spk => {
            spkSelect.insertAdjacentHTML(
                'beforeend',
                `<option value="${spk.spk_id}">
                ${spk.sup} (Qty: ${spk.qty})
            </option>`
            );
        });
    });
tableBody.addEventListener('click', async e => {

    if (!e.target.classList.contains('btnSaveRow')) return;

    const row = e.target.closest('tr');

    const inQty = +row.querySelector('.inQty').value || 0;
    const outQty = +row.querySelector('.outQty').value || 0;
    const sub = row.querySelector('.subSelect').value;
    const spkId = row.querySelector('.spkSelect').value;

    if (!sub || !spkId || (!inQty && !outQty)) {
        return Swal.fire('Oops', 'Data belum lengkap', 'warning');
    }

    const payload = {
        detail_po_id: document.getElementById('detailId').value,
        type: inQty ? 'in' : 'out',
        po_id: currentPoId,
        qty: inQty || outQty,
        sup: sub,
        spk_id: spkId,
        date: row.querySelector('.dateInput').value,
        remark: row.querySelector('.remarkInput').value,
        is_service: row.querySelector('.serviceCheck')?.checked ? 1 : 0
    };

    try {

        const res = await fetch(`/production-timeline/${row.dataset.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (!res.ok) {
            return Swal.fire('Error', data.message, 'error');
        }

        await Swal.fire({
            icon: 'success',
            title: 'Updated',
            timer: 1000,
            showConfirmButton: false
        });

        row.querySelector('.btnSaveRow').classList.add('d-none');

        loadTimeline();

    } catch (err) {
        console.error(err);
    }

});
saveBtn.addEventListener('click', async () => {

    const rows = [...tableBody.querySelectorAll('tr')]
        .filter(r => !r.dataset.id); // hanya row baru

    if (!rows.length) {
        return Swal.fire('Info', 'Tidak ada data baru', 'info');
    }

    try {

        for (const row of rows) {

            const inQty = +row.querySelector('.inQty').value || 0;
            const outQty = +row.querySelector('.outQty').value || 0;
            const sub = row.querySelector('.subSelect').value;
            const spkId = row.querySelector('.spkSelect').value;

            if (!sub || !spkId || (!inQty && !outQty)) {
                return Swal.fire('Oops', 'Ada baris belum lengkap', 'warning');
            }

            const payload = {
                detail_po_id: document.getElementById('detailId').value,
                type: inQty ? 'in' : 'out',
                po_id: currentPoId,
                qty: inQty || outQty,
                sup: sub,
                spk_id: spkId,
                date: row.querySelector('.dateInput').value,
                remark: row.querySelector('.remarkInput').value,
                is_service: row.querySelector('.serviceCheck')?.checked ? 1 : 0
            };

            const res = await fetch(`/production-timeline/store`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (!res.ok) {
                await Swal.fire('Error', data.message, 'error');
                throw new Error(data.message);
            }
        }

        await Swal.fire({
            icon: 'success',
            title: 'Data tersimpan',
            timer: 1200,
            showConfirmButton: false
        });

        loadTimeline();

    } catch (err) {
        console.error(err);
    }

});




    /* ================= FILTER KATEGORI ================= */
    kategoriSelect.addEventListener('change', loadTimeline);

    function getFilteredSpkBySub(sub) {
        const kategori = kategoriSelect.value;
        let raw = currentSpk[sub] || {};

        if (!kategori) return raw; // semua kategori
        const filtered = {};
        Object.values(raw).forEach(spk => {
            if (spk.kategori === kategori) filtered[spk.spk_id] = spk;
        });
        return filtered;
    }
    // render
    function renderSummary(kesimpulan) {
    const container = document.getElementById('summaryContainer');
    container.innerHTML = '';

    Object.entries(kesimpulan).forEach(([kategori, val]) => {
        const inQty = val.in || 0;
        const outQty = val.out || 0;
        const totalSpk = val.total_spk || 0;
        const belumMasuk = val.belum_masuk || 0;
        const serviceOut = val.service_out || 0;
        const netIn = inQty - serviceOut;
        const prosesLain = val.proses_lain || 0;

        const html = `
            <div class="mb-3">
                <strong>${kategori.toUpperCase()}</strong>
                <table class="table table-sm table-bordered mt-1">
                    <thead class="table-light text-center">
                        <tr>
                            <th>In</th>
                            <th>Net</th>
                            ${serviceOut ? '<th>Service</th>' : ''}
                            <th>Out</th>
                            <th>Total SPK</th>
                            <th>Belum Masuk</th>
                            ${prosesLain ? '<th>Proses Lain</th>' : ''}
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="text-center">
                            <td>${inQty}</td>
                            <td>${netIn}</td>
                            ${serviceOut ? `<td>${serviceOut}</td>` : ''}
                            <td>${outQty}</td>
                            <td>${totalSpk}</td>
                            <td>${belumMasuk}</td>
                            ${prosesLain ? `<td>${prosesLain}</td>` : ''}
                        </tr>
                    </tbody>
                </table>
                <small class="text-muted">Note: NET adalah barang faktual di pabrik</small>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    });
}

</script>

<style>
    .table-input,
    .table-select {
        border: 0 !important;
        box-shadow: none !important;
        background: #fff !important;
        padding: 4px 6px;
        font-size: 13px;
    }

    /* fokus ala excel */
    .table-input:focus,
    .table-select:focus {
        outline: none;
        border-bottom: 2px solid #0d6efd;
        background: #f8f9fa;
    }

    /* select */
    .table-select {
        appearance: auto;
        cursor: pointer;
    }

    /* option */
    .table-select option {
        background: #fff;
        color: #212529;
    }

    /* row aktif */
    #editTable tbody tr:focus-within {
        background-color: #f1f5ff;
    }

    .table-checkbox {
        transform: scale(.9);
    }
</style>
