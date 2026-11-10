<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Minutes of Meeting #{{ $mom->id }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #172b48;
            font-size: 11.5px;
        }

        h1 {
            text-align: center;
            font-size: 23px;
            margin: 0 0 3px;
        }

        .company {
            text-align: center;
            font-size: 13px;
            margin-bottom: 22px;
        }

        h3 {
            font-size: 14.5px;
            margin: 21px 0 9px;
            page-break-after: avoid;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
            font-size: 11.5px;
        }

        th,
        td {
            border: 1px solid #cbd7e8;
            padding: 9px;
            vertical-align: top;
            text-align: left;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        th {
            background: #eaf0fa;
            color: #1d3150;
            font-weight: bold;
        }

        tr {
            page-break-inside: avoid;
        }

        thead {
            display: table-header-group;
        }

        ul {
            margin: 0;
            padding-left: 16px;
        }

        li {
            margin: 2px 0;
        }

        .preline {
            white-space: pre-line;
        }
    </style>
</head>

<body>
    <h1>MINUTES OF MEETING</h1>
    <div class="company">PT. NEWWICKER INDONESIA</div>
    <table>
        <tr>
            <th style="width:180px">Day / Date</th>
            <td>{{ $mom->meeting_date ? \Illuminate\Support\Carbon::parse($mom->meeting_date)->format('d/m/Y') : '-' }}
            </td>
        </tr>
        <tr>
            <th>Start Month</th>
            <td>{{ $mom->start_month ? \Illuminate\Support\Carbon::parse(substr((string) $mom->start_month, 0, 7) . '-01')->format('F Y') : '-' }}
            </td>
        </tr>
        <tr>
            <th>Subject</th>
            <td>{{ $mom->subject ?: '-' }}</td>
        </tr>
    </table>
    <h3>ATTENDEES</h3>
    <table>
        <thead>
            <tr>
                <th style="width:50px">No.</th>
                <th>Name</th>
                <th>Position</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendees as $person)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $person['name'] ?? '' }}</td>
                    <td>{{ $person['position'] ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">-</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <h3>SUBJECT / DESCRIPTION / URAIAN</h3>
    <table>
        <thead>
            <tr>
                <th style="width:5%">No.</th>
                <th style="width:17%">Person / Position</th>
                <th style="width:58%">Description</th>
                <th style="width:20%">Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($discussions as $discussion)
                @php
                    $person = $people->get((string) ($discussion['personId'] ?? ''));
                    $lines = preg_split('/\r\n|\r|\n/', (string) ($discussion['description'] ?? ''));
                    $lines = array_values(array_filter($lines, fn($line) => trim($line) !== ''));
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $person['name'] ?? '-' }}</strong>
                        <div>{{ $person['position'] ?? '' }}</div>
                    </td>
                    <td>
                        @if (count($lines))
                            <ul>
                                @foreach ($lines as $line)
                                    <li>{{ preg_replace('/^[•\-]\s*/u', '', trim($line)) }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                    <td class="preline">{{ $discussion['notes'] ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">-</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <h3>RECORDED BY</h3>
    <table>
        <tr>
            <th style="width:180px">Name</th>
            <td>{{ $mom->write_by ?: '-' }}</td>
        </tr>
        <tr>
            <th>Position / Department</th>
            <td>{{ $mom->divisi ?: '-' }}</td>
        </tr>
    </table>
</body>

</html>
