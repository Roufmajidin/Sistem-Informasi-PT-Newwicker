@extends('master.master')
@section('title', "Portal NewWicker")
@section('content')
@php
    use App\Models\Karyawan;
    $k = Karyawan::get();
@endphp
<div class="padding">
	<div class="row">
		<div class="col-xs-12 col-sm-4">
	        <div class="box p-a">
	          <div class="pull-left m-r">
	            <span class="w-48 rounded  accent">
	              <i class="material-icons">&#xe151;</i>
	            </span>
	          </div>
	          <div class="clear">
	            <h4 class="m-0 text-lg _300"><a href>{{$k->count()}} <span class="text-sm">Karyawan Bulanan</span></a></h4>
	            <small class="text-muted">Active.</small>
	          </div>
	        </div>
	    </div>
	    <div class="col-xs-6 col-sm-4">
	        <div class="box p-a">
	          <div class="pull-left m-r">
	            <span class="w-48 rounded primary">
	              <i class="material-icons">&#xe54f;</i>
	            </span>
	          </div>
	          <div class="clear">
	            <h4 class="m-0 text-lg _300"><a href>40 <span class="text-sm">Projects</span></a></h4>
	            <small class="text-muted">38 open.</small>
	          </div>
	        </div>
	    </div>
	    <div class="col-xs-6 col-sm-4">
	        <div class="box p-a">
	          <div class="pull-left m-r">
	            <span class="w-48 rounded warn">
	              <i class="material-icons">&#xe8d3;</i>
	            </span>
	          </div>
	          <div class="clear">
	            <h4 class="m-0 text-lg _300"><a href>600 <span class="text-sm">Users</span></a></h4>
	            <small class="text-muted">632 vips.</small>
	          </div>
	        </div>
	    </div>
	</div>
  <div class="box-body">
    <div class="row">

        <!-- ================= LEFT : TIMELINE ================= -->
        <div class="col-md-6">
            <div class="p-a">
                <div class="streamline b-l m-b" id="timelineContainer">
                    <!-- Data timeline via JS -->
                </div>
            </div>
        </div>


        <!-- ================= RIGHT : TASK ================= -->
        <div class="col-md-6">

         <div class="box">
    <div class="box-header">
        <h3>SPK Timeline</h3>
    </div>

    <div class="box-body">
        <div class="streamline b-l m-l" id="spkTimelineContainer">
        </div>
    </div>
</div>

        </div>

    </div>
</div>

</div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
  function loadSpkTimeline()
{
    $.get("{{ route('spk.time') }}", function(res){

        let html = '';

        res.forEach((row, index) => {

            let badge = '';
            let content = '';
            let collapseId = `collapse-${row.id}`;

     if (row.type === 'create') {

    badge = `<span class="badge badge-success">CREATE</span>`;

    let itemsHtml = '';

    if (row.after?.items && Array.isArray(row.after.items)) {
        itemsHtml = `
            <ul class="mb-0 pl-3 small text-muted">
                ${row.after.items.map(item => `
                    <li>
                        ${item.nama}
                        <b>
                            (${item.qty} ${item.satuan})
                        </b>
                    </li>
                `).join('')}
            </ul>
        `;
    }

    content = `
        <div class="mt-1 text-muted">
            <div><b>SPK dibuat</b></div>
            <div>No SPK : <b>${row.after?.no_spk ?? '-'}</b></div>
            <div>Supplier : <b>${row.after?.sup ?? '-'}</b></div>
            ${itemsHtml}
        </div>
    `;
}

            if (row.type === 'update') {
                badge = `<span class="badge badge-warning">UPDATE</span>`;

                let changeCount = row.changes
                    ? Object.keys(row.changes).length
                    : 0;

                // HEADER RINGKAS
                content = `
                    <div class="mt-1">
                        <a data-toggle="collapse"
                           href="#${collapseId}"
                           style="text-decoration:none">
                            SPK diperbarui
                            <span class="text-muted">
                                (${changeCount} perubahan)
                            </span>
                            <i class="ml-1 fa fa-chevron-down"></i>
                        </a>

                        <div class="collapse mt-2" id="${collapseId}">
                            ${renderChanges(row.changes)}
                        </div>
                    </div>
                `;
            }

            html += `
                <div class="sl-item mb-2">
                    <div class="sl-content">

                        <div class="d-flex justify-content-between">
                            <div>
                                ${badge}
                                <strong class="ml-1">${row.user ?? '-'}</strong>
                            </div>
                            <small class="text-muted">
                                ${formatTanggal(row.time)}
                            </small>
                        </div>

                        ${content}

                    </div>
                </div>
            `;
        });

        $('#spkTimelineContainer').html(html);
    });
}

function renderChanges(changes)
{
    if (!changes) return '';

    let html = '<ul class="list-unstyled small mb-0">';

    Object.keys(changes).forEach(key => {
        let before = formatValue(changes[key].before);
        let after  = formatValue(changes[key].after);

        html += `
            <li>
                <code>${key}</code> :
                <span class="text-danger">${before}</span>
                →
                <span class="text-success">${after}</span>
            </li>
        `;
    });

    html += '</ul>';
    return html;
}

function formatValue(val)
{
    if (val === null) return '<i>null</i>';

    // IMAGE DETECT
    if (typeof val === 'string' && val.match(/\.(jpg|png|jpeg|webp)$/)) {
        return `<img src="${val}" style="height:30px;border:1px solid #ccc">`;
    }

    if (typeof val === 'object') {
        return JSON.stringify(val);
    }

    return val;
}

function formatTanggal(date)
{
    return new Date(date).toLocaleString('id-ID');
}

loadSpkTimeline();


    // timeline lama
    function loadTimeline() {
        $.ajax({
            url: "{{ route('timeline.data') }}",
            type: "GET",
            dataType: "json",
            success: function(data) {

                let html = '';
                console.log(data)
                data.forEach(function(item) {

                    let userName = item.user?.name ?? 'Unknown';
                    let remark   = item.isi?.remark ?? '-';
                    let created  = item.isi?.timestamp
                                    ? new Date(item.isi.timestamp).toLocaleString()
                                    : new Date(item.created_at).toLocaleString();

                    let jenisClass = item.jenis === 'edit pfi' ? 'b-success' : '';

                    html += `
                        <div class="sl-item ${jenisClass}">
                            <div class="sl-content">
                                <div class="sl-date text-muted">${created}</div>
                                <p><strong>${userName}</strong>: ${remark}</p>
                            </div>
                        </div>
                    `;
                });

                $('#timelineContainer').html(html);
            }
        });
    }

    // 🔥 WAJIB DIPANGGIL
    loadTimeline();
    // loadSpkTimeline();



</script>
@endsection
