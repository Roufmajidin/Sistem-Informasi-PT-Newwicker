@extends('master.master')

@section('content')
    <style>
        .spek-create {
            --blue: #2563eb;
            --soft: #eff6ff;
            --text: #172033;
            --muted: #667085;
            --border: #e5e7eb;
            background: #fff;
            color: var(--text);
            padding: 5px 8px 30px;
            font-family: Inter, system-ui, sans-serif
        }

        .spek-create * {
            box-sizing: border-box
        }

        .top {
            height: 58px;
            border-bottom: 1px solid #edf0f4;
            display: flex;
            align-items: center;
            justify-content: space-between
        }

        .crumb {
            font-size: 13px;
            color: #667085;
            display: flex;
            gap: 10px;
            align-items: center
        }

        .crumb b {
            color: #172033
        }

        .arrow {
            color: #98a2b3;
            font-size: 18px
        }

        .head {
            padding: 18px 0 14px
        }

        .head h1 {
            margin: 0 0 4px;
            font-size: 22px
        }

        .head p {
            margin: 0;
            color: var(--muted);
            font-size: 12px
        }

        .btn {
            height: 37px;
            padding: 0 13px;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: #fff;
            color: #344054;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px
        }

        .primary {
            background: var(--blue);
            border-color: var(--blue);
            color: #fff
        }

        .primary:hover {
            background: #1d4ed8
        }

        .product,
        .panel {
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 1px 2px #10182808
        }

        .product {
            padding: 15px;
            margin-bottom: 13px
        }

        .form-title,
        .title {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 10px
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1.5fr .7fr;
            gap: 12px
        }

        .label {
            display: block;
            font-size: 11px;
            font-weight: 650;
            margin-bottom: 6px
        }

        .req {
            color: #ef4444
        }

        .input,
        .select {
            width: 100%;
            height: 36px;
            border: 1px solid #dfe3e8;
            border-radius: 6px;
            padding: 0 10px;
            font-size: 11.5px;
            outline: none
        }

        .input:focus,
        .select:focus {
            border-color: #8fb3ff;
            box-shadow: 0 0 0 3px #2563eb12
        }

        .grid {
            display: grid;
            grid-template-columns: minmax(0, 1.55fr) minmax(350px, .78fr);
            gap: 18px
        }

        .panel {
            overflow: hidden
        }

        .panel-head {
            min-height: 64px;
            padding: 12px 16px;
            border-bottom: 1px solid #eef1f5;
            display: flex;
            align-items: center;
            justify-content: space-between
        }

        .sub {
            font-size: 10px;
            color: #98a2b3;
            margin-top: -5px
        }

        .wrap {
            overflow: auto
        }

        .table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse
        }

        .table th {
            height: 38px;
            /* background: #fcfcfd; */
            border-bottom: 1px solid #e9edf2;
            padding: 0 7px;
            text-align: left;
            font-size: 10px
        }

        .table td {
            padding: 6px 7px;
            border-bottom: 1px solid #f0f2f5;
            font-size: 11px;
            vertical-align: middle
        }

        .table th:nth-child(1) {
            width: 38px
        }

        .table th:nth-child(2) {
            width: 28px
        }

        .table th:nth-child(3) {
            width: 145px
        }

        .table th:nth-child(4) {
            width: 105px
        }

        .table th:nth-child(5) {
            width: 145px
        }

        .table th:nth-child(6) {
            width: 58px
        }

        .table th:nth-child(7) {
            width: 52px
        }

        .table th:nth-child(8) {
            width: 175px
        }

        .table th:nth-child(9) {
            width: 70px
        }

        .small {
            width: 100%;
            height: 32px;
            border: 1px solid #dfe3e8;
            border-radius: 5px;
            padding: 0 8px;
            font-size: 11px
        }

        .handle {
            color: #98a2b3;
            cursor: grab
        }

        .check {
            width: 15px;
            height: 15px;
            accent-color: var(--blue)
        }

        .attachments {
            display: flex;
            gap: 5px;
            align-items: center
        }

        .thumb {
            width: 38px;
            height: 38px;
            position: relative;
            border: 1px solid #dfe3e8;
            border-radius: 5px;
            overflow: hidden
        }

        .thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover
        }

        .x {
            position: absolute;
            right: 1px;
            top: 1px;
            border: 0;
            border-radius: 50%;
            background: #111c;
            color: #fff;
            width: 14px;
            height: 14px;
            font-size: 9px;
            line-height: 14px;
            padding: 0
        }

        .add-img {
            width: 38px;
            height: 38px;
            border: 1px dashed #cbd5e1;
            background: #fff;
            border-radius: 5px;
            font-size: 17px;
            color: #475467
        }

        .file {
            display: none
        }

        .icon {
            width: 30px;
            height: 30px;
            border: 1px solid #dfe5ec;
            background: #fff;
            border-radius: 5px
        }

        .del {
            color: #ef4444;
            border-color: #fecaca
        }

        .foot {
            padding: 9px 16px;
            border-top: 1px solid #eef1f5;
            color: #98a2b3;
            font-size: 10px
        }

        .preview {
            padding: 13px
        }

        .preview-list {
            border: 1px solid #e6eaf0;
            border-radius: 7px;
            overflow: hidden
        }

        .prow {
            min-height: 58px;
            padding: 8px 11px;
            border-bottom: 1px solid #edf0f4;
            display: flex;
            align-items: center;
            gap: 10px
        }

        .prow:last-child {
            border: 0
        }

        .plabel {
            font-size: 11px;
            font-weight: 700;
            min-width: 95px
        }

        .pvalue {
            margin-left: auto;
            font-size: 11px;
            color: #475467;
            text-align: right
        }

        .pimgs {
            display: flex;
            gap: 4px;
            margin-left: auto
        }

        .pimgs img {
            width: 42px;
            height: 42px;
            object-fit: cover;
            border-radius: 5px
        }

        .notice {
            margin-top: 12px;
            padding: 10px;
            background: var(--soft);
            border: 1px solid #dbeafe;
            border-radius: 6px;
            color: var(--blue);
            font-size: 10px
        }

        .bottom {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding-top: 12px;
            margin-top: 12px;
            border-top: 1px solid #edf0f4
        }

        .json {
            display: none;
            margin-top: 12px
        }

        .json.show {
            display: block
        }

        .json pre {
            margin: 0;
            padding: 15px;
            background: #111827;
            color: #d1fae5;
            border-radius: 7px;
            min-height: 350px;
            overflow: auto;
            font: 11px/1.6 Consolas, monospace
        }

        .toast {
            display: none;
            position: fixed;
            right: 20px;
            bottom: 20px;
            padding: 12px 15px;
            border-radius: 8px;
            background: #172033;
            color: #fff;
            z-index: 99999;
            font-size: 11px
        }

        .toast.show {
            display: block
        }

        .toast.ok {
            background: #15803d
        }

        .toast.err {
            background: #b91c1c
        }

        .empty {
            text-align: center;
            padding: 35px;
            color: #98a2b3;
            font-size: 11px
        }

        @media(max-width:1200px) {
            .grid {
                grid-template-columns: 1fr
            }
        }

        @media(max-width:700px) {

            .top,
            .form-grid {
                display: block
            }

            .top {
                height: auto;
                padding: 10px 0
            }

            .grid {
                display: block
            }

            .panel {
                margin-bottom: 12px
            }

            .form-grid>div {
                margin-bottom: 10px
            }
        }
    </style>

    <div class="spek-create">
        <div class="top">
            <div class="crumb"><span>Master</span><span class="arrow">›</span><span>Spesifikasi Produk</span><span
                    class="arrow">›</span><b>{{ $isEdit ?? false ? 'Edit' : 'Tambah' }}</b></div>
            <a href="{{ route('spek.index') }}" class="btn">← Kembali</a>
        </div>

    @section('btn')
        <div class="head">
            <h1>{{ $isEdit ?? false ? 'Edit Spesifikasi Produk' : 'Tambah Spesifikasi Produk' }}</h1>
            <p>{{ $isEdit ?? false ? 'Perbarui informasi dan field spesifikasi produk.' : 'Buat spesifikasi produk dinamis sesuai kebutuhan setiap artikel.' }}
            </p>
        </div>
    @endsection

    <div class="product">
        <div class="form-title">Informasi Produk</div>
        <div class="form-grid">
            <div>
                <label class="label">Nama Produk <span class="req">*</span></label>
                <input id="productName" class="input" maxlength="255"
                    value="{{ old('name', $specification->name ?? '') }}" placeholder="Contoh: Kursi Rotan Minimalis">
            </div>
            <div>
                <label class="label">Article Code <span class="req">*</span></label>
                <input id="articleCode" class="input" maxlength="100"
                    value="{{ old('article_code', $specification->article_code ?? '') }}" placeholder="Contoh: KR-001">
            </div>
        </div>
    </div>

    <div class="grid">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <div class="title">Kelola Spesifikasi</div>
                    <div class="sub">Setiap produk dapat memiliki field yang berbeda.</div>
                </div>
                <button id="addField" type="button" class="btn primary">+ Tambah Field</button>
            </div>

            <div class="wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th></th>
                            <th>Nama Field</th>
                            <th>Tipe</th>
                            <th>Nilai</th>
                            <th>Satuan</th>
                            <th>Wajib</th>
                            <th>Attachment</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="rows"></tbody>
                </table>
            </div>

            <div class="foot">◈ &nbsp; Drag &amp; drop row untuk mengubah urutan.</div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <div class="title">Preview Spesifikasi</div>
                    <div class="sub">Preview</div>
                </div>
            </div>
            <div class="preview">
                <div class="preview-list" id="preview"></div>
                <div class="notice">ⓘ &nbsp; Attachment optional. Setiap field dapat memiliki lebih dari satu gambar.
                </div>
            </div>
        </div>
    </div>

    <div class="json" id="jsonPanel">
        <div class="panel">
            <div class="panel-head">
                <div class="title">JSON Preview</div>
            </div>
            <pre id="jsonCode">{}</pre>
        </div>
    </div>

    <div class="bottom">
        <a href="{{ route('spek.index') }}" class="btn">Batal</a>
        <button id="jsonBtn" type="button" class="btn">{ } JSON</button>
        <button id="save" type="button"
            class="btn primary">{{ $isEdit ?? false ? 'Update Spesifikasi' : 'Simpan Spesifikasi' }}</button>
    </div>
