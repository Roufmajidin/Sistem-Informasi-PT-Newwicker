@extends('master.master')

@section('content')
    <style>
        .ph-page {
            padding: 20px;
            background: #f4f7fb;
            min-height: 80vh;
            font-size: 13px;
            color: #26374a
        }

        .ph-page * {
            box-sizing: border-box
        }

        .ph-panel {
            background: #fff;
            border: 1px solid #dfe7f1;
            border-radius: 10px;
            box-shadow: 0 2px 9px rgba(25, 47, 78, .04);
            margin-bottom: 16px;
            overflow: hidden
        }

        .ph-filter {
            padding: 16px;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 12px
        }

        .ph-filter .ph-search {
            flex: 0 1 370px;
            min-width: 230px;
            max-width: 100%
        }

        .ph-search-group {
            display: flex;
            align-items: stretch;
            gap: 7px
        }

        .ph-search-group .ph-control {
            flex: 1;
            min-width: 0
        }

        .ph-search-group .ph-btn {
            flex: none
        }

        .ph-filter .ph-filter-field {
            flex: 0 1 175px;
            min-width: 140px
        }

        .ph-filter .ph-reset {
            flex: none
        }

        .ph-page label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #50627a;
            margin: 0 0 5px;
            line-height: 1.4
        }

        .ph-control {
            display: block;
            width: 100%;
            min-width: 0;
            height: 36px;
            padding: 7px 10px;
            border: 1px solid #cfdbea;
            border-radius: 6px;
            background: #fff;
            color: #273b53;
            font-size: 12px;
            line-height: 1.4;
            outline: none
        }

        .ph-control:focus {
            border-color: #3977d5;
            box-shadow: 0 0 0 3px rgba(57, 119, 213, .10)
        }

        .ph-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            min-height: 35px;
            padding: 7px 12px;
            border-radius: 6px;
            border: 1px solid transparent;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            text-decoration: none;
            line-height: 1.3
        }

        .ph-btn:disabled {
            opacity: .55;
            cursor: wait
        }

        .ph-primary {
            background: #245dc2;
            color: white
        }

        .ph-primary:hover {
            background: #1c4eaa;
            color: white
        }

        .ph-save {
            background: #13865c;
            color: #fff
        }

        .ph-save:hover {
            background: #0e704c
        }

        .ph-delete {
            background: #fff2f2;
            color: #bb3030;
            border-color: #f3caca
        }

        .ph-soft {
            background: #eef4ff;
            color: #2456aa;
            border-color: #ccdbf7
        }

        .ph-soft:hover {
            background: #e3edff;
            color: #184a9d
        }

        .ph-pohead {
            padding: 14px 17px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            cursor: pointer;
            color: #174782;
            font-weight: 800;
            font-size: 13px
        }

        .ph-pohead small {
            color: #687e98;
            font-weight: 500;
            margin-left: 7px
        }

        .ph-chevron {
            transition:transform .15s}.ph-pohead[aria-expanded="false"] .ph-chevron {
                transform: rotate(-90deg)
            }

            .ph-table-scroll {
                overflow-x: auto
            }

            .ph-table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed
            }

            .ph-table thead th {
                padding: 11px 12px;
                text-align: left;
                background: #2a3c51;
                color: #fff;
                font-size: 12px;
                font-weight: 700
            }

            .ph-table tbody td {
                padding: 13px 12px;
                border-top: 1px solid #e8edf5;
                vertical-align: top
            }

            .ph-table tbody tr:nth-child(even) {
                background: #fafcff
            }

            .ph-photo {
                width: 68px;
                height: 68px;
                object-fit: contain;
                border: 1px solid #e0e7f0;
                border-radius: 7px;
                background: white
            }

            .ph-qty {
                font-weight: 800;
                color: #2059b4
            }

            .ph-article {
                font-weight: 800;
                color: #1753b5;
                word-break: break-word
            }

            .ph-description {
                margin-top: 6px;
                color: #687b92;
                font-size: 12px;
                line-height: 1.5;
                overflow-wrap: anywhere
            }

            .ph-work {
                min-width: 0
            }

            .ph-job {
                border: 1px solid #dbe5f1;
                border-radius: 8px;
                background: #fff;
                padding: 14px;
                margin-bottom: 10px
            }

            .ph-job-top {
                display: grid;
                grid-template-columns: minmax(150px, 1.4fr) repeat(3, minmax(90px, 1fr)) auto;
                gap: 10px;
                align-items: end
            }

            .ph-job-field {
                min-width: 0
            }

            .ph-combo {
                position: relative
            }

            .ph-combo-menu {
                position: absolute;
                top: calc(100% + 3px);
                left: 0;
                right: 0;
                z-index: 60;
                background: #fff;
                border: 1px solid #cfdbea;
                border-radius: 7px;
                box-shadow: 0 8px 24px #14263d25;
                max-height: 230px;
                overflow: auto
            }

            .ph-combo-menu[hidden] {
                display: none
            }

            .ph-combo-option {
                display: block;
                width: 100%;
                text-align: left;
                background: white;
                border: 0;
                padding: 9px 11px;
                cursor: pointer;
                font-size: 12px;
                color: #273b53
            }

            .ph-combo-option:hover,
            .ph-combo-option:focus {
                background: #eaf2ff
            }

            .ph-combo-create {
                color: #1455b3;
                font-weight: 700;
                border-top: 1px solid #e5eaf1
            }

            .ph-combo-input {
                padding-right: 24px
            }

            .ph-actions {
                display: flex;
                gap: 6px;
                align-items: end
            }

            .ph-actions .ph-btn {
                height: 36px
            }

            .ph-remarks[hidden] {
                display: none !important
            }

            .ph-remarks {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 12px;
                margin-top: 13px;
                padding-top: 12px;
                border-top: 1px solid #edf1f6
            }

            .ph-remarks textarea.ph-control {
                height: 65px;
                min-height: 65px;
                resize: vertical;
                line-height: 1.5
            }

            .ph-status {
                display: block;
                min-height: 0;
                margin-top: 6px;
                font-size: 11px;
                color: #56718f
            }

            .ph-status:not(:empty) {
                padding-top: 3px
            }

            .ph-remark-toggle {
                margin-top: 10px;
                background: transparent;
                border: 0;
                color: #245dc2;
                font-size: 12px;
                font-weight: 700;
                cursor: pointer;
                padding: 4px 0
            }

            .ph-category-add {
                margin-top: 6px;
                border: 0;
                background: none;
                color: #245dc2;
                font-size: 11px;
                font-weight: 700;
                cursor: pointer;
                padding: 0
            }

            .ph-category-panel {
                display: flex;
                gap: 6px;
                margin-top: 7px
            }

            .ph-category-panel[hidden] {
                display: none
            }

            .ph-category-panel input {
                flex: 1;
                min-width: 0
            }

            .ph-category-panel .ph-btn {
                min-height: 36px
            }

            .ph-add {
                margin-top: 2px
            }

            .ph-empty {
                text-align: center;
                padding: 25px;
                color: #7c8b9c
            }

            @media(max-width:1450px) {
                .ph-job-top {
                    grid-template-columns: minmax(150px, 1.4fr) repeat(3, minmax(90px, 1fr)) auto
                }

                .ph-job-field:first-child {
                    grid-column: auto
                }

                .ph-actions {
                    grid-column: auto;
                    justify-content: flex-end
                }

                .ph-table col.ph-col-photo {
                    width: 85px
                }

                .ph-table col.ph-col-qty {
                    width: 65px
                }

                .ph-table col.ph-col-item {
                    width: 230px
                }
            }

            @media(max-width:1050px) {
                .ph-table {
                    min-width: 940px
                }

                .ph-job-top {
                    grid-template-columns: repeat(4, minmax(0, 1fr))
                }

                .ph-filter .ph-search {
                    flex: 1 1 300px
                }
            }

            @media(max-width:600px) {
                .ph-page {
                    padding: 10px
                }

                .ph-filter {
                    gap: 9px;
                    padding: 12px
                }

                .ph-filter .ph-search {
                    flex: 1 1 100%
                }

                .ph-filter .ph-filter-field {
                    flex: 1 1 130px
                }

                .ph-search-group .ph-btn {
                    width: auto
                }

                .ph-filter .ph-reset {
                    width: auto
                }

                .ph-pohead {
                    padding: 12px
                }

                .ph-job {
                    padding: 10px
                }

                .ph-remarks {
                    gap: 8px
                }
            }

            /* Dropdown category inline edit */
            .ph-combo-item {
                display: flex;
                align-items: center;
                min-width: 0
            }

            .ph-combo-item .ph-combo-option {
                flex: 1;
                min-width: 0
            }

            .ph-combo-edit {
                border: 0;
                background: transparent;
                color: #5273a4;
                cursor: pointer;
                padding: 8px 12px;
                font-size: 17px
            }

            .ph-combo-edit:hover {
                color: #1455b3;
                background: #edf4ff
            }

            .ph-combo-rename {
                flex: 1;
                min-width: 0;
                margin: 4px;
                padding: 7px;
                border: 1px solid #8bb4f3;
                border-radius: 5px;
                font-size: 12px
            }

            .ph-combo-confirm,
            .ph-combo-cancel {
                border: 0;
                background: transparent;
                cursor: pointer;
                padding: 7px;
                color: #198754;
                font-size: 17px
            }

            .ph-combo-cancel {
                color: #b42318
            }
    </style>

    <div class="ph-page">
        <div class="ph-panel">
            <form method="GET" action="{{ route('pengajuan-harga.index') }}" class="ph-filter">
                <div class="ph-search">
                    <label for="ph-search-input">CARI PO / BUYER / ARTICLE</label>
                    <div class="ph-search-group">
                        <input id="ph-search-input" type="search" class="ph-control" name="q"
                            value="{{ $search }}" placeholder="Contoh: 26-80" autocomplete="off">
                        <button class="ph-btn ph-primary" type="submit">Cari</button>
                    </div>
                </div>
                <div class="ph-filter-field"><label>COMPANY</label><select class="ph-control" name="company">
                        <option value="">Semua Company</option>
                        @foreach (['NW', 'NWS', 'NWR', 'NWD'] as $c)
                            <option value="{{ $c }}" @selected($company === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ph-filter-field"><label>RELEASE</label><select class="ph-control" name="release">
                        <option value="desc" @selected($release === 'desc')>Terbaru</option>
                        <option value="asc" @selected($release === 'asc')>Terlama</option>
                    </select></div>
                <a class="ph-btn ph-soft ph-reset" href="{{ route('pengajuan-harga.index') }}">Reset</a>
            </form>
        </div>

        @forelse($pos as $po)
            <div class="ph-panel">
                <div class="ph-pohead" role="button" tabindex="0" aria-expanded="true">
                    <span>PO : {{ $po->order_no }} <small>({{ $po->company_name }})</small></span>
                    <span class="ph-chevron">▾</span>
                </div>
                <div class="ph-pobody ph-table-scroll">
                    <table class="ph-table">
                        <colgroup>
                            <col class="ph-col-photo" style="width:85px">
                            <col class="ph-col-qty" style="width:65px">
                            <col class="ph-col-item" style="width:240px">
                            <col>
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Qty</th>
                                <th>Item</th>
                                <th>Pekerjaan &amp; Pengajuan Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($po->details as $item)
                                @php
                                    $d = $item->detail ?? [];
                                    if (is_string($d)) {
                                        $d = json_decode($d, true) ?: [];
                                    }
                                    if (!is_array($d)) {
                                        $d = [];
                                    }
                                    $article =
                                        $d['article_nr_'] ??
                                        ($d['article_code'] ?? ($d['article_nr'] ?? ($d['article'] ?? '-')));
                                    $description =
                                        $d['nw_description'] ?? ($d['description'] ?? ($d['buyer_desc'] ?? '-'));
                                    $photo = $d['photo'] ?? ($d['image'] ?? ($d['picture'] ?? null));
                                    $qty = $d['qty'] ?? ($d['quantity'] ?? ($item->qty ?? '-'));
                                    $jobs = $pekerjaan->get($item->id, collect());
                                @endphp
                                <tr>
                                    <td>
                                        @if ($photo)
                                            <img class="ph-photo" src="{{ $photo }}" alt="Foto barang"
                                                onerror="this.style.display='none'">
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="ph-qty">{{ $qty }}</td>
                                    <td>
                                        <div class="ph-article">{{ $article }}</div>
                                        <div class="ph-description">{{ $description }}</div>
                                    </td>
                                    <td>
                                        <div class="ph-work" data-po-id="{{ $po->id }}"
                                            data-detail-id="{{ $item->id }}" data-article="{{ $article }}">
                                            <div class="ph-jobs">
                                                @forelse($jobs as $job)
                                                    <div class="ph-job" data-job-id="{{ $job->id }}">
                                                        <div class="ph-job-top">
                                                            <div class="ph-job-field"><label>Pekerjaan</label><select
                                                                    class="job-category" hidden>
                                                                    <option value="">Pilih Pekerjaan</option>
                                                                    @foreach ($masterPekerjaan as $cat)
                                                                        <option value="{{ $cat->id }}"
                                                                            @selected((int) $job->bahan_kategori_id === (int) $cat->id)>
                                                                            {{ $cat->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <div class="ph-combo"><input type="text"
                                                                        class="ph-control ph-combo-input" autocomplete="off"
                                                                        placeholder="Cari / ketik pekerjaan..."
                                                                        role="combobox" aria-expanded="false">
                                                                    <div class="ph-combo-menu" hidden></div>
                                                                </div>
                                                            </div>
                                                            @foreach (['ppic_price' => 'PPIC Price', 'director_price' => 'Director Price', 'pengajuan_sub_price' => 'Pengajuan Sub'] as $field => $label)
                                                                <div class="ph-job-field">
                                                                    <label>{{ $label }}</label><input type="text"
                                                                        inputmode="decimal" class="ph-control ph-price"
                                                                        data-field="{{ $field }}"
                                                                        value="{{ $job->{$field} }}" placeholder="0"></div>
                                                            @endforeach
                                                            <div class="ph-actions"><button type="button"
                                                                    class="ph-btn ph-save">Save</button><button
                                                                    type="button" class="ph-btn ph-delete"
                                                                    aria-label="Hapus pekerjaan">×</button></div>
                                                        </div>
                                                        <button type="button" class="ph-remark-toggle"
                                                            aria-expanded="false">▸ Show Remark</button>
                                                        <div class="ph-remarks" hidden>
                                                            @foreach (['ppic_remark' => 'PPIC Remark', 'sub_remark' => 'Sub Remark', 'director_remark' => 'Director Remark'] as $field => $label)
                                                                <div class="ph-job-field">
                                                                    <label>{{ $label }}</label>
                                                                    <textarea class="ph-control ph-remark" data-field="{{ $field }}" placeholder="{{ $label }}">{{ $job->{$field} }}</textarea>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <span class="ph-status" aria-live="polite"></span>
                                                    </div>
                                                @empty
                                                    <div class="ph-job" data-job-id="">
                                                        <div class="ph-job-top">
                                                            <div class="ph-job-field"><label>Pekerjaan</label><select
                                                                    class="job-category" hidden>
                                                                    <option value="">Pilih Pekerjaan</option>
                                                                    @foreach ($masterPekerjaan as $cat)
                                                                        <option value="{{ $cat->id }}">
                                                                            {{ $cat->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <div class="ph-combo"><input type="text"
                                                                        class="ph-control ph-combo-input"
                                                                        autocomplete="off"
                                                                        placeholder="Cari / ketik pekerjaan..."
                                                                        role="combobox" aria-expanded="false">
                                                                    <div class="ph-combo-menu" hidden></div>
                                                                </div>
                                                            </div>
                                                            @foreach (['ppic_price' => 'PPIC Price', 'director_price' => 'Director Price', 'pengajuan_sub_price' => 'Pengajuan Sub'] as $field => $label)
                                                                <div class="ph-job-field">
                                                                    <label>{{ $label }}</label><input
                                                                        type="text" inputmode="decimal"
                                                                        class="ph-control ph-price"
                                                                        data-field="{{ $field }}" placeholder="0">
                                                                </div>
                                                            @endforeach
                                                            <div class="ph-actions"><button type="button"
                                                                    class="ph-btn ph-save">Save</button><button
                                                                    type="button" class="ph-btn ph-delete"
                                                                    aria-label="Hapus pekerjaan">×</button></div>
                                                        </div>
                                                        <button type="button" class="ph-remark-toggle"
                                                            aria-expanded="false">▸ Show Remark</button>
                                                        <div class="ph-remarks" hidden>
                                                            @foreach (['ppic_remark' => 'PPIC Remark', 'sub_remark' => 'Sub Remark', 'director_remark' => 'Director Remark'] as $field => $label)
                                                                <div class="ph-job-field">
                                                                    <label>{{ $label }}</label>
                                                                    <textarea class="ph-control ph-remark" data-field="{{ $field }}" placeholder="{{ $label }}"></textarea>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <span class="ph-status" aria-live="polite"></span>
                                                    </div>
                                                @endforelse
                                            </div>
                                            <button type="button" class="ph-btn ph-soft ph-add">+ Tambah
                                                Pekerjaan</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="ph-empty">Belum ada item PO.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="ph-panel ph-empty">Tidak ada PO ditemukan.</div>
        @endforelse
        <div>{{ $pos->links() }}</div>
    </div>

    <script>
        (function() {
            const token = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());
            const saveUrl = @json(route('pengajuan-harga.save'));
            const deleteUrl = @json(route('pengajuan-harga.delete'));
            const categoryUrl = @json(route('pengajuan-harga.category.store'));
            const categoryUpdateUrl = @json(url('/pengajuan-harga/kategori')); // PATCH /{id}

            function money(s) {
                s = String(s || '').trim().replace(/\s/g, '').replace(/^Rp/i, '');
                if (!s) return null;
                if (s.includes(',')) s = s.replace(/\./g, '').replace(',', '.');
                else if (/^\d{1,3}(\.\d{3})+$/.test(s)) s = s.replace(/\./g, '');
                if (!/^\d+(\.\d{1,2})?$/.test(s)) throw new Error('Harga harus angka positif, maksimal 2 desimal.');
                return s;
            }
            async function send(url, method, data) {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(data)
                });
                const json = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(json.message || Object.values(json.errors || {}).flat().join(
                    ', ') || 'Gagal menyimpan.');
                return json;
            }

            function updateCombo(input) {
                const combo = input.closest('.ph-combo');
                const select = combo.parentElement.querySelector('.job-category');
                const menu = combo.querySelector('.ph-combo-menu');
                const query = input.value.trim().toLocaleLowerCase('id-ID');
                const options = [...select.options].filter(o => o.value);
                const exact = options.find(o => o.text.trim().toLocaleLowerCase('id-ID') === query);
                select.value = exact ? exact.value : '';
                menu.replaceChildren();
                options.filter(o => o.text.toLocaleLowerCase('id-ID').includes(query)).forEach(o => {
                    const item = document.createElement('div');
                    item.className = 'ph-combo-item';
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'ph-combo-option';
                    button.dataset.value = o.value;
                    button.textContent = o.text;
                    const edit = document.createElement('button');
                    edit.type = 'button';
                    edit.className = 'ph-combo-edit';
                    edit.dataset.id = o.value;
                    edit.title = 'Edit nama kategori';
                    edit.setAttribute('aria-label', 'Edit kategori ' + o.text);
                    edit.textContent = '✎';
                    item.append(button, edit);
                    menu.appendChild(item);
                });
                if (query && !exact) {
                    const add = document.createElement('button');
                    add.type = 'button';
                    add.className = 'ph-combo-option ph-combo-create';
                    add.dataset.create = '1';
                    add.textContent = '+ Tambah kategori: ' + input.value.trim();
                    menu.appendChild(add);
                }
                menu.hidden = menu.children.length === 0;
                input.setAttribute('aria-expanded', String(!menu.hidden));
            }
            document.addEventListener('focusin', e => {
                if (e.target.matches('.ph-combo-input')) updateCombo(e.target);
            });
            document.addEventListener('input', e => {
                if (e.target.matches('.ph-combo-input')) updateCombo(e.target);
            });
            document.addEventListener('keydown', e => {
                if (e.target.matches('.ph-combo-input') && e.key === 'Enter') {
                    const first = e.target.closest('.ph-combo').querySelector('.ph-combo-option');
                    if (first) {
                        e.preventDefault();
                        first.click();
                    }
                }
                if (e.target.matches('.ph-combo-input') && e.key === 'Escape') {
                    e.target.closest('.ph-combo').querySelector('.ph-combo-menu').hidden = true;
                }
            });
            document.addEventListener('click', e => {
                if (!e.target.closest('.ph-combo')) document.querySelectorAll('.ph-combo-menu').forEach(menu =>
                    menu.hidden = true);
            });
            document.querySelectorAll('.ph-combo').forEach(combo => {
                const select = combo.parentElement.querySelector('.job-category');
                combo.querySelector('.ph-combo-input').value = select.selectedOptions[0]?.value ? select
                    .selectedOptions[0].text : '';
            });
            document.addEventListener('click', async function(e) {
                const header = e.target.closest('.ph-pohead');
                if (header) {
                    const body = header.nextElementSibling;
                    body.hidden = !body.hidden;
                    header.setAttribute('aria-expanded', String(!body.hidden));
                    return;
                }
                const add = e.target.closest('.ph-add');
                if (add) {
                    const work = add.closest('.ph-work');
                    const list = work.querySelector('.ph-jobs');
                    const template = list.querySelector('.ph-job');
                    if (!template) return;
                    const clone = template.cloneNode(true);
                    clone.dataset.jobId = '';
                    clone.querySelector('.job-category').value = '';
                    clone.querySelectorAll('input,textarea').forEach(el => el.value = '');
                    clone.querySelector('.ph-status').textContent = '';
                    clone.querySelector('.ph-remarks').hidden = true;
                    clone.querySelector('.ph-remark-toggle').textContent = '▸ Show Remark';
                    clone.querySelector('.ph-remark-toggle').setAttribute('aria-expanded', 'false');
                    clone.querySelector('.ph-combo-menu').hidden = true;
                    clone.querySelector('.ph-combo-input').value = '';
                    list.appendChild(clone);
                    return;
                }
                const toggle = e.target.closest('.ph-remark-toggle');
                if (toggle) {
                    const panel = toggle.closest('.ph-job').querySelector('.ph-remarks');
                    panel.hidden = !panel.hidden;
                    toggle.textContent = panel.hidden ? '▸ Show Remark' : '▾ Hide Remark';
                    toggle.setAttribute('aria-expanded', String(!panel.hidden));
                    return;
                }
                const editCategory = e.target.closest('.ph-combo-edit');
                if (editCategory) {
                    const item = editCategory.closest('.ph-combo-item');
                    const combo = editCategory.closest('.ph-combo');
                    const select = combo.parentElement.querySelector('.job-category');
                    const id = editCategory.dataset.id;
                    const oldName = [...select.options].find(o => String(o.value) === String(id))?.text ||
                        '';
                    item.replaceChildren();
                    const field = document.createElement('input');
                    field.type = 'text';
                    field.className = 'ph-combo-rename';
                    field.value = oldName;
                    field.maxLength = 150;
                    const ok = document.createElement('button');
                    ok.type = 'button';
                    ok.className = 'ph-combo-confirm';
                    ok.textContent = '✓';
                    ok.title = 'Simpan';
                    const cancel = document.createElement('button');
                    cancel.type = 'button';
                    cancel.className = 'ph-combo-cancel';
                    cancel.textContent = '×';
                    cancel.title = 'Batal';
                    item.append(field, ok, cancel);
                    field.focus();
                    field.select();
                    cancel.addEventListener('click', () => updateCombo(combo.querySelector(
                        '.ph-combo-input')));
                    field.addEventListener('keydown', ev => {
                        if (ev.key === 'Enter') {
                            ev.preventDefault();
                            ev.stopPropagation();
                            ok.click();
                        }
                        if (ev.key === 'Escape') {
                            ev.stopPropagation();
                            cancel.click();
                        }
                    });
                    ok.addEventListener('click', async () => {
                        const name = field.value.trim();
                        const status = combo.closest('.ph-job').querySelector('.ph-status');
                        if (!name) {
                            status.textContent = 'Nama kategori wajib diisi.';
                            return;
                        }
                        try {
                            ok.disabled = true;
                            const result = await send(categoryUpdateUrl + '/' +
                                encodeURIComponent(id), 'PATCH', {
                                    name
                                });
                            document.querySelectorAll('.job-category').forEach(sel => {
                                const opt = [...sel.options].find(o => String(o
                                    .value) === String(id));
                                if (opt) opt.text = result.name || name;
                            });
                            document.querySelectorAll('.ph-combo').forEach(c => {
                                const sel = c.parentElement.querySelector(
                                    '.job-category');
                                if (String(sel.value) === String(id)) c.querySelector(
                                    '.ph-combo-input').value = result.name || name;
                            });
                            status.textContent = 'Nama kategori diperbarui ✓';
                            updateCombo(combo.querySelector('.ph-combo-input'));
                        } catch (err) {
                            status.textContent = err.message;
                            ok.disabled = false;
                        }
                    });
                    return;
                }
                const option = e.target.closest('.ph-combo-option');
                if (option) {
                    const combo = option.closest('.ph-combo');
                    const select = combo.parentElement.querySelector('.job-category');
                    const input = combo.querySelector('.ph-combo-input');
                    const menu = combo.querySelector('.ph-combo-menu');
                    if (option.dataset.create === '1') {
                        const name = input.value.trim();
                        if (!name) return;
                        const status = option.closest('.ph-job').querySelector('.ph-status');
                        try {
                            option.disabled = true;
                            status.textContent = 'Menambahkan kategori...';
                            const result = await send(categoryUrl, 'POST', {
                                name
                            });
                            document.querySelectorAll('.job-category').forEach(sel => {
                                if (![...sel.options].some(o => String(o.value) === String(result
                                        .id))) {
                                    sel.add(new Option(result.name, result.id));
                                }
                            });
                            select.value = String(result.id);
                            input.value = result.name;
                            status.textContent = 'Kategori berhasil ditambahkan ✓';
                        } catch (err) {
                            status.textContent = err.message;
                        } finally {
                            option.disabled = false;
                        }
                    } else {
                        select.value = option.dataset.value;
                        input.value = [...select.options].find(o => o.value === option.dataset.value)
                            ?.text || option.textContent;
                    }
                    menu.hidden = true;
                    input.setAttribute('aria-expanded', 'false');
                    return;
                }
                const save = e.target.closest('.ph-save');
                if (save) {
                    const row = save.closest('.ph-job');
                    const work = save.closest('.ph-work');
                    const status = row.querySelector('.ph-status');
                    const data = {
                        id: row.dataset.jobId || null,
                        po_id: work.dataset.poId,
                        detail_po_id: work.dataset.detailId,
                        article_code: work.dataset.article,
                        bahan_kategori_id: row.querySelector('.job-category').value
                    };
                    if (!data.bahan_kategori_id) {
                        status.textContent = 'Pilih pekerjaan dahulu.';
                        return;
                    }
                    try {
                        row.querySelectorAll('.ph-price').forEach(el => data[el.dataset.field] = money(el
                            .value));
                        row.querySelectorAll('.ph-remark').forEach(el => data[el.dataset.field] = el.value);
                        save.disabled = true;
                        status.textContent = 'Menyimpan...';
                        const result = await send(saveUrl, 'POST', data);
                        row.dataset.jobId = result.id;
                        status.textContent = 'Tersimpan ✓';
                    } catch (err) {
                        status.textContent = err.message;
                    } finally {
                        save.disabled = false;
                    }
                    return;
                }
                const del = e.target.closest('.ph-delete');
                if (del) {
                    const row = del.closest('.ph-job');
                    const list = del.closest('.ph-jobs');
                    const status = row.querySelector('.ph-status');
                    if (row.dataset.jobId) {
                        if (!confirm('Hapus pekerjaan ini dari database?')) return;
                        try {
                            del.disabled = true;
                            await send(deleteUrl, 'DELETE', {
                                id: row.dataset.jobId
                            });
                        } catch (err) {
                            status.textContent = err.message;
                            del.disabled = false;
                            return;
                        }
                    }
                    if (list.children.length > 1) row.remove();
                    else {
                        row.dataset.jobId = '';
                        row.querySelector('.job-category').value = '';
                        row.querySelectorAll('input,textarea').forEach(el => el.value = '');
                        status.textContent = '';
                        del.disabled = false;
                    }
                }
            });
            document.addEventListener('keydown', function(e) {
                if ((e.key === 'Enter' || e.key === ' ') && e.target.matches('.ph-pohead')) {
                    e.preventDefault();
                    e.target.click();
                }
            });
        })();
    </script>
@endsection
