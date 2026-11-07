@extends('master.master')
@section('content')
<div class="container-fluid py-3" id="pengajuanHargaPage">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div><h4 class="mb-1 fw-bold">Pengajuan Harga</h4><div class="small text-muted">Daftar pengajuan per kategori dan sub bahan</div></div>
        <div class="d-flex align-items-center gap-2"><span class="badge bg-light text-dark border px-3 py-2">Total: {{ $pengajuans->total() }}</span>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPengajuanModal">+ Create Pengajuan</button></div>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><strong>Gagal:</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="card shadow-sm border-0 mb-3"><div class="card-body">
        <form method="GET" action="{{ route('pengajuan-harga.index') }}" class="row g-2 align-items-end">
            <div class="col-md-5"><label class="form-label small">Cari article / item / sub bahan</label><input type="search" class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari pengajuan..."></div>
            <div class="col-md-3"><label class="form-label small">Kategori</label><select class="form-select" name="kategori"><option value="">Semua kategori</option>@foreach($kategoriList as $kategori)<option value="{{ $kategori->id }}" @selected((string)request('kategori') === (string)$kategori->id)>{{ $kategori->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label small">Status</label><select class="form-select" name="status"><option value="">Semua status</option>@foreach(['draft'=>'Draft','pending'=>'Pending','revision'=>'Revision','approved'=>'Approved','rejected'=>'Rejected'] as $key=>$label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary flex-grow-1">Filter</button><a class="btn btn-outline-secondary" href="{{ route('pengajuan-harga.index') }}">Reset</a></div>
        </form>
    </div></div>
    <div class="card shadow-sm border-0"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0"><thead class="table-light"><tr class="text-nowrap">
            <th>No</th><th>Foto</th><th>Artl. Code</th><th>Description</th><th>Tanggal Dibuat</th><th>Kategori</th><th>Sub Kategori</th><th>Subkon</th><th>Sign Directur</th><th>Requester</th><th>Action</th>
        </tr></thead><tbody>
        @forelse($pengajuans as $row)
            @php
                $header = $row->pengajuanHarga;
                $photo = $header?->image;
                $photoUrl = $photo ? (preg_match('~^https?://~i', $photo) ? $photo : asset('storage/'.ltrim(preg_replace('~^/?storage/~', '', $photo), '/'))) : null;
                $status = $row->status ?? 'draft';
                $statusColor = ['approved'=>'success','pending'=>'warning','revision'=>'info','rejected'=>'danger','draft'=>'secondary'][$status] ?? 'secondary';
                $isFactory = str_starts_with(strtolower(trim((string)(auth()->user()?->email ?? ''))), 'factory@');
                $isOwner = (int)($row->pic_user_id ?: $header?->user_id) === (int)auth()->id();
                $canDelete = ($isFactory || $isOwner) && $status !== 'approved';
            @endphp
            <tr>
                <td>{{ $pengajuans->firstItem() + $loop->index }}</td>
                <td class="text-center">@if($photoUrl)<img src="{{ $photoUrl }}" alt="Produk" class="ph-product-thumb" style="width:72px;height:72px;object-fit:contain;object-position:center;border-radius:4px!important;clip-path:inset(0 round 4px);background:#fff;display:block;margin:auto">@else<span class="small text-muted">No photo</span>@endif</td>
                <td class="fw-semibold">{{ $header?->article_nr ?? '-' }}</td><td>{{ $header?->item_name ?? '-' }}</td>
                <td class="text-nowrap">{{ $header?->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                <td>{{ $row->kategori?->name ?? '-' }}</td><td>{{ $row->subBahan?->name ?? '-' }}</td><td>{{ $header?->sub_name ?: '-' }}</td>
                <td><span class="badge bg-{{ $statusColor }}">{{ ucfirst($status) }}</span>@if($row->approved_at)<div class="small text-muted">{{ $row->approved_at->format('d/m/Y H:i') }}</div>@endif</td>
                <td>{{ $row->pic?->name ?? $header?->user?->name ?? '-' }}</td>
                <td class="text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#approvalModal{{ $row->id }}">View</button>
                    @if($canDelete)<button type="submit" form="deletePengajuan{{ $row->id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus pengajuan ini dan rincian bahannya?')">Delete</button>@endif
                </td>
            </tr>
        @empty<tr><td colspan="11" class="text-center text-muted py-5">Belum ada pengajuan.</td></tr>@endforelse
        </tbody></table>
    </div></div>@if($pengajuans->hasPages())<div class="card-footer">{{ $pengajuans->links() }}</div>@endif</div>
</div>
@include('pages.pengajuan_harga.modals.create')
@foreach($pengajuans as $row)
    @php
        $header = $row->pengajuanHarga;
        $isFactory = str_starts_with(strtolower(trim((string)(auth()->user()?->email ?? ''))), 'factory@');
        $isOwner = (int)($row->pic_user_id ?: $header?->user_id) === (int)auth()->id();
    @endphp
    @if(($isFactory || $isOwner) && $row->status !== 'approved')
        <form id="deletePengajuan{{ $row->id }}" method="POST" action="{{ route('pengajuan-harga.destroy', $row->id) }}" class="d-none">@csrf @method('DELETE')</form>
    @endif
    @include('pages.pengajuan_harga.modals.approval', ['pengajuan' => $row])
@endforeach
<style>
#pengajuanHargaPage img.ph-product-thumb { border-radius:4px!important; width:72px!important; height:72px!important; object-fit:contain!important; clip-path:inset(0 round 4px); }
</style>
@endsection
