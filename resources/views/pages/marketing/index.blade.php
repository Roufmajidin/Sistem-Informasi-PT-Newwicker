@extends('master.master')
@section('title', "Preview Excel + Save Items")

@section('content')
<div class="padding">

    {{-- Upload Excel --}}
    <div class="mb-3">
        <label for="excelFile" class="form-label">Upload File Excel PFI</label>
        <input type="file" class="form-control" id="excelFile" accept=".xls,.xlsx">
    </div>

   <div class="row">
    <!-- Kiri: Preview Excel -->
    <div class="col-md-6">
        <h5>Preview Excel</h5>
        <div id="excelPreviewContainer" class="table-responsive"
             style="max-height:500px; overflow:auto; border:1px solid #ddd; padding:5px;">
        </div>
    </div>

    <!-- Kanan: Company Profile + Items -->
    <div class="col-md-6">
        <h5>Company Profile</h5>
        <div class="table-responsive" style="max-height:200px; overflow:auto; border:1px solid #ddd; padding:5px;">
            <table class="table table-bordered" id="companyTable"></table>
        </div>

        <h5 class="mt-4">Items</h5>
        <div class="table-responsive" style="max-height:400px; overflow:auto; border:1px solid #ddd; padding:5px;">
            <table class="table table-bordered table-striped" id="itemsTable">
                <thead></thead>
                <tbody></tbody>
            </table>
        </div>

        <button id="convertCheckedRows" class="btn btn-primary mt-2">Convert Checked Rows</button>
        <button id="saveItems" class="btn btn-success mt-2">Save Items</button>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

<script>
$(document).ready(function() {

    const skipCheckboxRows = 11; // checkbox mulai dari baris 12
    const companyProfileStartRow = 4; // A5
    const companyProfileEndRow = 9;   // A10
    let rows = [];
    let images = [];

    $('#excelFile').on('change', function(e){
        const file = e.target.files[0];
        if(!file) return;

        let formData = new FormData();
        formData.append('excel_file', file);
        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: '{{ route("marketing.excel.upload") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res){
                rows = res.rows;
                images = res.images;

                const footerKeywords = [
                    'cbm','surcharge','total fob price','payment','production time',
                    'bank details','name of bank','address of bank','swift code',
                    'account name','account number','made by','approved by'
                ];

                // --- Render Preview Excel dengan checkbox ---
                let html = '<table border="1" style="border-collapse:collapse;width:100%;">';
                rows.forEach((row,rIndex)=>{
                    if(rIndex < skipCheckboxRows) return;
                    const firstCol = (row[0] || '').toString().trim().toLowerCase();
                    if(footerKeywords.includes(firstCol)) return;

                    html += '<tr>';
                    html += `<td><input type="checkbox" class="rowCheckbox" data-row="${rIndex}"></td>`;
                    row.forEach((val,cIndex)=>{
                        html += `<td>${val ?? ''}</td>`;
                    });
                    html += '</tr>';
                });
                html += '</table>';
                $('#excelPreviewContainer').html(html);

                // --- Company Profile A5:C10 ---
                const companyTable = $('#companyTable');
                companyTable.empty();
                for(let r=companyProfileStartRow; r<=companyProfileEndRow; r++){
                    let key = rows[r][0] ?? '';
                    let val = rows[r][2] ?? '';
                    val = val.toString().replace(/^:\s*/,''); // hapus ':' di depan
                    if(key && val){
                        companyTable.append(`<tr><th>${key}</th><td>${val}</td></tr>`);
                    }
                }

                // --- Items Table otomatis ---
                renderItemsTable(rows, images, skipCheckboxRows, footerKeywords);
            }
        });
    });

    // --- Convert Checked Rows ---
   $('#convertCheckedRows').on('click', function(){
    const checkedRows = $('#excelPreviewContainer tr').has('input.rowCheckbox:checked');
    if(checkedRows.length===0) return;

    const filteredRows = [];
    checkedRows.each(function(){
        const rowIndex = parseInt($(this).find('input.rowCheckbox').data('row'));
        filteredRows.push(rows[rowIndex]);
    });

    const newImagesMap = mapImagesForFilteredRows(filteredRows, rows, images);

    renderItemsTable(filteredRows, newImagesMap, 0, []);
});

    // --- Save Items via AJAX ---
    $('#saveItems').on('click', function(){
        const itemsTable = $('#itemsTable');
        const items = [];

        // Ambil header dari thead
        const headers = [];
        itemsTable.find('thead th').each(function(){
            let text = $(this).text().trim()
                          .replace(/\n/g, ' ')
                          .replace(/\s+/g,' ')
                          .replace(/"/g,'');
            headers.push(text);
        });

        itemsTable.find('tbody tr').each(function(){
            const rowObj = {};
            $(this).find('td').each(function(i){
                const img = $(this).find('img');
                const value = img.length ? img.attr('src') : $(this).text().trim();
                if(headers[i]) rowObj[headers[i]] = value;
            });

            if(Object.values(rowObj).some(v => v !== '')) items.push(rowObj);
        });

        console.log({ items });

        $.ajax({
            url: '{{ route("marketing.excel.save") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                company: getCompanyProfile(),
                items: items
            },
            success: function(res){
                alert('Items berhasil disimpan!');
            },
            error: function(err){
                alert('Terjadi kesalahan saat menyimpan!');
                console.log(err);
            }
        });
    });
