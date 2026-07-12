@forelse($inspection as $inspected)

<tr>

    <td>{{ $loop->iteration }}</td>

    <td>
        {{ \Carbon\Carbon::parse($inspected->tanggal_inspect)->format('d M Y') }}
        <br>
        <small class="text-muted">
            {{ $inspected->created_at->diffForHumans() }}
        </small>
    </td>

    <td>{{ optional($inspected->po)->order_no }}</td>

    <td>{{ optional($inspected->po)->company_name }}</td>

    <td>{{ optional($inspected->spk)->data['no_spk'] ?? '-' }}</td>

    <td>{{ optional($inspected->user)->name }}</td>

    <td>{{ $inspected->jumlah_inspect }}</td>

    <td>{{ $inspected->passed }}</td>

    <td>{{ $inspected->rejected }}</td>

</tr>

@empty

<tr>

<td colspan="9" class="text-center py-5">

Tidak ada data.

</td>

</tr>

@endforelse
