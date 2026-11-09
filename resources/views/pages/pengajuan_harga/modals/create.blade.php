<div class="modal fade" id="createPengajuanModal" tabindex="-1" aria-labelledby="createPengajuanLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable ph-create-dialog"
        style="width:96vw;max-width:96vw">

        <div class="modal-content ph-create-content" style="max-height:95vh">

            <div class="modal-header">

                <div>

                    <h5 class="modal-title fw-bold" id="createPengajuanLabel">Create Pengajuan Harga</h5><small
                        class="text-muted">Cari produk Detail PO, pilih kategori/sub bahan, isi pembahanan</small>

                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal"
                    aria-label="Close"><span class="ph-close-fallback" aria-hidden="true">&times;</span></button>

            </div>

            <form id="createPengajuanForm" method="POST" enctype="multipart/form-data"
                action="{{ route('pengajuan-harga.store') }}">

                @csrf

                <input type="hidden" name="detail_po_id" id="phDetailPoId" required>

                <div class="modal-body p-3">

                    <div class="ph-create-layout">

                        <section class="border rounded p-3 bg-white">

                            <h6 class="fw-bold mb-3">01 — Detail Produk & Pembahanan</h6>

                            <div class="row g-3 mb-3">

                                <div class="col-lg-4">

                                    <label class="form-label fw-semibold">Nama Item / Article Nr. — cari di sini</label>

                                    <div class="position-relative">

                                        <input type="search" id="phArticleSearch" class="form-control"
                                            autocomplete="off" placeholder="Cari article / nama item...">

                                        <div id="phArticleResults"
                                            class="list-group position-absolute w-100 shadow bg-white"
                                            style="display:none;z-index:1100;max-height:280px;overflow:auto"></div>

                                    </div>

                                    <small id="phSearchStatus" class="text-muted">Ketik minimal 2 karakter.</small>

                                    <div class="border rounded bg-light d-flex align-items-center justify-content-center mt-3"
                                        style="height:190px">

                                        <img id="phProductImage" alt="Foto produk"
                                            style="display:none;max-width:100%;max-height:185px;object-fit:contain">

                                        <span id="phNoPhoto" class="small text-muted">Foto produk belum dipilih</span>

                                    </div>
                                    <div class="ph-photo-upload mt-2" data-preview="phProductImage"
                                        data-placeholder="phNoPhoto">
                                        <input type="file" name="image_file"
                                            accept="image/png,image/jpeg,image/webp,image/gif"
                                            class="ph-photo-input d-none">
                                        <div class="d-flex gap-2 align-items-center flex-wrap">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary ph-photo-browse">Browse
                                                Foto</button>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-secondary ph-photo-clear">Pakai Foto
                                                PO</button>
                                        </div>
                                        <div class="ph-photo-paste border p-2 mt-2 small text-muted"
                                            tabindex="0" role="button">Klik di sini lalu Ctrl+V untuk paste gambar
                                            dari Excel / Explorer, atau drag & drop foto</div>
                                        <small class="ph-photo-error text-danger d-block"></small>
                                    </div>
                                </div>

                                <div class="col-lg-8">

                                    <div class="table-responsive">

                                        <table
                                            class="table table-sm table-bordered align-middle mb-0 ph-product-edit-table">

                                            <tbody>

                                                <tr>

                                                    <th style="width:155px">Nama Item</th>

                                                    <td><input type="text" class="form-control form-control-sm"
                                                            id="phItemName" name="item_name" maxlength="255" required
                                                            placeholder="Nama item"></td>

                                                </tr>

                                                <tr>

                                                    <th>Article Nr.</th>

                                                    <td><input type="text" class="form-control form-control-sm"
                                                            id="phArticleNr" name="article_nr" maxlength="100" required
                                                            placeholder="Article number"></td>

                                                </tr>

                                                <tr>

                                                    <th>PO</th>

                                                    <td><input type="text" class="form-control form-control-sm"
                                                            id="phPoInfo" readonly
                                                            placeholder="Nomor PO dari database"></td>

                                                </tr>

                                                <tr>

                                                    <th>Buyer</th>

                                                    <td><input type="text" class="form-control form-control-sm"
                                                            id="phBuyer" readonly placeholder="Buyer dari PO"></td>

                                                </tr>

                                                <tr>

                                                    <th>Dimensi (W × D × H)</th>

                                                    <td>

                                                        <div class="d-flex align-items-center gap-1 flex-nowrap">

                                                            <input type="number" id="createItemW" name="item_w"
                                                                class="form-control form-control-sm ph-dim"
                                                                step="any" placeholder="W"><span>×</span>

                                                            <input type="number" id="createItemD" name="item_d"
                                                                class="form-control form-control-sm ph-dim"
                                                                step="any" placeholder="D"><span>×</span>

                                                            <input type="number" id="createItemH" name="item_h"
                                                                class="form-control form-control-sm ph-dim"
                                                                step="any" placeholder="H"><span
                                                                class="small text-muted">cm</span>

                                                        </div>

                                                    </td>

                                                </tr>

                                            </tbody>

                                        </table>

                                    </div>

                                    <small class="text-muted">Nama, article, dan dimensi dapat dikoreksi. PO dan Buyer

                                        mengikuti Detail PO.</small>

                                </div>

                            </div>

                            <div class="row g-3 mb-3">

                                <div class="col-md-6">

                                    <label class="form-label fw-semibold">Kategori Bahan</label>

                                    <div class="d-flex gap-1 align-items-center">

                                        <select name="kategori_id" id="phKategoriSelect"
                                            class="form-select form-select-sm flex-grow-1" required>

                                            <option value="">Pilih kategori</option>

                                            @foreach ($kategoriList as $kategori)
                                                <option value="{{ $kategori->id }}">{{ $kategori->name }}</option>
                                            @endforeach

                                        </select>

                                        <button type="button" class="btn btn-sm btn-outline-success ph-master-add"
                                            data-kind="kategori" title="Tambah kategori">＋</button>

                                        <button type="button" class="btn btn-sm btn-outline-primary ph-master-edit"
                                            data-kind="kategori" title="Edit kategori terpilih" disabled>✎</button>

                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <label class="form-label fw-semibold">Sub Bahan</label>

                                    <div class="d-flex gap-1 align-items-center">

                                        <select name="sub_bahan_id" id="phSubSelect"
                                            class="form-select form-select-sm flex-grow-1" required disabled>

                                            <option value="">Pilih kategori dahulu</option>

                                        </select>

                                        <button type="button" class="btn btn-sm btn-outline-success ph-master-add"
                                            data-kind="sub" title="Tambah sub bahan" disabled>＋</button>

                                        <button type="button" class="btn btn-sm btn-outline-primary ph-master-edit"
                                            data-kind="sub" title="Edit sub bahan terpilih" disabled>✎</button>

                                    </div>

                                </div>

                            </div>

                            <div class="p-2 fw-bold text-white" style="background:#26394e" id="phSubHeading">PILIH

                                KATEGORI & SUB BAHAN</div>

                            <div class="table-responsive ph-material-scroll">

                                <table class="table table-sm table-bordered align-middle mb-0" id="phMaterialTable">

                                    <thead>

                                        <tr>

                                            <th style="min-width:240px">NAMA BAHAN</th>

                                            <th style="width:90px">QTY</th>

                                            <th style="width:90px">UNIT</th>

                                            <th style="width:125px">HARGA</th>

                                            <th style="width:125px">TOTAL</th>

                                            <th style="width:40px"></th>

                                        </tr>

                                    </thead>

                                    <tbody id="phMaterialBody"></tbody>

                                    <tfoot>

                                        <tr>

                                            <th colspan="4">TOTAL</th>

                                            <th id="phSubtotal" class="text-end">Rp0</th>

                                            <th></th>

                                        </tr>

                                    </tfoot>

                                </table>

                            </div>

                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="phAddMaterial">+

                                Tambah Baris</button>

                            <small class="d-block text-muted mt-2">Nama Bahan: ketik minimal 2 huruf, gunakan ↑/↓ lalu

                                Tab atau Enter untuk memilih suggestion.</small>

                        </section>

                        <section class="border rounded p-3" style="background:#f7f9fc">

                            <h6 class="fw-bold mb-3">02 — Pengajuan</h6>

                            <label class="form-label fw-semibold" for="phSubName">Tujuan / Nama Sub (Supplier)</label>
                            <div class="position-relative mb-3" id="phSupplierWrap">
                                <input type="text" id="phSubName" name="sub_name" class="form-control"
                                    maxlength="255" autocomplete="off"
                                    placeholder="Cari nama supplier atau isi manual..." required>
                                <div id="phSupplierResults" class="list-group position-absolute w-100 bg-white shadow"
                                    style="display:none;z-index:1300;max-height:190px;overflow:auto"></div>
                                <small class="text-muted">Ketik minimal 2 huruf untuk mencari supplier.</small>
                            </div>
                            <label class="form-label">PIC</label>

                            <div class="form-control bg-light mb-3">{{ auth()->user()?->name ?? 'User login' }}</div>

                            <label class="form-label">Status</label>

                            <div class="mb-3"><span class="badge bg-secondary">Draft</span></div>

                            <label class="form-label">Harga Pengajuan</label>

                            <div class="rounded border bg-white p-3 fw-bold mb-3" id="phSummaryTotal">Rp0</div>

                            <label class="form-label">Remark</label>

                            <textarea name="remark" rows="4" class="form-control" placeholder="Catatan pengajuan..."></textarea>

                            <div class="small text-muted mt-3">Setelah simpan Draft, ajukan melalui Detail untuk

                                menjadi Pending.</div>

                        </section>

                    </div>

                </div>

                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary"
                        data-bs-dismiss="modal" data-dismiss="modal">Batal</button><button type="submit"
                        class="btn btn-primary" id="phSaveBtn">Simpan Draft Pengajuan</button></div>

            </form>

        </div>

    </div>