function mapImagesForFilteredRows(filteredRows, originalRows, originalImages) {
    const newMap = {};
    filteredRows.forEach((row, newRIndex) => {
        const oldRIndex = originalRows.indexOf(row);
        row.forEach((val, cIndex) => {
            const key = `${oldRIndex}-${cIndex}`;
            if(originalImages[key]){
                newMap[`${newRIndex}-${cIndex}`] = originalImages[key];
            }
        });
    });
    return newMap;
}
    // --- Fungsi ambil Company Profile ---
    function getCompanyProfile(){
        const company = {};
        $('#companyTable tr').each(function(){
            const key = $(this).find('th').text();
            const val = $(this).find('td').text();
            company[key] = val;
        });
        return company;
    }

    // --- Fungsi render items table ---
    function renderItemsTable(dataRows, imagesMap, skip=0, footerKeywords=[]){
        const itemsTable = $('#itemsTable');
        itemsTable.find('thead').empty();
        itemsTable.find('tbody').empty();

        if(dataRows.length===0) return;

        // Header
        const firstRow = dataRows[0];
        const headers = firstRow.map((val,idx)=>{
            let h = val?.toString().replace(/\n/g,' ').trim() ?? '';
            // Mapping W/D/H ke item_/pack_
            if(idx>=9 && idx<=11) return 'item_w item_d item_h'.split(' ')[idx-9];
            if(idx>=12 && idx<=14) return 'pack_w pack_d pack_h'.split(' ')[idx-12];
            return h;
        });

        let headerHtml = '<tr>';
        headers.forEach(h=>{
            headerHtml += `<th>${h}</th>`;
        });
        headerHtml += '</tr>';
        itemsTable.find('thead').append(headerHtml);

        // Body
        for(let r=skip; r<dataRows.length; r++){
            const row = dataRows[r];
            const firstCol = (row[0] ?? '').toString().trim().toLowerCase();
            if(footerKeywords.includes(firstCol)) continue;
            if(row.every(cell => cell===null || cell.toString().trim()==='')) continue;

            let rowHtml = '<tr>';
            row.forEach((val,cIndex)=>{
                const key = headers[cIndex] ?? `col_${cIndex}`;
                if(imagesMap[`${r}-${cIndex}`]){
                    rowHtml += `<td><img src="${imagesMap[`${r}-${cIndex}`]}" style="max-width:100px; max-height:80px;"></td>`;
                } else {
                    let text = val ?? '';
                    text = text.toString().replace(/\n/g,' ').trim();
                    rowHtml += `<td>${text}</td>`;
                }
            });
            rowHtml += '</tr>';
            itemsTable.find('tbody').append(rowHtml);
        }
    }

});
</script>

<style>
.rowCheckbox {
    width: 30px;
    height: 30px;
    cursor: pointer;
}
#itemsTable img {
    max-width:100px;
    max-height:80px;
    object-fit:contain;
}
</style>
@endpush
