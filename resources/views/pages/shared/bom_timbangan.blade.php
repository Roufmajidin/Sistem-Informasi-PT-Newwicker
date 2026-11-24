@php
    $btPhotos =
        isset($isEdit, $bom) && $bom instanceof \Illuminate\Database\Eloquent\Model
            ? $bom
                ->timbangan()
                ->get()
                ->map(fn($p) => ['key' => $p->client_key, 'url' => '/storage/' . ltrim($p->image, '/')])
                ->values()
            : [];
@endphp
<style>
    #bt-gallery {
        border-top: 1px solid #e2e8f0;
        margin-top: 12px;
        padding-top: 12px;
        text-align: left
    }

    #bt-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 8px 0
    }

    #bt-list .bt-tile {
        position: relative;
        width: 48px;
        height: 48px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #f8fafc;
        overflow: hidden
    }

    #bt-list img {
        width: 100%;
        height: 100%;
        object-fit: contain
    }

    #bt-list .bt-add {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 1px dashed #94a3b8;
        color: #64748b;
        font-size: 26px;
        cursor: pointer
    }

    #bt-list .bt-del {
        position: absolute;
        top: 2px;
        right: 2px;
        border: 0;
        border-radius: 5px;
        background: #b91c1c;
        color: white;
        opacity: 0;
        cursor: pointer;
        padding: 2px 7px
    }

    #bt-list .bt-tile:hover .bt-del,
    #bt-list .bt-tile:focus-within .bt-del {
        opacity: 1
    }

    @media(hover:none) {
        #bt-list .bt-del {
            opacity: 1
        }
    }

    #bt-gallery:focus-within {
        outline: 1px solid #94a3b8
    }

    #bt-list .bt-preview {
        position: relative;
        width: 100%;
        height: 100%;
        padding: 0;
        border: 0;
        background: transparent;
        cursor: zoom-in;
        display: block
    }

    #bt-list .bt-lens {
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        background: #0006;
        color: white;
        opacity: 0;
        pointer-events: none
    }

    #bt-list .bt-preview:hover .bt-lens,
    #bt-list .bt-preview:focus-visible .bt-lens {
        opacity: 1
    }

    #bt-list .bt-del {
        z-index: 2;
        padding: 0 4px;
        font-size: 12px
    }

    .bt-lightbox {
        width: min(94vw, 1000px);
        max-height: 92vh;
        background: #101827;
        color: white;
        border: 0;
        border-radius: 12px;
        padding: 12px
    }

    .bt-lightbox::backdrop {
        background: #000b
    }

    .bt-toolbar {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-bottom: 10px
    }

    .bt-toolbar button {
        border: 0;
        border-radius: 5px;
        padding: 7px 14px;
        background: #fff;
        color: #142c4b;
        cursor: pointer
    }

    .bt-stage {
        height: 72vh;
        overflow: auto;
        text-align: center
    }

    .bt-stage img {
        height: auto;
        object-fit: contain;
        vertical-align: middle
    }
</style>
<div id="bt-gallery" tabindex="0">
    <strong>Foto Timbangan</strong>
    <div id="bt-list"></div>
    <input id="bt-files" type="file" accept="image/jpeg,image/png,image/webp" multiple hidden>
    <small id="bt-hint">Klik +, drag &amp; drop atau fokus area ini lalu Ctrl + V. Maks. 5 MB/foto.</small>