</div>

<!-- Panel master dipindah ke dalam modal Create via JS, tanpa backdrop kedua -->

<div class="ph-master-overlay" id="phMasterModal" role="dialog" aria-modal="true" aria-label="Master Bahan"
    hidden>

    <div class="modal-dialog modal-dialog-centered ph-master-dialog">

        <div class="modal-content ph-master-content">

            <form id="phMasterForm">

                <div class="modal-header">

                    <h5 class="modal-title" id="phMasterTitle">Master Bahan</h5><button type="button"
                        class="btn-close ph-master-cancel" aria-label="Close"><span class="ph-close-fallback"
                            aria-hidden="true">&times;</span></button>

                </div>

                <div class="modal-body">

                    <div id="phMasterError" class="alert alert-danger py-2 d-none"></div>

                    <label class="form-label" for="phMasterName">Nama</label>

                    <input id="phMasterName" class="form-control" name="name" maxlength="255" required
                        autocomplete="off" placeholder="Nama kategori / sub bahan">

                    <small id="phMasterHelp" class="text-muted"></small>

                </div>

                <div class="modal-footer"><button type="button"
                        class="btn btn-outline-secondary ph-master-cancel">Batal</button><button
                        class="btn btn-primary" id="phMasterSave" type="submit">Simpan</button></div>

            </form>

        </div>

    </div>

