<!DOCTYPE html>
<html>
<head>
    <title>Preview Excel</title>
    <style>
        table { border-collapse: collapse; margin-top: 10px; }
        td, th { border: 1px solid #aaa; padding: 6px; cursor: text; user-select: text; }
        img { max-width: 100px; max-height: 100px; display: block; }
    </style>
</head>
<body>

<h3>Preview Excel</h3>

<form action="{{ route('pfi.import.process') }}" method="POST">
@csrf

<table id="excel-table">
    @foreach($rows as $rowIndex => $row)
        <tr>
            <td>
                <input type="checkbox" name="rows[]" value="{{ $rowIndex }}">
            </td>
            @foreach($row as $cell)
                @if(str_starts_with($cell, 'data:image'))
                    <td><img src="{{ $cell }}" alt="image"></td>
                @else
                    <td>{{ $cell }}</td>
                @endif
            @endforeach
        </tr>
    @endforeach
</table>

<br>
<button type="submit">Process Selected</button>

</form>

<!-- JSON Preview -->
<pre id="json-output" style="border:1px solid #aaa; padding:10px; margin-top:20px; font-family: monospace;"></pre>
<script>
    // Kirim PHP $detailOrder ke JS
    const detailOrder = @json($detailOrder); // ini sudah format { "Order No.": ": NW 25 - 79", ... }

    const companyProfile = {
        CompanyName: {
            orderNo: "{{ $rows[3][2] ?? '' }}",
            companyName: "{{ $rows[4][2] ?? '' }}",
            country: "{{ $rows[5][2] ?? '' }}",
            shipmentDate: "{{ $rows[6][2] ?? '' }}",
            packing: "{{ $rows[7][2] ?? '' }}",
            contactPerson: "{{ $rows[8][2] ?? '' }}",
        },
        // OrderInfo: detailOrder
    };

    console.log(companyProfile); // cek output
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('excel-table');
    if(!table) return;

    const trs = Array.from(table.querySelectorAll('tr'));
    const rows = trs.map(tr => Array.from(tr.querySelectorAll('td'))
        .slice(1) // skip checkbox jika ada
        .map(td => td.querySelector('img') ? td.querySelector('img').src : td.textContent.trim())
    );

    // let companyProfile = { CompanyName: {}, OrderInfo: {} };
    let headers = [];
    let subHeaders = [];
    let items = [];
    let isItemSection = false;

    for (let lineIndex = 0; lineIndex < rows.length; lineIndex++) {
        const r = rows[lineIndex];
        if (r.length === 0) continue;

        // ======= Header Items =======
        if (r[0].toLowerCase() === 'no.') {
            headers = r.map(h => h.replace(/\n/g, ' ').trim());
            isItemSection = true;
            continue;
        }

        // ======= Sub-header W/D/H =======
        if (isItemSection && r.every(c => ['W','D','H',''].includes(c))) {
            subHeaders = r;
            headers = headers.map((h,i) => {
                if(subHeaders[i] && ['W','D','H'].includes(subHeaders[i])){
                    return h + ' ' + subHeaders[i];
                }
                return h || `col${i}`;
            });
            continue;
        }

        // ======= Data Items =======
        if (isItemSection && /^\d+$/.test(r[0])) {
            let item = {};
            let i = 0;
            while(i < headers.length){
                const key = headers[i];
                if(key.includes('Item Dimention') || key.includes('Packing Dimention')){
                    item[key.split(' ')[0]] = {
                        W: r[i] || '',
                        D: r[i+1] || '',
                        H: r[i+2] || ''
                    };
                    i += 3;
                } else {
                    item[key] = r[i] || '';
                    i++;
                }
            }
            items.push(item);
            continue;
        }

        // ======= Company / Order Profile =======
        if(!isItemSection){
            if(lineIndex < 3){
                // Ambil beberapa baris pertama sebagai CompanyName
                companyProfile.CompanyName[`line${lineIndex+1}`] = r.find(c => c.length > 0) || '';
            } else {
                // Ambil OrderInfo: key di kolom B, value di kolom C
                let key = r[1]?.trim() || '';
                let value = r[2] ? `: ${r[2]}` : ':';
                if(key.length > 0){
                    companyProfile.OrderInfo[key] = value;
                }
            }
        }
    }

    // Generate JSON
    const jsonData = {
        CompanyProfile: companyProfile.CompanyName,
        Items: items
    };

    // Tampilkan JSON
    const pre = document.getElementById('json-output');
    pre.textContent = JSON.stringify(jsonData, null, 2);

    // Sembunyikan tabel setelah JSON muncul
    table.style.display = 'none';
});
</script>

</body>
</html>
