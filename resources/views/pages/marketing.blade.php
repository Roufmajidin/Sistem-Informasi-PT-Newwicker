@extends('master.master')
@section('title', "sales marketing")
@section('content')
<div class="padding">

    <div class="box">
        <div class="box-header">
            <h2>PFI Bank</h2>
            <small>Bank Data PFI Rodiyah</small>
        </div>
        <form action="{{ route('pdf.to.excel') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label for="pdf">Upload PDF:</label>
            <input type="file" name="pdf" id="pdf" accept="application/pdf" required>
            <button type="submit">Convert to Excel</button>
        </form>
        <div class="container mt-4">
            <div class="row">
                <div class="col-lg-12">
                    <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <input type="file" name="file" id="fileInput" class="btn btn-sm btn-info pull-right hidden-print" required>
                        <div class="form-group">
                            <label for="file">Import Excel File</label>
                        </div>

                        <button type="submit" id="importBtn" class="btn btn-sm btn-info pull-right hidden-print2" style="display: none;">
                            Import Excel
                        </button>
                    </form>
                </div>
                <form action="{{ route('products.importb') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" accept=".csv">
    <button type="submit">Import CSV</button>
</form>

            </div>
        </div>
        <div class="col-12">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th rowspan="2">No.</th>
                            <th rowspan="2">Company Name</th>
                            <th rowspan="2">Country.</th>
                            <th rowspan="2">Shipment Date</th>
                            <th rowspan="2">Packing</th>
                            <th rowspan="2">Contact person</th>
                            <th rowspan="2">status</th>
                            <th rowspan="2">View</th>


                    </thead>
                    @php
                    $no = 1;

                    @endphp
                    <tbody>
                        @forelse ($buyer as $item)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ ltrim($item->company_name, ': ') }}</td>
                            <td>{{ ltrim($item->country, ': ') }}</td>
                            <td>{{ ltrim($item->shipment_date ?? '-', ': ') }}</td>
                            <td>{{ ltrim($item->packing, ': ') }}</td>
                            <td>{{ ltrim($item->contact_person, ': ') }}</td>
                            <td>{{ ltrim($item->sttus, ': ') }}</td>
                            <td>
                                <a href="/pvi/  {{$item->id}}" class="btn btn-sm btn-info">PVI</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Not yet Order.</td>
                        </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-12">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr style="font-size: 12px;">
                            @php
                            $showCushion = false;
                            $showGlass = false;

                            foreach ($products as $product) {
                            $content = $product->content ?? [];
                            if (!empty($content['cushion'])) {
                            $showCushion = true;

                            }
                            if (!empty($content['glass'])) {
                            $showGlass = true;
                            }
                            if ($showCushion && $showGlass) {
                            break;
                            }
                            }
                            @endphp
                            <th rowspan="2">No.</th>
                            <th rowspan="2">Photo</th>
                            <th rowspan="2">Description</th>
                            <th rowspan="2">Article Nr.</th>
                            <th rowspan="2">Remark</th>
                            @if($showCushion)
                            <th rowspan="2">Cushion</th>
                            @endif

                            @if($showGlass)
                            <th rowspan="2">Glass</th>
                            @endif
                            <th colspan="3" style="font-size: 12px;">Item Dimension</th>
                            <th colspan="3" style="font-size: 12px;">packing Dimension</th>
                            <th rowspan="2" style="font-size: 10px;">Composition</th>
                            <th rowspan="2" style="font-size: 10px;">Finishing</th>
                            <th rowspan="2" style="font-size: 10px;">QTY</th>
                            <th rowspan="2" style="font-size: 10px;">CBM</th>
                            <th style="font-size: 10px;" rowspan="2" class="highlight">EXWORK PRICE IN IDR</th>
                            <th rowspan="2" style="font-size: 10px;">Total CBM</th>
                            <th rowspan="2" style="font-size: 10px;">Value in IDR</th>
                        </tr>
                        <tr style="font-size: 10px;">
                            <th>W</th>
                            <th>D</th>
                            <th>H</th>
                            <th>W</th>
                            <th>D</th>
                            <th>H</th>
                        </tr>

                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach($products as $product)
                        @php $content = $product->content ?? [];
                        $basePath = 'storage/article/' . $content['article_nr'];
                        $imagePath = null;

                        if (file_exists(public_path($basePath . '.png'))) {
                        $imagePath = asset($basePath . '.png');
                        } elseif (file_exists(public_path($basePath . '.jpg'))) {
                        $imagePath = asset($basePath . '.jpg');
                        }

                        @endphp
                        <tr style="font-size: 10px;">
                            <td>{{ $no++ }}</td>
                            <td>
                                @if($imagePath)
                                <img src="{{ $imagePath }}" alt="product" width="60">
                                @else
                                <span>No image found</span>
                                @endif
                            </td>
                            <td>{{ $content['description'] ?? '-' }}</td>
                            <td>{{ $content['article_nr'] ?? '-' }}</td>
                            <td>{{ $content['remark'] ?? '-' }}</td>
                            @if($showCushion)
                            <td>{{ $content['cushion'] ?? '-' }}</td>
                            @endif

                            @if($showGlass)
                            <td>{{ $content['glass'] ?? '-' }}</td>
                            @endif
                            <td>{{ $content['item_dimention__cm_']['w'] ?? '-' }}</td>
                            <td>{{ $content['item_dimention__cm_']['d'] ?? '-' }}</td>
                            <td>{{ $content['item_dimention__cm_']['h'] ?? '-' }}</td>
                            <!-- packing -->
                            <td>{{ $content['packing_dimention__cm_']['w'] ?? '-' }}</td>
                            <td>{{ $content['packing_dimention__cm_']['d'] ?? '-' }}</td>
                            <td>{{ $content['packing_dimention__cm_']['h'] ?? '-' }}</td>

                            <td>{{ $content['composition'] ?? '-' }}</td>
                            <td>{{ $content['finishing'] ?? '-' }}</td>
                            <td>{{ $content['qty'] ?? '0' }}</td>
                            <td>{{ $content['cbm'] ?? '0.00' }}</td>
                            <td>{{ number_format($content['fob_jakarta_in_usd'] ?? 0, 0, ',', '.') }}</td>
                            <td>{{ $content['total_cbm'] ?? '0.00' }}</td>
                            <td>{{ number_format($content['value_in_usd'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
        <div class="col-3"></div>
    </div>
</div>
</div>

<script>
    // Tampilkan tombol hanya setelah file dipilih
    document.getElementById('fileInput').addEventListener('change', function() {
        const btn = document.getElementById('importBtn');
        btn.style.display = this.files.length > 0 ? 'inline-block' : 'none';
    });
</script>
@endsection
