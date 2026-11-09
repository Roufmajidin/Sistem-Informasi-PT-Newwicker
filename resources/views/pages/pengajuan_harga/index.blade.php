@extends('master.master')

@section('content')
    <style>
        .ph-page {
            background: #f3f5f9;
            min-height: 100vh;
            padding: 20px;
            font-family: inherit;
            color: #29384e;
            font-size: 12px
        }

        .ph-filter,
        .ph-po-card {
            background: #fff;
            border: 1px solid #dfe6f0;
            border-radius: 9px;
            box-shadow: 0 2px 8px rgba(30, 53, 87, .035)
        }

        .ph-filter {
            padding: 13px 15px;
            margin-bottom: 18px
        }

        .ph-filter-grid {
            display: flex;
            gap: 10px;
            align-items: flex-end;
            flex-wrap: wrap
        }

        .ph-search {
            flex: 1 1 350px;
            min-width: 220px
        }

        .ph-filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px
        }

        .ph-filter-label {
            font-size: 9px;
            font-weight: 800;
            color: #526c8f;
            letter-spacing: .08em;
            text-transform: uppercase
        }

        .ph-input,
        .ph-select {
            height: 33px;
            border: 1px solid #dce5f0;
            border-radius: 6px;
            padding: 5px 10px;
            background: #fff;
            color: #31425a;
            font-size: 12px;
            outline: none
        }

        .ph-input:focus,
        .ph-select:focus,
        .ph-price-input:focus,
        .ph-remark-input:focus {
            border-color: #3978e5;
            box-shadow: 0 0 0 2px rgba(57, 120, 229, .08)
        }

        .ph-search .ph-input {
            width: 100%
        }

        .ph-help {
            font-size: 10px;
            color: #94a2b7;
            margin-top: 5px
        }

        .ph-company-tabs {
            display: flex;
            border: 1px solid #dce5f0;
            border-radius: 6px;
            padding: 3px;
            gap: 2px
        }

        .ph-company-tabs a {
            padding: 6px 10px;
            text-decoration: none;
            color: #516783;
            font-size: 10px;
            border-radius: 4px;
            font-weight: 700
        }

        .ph-company-tabs a.active {
            color: #245be7;
            background: #eaf0ff
        }

        .ph-btn {
            border: 1px solid #dbe3ed;
            border-radius: 5px;
            padding: 7px 10px;
            font-size: 11px;
            font-weight: 700;
            background: #fff;
            color: #465c79;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px
        }

        .ph-btn-primary {
            background: #245fe6;
            color: #fff;
            border-color: #245fe6
        }

        .ph-btn-add {
            background: #f0f6ff;
            border-color: #bfd4fa;
            color: #1f61cc
        }

        .ph-btn-danger {
            background: #fff3f3;
            border-color: #ffd4d4;
            color: #d44141;
            padding: 5px 8px
        }

        .ph-po-card {
            margin-bottom: 15px;
            overflow: hidden
        }

        .ph-po-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 12px;
            gap: 12px
        }

        .ph-po-title {
            font-size: 12px;
            font-weight: 800;
            color: #173d79
        }

        .ph-po-title small {
            font-weight: 500;
            color: #426080
        }

        .ph-po-toggle {
            border: 0;
            background: #51b66c;
            color: #fff;
            border-radius: 3px;
            width: 24px;
            height: 23px;
            cursor: pointer;
            font-weight: 900;
            font-size: 16px
        }

        .ph-table-wrap {
            overflow-x: auto
        }

        .ph-table {
            width: 100%;
            min-width: 1150px;
            border-collapse: collapse;
            table-layout: fixed
        }

        .ph-table th {
            background: #2b3d50;
            color: #fff;
            border: 1px solid #33475b;
            padding: 11px 9px;
            font-size: 10px;
            text-align: center
        }

        .ph-table td {
            border: 1px solid #edf0f4;
            padding: 12px 10px;
            vertical-align: top;
            background: #fff
        }

        .ph-table tbody tr:nth-child(2n) td {
            background: #fafbfd
        }

        .ph-table .ph-left {
            text-align: left
        }

        .ph-photo {
            height: 65px;
            width: 65px;
            object-fit: contain;
            border: 1px solid #e3e8ef;
            padding: 3px;
            border-radius: 5px;
            background: #fff
        }

        .ph-article {
            font-weight: 800;
            color: #1755d6;
            font-size: 11px
        }

        .ph-desc {
            color: #60718b;
            margin-top: 4px;
            line-height: 1.4
        }

        .ph-qty {
            color: #2350a1;
            font-weight: 600
        }

        .ph-empty {
            color: #94a3b8
        }

        .ph-work-head,
        .ph-work-row {
            display: grid;
            grid-template-columns: minmax(125px, 1.2fr) repeat(4, minmax(95px, 1fr)) 29px;
            gap: 7px;
            align-items: start
        }

        .ph-work-head {
            font-size: 9px;
            color: #59718d;
            font-weight: 800;
            text-align: left;
            padding-bottom: 6px;
            border-bottom: 1px solid #e6ebf2;
            margin-bottom: 8px
        }

        .ph-work-row {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #e8edf4
        }

        .ph-work-row:last-child {
            border-bottom: 0;
            margin-bottom: 4px
        }

        .ph-work-cell {
            min-width: 0
        }

        .ph-job-select,
        .ph-price-input {
            width: 100%;
            height: 31px;
            border: 1px solid #d8e2ef;
            border-radius: 5px;
            background: #fff;
            color: #2f435d;
            padding: 4px 6px;
            font-size: 11px
        }

        .ph-price-input {
            text-align: right
        }

        .ph-remark-input {
            display: block;
            width: 100%;
            min-height: 42px;
            margin-top: 5px;
            border: 1px solid #e0e7f0;
            border-radius: 5px;
            padding: 6px;
            font-size: 10px;
            resize: vertical;
            color: #475b75;
            background: #fbfcfe
        }

        .ph-job-remark {
            min-height: 60px
        }

        .ph-price-label {
            font-size: 9px;
            color: #94a3b8;
            display: block;
            margin-top: 4px
        }

        .ph-work-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-top: 6px
        }

        .ph-note {
            font-size: 10px;
            color: #94a3b8
        }

        .ph-pagination {
            padding: 13px 15px
        }

        .ph-empty-row {
            text-align: center;
            color: #93a2b5;
            padding: 25px !important
        }

        .ph-hidden {
            display: none !important
        }

        @media(max-width:700px) {
            .ph-page {
                padding: 10px
            }

            .ph-filter-grid {
                align-items: stretch
            }

            .ph-search {
                flex-basis: 100%
            }

            .ph-table {
                min-width: 1150px
            }
        }
    </style>

    @php
        $currentCompany = strtoupper((string) request('company', 'ALL'));
        $allowedCompanies = ['ALL', 'NW', 'NWS', 'NWR', 'NWD'];
        if (!in_array($currentCompany, $allowedCompanies, true)) {
            $currentCompany = 'ALL';
        }
    @endphp

    <div class="ph-page">
        <div class="ph-filter">
            <form action="{{ route('pengajuan-harga.index') }}" method="GET" class="ph-filter-grid">
                <div class="ph-search">
                    <label class="ph-filter-label" for="ph-search">SEARCH</label>
                    <input id="ph-search" class="ph-input" type="search" name="q" value="{{ $search }}"
                        placeholder="⌕  Cari No PO atau Buyer...">
                    <div class="ph-help">Cari nomor PO, buyer, atau article code.</div>
                </div>
                <div class="ph-filter-group">
                    <span class="ph-filter-label">COMPANY</span>
                    <div class="ph-company-tabs">
                        @foreach ($allowedCompanies as $company)
                            <a class="{{ $currentCompany === $company ? 'active' : '' }}"
                                href="{{ route('pengajuan-harga.index', array_filter(['q' => $search, 'company' => $company === 'ALL' ? null : $company, 'release' => request('release')])) }}">
                                {{ $company === 'ALL' ? 'All' : $company }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="ph-filter-group">
                    <label for="ph-release" class="ph-filter-label">RELEASE</label>
                    <select id="ph-release" class="ph-select" name="release">
                        <option value="desc" {{ request('release', 'desc') === 'desc' ? 'selected' : '' }}>DESC</option>
                        <option value="asc" {{ request('release') === 'asc' ? 'selected' : '' }}>ASC</option>
                    </select>
                </div>
                <button class="ph-btn ph-btn-primary" type="submit">▣ Filter</button>
                <a class="ph-btn" href="{{ route('pengajuan-harga.index') }}" title="Reset filter">↻</a>
            </form>
        </div>

        @forelse($pos as $po)
            <div class="ph-po-card">
                <div class="ph-po-heading">
                    <div class="ph-po-title">PO : {{ $po->order_no }} <small>({{ $po->company_name }})</small></div>
                    <button type="button" class="ph-po-toggle" aria-label="Toggle detail PO"
                        aria-expanded="true">⌄</button>
                </div>
                <div class="ph-po-body">
                    <div class="ph-table-wrap">
                        <table class="ph-table">
                            <thead>
                                <tr>
                                    <th style="width:90px">Gambar</th>
                                    <th style="width:65px">Qty</th>
                                    <th style="width:185px">Item</th>
                                    <th class="ph-left">Pekerjaan &amp; Pengajuan Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($po->details as $item)
                                    @php
                                        $detail = $item->detail ?? [];
                                        if (is_string($detail)) {
                                            $detail = json_decode($detail, true) ?: [];
                                        }
                                        if (!is_array($detail)) {
                                            $detail = [];
                                        }
                                        $article =
                                            $detail['article_nr_'] ??
                                            ($detail['article_code'] ??
                                                ($detail['article_nr'] ?? ($detail['article'] ?? '-')));
                                        $description =
                                            $detail['nw_description'] ??
                                            ($detail['description'] ?? ($detail['buyer_desc'] ?? '-'));
                                        $photo = $detail['photo'] ?? ($detail['image'] ?? ($detail['picture'] ?? null));
                                        $qty = $detail['qty'] ?? ($detail['quantity'] ?? ($item->qty ?? '-'));
                                        $jobs = $pekerjaan->get($item->id, collect());
                                    @endphp
                                    <tr>
                                        <td class="ph-left">
                                            @if (is_string($photo) && $photo !== '')
                                                <img class="ph-photo" src="{{ $photo }}" alt="{{ $article }}"
                                                    loading="lazy" onerror="this.style.display='none'">
                                            @else
                                                <span class="ph-empty">No image</span>
                                            @endif
                                        </td>
                                        <td><span class="ph-qty">{{ $qty }}</span></td>
                                        <td class="ph-left">
                                            <div class="ph-article">{{ $article }}</div>
                                            <div class="ph-desc">{{ $description }}</div>
                                        </td>
                                        <td>
                                            <div class="ph-work" data-po-id="{{ $po->id }}"
                                                data-detail-po-id="{{ $item->id }}"
                                                data-article="{{ $article }}">
                                                <div class="ph-work-head">
                                                    <div>Pekerjaan / Remark</div>
                                                    <div>PPIC Price / Remark</div>
                                                    <div>Director Price</div>
                                                    <div>Pengajuan Sub / Remark</div>
                                                    <div>Director Sub / Remark</div>
                                                    <div></div>
                                                </div>
                                                <div class="ph-job-list">
                                                    @forelse($jobs as $job)
                                                        <div class="ph-work-row ph-job-row"
                                                            data-job-id="{{ $job->id }}">
                                                            <div class="ph-work-cell">
                                                                <select class="ph-job-select" data-field="bahan_kategori_id"
                                                                    aria-label="Pekerjaan">
                                                                    <option value="">Pilih Pekerjaan</option>
                                                                    @foreach ($masterPekerjaan as $kategori)
                                                                        <option value="{{ $kategori->id }}"
                                                                            {{ (int) $job->bahan_kategori_id === (int) $kategori->id ? 'selected' : '' }}>
                                                                            {{ $kategori->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <textarea class="ph-remark-input ph-job-remark" data-field="remark" placeholder="Remark umum...">{{ $job->remark }}</textarea>
                                                            </div>
                                                            <div class="ph-work-cell">
                                                                <input class="ph-price-input" type="number" min="0"
                                                                    step="0.01" data-field="ppic_price"
                                                                    value="{{ $job->ppic_price }}" placeholder="0">
                                                                <textarea class="ph-remark-input" data-field="ppic_remark" placeholder="PPIC remark...">{{ $job->ppic_remark }}</textarea>
                                                            </div>
                                                            <div class="ph-work-cell">
                                                                <input class="ph-price-input" type="number" min="0"
                                                                    step="0.01" data-field="director_price"
                                                                    value="{{ $job->director_price }}" placeholder="0">
                                                            </div>
                                                            <div class="ph-work-cell">
                                                                <input class="ph-price-input" type="number" min="0"
                                                                    step="0.01" data-field="pengajuan_sub_price"
                                                                    value="{{ $job->pengajuan_sub_price }}"
                                                                    placeholder="0">
                                                                <textarea class="ph-remark-input" data-field="sub_remark" placeholder="Sub remark...">{{ $job->sub_remark }}</textarea>
                                                            </div>
                                                            <div class="ph-work-cell">
                                                                <input class="ph-price-input" type="number"
                                                                    min="0" step="0.01"
                                                                    data-field="director_sub_price"
                                                                    value="{{ $job->director_sub_price }}"
                                                                    placeholder="0">
                                                                <textarea class="ph-remark-input" data-field="director_remark" placeholder="Director remark...">{{ $job->director_remark }}</textarea>
                                                            </div>
                                                            <div><button type="button"
                                                                    class="ph-btn ph-btn-danger ph-remove-job"
                                                                    title="Hapus baris">×</button></div>
                                                        </div>
                                                    @empty
                                                        <div class="ph-work-row ph-job-row" data-job-id="">
                                                            <div class="ph-work-cell">
                                                                <select class="ph-job-select"
                                                                    data-field="bahan_kategori_id" aria-label="Pekerjaan">
                                                                    <option value="" selected>Pilih Pekerjaan
                                                                    </option>
                                                                    @foreach ($masterPekerjaan as $kategori)
                                                                        <option value="{{ $kategori->id }}">
                                                                            {{ $kategori->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <textarea class="ph-remark-input ph-job-remark" data-field="remark" placeholder="Remark umum..."></textarea>
                                                            </div>
                                                            <div class="ph-work-cell">
                                                                <input class="ph-price-input" type="number"
                                                                    min="0" step="0.01" data-field="ppic_price"
                                                                    placeholder="0">
                                                                <textarea class="ph-remark-input" data-field="ppic_remark" placeholder="PPIC remark..."></textarea>
                                                            </div>
                                                            <div class="ph-work-cell"><input class="ph-price-input"
                                                                    type="number" min="0" step="0.01"
                                                                    data-field="director_price" placeholder="0"></div>
                                                            <div class="ph-work-cell">
                                                                <input class="ph-price-input" type="number"
                                                                    min="0" step="0.01"
                                                                    data-field="pengajuan_sub_price" placeholder="0">
                                                                <textarea class="ph-remark-input" data-field="sub_remark" placeholder="Sub remark..."></textarea>
                                                            </div>
                                                            <div class="ph-work-cell">
                                                                <input class="ph-price-input" type="number"
                                                                    min="0" step="0.01"
                                                                    data-field="director_sub_price" placeholder="0">
                                                                <textarea class="ph-remark-input" data-field="director_remark" placeholder="Director remark..."></textarea>
                                                            </div>
                                                            <div><button type="button"
                                                                    class="ph-btn ph-btn-danger ph-remove-job"
                                                                    title="Hapus baris">×</button></div>
                                                        </div>
                                                    @endforelse
                                                </div>
                                                <div class="ph-work-footer">
                                                    <button type="button" class="ph-btn ph-btn-add ph-add-job">+ Add
                                                        Row</button>
                                                    <span class="ph-note">Perubahan belum tersimpan (UI only)</span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="ph-empty-row">Tidak ada detail item pada PO ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="ph-po-card">
                <div class="ph-empty-row">Tidak ada PO ditemukan.</div>
            </div>
        @endforelse

        <div class="ph-pagination">{{ $pos->links() }}</div>
    </div>

    <script>
        document.addEventListener('click', function(event) {
            const toggle = event.target.closest('.ph-po-toggle');
            if (toggle) {
                const body = toggle.closest('.ph-po-card').querySelector('.ph-po-body');
                const hidden = body.classList.toggle('ph-hidden');
                toggle.setAttribute('aria-expanded', String(!hidden));
                toggle.textContent = hidden ? '›' : '⌄';
                return;
            }

            const add = event.target.closest('.ph-add-job');
            if (add) {
                const list = add.closest('.ph-work').querySelector('.ph-job-list');
                const template = list.querySelector('.ph-job-row');
                if (!template) return;
                const row = template.cloneNode(true);
                row.dataset.jobId = '';
                row.querySelectorAll('input, textarea, select').forEach(function(field) {
                    field.value = '';
                });
                list.appendChild(row);
                return;
            }

            const remove = event.target.closest('.ph-remove-job');
            if (remove) {
                const row = remove.closest('.ph-job-row');
                const list = row.closest('.ph-job-list');
                if (list.querySelectorAll('.ph-job-row').length > 1) {
                    row.remove();
                } else {
                    row.dataset.jobId = '';
                    row.querySelectorAll('input, textarea, select').forEach(function(field) {
                        field.value = '';
                    });
                }
            }
        });
    </script>
@endsection