</div>

<style>
    /* Layout Create: header/footer tetap terlihat, hanya isi form yang scroll. */
    #createPengajuanModal .ph-create-dialog {
        height: calc(100dvh - 24px);
        margin: 12px auto;
        max-height: calc(100dvh - 24px);
    }

    #createPengajuanModal .ph-create-content {
        height: 100%;
        max-height: 100% !important;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    #createPengajuanModal .ph-create-content>.modal-header {
        flex: 0 0 auto;
        padding: 12px 18px;
    }

    #createPengajuanForm {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
    }

    #createPengajuanForm>.modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto !important;
        overflow-x: auto;
    }

    #createPengajuanForm>.modal-footer {
        flex: 0 0 auto;
        position: relative;
        z-index: 2;
        background: #fff;
        border-top: 1px solid #dee2e6;
        padding: 10px 18px;
        justify-content: flex-end;
    }

    /* Editor master ringkas: tidak mengikuti tinggi modal Create. */
    #phMasterModal.ph-master-overlay {
        position: absolute;
        inset: 0;
        z-index: 105;
        padding: 16px;
        overflow-y: auto;
        border-radius: 0;
    }

    #phMasterModal .ph-master-dialog {
        display: block;
        width: min(100%, 460px);
        max-width: 460px;
        height: auto !important;
        min-height: 0 !important;
        max-height: none !important;
        margin: auto !important;
        flex: 0 1 auto;
    }

    #phMasterModal .ph-master-content {
        display: block !important;
        height: auto !important;
        min-height: 0 !important;
        max-height: calc(100dvh - 60px) !important;
        overflow: hidden !important;
        background: #fff;
        border-radius: 8px;
    }

    #phMasterForm {
        display: flex;
        flex-direction: column;
        min-height: 0;
        max-height: calc(100dvh - 60px);
    }

    #phMasterForm>.modal-header,
    #phMasterForm>.modal-footer {
        flex: 0 0 auto;
        background: #fff;
        padding: 12px 16px;
    }

    #phMasterForm>.modal-body {
        flex: 0 1 auto;
        min-height: 0;
        padding: 16px;
        overflow-y: auto !important;
    }

    #phMasterForm>.modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }

    @media (max-width: 575px) {
        #createPengajuanModal .ph-create-dialog {
            width: 100vw !important;
            max-width: 100vw !important;
            height: 100dvh;
            max-height: 100dvh;
            margin: 0;
        }

        #createPengajuanModal .ph-create-content {
            border-radius: 0;
        }

        #phMasterModal.ph-master-overlay {
            padding: 10px;
        }
    }

    /* Master editor overlay: one Bootstrap modal only */
    #createPengajuanModal .modal-content {
        position: relative;
    }

    #phMasterModal.ph-master-overlay[hidden] {
        display: none !important;
    }

    #phMasterModal.ph-master-overlay {
        position: absolute;
        inset: 0;
        z-index: 50;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, .40);
        padding: 18px;
        border-radius: inherit;
    }

    #phMasterModal .modal-dialog {
        width: min(100%, 510px);
        max-width: 510px;
        margin: 0;
        pointer-events: auto;
    }

    #phMasterModal .modal-content {
        overflow: hidden;
    }

    #phMasterModal .btn-close {
        cursor: pointer;
    }

    /* Tombol tutup tetap terlihat pada Bootstrap 4 maupun 5 */
    .ph-close-fallback {
        font-size: 20px;
        line-height: 1;
    }

    .btn-close .ph-close-fallback {
        display: none;
    }

    .close .ph-close-fallback {
        display: inline;
    }


    .ph-create-layout {

        display: grid !important;

        grid-template-columns: minmax(0, 2fr) minmax(290px, 1fr) !important;

        gap: 16px
    }



    .ph-create-layout>section {

        min-width: 0
    }



    #phMaterialTable {

        min-width: 620px
    }



    #phMaterialTable thead th {

        background: #26394e;

        color: #fff
    }



    #phMaterialTable td,

    #phMaterialTable th {

        padding: 7px;

        vertical-align: middle
    }



    #phMaterialTable tfoot th {

        background: #9bdef4;

        color: #153347
    }



    #phMaterialTable input {

        min-width: 0
    }



    .ph-dim {

        width: 65px !important;

        max-width: 65px !important;

        flex: 0 0 65px !important
    }



    .ph-material-cell {

        position: relative;

        min-width: 240px
    }



    .ph-material-results {

        position: absolute;

        left: 0;

        top: 100%;

        min-width: 310px;

        max-width: 480px;

        width: max-content;

        max-height: 230px;

        overflow-y: auto;

        background: #fff;

        z-index: 1200;

        border: 1px solid #ced4da;

        box-shadow: 0 4px 14px #0002;

        display: none
    }



    .ph-material-option {

        display: block;

        width: 100%;

        padding: 8px 10px;

        text-align: left;

        background: white;

        border: 0;

        border-bottom: 1px solid #eee;

        cursor: pointer
    }



    .ph-material-option.active,

    .ph-material-option:hover {

        background: #e5f0ff;

        color: #143f77
    }



    .ph-material-option small {

        display: block;

        color: #667085
    }



    .ph-material-scroll {

        overflow: visible
    }



    @media(max-width:900px) {

        .ph-create-layout {

            grid-template-columns: minmax(0, 1fr) !important
        }



        .ph-material-scroll {

            overflow-x: auto
        }

    }

    /* Override modal editor agar mengikuti tinggi konten, tidak memanjang */
    #phMasterModal.ph-master-overlay {
        position: absolute !important;
        inset: 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 12px !important;
        overflow: auto !important;
    }

    #phMasterModal.ph-master-overlay[hidden] {
        display: none !important;
    }

    #phMasterModal .ph-master-dialog {
        width: min(100%, 380px) !important;
        max-width: 380px !important;
        height: auto !important;
        min-height: 0 !important;
        max-height: none !important;
        margin: auto !important;
    }

    #phMasterModal .ph-master-content {
        height: auto !important;
        min-height: 0 !important;
        max-height: none !important;
        display: block !important;
        overflow: visible !important;
    }

    #phMasterForm {
        display: block !important;
        height: auto !important;
        min-height: 0 !important;
        max-height: none !important;
        overflow: visible !important;
    }

    #phMasterForm>.modal-body {
        padding: 14px !important;
        min-height: 0 !important;
        max-height: none !important;
        overflow: visible !important;
        flex: none !important;
    }

    #phMasterForm>.modal-header,
    #phMasterForm>.modal-footer {
        padding: 10px 14px !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const modal = document.getElementById('createPengajuanModal');
        const isBs4 = String(window.jQuery?.fn?.modal?.Constructor?.VERSION || '').startsWith('4.');
        if (isBs4) {
            document.querySelectorAll('#createPengajuanModal .btn-close, #phMasterModal .btn-close').forEach(
                btn => {
                    btn.classList.remove('btn-close');
                    btn.classList.add('close');
                });
        }

        if (!modal || modal.dataset.phInitialized === '1') return;

        modal.dataset.phInitialized = '1';

        const $ = id => document.getElementById(id);

        let allSubs = @json($subBahanList->map(fn($s) => ['id' => $s->id, 'kategori_id' => $s->bahan_kategori_id, 'name' => $s->name])->values());

        const articleUrl = @json(route('pengajuan-harga.search-article'));

        const materialUrl = @json(route('pengajuan-harga.search-material'));

        const rupiah = n => 'Rp' + Math.round(n).toLocaleString('id-ID');

        const kategori = $('phKategoriSelect'),

            sub = $('phSubSelect');



        function heading() {

            $('phSubHeading').textContent = ((kategori.selectedOptions[0]?.text || 'Kategori') + ' > ' + (sub

                .selectedOptions[0]?.text || 'Sub Bahan')).toUpperCase();

        }



        function syncButtons() {

            modal.querySelector('.ph-master-edit[data-kind="kategori"]').disabled = !kategori.value;

            modal.querySelector('.ph-master-add[data-kind="sub"]').disabled = !kategori.value;

            modal.querySelector('.ph-master-edit[data-kind="sub"]').disabled = !sub.value;

        }



        function rebuildSubs(selectedId = '') {

            sub.innerHTML = '<option value="">Pilih sub bahan</option>';

            allSubs.filter(s => String(s.kategori_id ?? s.bahan_kategori_id) === String(kategori.value))
                .forEach(s => sub.add(new Option(s

                    .name, s.id)));

            sub.disabled = !kategori.value;

            if (selectedId) sub.value = String(selectedId);

            syncButtons();

            heading();

        }

        kategori.addEventListener('change', () => rebuildSubs());

        sub.addEventListener('change', () => {

            heading();

            syncButtons();

        });

        syncButtons();



        // Panel master berada DI DALAM modal Create. Tidak menggunakan Bootstrap Modal kedua.
        // Ini menghindari backdrop ganda dan tombol yang tidak dapat diklik.
        const masterEl = $('phMasterModal');
        const createContent = modal.querySelector('.modal-content');
        createContent.appendChild(masterEl);
        const masterModal = {
            show() {
                masterEl.hidden = false;
                $('phMasterName').focus();
            },
            hide() {
                masterEl.hidden = true;
            }
        };

        const masterUrls = {

            kategori: @json(route('pengajuan-harga.master-kategori.store')),

            sub: @json(route('pengajuan-harga.master-sub.store'))

        };

        const masterBases = {

            kategori: @json(url('/pengajuan-harga/master-kategori')),

            sub: @json(url('/pengajuan-harga/master-sub'))

        };

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || modal.querySelector(

            'input[name="_token"]')?.value;

        let masterState = {

                kind: 'kategori',

                id: null

            },

            openingMaster = false,

            returnCreate = false;



        function openMaster(kind, edit) {

            if (kind === 'sub' && !kategori.value) {

                alert('Pilih kategori terlebih dahulu.');

                return;

            }

            const id = edit ? (kind === 'kategori' ? kategori.value : sub.value) : null;

            if (edit && !id) return;

            masterState = {

                kind,

                id

            };

            $('phMasterError').classList.add('d-none');

            $('phMasterName').value = edit ? (kind === 'kategori' ? kategori.selectedOptions[0].text : sub

                .selectedOptions[0].text) : '';

            $('phMasterTitle').textContent = (edit ? 'Edit ' : 'Tambah ') + (kind === 'kategori' ?

                'Kategori Bahan' : 'Sub Bahan');

            $('phMasterHelp').textContent = kind === 'sub' ? 'Kategori: ' + kategori.selectedOptions[0].text :

                'Master kategori untuk pengajuan harga';

            masterModal.show();

        }

        masterEl.querySelectorAll('.ph-master-cancel').forEach(btn => {
            btn.addEventListener('click', () => masterModal.hide());
        });
        masterEl.addEventListener('click', e => {
            if (e.target === masterEl) masterModal.hide();
        });
        masterEl.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                e.stopPropagation();
                masterModal.hide();
            }
        });

        modal.querySelectorAll('.ph-master-add,.ph-master-edit').forEach(btn => btn.addEventListener('click',

            () => openMaster(btn.dataset.kind, btn.classList.contains('ph-master-edit'))));

        $('phMasterForm').addEventListener('submit', async e => {

            e.preventDefault();

            const {

                kind,

                id

            } = masterState, name = $('phMasterName').value.trim();

            if (!name) return;

            const btn = $('phMasterSave');

            btn.disabled = true;

            $('phMasterError').classList.add('d-none');

            try {

                const payload = {

                    name

                };

                if (kind === 'sub') payload.bahan_kategori_id = kategori.value;

                const res = await fetch(id ? masterBases[kind] + '/' + encodeURIComponent(id) :

                    masterUrls[kind], {

                        method: id ? 'PUT' : 'POST',

                        headers: {

                            'Content-Type': 'application/json',

                            'Accept': 'application/json',

                            'X-CSRF-TOKEN': csrf

                        },

                        body: JSON.stringify(payload)

                    });

                const data = await res.json();

                if (!res.ok) throw new Error(data.message || Object.values(data.errors || {}).flat()

                    .join(' ') || 'Gagal menyimpan');

                const record = data.data;

                if (kind === 'kategori') {

                    let opt = [...kategori.options].find(o => o.value === String(record.id));

                    if (!opt) {

                        opt = new Option(record.name, record.id);

                        kategori.add(opt);

                    } else opt.text = record.name;

                    kategori.value = String(record.id);

                    rebuildSubs();

                } else {

                    // API mengembalikan bahan_kategori_id, sedangkan data awal memakai kategori_id.
                    // Normalisasi sebelum memperbarui daftar supaya hasil edit tidak hilang.
                    const i = allSubs.findIndex(x => String(x.id) === String(record.id));
                    const normalized = {
                        ...(i >= 0 ? allSubs[i] : {}),
                        ...record,
                        kategori_id: String(record.bahan_kategori_id ?? record.kategori_id ??
                            kategori.value),
                        bahan_kategori_id: String(record.bahan_kategori_id ?? record
                            .kategori_id ?? kategori.value)
                    };

                    if (i >= 0) allSubs[i] = normalized;
                    else allSubs.push(normalized);

                    rebuildSubs(normalized.id);

                }

                masterModal.hide();

            } catch (err) {

                $('phMasterError').textContent = err.message;

                $('phMasterError').classList.remove('d-none');

            } finally {

                btn.disabled = false;

            }

        });

        let rowId = 0;

        const escapeHtml = s => String(s ?? '').replace(/[&<>"']/g, c => ({

            '&': '&amp;',

            '<': '&lt;',

            '>': '&gt;',

            '"': '&quot;',

            "'": '&#39;'

        } [c]));



        function recalc() {

            let sum = 0;

            $('phMaterialBody').querySelectorAll('tr').forEach(tr => {

                const q = Number(tr.querySelector('.ph-qty')?.value || 0),

                    p = Number(tr.querySelector('.ph-price')?.value || 0),

                    line = q * p;

                sum += line;

                tr.querySelector('.ph-line-total').textContent = rupiah(line);

            });

            $('phSubtotal').textContent = rupiah(sum);

            $('phSummaryTotal').textContent = rupiah(sum);

        }



        function addRow(data = {}) {

            const id = rowId++,

                tr = document.createElement('tr');

            tr.innerHTML =

                `<td class="ph-material-cell"><input class="form-control form-control-sm ph-material-name" name="details[${id}][nama_bahan]" autocomplete="off" required placeholder="Cari nama material..." value="${escapeHtml(data.nama_bahan||'')}"><div class="ph-material-results"></div></td><td><input class="form-control form-control-sm ph-qty" name="details[${id}][qty]" type="number" step="0.001" min="0" value="${data.qty??1}" required></td><td><input class="form-control form-control-sm ph-unit" name="details[${id}][unit]" value="${escapeHtml(data.unit||'pcs')}" required></td><td><input class="form-control form-control-sm ph-price" name="details[${id}][harga]" type="number" min="0" step="any" value="${data.harga??0}" required></td><td class="ph-line-total text-end">Rp0</td><td><button type="button" class="btn btn-sm btn-outline-danger ph-remove">×</button></td>`;

            $('phMaterialBody').appendChild(tr);

            recalc();

        }

        $('phAddMaterial').addEventListener('click', () => addRow());

        $('phMaterialBody').addEventListener('input', recalc);

        $('phMaterialBody').addEventListener('click', e => {

            if (e.target.closest('.ph-remove')) {

                e.target.closest('tr').remove();

                if (!$('phMaterialBody').children.length) addRow();

                recalc();

            }

        });

        addRow();

        let materialTimer, materialController;



        function hideMaterials() {

            modal.querySelectorAll('.ph-material-results').forEach(el => {

                el.style.display = 'none';

                el.innerHTML = '';

            });

        }



        function chooseMaterial(input, item) {

            const tr = input.closest('tr');

            input.value = item.name || '';

            if (item.unit !== null && item.unit !== undefined) tr.querySelector('.ph-unit').value = item.unit;

            if (item.harga !== null && item.harga !== undefined) tr.querySelector('.ph-price').value = item

                .harga;

            hideMaterials();

            recalc();

        }

        $('phMaterialBody').addEventListener('input', e => {

            if (!e.target.matches('.ph-material-name')) return;

            const input = e.target,

                q = input.value.trim(),

                list = input.nextElementSibling;

            clearTimeout(materialTimer);

            if (materialController) materialController.abort();

            hideMaterials();

            if (q.length < 2) return;

            materialTimer = setTimeout(async () => {

                materialController = new AbortController();

                try {

                    const res = await fetch(materialUrl + '?q=' + encodeURIComponent(q), {

                        headers: {

                            Accept: 'application/json'

                        },

                        signal: materialController.signal

                    });

                    if (!res.ok) throw new Error('HTTP ' + res.status);

                    const items = await res.json();

                    if (input.value.trim() !== q || !input.isConnected) return;

                    list.innerHTML = '';

                    items.forEach((item, i) => {

                        const btn = document.createElement('button');

                        btn.type = 'button';

                        btn.className = 'ph-material-option' + (i === 0 ?

                            ' active' : '');

                        const strong = document.createElement('strong');

                        strong.textContent = item.name || '';

                        const meta = document.createElement('small');

                        meta.textContent = (item.source === 'finishing' ?

                            'Finishing' : 'Material') + ' · ' + (item.unit ||

                            '-') + ' · ' + (item.harga == null ?

                            'Harga manual' : rupiah(Number(item.harga)));

                        btn.append(strong, meta);

                        btn.addEventListener('mousedown', ev => {

                            ev.preventDefault();

                            chooseMaterial(input, item);

                            input.focus();

                        });

                        btn._material = item;

                        list.appendChild(btn);

                    });

                    list.style.display = items.length ? 'block' : 'none';

                } catch (err) {

                    if (err.name !== 'AbortError') console.error('Material search:', err);

                }

            }, 250);

        });

        $('phMaterialBody').addEventListener('keydown', e => {

            if (!e.target.matches('.ph-material-name')) return;

            const input = e.target,

                list = input.nextElementSibling;

            if (list.style.display === 'none') return;

            const options = [...list.querySelectorAll('.ph-material-option')];

            if (!options.length) return;

            let index = options.findIndex(o => o.classList.contains('active'));

            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {

                e.preventDefault();

                index = (index + (e.key === 'ArrowDown' ? 1 : -1) + options.length) % options.length;

                options.forEach((o, i) => o.classList.toggle('active', i === index));

                options[index].scrollIntoView({

                    block: 'nearest'

                });

            } else if (e.key === 'Tab' || e.key === 'Enter') {

                const selected = options[index >= 0 ? index : 0];

                if (e.key === 'Enter') e.preventDefault();

                chooseMaterial(input, selected._material);

                if (e.key === 'Enter') input.closest('tr').querySelector('.ph-qty').focus();

            } else if (e.key === 'Escape') {

                hideMaterials();

            }

        });

        const search = $('phArticleSearch'),

            results = $('phArticleResults');

        let articleTimer, articleController, selected = false;

        search.addEventListener('input', () => {

            selected = false;

            $('phDetailPoId').value = '';

            clearTimeout(articleTimer);

            if (articleController) articleController.abort();

            results.style.display = 'none';

            results.innerHTML = '';

            const q = search.value.trim();

            if (q.length < 2) {

                $('phSearchStatus').textContent = 'Ketik minimal 2 karakter.';

                return;

            }

            $('phSearchStatus').textContent = 'Mencari...';

            articleTimer = setTimeout(async () => {

                articleController = new AbortController();

                try {

                    const res = await fetch(articleUrl + '?q=' + encodeURIComponent(q), {

                        headers: {

                            Accept: 'application/json'

                        },

                        signal: articleController.signal

                    });

                    if (!res.ok) throw new Error('HTTP ' + res.status);

                    const data = await res.json();

                    if (search.value.trim() !== q) return;

                    results.innerHTML = '';

                    $('phSearchStatus').textContent = data.length ? data.length +

                        ' hasil ditemukan' : 'Tidak ditemukan.';

                    data.forEach(item => {

                        const btn = document.createElement('button');

                        btn.type = 'button';

                        btn.className =

                            'list-group-item list-group-item-action text-start';

                        btn.textContent = (item.article || item.article_nr || '-') +

                            ' — ' + (item.item_name || item.nw_description || item

                                .description || '-') + ' | PO: ' + (item

                                .po_number || item.order_no || '-');

                        btn.addEventListener('click', () => chooseArticle(item));

                        results.appendChild(btn);

                    });

                    results.style.display = data.length ? 'block' : 'none';

                } catch (err) {

                    if (err.name !== 'AbortError') $('phSearchStatus').textContent =

                        'Pencarian gagal: ' + err.message;

                }

            }, 300);

        });



        function chooseArticle(item) {

            selected = true;

            $('phDetailPoId').value = item.detail_po_id || item.id || '';

            search.value = (item.article || item.article_nr || '') + ' — ' + (item.item_name || item

                .nw_description || item.description || '');

            $('phItemName').value = item.item_name || item.nw_description || item.description || item

                .buyer_description || '';

            $('phArticleNr').value = item.article || item.article_nr || '';

            $('phPoInfo').value = (item.po_number || item.order_no || '-') + ' (ID: ' + (item.po_id || '-') +

                ')';

            $('phBuyer').value = item.buyer || item.company_name || '';

            $('createItemW').value = item.item_w ?? '';

            $('createItemD').value = item.item_d ?? '';

            $('createItemH').value = item.item_h ?? '';

            const photo = String(item.photo || '').trim();
            const isHttpPhoto = photo.startsWith('https://') || photo.startsWith('http://');
            const normalizedPhoto = photo.replace(/^\/+/, '');
            const isStoragePhoto = normalizedPhoto.startsWith('storage/');
            const ok = isHttpPhoto || isStoragePhoto;

            $('phProductImage').dataset.originalSrc = ok ? (isHttpPhoto ? photo : '/' + normalizedPhoto) : '';
            if ($('createPengajuanForm').querySelector('.ph-photo-input')?.files?.length) return;
            $('phProductImage').style.display = ok ? 'block' : 'none';
            $('phNoPhoto').style.display = ok ? 'none' : 'block';
            if (ok) {
                $('phProductImage').src = isHttpPhoto ? photo : '/' + normalizedPhoto;
            } else {
                $('phProductImage').removeAttribute('src');
            }

            results.style.display = 'none';

            $('phSearchStatus').textContent = 'Produk dipilih.';

        }

        document.addEventListener('click', e => {

            if (!e.target.closest('#phArticleSearch,#phArticleResults')) results.style.display = 'none';

            if (!e.target.closest('.ph-material-cell')) hideMaterials();

        });

        // Autocomplete supplier untuk tujuan/nama sub, tetap bisa input manual.
        const supplierInput = $('phSubName');
        const supplierResults = $('phSupplierResults');
        const supplierUrl = @json(route('pengajuan-harga.search-supplier'));
        let supplierTimer = null;
        let supplierAbort = null;
        let supplierActive = 0;

        function closeSupplierResults() {
            supplierResults.style.display = 'none';
            supplierResults.replaceChildren();
        }
        supplierInput.addEventListener('input', () => {
            clearTimeout(supplierTimer);
            if (supplierAbort) supplierAbort.abort();
            closeSupplierResults();
            const query = supplierInput.value.trim();
            if (query.length < 2) return;
            supplierTimer = setTimeout(async () => {
                const controller = new AbortController();
                supplierAbort = controller;
                try {
                    const response = await fetch(supplierUrl + '?q=' + encodeURIComponent(
                        query), {
                        headers: {
                            'Accept': 'application/json'
                        },
                        signal: controller.signal
                    });
                    if (!response.ok) throw new Error('Supplier search HTTP ' + response
                        .status);
                    const items = await response.json();
                    if (supplierInput.value.trim() !== query) return;
                    supplierActive = 0;
                    items.forEach((item, index) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className =
                            'list-group-item list-group-item-action' + (index ===
                                0 ? ' active' : '');
                        button.textContent = item.name;
                        button.addEventListener('mousedown', ev => {
                            ev.preventDefault();
                            supplierInput.value = item.name;
                            closeSupplierResults();
                        });
                        supplierResults.appendChild(button);
                    });
                    supplierResults.style.display = items.length ? 'block' : 'none';
                } catch (err) {
                    if (err.name !== 'AbortError') console.error('Supplier search:', err);
                }
            }, 250);
        });
        supplierInput.addEventListener('keydown', e => {
            if (supplierResults.style.display === 'none') return;
            const choices = [...supplierResults.querySelectorAll('button')];
            if (!choices.length) return;
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                supplierActive = (supplierActive + (e.key === 'ArrowDown' ? 1 : -1) + choices.length) %
                    choices.length;
                choices.forEach((b, i) => b.classList.toggle('active', i === supplierActive));
            } else if (e.key === 'Enter' || e.key === 'Tab') {
                if (e.key === 'Enter') e.preventDefault();
                supplierInput.value = choices[supplierActive].textContent;
                closeSupplierResults();
            } else if (e.key === 'Escape') closeSupplierResults();
        });
        document.addEventListener('click', e => {
            if (!e.target.closest('#phSupplierWrap')) closeSupplierResults();
        });

        $('createPengajuanForm').addEventListener('submit', e => {

            if (!selected || !$('phDetailPoId').value) {

                e.preventDefault();

                alert('Pilih produk dari hasil pencarian terlebih dahulu.');

                search.focus();

                return;

            }

            if (!$('phMaterialBody').children.length) {

                e.preventDefault();

                alert('Tambahkan minimal satu bahan.');

            }

        });

    });