</div>
<script>
    (function() {
        const gallery = document.getElementById('bt-gallery'),
            list = document.getElementById('bt-list'),
            input = document.getElementById('bt-files');
        const editable = @json(!isset($isEdit) || (bool) $isEdit);
        let photos = @json($btPhotos),
            removed = new Set();

        function dirty() {
            gallery.dispatchEvent(new Event('bt-change', {
                bubbles: true
            }));
        }

        function render() {
            list.replaceChildren();
            photos.forEach(p => {
                const tile = document.createElement('div');
                tile.className = 'bt-tile';
                const img = document.createElement('img');
                img.src = p.url;
                img.alt = 'Foto timbangan';
                img.onerror = () => {
                    img.title = 'File foto tidak dapat diakses: ' + p.url;
                };
                const preview = document.createElement('button');
                preview.type = 'button';
                preview.className = 'bt-preview';
                preview.setAttribute('aria-label', 'Perbesar foto timbangan');
                preview.append(img);
                const lens = document.createElement('span');
                lens.className = 'bt-lens';
                lens.innerHTML =
                    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="10" cy="10" r="6"/><path d="m14 14 6 6"/></svg>';
                preview.append(lens);
                preview.onclick = () => openPreview(p.url);
                tile.append(preview);
                if (editable) {
                    const del = document.createElement('button');
                    del.type = 'button';
                    del.className = 'bt-del';
                    del.textContent = '×';
                    del.setAttribute('aria-label', 'Hapus foto');
                    del.onclick = () => {
                        if (!confirm('Hapus foto timbangan ini?')) return;
                        removed.add(p.key);
                        if (p.file) URL.revokeObjectURL(p.url);
                        photos = photos.filter(x => x !== p);
                        render();
                        dirty();
                    };
                    tile.append(del);
                }
                list.append(tile);
            });
            if (editable) {
                const add = document.createElement('button');
                add.type = 'button';
                add.className = 'bt-tile bt-add';
                add.textContent = '+';
                add.setAttribute('aria-label', 'Tambah foto timbangan');
                add.onclick = () => input.click();
                list.append(add);
            }
        }

        function openPreview(url) {
            const d = document.createElement('dialog');
            d.className = 'bt-lightbox';
            d.innerHTML =
                '<div class="bt-toolbar"><button type="button" data-step="-1" aria-label="Zoom out">−</button><button type="button" data-reset>100%</button><button type="button" data-step="1" aria-label="Zoom in">+</button><button type="button" data-close aria-label="Tutup">×</button></div><div class="bt-stage"><img alt="Preview foto timbangan"></div>';
            document.body.append(d);
            const img = d.querySelector('img'),
                stage = d.querySelector('.bt-stage');
            img.src = url;
            let scale = 1;

            function zoom(n) {
                scale = Math.min(4, Math.max(.5, n));
                img.style.width = (scale * 100) + '%';
                d.querySelector('[data-reset]').textContent = Math.round(scale * 100) + '%';
            }
            d.querySelectorAll('[data-step]').forEach(b => b.onclick = () => zoom(scale + Number(b.dataset.step) *
                .25));
            d.querySelector('[data-reset]').onclick = () => zoom(1);
            d.querySelector('[data-close]').onclick = () => d.close();
            stage.addEventListener('wheel', e => {
                e.preventDefault();
                zoom(scale + (e.deltaY < 0 ? .25 : -.25));
            }, {
                passive: false
            });
            d.addEventListener('click', e => {
                if (e.target === d) d.close();
            });
            d.addEventListener('close', () => d.remove());
            d.showModal();
            zoom(1);
        }

        function uuid() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
                const r = crypto.getRandomValues(new Uint8Array(1))[0] & 15;
                return (c === 'x' ? r : (r & 3) | 8).toString(16);
            });
        }

        function addFiles(files) {
            if (!editable) return;
            for (const f of files) {
                if (!['image/jpeg', 'image/png', 'image/webp'].includes(f.type) || f.size > 5 * 1024 * 1024) {
                    alert('Gunakan JPG, PNG atau WEBP maksimal 5 MB.');
                    continue;
                }
                if (photos.filter(p => p.file).length >= 20) {
                    alert('Maksimal 20 foto baru per penyimpanan.');
                    break;
                }
                photos.push({
                    key: uuid(),
                    url: URL.createObjectURL(f),
                    file: f
                });
            }
            render();
            dirty();
        }
        input.addEventListener('change', () => {
            addFiles(input.files);
            input.value = '';
            gallery.focus();
        });
        gallery.addEventListener('dragover', e => e.preventDefault());
        gallery.addEventListener('drop', e => {
            e.preventDefault();
            e.stopPropagation();
            addFiles(e.dataTransfer.files);
        });
        gallery.addEventListener('paste', e => {
            const files = Array.from(e.clipboardData?.items || []).filter(i => i.kind === 'file' && i.type
                .startsWith('image/')).map(i => i.getAsFile()).filter(Boolean);
            if (files.length) {
                e.preventDefault();
                e.stopPropagation();
                addFiles(files);
            }
        });
        window.bomTimbanganAppend = function(fd) {
            if (!editable) return;
            photos.forEach(p => {
                if (p.file) fd.append('timbangan_files[' + p.key + ']', p.file);
            });
            removed.forEach(k => fd.append('timbangan_remove[]', k));
        };
        // Existing AJAX success: release only files actually acknowledged by that request.
        window.bomTimbanganAck = function(fd) {
            photos.forEach(p => {
                if (p.file && fd.has('timbangan_files[' + p.key + ']')) delete p.file;
            });
            for (const k of fd.getAll('timbangan_remove[]')) removed.delete(k);
        };
        if (!editable) document.getElementById('bt-hint').textContent =
            'Mode lihat saja. Buka halaman Edit BOM untuk menambah atau menghapus foto.';
        render();
    })();
</script>
