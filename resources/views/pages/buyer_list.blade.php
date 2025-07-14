@extends('master.master')
@section('title', "Buyyers list")
@section('content')
<div class="padding">
    <div class="box">
        <div class="box-header">
            <h2>Buyer Lists</h2>
            <small>Bank Data Buyers</small>
        </div>
        <div class="form-group col-lg-4" style="height: 20px;margin-bottom:15px">
            <div class="input-group" style="height: 30px">
                <div class="input-group-addon">@</div>
                <input id="searchBuyer" class="form-control" type="text" placeholder="Cari buyyers">
            </div>
        </div>

        <div class="col-12">
            <div class="table-wrapper">
                <table id="buyersTable" class="table table-hover">
                    <thead style="background-color: #6c7ae0;">
                        <tr  class="sticky-header">
                            <th rowspan="2">No.</th>
                            <th rowspan="2">Buyers Name</th>
                            <th rowspan="2">Status</th>
                            <th rowspan="2">Updated At</th>
                            <th >Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php$no = 1;
                        @endphp
                        @foreach($lb as $item)
                        @php

                        $ck = \App\Models\Barangs::where('buyer_id', $item->id)->get();
                        @endphp
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->name }}</td>
                            @if(count($ck) > 0)
                            <td>
                                {{ count($ck) }} items


                            </td>
                            @elseif(count($ck) == 0)
                            <td>
                                {{ count($ck) }} items

                            </td>
                            @endif
                            <td>{{ $item->updated_at }}</td>
                            <td>

                                <a href="/buyers_detail/{{ $item->id }}">

                                    <i class="fa - fa-external-link"></i>
                                </a>
                            </td>
                        </tr>
                        @php$no++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const tableWrapper = document.querySelector(".table-wrapper");
  const headerCells = document.querySelectorAll("thead th");

  tableWrapper.addEventListener("scroll", function () {
    if (tableWrapper.scrollTop > 0) {
      headerCells.forEach(th => th.classList.add("scrolled"));
    } else {
      headerCells.forEach(th => th.classList.remove("scrolled"));
    }
  });
});
</script>
<script>
    $('#searchBuyer').on('keyup', function() {
        var search = $(this).val().trim();

        if (search.length == 0) {
            // jika search kosong, table juga harus kosong
            $('#buyersTable tbody').empty();
            return;
        }

        // fetch data dari controller sesuai search
        $.ajax({
            url: "{{ route('buyers.search') }}",
            data: {
                search
            },
            success: function(data) {
                var tbody = '';
                data.forEach(function(item) {
                    var buttonLabel = item.has_barang ? "Update CSV" : "Import CSV";

                    tbody += `
                    <tr>
                        <td>${item.id}</td>
                        <td>${item.name}</td>
                        <td>${item.status}</td>
                        <td>${item.updated_at}</td>

                        <td>
                            <form action="{{ route('products.importb') }}" method="POST" enctype="multipart/form-data">
@csrf
                                <input type="file" name="file">
                                <input type="hidden" name="buyer_id" value="${item.id}">
                                <button type="submit" class="btn btn-primary" style="width:100px;height:auto;padding:6px;font-size:12px">
                                   ${buttonLabel}
                                </button>
                            </form>
                            <br>
                           <form action="{{ route('products.importImage') }}" method="POST" enctype="multipart/form-data">
@csrf
                            <div class="form-group">
                                <label>Range Image (contoh: A C)</label>
                                <input type="text" name="range" class="form-control">
                            </div>
                            <input type="hidden" name="buyer_id" value="${item.id}">
                            <input type="file" name="file">
                         <button type="submit" class="btn btn-primary" style="width:100px;height:auto;padding:6px;font-size:12px">
                                   Import Xslx
                                </button>
                            </form>
                        </td>
                         <td>

                                <a href="/buyers_detail/${item.id}">

                                    <i class="fa - fa-external-link"></i>
                                </a>
                            </td>
                    </tr>`;
                });
                $('#buyersTable tbody').html(tbody);
            },
            error: function(error) {
                console.error(error);
            }
        });
    });
</script>


@endsection