</script>


<style>
    .ph-photo-paste {
        cursor: pointer;
        background: #f8fafc;
        border-style: dashed !important;
        outline-offset: 2px
    }

    .ph-photo-paste:focus,
    .ph-photo-paste.dragging {
        background: #e7f3ff;
        border-color: #0d6efd !important
    }
</style>
<script>
    (function() {
        if (window.__phPhotoUploadInitialized) return;
        window.__phPhotoUploadInitialized = true;
        const MAX = 5 * 1024 * 1024;
        const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        const state = new WeakMap();

        function elements(widget) {
            return {
                input: widget.querySelector('.ph-photo-input'),
                preview: document.getElementById(widget.dataset.preview),
                placeholder: document.getElementById(widget.dataset.placeholder),
                error: widget.querySelector('.ph-photo-error')
            };
        }

        function apply(widget, file) {
            const el = elements(widget);
            if (!file || !allowed.includes(file.type)) {
                el.error.textContent = 'Format gambar harus JPG, PNG, WEBP, atau GIF.';
                return;
            }
            if (file.size > MAX) {
                el.error.textContent = 'Maksimal ukuran foto 5 MB.';
                return;
            }
            el.error.textContent = '';
            const dt = new DataTransfer();
            dt.items.add(file);
            el.input.files = dt.files;
            const old = state.get(widget);
            if (old) URL.revokeObjectURL(old);
            const url = URL.createObjectURL(file);
            state.set(widget, url);
            el.preview.src = url;
            el.preview.style.display = 'block';
            if (el.placeholder) el.placeholder.style.display = 'none';
        }

        function reset(widget) {
            const el = elements(widget),
                old = state.get(widget);
            if (old) URL.revokeObjectURL(old);
            state.delete(widget);
            el.input.value = '';
            el.error.textContent = '';
            const original = el.preview.dataset.originalSrc || '';
            if (original) {
                el.preview.src = original;
                el.preview.style.display = 'block';
                if (el.placeholder) el.placeholder.style.display = 'none';
            } else {
                el.preview.removeAttribute('src');
                el.preview.style.display = 'none';
                if (el.placeholder) el.placeholder.style.display = '';
            }
        }
        document.addEventListener('click', function(e) {
            const browse = e.target.closest('.ph-photo-browse');
            if (browse) {
                browse.closest('.ph-photo-upload').querySelector('.ph-photo-input').click();
                return;
            }
            const clear = e.target.closest('.ph-photo-clear');
            if (clear) {
                reset(clear.closest('.ph-photo-upload'));
                return;
            }
            const zone = e.target.closest('.ph-photo-paste');
            if (zone) zone.focus();
        });
        document.addEventListener('change', function(e) {
            if (e.target.matches('.ph-photo-input') && e.target.files[0]) apply(e.target.closest(
                '.ph-photo-upload'), e.target.files[0]);
        });
        document.addEventListener('paste', function(e) {
            const target = e.target instanceof Element ? e.target : null;
            const widget = target?.closest('.ph-photo-upload') || target?.closest('.modal')?.querySelector(
                '.ph-photo-upload');
            if (!widget) return;
            const image = Array.from(e.clipboardData?.items || []).find(i => i.kind === 'file' && i.type
                .startsWith('image/'));
            if (!image) return;
            e.preventDefault();
            apply(widget, image.getAsFile());
        });
        document.addEventListener('dragover', function(e) {
            const zone = e.target.closest?.('.ph-photo-paste');
            if (zone) {
                e.preventDefault();
                zone.classList.add('dragging');
            }
        });
        document.addEventListener('dragleave', function(e) {
            const zone = e.target.closest?.('.ph-photo-paste');
            if (zone) zone.classList.remove('dragging');
        });
        document.addEventListener('drop', function(e) {
            const zone = e.target.closest?.('.ph-photo-paste');
            if (!zone) return;
            e.preventDefault();
            zone.classList.remove('dragging');
            const file = Array.from(e.dataTransfer?.files || []).find(f => f.type.startsWith('image/'));
            if (file) apply(zone.closest('.ph-photo-upload'), file);
        });
    })();
</script>
