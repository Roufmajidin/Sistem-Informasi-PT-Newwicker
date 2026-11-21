@extends('master.master')

@section('btn')
    <div style="padding:13px 22px;font-size:12px;color:#64748b">R&D Archive › Sample Pictures › Daftar Artikel</div>
@endsection

@section('content')
<div class="rnd-articles-page">
    <div class="rnd-articles-toolbar">
        <div class="rnd-articles-heading">
            <div class="rnd-articles-icon"><i class="fa fa-folder-open"></i></div>
            <div><strong>Daftar Image yang sudah terupload</strong><small></small></div>
        </div>
        <div class="rnd-articles-actions">
            <a class="rnd-articles-back" href="{{ route('rnd.sample-pictures') }}"><i class="fa fa-arrow-left"></i> Back to Purchase Order</a>
            <form method="GET" action="{{ route('rnd.sample-pictures.articles') }}" class="rnd-articles-search">
                <i class="fa fa-search"></i>
                <input name="search" value="{{ $search }}" placeholder="Cari PO, Article Code, nama barang..." autocomplete="off">
                @if($search !== '')<a href="{{ route('rnd.sample-pictures.articles') }}" title="Hapus pencarian">×</a>@endif
                <button type="submit">Cari</button>
            </form>
        </div>
    </div>
    <div class="rnd-articles-card">
        <div class="rnd-articles-scroll">
            <table class="rnd-articles-table">
                <thead><tr><th>No.</th><th>Article Code</th><th>Description</th><th>Nomor PO</th><th>Folder</th><th>Foto</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($rows as $index => $row)
                    <tr>
                        <td>{{ ($items->firstItem() ?? 1) + $index }}</td>
                        <td><strong>{{ $row['article_code'] ?: '-' }}</strong></td>
                        <td>{{ $row['description'] ?: '-' }}</td>
                        <td>{{ $row['po']?->order_no ?: '-' }}</td>
                        <td><span class="rnd-folder-state"><i class="fa fa-folder-o"></i> {{ $row['folder_exists'] ? 'Folder siap' : 'Belum dibuat' }}</span></td>
                        <td class="rnd-center">{{ $row['photo_count'] }}</td>
                        <td class="rnd-center">
                            <a class="rnd-open" href="{{ route('rnd.sample-pictures', ['search' => $row['article_code'] ?: ($row['description'] ?: $row['po']?->order_no)]) }}" title="Buka di halaman pengelolaan foto"><i class="fa fa-folder-open"></i> Buka</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="rnd-center" style="padding:32px">Belum ada artikel dengan foto upload yang sesuai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="rnd-articles-footer"><span>Menampilkan {{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }} dari {{ $items->total() }} artikel</span><div>{{ $items->onEachSide(1)->links() }}</div></div>
    </div>
</div>
<style>
.rnd-articles-page{padding:22px 18px 32px;font-family:Arial,sans-serif;color:#17243a}
.rnd-articles-toolbar{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:15px}
.rnd-articles-heading,.rnd-articles-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.rnd-articles-heading strong,.rnd-articles-heading small{display:block}.rnd-articles-heading strong{font-size:13px}.rnd-articles-heading small{font-size:11px;color:#718096;margin-top:5px}
.rnd-articles-icon{width:42px;height:42px;display:grid;place-items:center;background:#eff6ff;color:#1769e0;border-radius:9px}
.rnd-articles-back,.rnd-open{display:inline-flex;align-items:center;gap:7px;border:1px solid #bfdbfe;background:#eff6ff;color:#1d4ed8;text-decoration:none;border-radius:7px;padding:9px 12px;font-size:11px;font-weight:700;white-space:nowrap}
.rnd-articles-back:hover,.rnd-open:hover{background:#dbeafe;text-decoration:none}
.rnd-articles-search{display:flex;align-items:center;gap:8px;background:#fff;border:1px solid #dce4ef;border-radius:8px;padding:4px 5px 4px 12px;margin:0}
.rnd-articles-search>i{color:#94a3b8}.rnd-articles-search input{border:0;outline:0;width:250px;max-width:45vw;font-size:12px}.rnd-articles-search button{background:#1769e0;color:#fff;border:0;border-radius:5px;padding:8px 12px;cursor:pointer;font-size:11px}
.rnd-articles-card{background:#fff;border:1px solid #e5eaf2;border-radius:12px;overflow:hidden}.rnd-articles-scroll{overflow-x:auto}
.rnd-articles-table{border-collapse:collapse;width:100%;min-width:800px;font-size:12px}.rnd-articles-table th{text-transform:uppercase;background:#f7f9fc;color:#536278;font-size:10px;text-align:left;padding:14px;border-bottom:1px solid #e5eaf2}.rnd-articles-table td{padding:12px 14px;border-bottom:1px solid #edf1f6;height:54px}.rnd-articles-table tr:hover td{background:#fbfdff}
.rnd-center{text-align:center}.rnd-folder-state{background:#f1f5f9;color:#64748b;padding:7px;border-radius:5px;white-space:nowrap}.rnd-articles-footer{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;padding:14px;color:#718096;font-size:11px}
@media(max-width:700px){.rnd-articles-page{padding:12px}.rnd-articles-actions{width:100%}.rnd-articles-search{width:100%}.rnd-articles-search input{width:100%;max-width:none;flex:1}}
</style>
@endsection
