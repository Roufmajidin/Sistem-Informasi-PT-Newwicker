<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: Arial;
            font-size: 12px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 4px;
        }

        .header td {
            border: none;
        }

        .yellow {
            background: #ffff00;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>

</head>

<body>

    <button
        onclick="window.print()"
        class="no-print">
        Print
    </button>

    <table class="header">

        <tr>

            <td width="50%">
                <img
                    src="{{ asset('/assets/images/NEWWICKER WHITE.png') }}"
                    height="80">
            </td>

            <td align="right">

                <b>PT. NewWicker Indonesia</b><br>

                Jalan Kisaba Lanang RT 019 RW 002, Bode Lor<br>

                Plumbon, Cirebon 45155<br>

                Indonesia<br>

                factory@newwicker.com

            </td>

        </tr>

    </table>

    <br>

    <table>

        <tr>
            <td width="120">NO SPK</td>
            <td>{{ $spk->no_spk }}</td>

            <td></td>

            <td class="yellow">
                {{ $spk->kode_spk }}
            </td>
        </tr>

        <tr>
            <td>Nama</td>
            <td class="yellow">
                {{ $spk->nama }}
            </td>

            <td colspan="2"></td>
        </tr>

        <tr>
            <td>Tgl Terima</td>
            <td>
                {{ $spk->tgl_terima }}
            </td>

            <td colspan="2"></td>
        </tr>

        <tr>
            <td>Tgl Selesai</td>
            <td class="yellow">
                {{ $spk->tgl_selesai }}
            </td>

            <td colspan="2"></td>
        </tr>

    </table>

    <br>

    <table>

        <thead>

            <tr>
                <th>Kode</th>
                <th>Gambar</th>
                <th>Nama</th>
                <th>P</th>
                <th>L</th>
                <th>T</th>
                <th>Material</th>
                <th>Qty</th>
            </tr>

        </thead>

        <tbody>

            @foreach($spk->details as $item)

            <tr>

                <td>
                    {{ $item->kode }}
                </td>

                <td>
                    @if($item->gambar)
                    <img
                        src="{{ asset($item->gambar) }}"
                        width="80">
                    @endif
                </td>

                <td>
                    {{ $item->nama }}
                </td>

                <td>
                    {{ $item->p }}
                </td>

                <td>
                    {{ $item->l }}
                </td>

                <td>
                    {{ $item->t }}
                </td>

                <td>
                    {{ $item->material }}
                </td>

                <td>
                    {{ $item->qty }}
                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>

</body>

</html>
