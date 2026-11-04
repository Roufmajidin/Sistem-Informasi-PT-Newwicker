@extends('master.master')
@section('content')
<div class="container-fluid py-3">
 <div class="d-flex justify-content-between align-items-center mb-3"><h3>Detail Minutes of Meeting #{{ $mom->id }}</h3><div><a class="btn btn-outline-primary" href="{{ route('mom.list') }}">Daftar</a> <a class="btn btn-primary" href="{{ route('mom.edit',$mom) }}">Edit Draft</a></div></div>
 <div class="card p-3 mb-3"><p><strong>Date:</strong> {{ $mom->meeting_date?->format('d/m/Y') }}</p><p><strong>Start Month:</strong> {{ $mom->start_month }}</p><p><strong>Subject:</strong> {{ $mom->subject }}</p><p><strong>Recorded By:</strong> {{ $mom->write_by }} — {{ $mom->divisi }}</p><p><strong>Status:</strong> {{ $mom->status }}</p></div>
 <h5>Attendees</h5><div class="table-responsive"><table class="table table-bordered"><thead><tr><th>No</th><th>Name</th><th>Position</th></tr></thead><tbody>@foreach(($mom->attendees ?? []) as $i => $a)<tr><td>{{ $i+1 }}</td><td>{{ $a['name'] ?? '' }}</td><td>{{ $a['position'] ?? '' }}</td></tr>@endforeach</tbody></table></div>
 <h5>Discussion</h5><div class="table-responsive"><table class="table table-bordered"><thead><tr><th>No</th><th>Person</th><th>Description / Uraian</th><th>Notes</th></tr></thead><tbody>
 @foreach(($mom->discussions ?? []) as $i => $d)
 @php $person = collect($mom->attendees ?? [])->first(fn($a) => (string)($a['id'] ?? '') === (string)($d['personId'] ?? '')); @endphp
 <tr><td>{{ $i+1 }}</td><td>{{ $person['name'] ?? '-' }}<div class="text-muted">{{ $person['position'] ?? '' }}</div></td><td style="white-space:pre-wrap">{{ $d['description'] ?? '' }}</td><td style="white-space:pre-wrap">{{ $d['notes'] ?? '' }}</td></tr>
 @endforeach
 </tbody></table></div>
</div>
@endsection
