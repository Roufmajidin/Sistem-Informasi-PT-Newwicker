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
    <div class="p-a">
        <div class="streamline b-l m-b" id="timelineContainer">
            <!-- Data timeline akan di-load di sini via JS -->
        </div>
    </div>
</div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>

    $(document).ready(function() {
        function loadTimeline() {
    $.ajax({
        url: "{{ route('timeline.data') }}",
        type: "GET",
        dataType: "json",
        success: function(data) {
            let html = '';

            data.forEach(function(item) {
                console.log(item);
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

        loadTimeline();
    })


</script>
@endsection