</div>

<div id="toast" class="toast"></div>


<script>
    document.addEventListener('DOMContentLoaded', function() {

        const IS_EDIT = @json($isEdit ?? false);
        const STORE_URL = @json(route('spek.store'));
        const UPDATE_URL = @json(($isEdit ?? false) && ($specification ?? null) ? route('spek.update', $specification->id) : null);

        // IMPORTANT: this is what makes edit data appear in the same Blade.
        let specs = @json($initialFields ?? []);
        let dragIndex = null;

        const rows = document.getElementById('rows');
        const preview = document.getElementById('preview');
        const jsonCode = document.getElementById('jsonCode');
        const nameInput = document.getElementById('productName');
        const codeInput = document.getElementById('articleCode');
        const toast = document.getElementById('toast');
        const saveButton = document.getElementById('save');

        function uid() {
            return 'field_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8);
        }

        function esc(value) {
            return String(value ?? '').replace(/[&<>"']/g, function(m) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                } [m];
            });
        }

        function notify(message, type = '') {
            toast.textContent = message;
            toast.className = 'toast show ' + type;

            setTimeout(function() {
                toast.className = 'toast';
            }, 3200);
        }

        function normalize() {
            specs = (Array.isArray(specs) ? specs : []).map(function(field) {
                return {
                    id: field.id || uid(),
                    label: field.label || '',
                    type: field.type || 'text',
                    value: field.value ?? '',
                    unit: field.unit ?? '',
                    required: !!field.required,
                    options: Array.isArray(field.options) ? field.options : [],

                    images: Array.isArray(field.images)
                        ? field.images.map(function(image) {
                            const existingPath =
                                image.existing_path ||
                                image.path ||
                                null;

                            const previewUrl =
                                image.url ||
                                (
                                    existingPath
                                        ? @json(url('/spek/image')) + '/' +
                                          existingPath.split('/').map(encodeURIComponent).join('/')
                                        : ''
                                ) ||
                                (
                                    typeof image.data === 'string' &&
                                    image.data.indexOf('data:image/') === 0
                                        ? image.data
                                        : ''
                                );

                            return {
                                name: image.name || 'image',
                                type: image.type || null,
                                size: image.size || null,

                                // URL yang dipakai hanya untuk preview.
                                url: previewUrl,

                                // Hanya gambar BARU yang menyimpan data base64.
                                data:
                                    typeof image.data === 'string' &&
                                    image.data.indexOf('data:image/') === 0
                                        ? image.data
                                        : '',

                                // Dipakai controller untuk mempertahankan gambar lama.
                                existing_path: existingPath
                            };
                        }).filter(function(image) {
                            return image.url ||
                                image.data ||
                                image.existing_path;
                        })
                        : []
                };
            });
        }

        function emptyField() {
            return {
                id: uid(),
                label: '',
                type: 'text',
                value: '',
                unit: '',
                required: false,
                options: [],
                images: []
            };
        }

        function valueHtml(field) {

            if (field.type === 'checkbox') {
                return `
                <input class="check"
                    type="checkbox"
                    data-field="value"
                    ${field.value ? 'checked' : ''}>
            `;
            }

            if (field.type === 'textarea') {
                return `
                <textarea class="small"
                    data-field="value">${esc(field.value)}</textarea>
            `;
            }

            if (field.type === 'select') {

                const options = field.options.length ?
                    field.options : ['Pilihan 1', 'Pilihan 2'];

                return `
                <select class="small" data-field="value">
                    ${options.map(function (option) {
                        return `
                            <option value="${esc(option)}"
                                ${String(option) === String(field.value) ? 'selected' : ''}>
                                ${esc(option)}
                            </option>
                        `;
                    }).join('')}
                </select>

                <input class="small"
                    data-field="options"
                    value="${esc(field.options.join(', '))}"
                    placeholder="Kayu, Rotan, Besi"
                    style="margin-top:4px">
            `;
            }

            return `
            <input class="small"
                type="${field.type === 'date' ? 'date' : 'text'}"
                data-field="value"
                value="${esc(field.value)}"
                placeholder="Nilai">
        `;
        }

        function render() {

            rows.innerHTML = '';

            if (!specs.length) {
                rows.innerHTML = `
                <tr>
                    <td colspan="9">
                        <div class="empty">
                            Belum ada field.
                            Klik <b>Tambah Field</b> untuk memulai.
                        </div>
                    </td>
                </tr>
            `;

                renderPreview();
                updateJson();
                return;
            }

            specs.forEach(function(field, index) {

                const tr = document.createElement('tr');
                tr.draggable = true;

                tr.innerHTML = `
                <td style="text-align:center">${index + 1}</td>

                <td>
                    <span class="handle">⠿</span>
                </td>

                <td>
                    <input class="small"
                        data-field="label"
                        value="${esc(field.label)}"
                        placeholder="Jenis Kaki">
                </td>

                <td>
                    <select class="small" data-field="type">
                        ${[
                            'text',
                            'number',
                            'decimal',
                            'select',
                            'textarea',
                            'date',
                            'checkbox'
                        ].map(function (type) {
                            return `
                                <option value="${type}"
                                    ${field.type === type ? 'selected' : ''}>
                                    ${type.charAt(0).toUpperCase() + type.slice(1)}
                                </option>
                            `;
                        }).join('')}
                    </select>
                </td>

                <td>${valueHtml(field)}</td>

                <td>
                    <input class="small"
                        data-field="unit"
                        value="${esc(field.unit)}"
                        placeholder="-">
                </td>

                <td style="text-align:center">
                    <input class="check"
                        type="checkbox"
                        data-field="required"
                        ${field.required ? 'checked' : ''}>
                </td>

                <td>
                    <div class="attachments">

                        ${(field.images || []).map(function (image, imageIndex) {
                            return `
                                <div class="thumb">
                  <img
    src="${esc(image.url || image.data)}"
    alt=""
>
                                    <button type="button"
                                        class="x"
                                        data-image-index="${imageIndex}">
                                        ×
                                    </button>
                                </div>
                            `;
                        }).join('')}

                        <button type="button" class="add-img">+</button>

                        <input type="file"
                            class="file"
                            accept="image/jpeg,image/png,image/webp"
                            multiple>
                    </div>
                </td>

                <td>
                    <button type="button" class="icon focus">✎</button>
                    <button type="button" class="icon del remove">×</button>
                </td>
            `;

                tr.querySelectorAll('[data-field]').forEach(function(element) {

                    element.addEventListener('input', function() {
                        syncField(field, element);
                    });

                    element.addEventListener('change', function() {

                        syncField(field, element);

                        if (element.dataset.field === 'type') {
                            render();
                        }
                    });
                });

                const optionsInput =
                    tr.querySelector('[data-field="options"]');

                if (optionsInput) {
                    optionsInput.addEventListener('input', function() {

                        field.options = optionsInput.value
                            .split(',')
                            .map(x => x.trim())
                            .filter(Boolean);

                        renderPreview();
                        updateJson();
                    });
                }

                const fileInput = tr.querySelector('.file');

                tr.querySelector('.add-img').onclick = function() {
                    fileInput.click();
                };

                fileInput.onchange = function(event) {

                    Array.from(event.target.files).forEach(function(file) {

                        if (!file.type.startsWith('image/')) {
                            return;
                        }

                        if (file.size > 5 * 1024 * 1024) {
                            notify(file.name + ' lebih dari 5MB.', 'err');
                            return;
                        }

                        const reader = new FileReader();

                        reader.onload = function(e) {

                            field.images.push({
                                name: file.name,
                                type: file.type,
                                size: file.size,

                                // Preview gambar baru.
                                url: e.target.result,

                                // Dikirim ke controller untuk disimpan.
                                data: e.target.result,

                                existing_path: null
                            });

                            render();
                        };

                        reader.readAsDataURL(file);
                    });

                    fileInput.value = '';
                };

                tr.querySelectorAll('[data-image-index]')
                    .forEach(function(button) {

                        button.onclick = function() {

                            field.images.splice(
                                Number(button.dataset.imageIndex),
                                1
                            );

                            render();
                        };
                    });

                tr.querySelector('.remove').onclick = function() {
                    specs.splice(index, 1);
                    render();
                };

                tr.querySelector('.focus').onclick = function() {
                    const input = tr.querySelector('[data-field="label"]');
                    input?.focus();
                    input?.select();
                };

                tr.ondragstart = function() {
                    dragIndex = index;
                    tr.style.opacity = '.45';
                };

                tr.ondragend = function() {
                    dragIndex = null;
                    tr.style.opacity = '';
                };

                tr.ondragover = function(event) {
                    event.preventDefault();
                };

                tr.ondrop = function(event) {

                    event.preventDefault();

                    if (dragIndex === null || dragIndex === index) {
                        return;
                    }

                    const moved = specs.splice(dragIndex, 1)[0];

                    specs.splice(index, 0, moved);

                    render();
                };

                rows.appendChild(tr);
            });

            renderPreview();
            updateJson();
        }

        function syncField(field, element) {

            const key = element.dataset.field;

            if (key === 'required') {
                field.required = element.checked;
            } else if (
                key === 'value' &&
                element.type === 'checkbox'
            ) {
                field.value = element.checked;
            } else {
                field[key] = element.value;
            }

            renderPreview();
            updateJson();
        }

        function renderPreview() {

            const visible = specs.filter(function(field) {
                return String(field.label || '').trim();
            });

            preview.innerHTML = '';

            if (!visible.length) {
                preview.innerHTML =
                    '<div class="empty">Preview akan muncul di sini.</div>';
                return;
            }

            visible.forEach(function(field) {

                let value = field.value ?? '';

                if (field.type === 'checkbox') {
                    value = value ? 'Ya' : 'Tidak';
                }

                if (value === '') {
                    value = '-';
                }

                const images = (field.images || [])
                    .map(image => `
        <img
            src="${esc(image.url || image.data)}"
            alt=""
        >
    `)
                    .join('');

                preview.insertAdjacentHTML('beforeend', `
                <div class="prow">

                    <div class="plabel">
                        ${esc(field.label)}
                        ${field.required
                            ? '<span style="color:#ef4444">*</span>'
                            : ''}
                    </div>

                    ${images
                        ? `<div class="pimgs">${images}</div>`
                        : ''}

                    <div class="pvalue">
                        ${esc(value)}
                        ${field.unit
                            ? ' ' + esc(field.unit)
                            : ''}
                    </div>

                </div>
            `);
            });
        }

        function payloadFields() {

            return specs
                .filter(field => String(field.label || '').trim())
                .map(function(field) {

                    return {
                        label: field.label.trim(),
                        type: field.type || 'text',
                        value: field.value ?? '',
                        unit: field.unit || null,
                        required: !!field.required,

                        options: field.type === 'select' ?
                            field.options || [] : [],

                        images: (field.images || []).map(function(image) {

                            return {
                                name: image.name || 'image',
                                type: image.type || null,
                                size: image.size || null,

                                // Preview only; tidak perlu dikirim sebagai base64.
                                url: image.url || '',

                                // Hanya gambar baru.
                                data:
                                    typeof image.data === 'string' &&
                                    image.data.indexOf('data:image/') === 0
                                        ? image.data
                                        : '',

                                // Gambar lama dipertahankan lewat path ini.
                                existing_path:
                                    image.existing_path ||
                                    image.path ||
                                    null
                            };
                        })
                    };
                });
        }

        function makeKey(label) {

            return String(label || '')
                .toLowerCase()
                .replace(/[^\p{L}\p{N}]+/gu, '_')
                .replace(/^_+|_+$/g, '') || 'field';
        }

        function updateJson() {

            const data = {};

            payloadFields().forEach(function(field) {

                let key = makeKey(field.label);
                const base = key;
                let n = 2;

                while (
                    Object.prototype.hasOwnProperty.call(data, key)
                ) {
                    key = base + '_' + n++;
                }

                data[key] = {
                    label: field.label,
                    type: field.type,
                    value: field.value,
                    unit: field.unit,
                    required: field.required,
                    images: field.images.map(image => ({
                        name: image.name,
                        existing_path: image.existing_path || null
                    }))
                };

                if (field.type === 'select') {
                    data[key].options = field.options;
                }
            });

            jsonCode.textContent =
                JSON.stringify(data, null, 4);
        }

        document.getElementById('addField').onclick = function() {

            specs.push(emptyField());

            render();

            setTimeout(function() {

                const inputs =
                    rows.querySelectorAll(
                        '[data-field="label"]'
                    );

                inputs[inputs.length - 1]?.focus();

            }, 30);
        };

        document.getElementById('jsonBtn').onclick = function() {

            const panel =
                document.getElementById('jsonPanel');

            panel.classList.toggle('show');

            this.textContent =
                panel.classList.contains('show') ?
                '× Tutup JSON' :
                '{ } JSON';
        };

        /*
        |--------------------------------------------------------------------------
        | AJAX CREATE + UPDATE
        |--------------------------------------------------------------------------
        */
        saveButton.onclick = async function() {

            const name = nameInput.value.trim();
            const articleCode = codeInput.value.trim();
            const fields = payloadFields();

            if (!name) {
                notify('Nama produk wajib diisi.', 'err');
                nameInput.focus();
                return;
            }

            if (!articleCode) {
                notify('Article code wajib diisi.', 'err');
                codeInput.focus();
                return;
            }

            if (!fields.length) {
                notify('Tambahkan minimal satu field spesifikasi.', 'err');
                return;
            }

            const url = IS_EDIT ?
                UPDATE_URL :
                STORE_URL;

            const method = IS_EDIT ?
                'PUT' :
                'POST';

            saveButton.disabled = true;
            saveButton.textContent =
                IS_EDIT ?
                'Mengupdate...' :
                'Menyimpan...';

            try {

                const response = await fetch(url, {
                    method: method,

                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token())
                    },

                    body: JSON.stringify({
                        name: name,
                        article_code: articleCode,
                        specifications: JSON.stringify(fields)
                    })
                });

                const result = await response.json();

                if (!response.ok) {

                    let message =
                        result.message ||
                        'Gagal menyimpan data.';

                    if (result.errors) {

                        const first =
                            Object.values(result.errors)[0];

                        if (Array.isArray(first)) {
                            message = first[0];
                        }
                    }

                    throw new Error(message);
                }

                if (!result.success) {
                    throw new Error(
                        result.message ||
                        'Gagal menyimpan data.'
                    );
                }

                notify(
                    result.message ||
                    (IS_EDIT ?
                        'Data berhasil diperbarui.' :
                        'Data berhasil disimpan.'),
                    'ok'
                );

                setTimeout(function() {
                    window.location.href =
                        result.redirect ||
                        @json(route('spek.index'));
                }, 700);

            } catch (error) {

                console.error(error);

                notify(
                    error.message ||
                    'Terjadi kesalahan.',
                    'err'
                );

                saveButton.disabled = false;

                saveButton.textContent =
                    IS_EDIT ?
                    'Update Spesifikasi' :
                    'Simpan Spesifikasi';
            }
        };

        /*
        |--------------------------------------------------------------------------
        | INITIAL
        |--------------------------------------------------------------------------
        */
        normalize();
        render();

    });
</script>

@endsection