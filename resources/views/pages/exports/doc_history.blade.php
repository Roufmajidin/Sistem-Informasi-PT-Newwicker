@extends('master.master')
{{-- IPL BLADE --}}
@section('btn')

    <div class="btn-group shadow-sm">

        <a href="/export/doc_exports" class="btn {{ request()->routeIs('export.ipl') ? 'btn-success' : 'btn-light' }}">

            <i class="fa fa-file-alt"></i>

            Form Upload

        </a>

        <a href="{{ route('export.document.index') }}"
            class="btn {{ request()->routeIs('export.document.index') ? 'btn-success' : 'btn-light' }}">

            <i class="fa fa-folder-open"></i>

            Documents

        </a>

    </div>
@endsection

@section('content')
    <div class="card mt-4 p-2">



        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="thead-light">

                        <tr>

                            <th>PO</th>

                            <th>Buyer</th>

                            <th>PEB No</th>

                            <th>SI</th>

                            <th>DO</th>

                            <th>Invoice</th>

                            <th>PL</th>

                            <th>BL</th>

                            <th>COO</th>

                            <th>Fumi</th>

                            <th>V-Legal</th>

                            <th>Phyto</th>

                            <th>ISF</th>

                            <th>Plant</th>

                            <th>Animal</th>

                            <th>Declaration</th>

                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($datas as $row)
                            @php

                                $files = $row->files->keyBy('document_type');

                                $declarations = $row->files->where('document_type', 'declaration');

                            @endphp

                            <tr>

                                <td>{{ optional($row->po)->order_no }}</td>

                                <td>{{ $row->buyer_name }}</td>

                                <td>{{ $row->peb_no }}</td>

                                {{-- Shipping Instruction --}}
                                <td>
                                    @if (isset($files['shipping_instruction']))
                                        <a href="{{ asset('storage/' . $files['shipping_instruction']->file_path) }}"
                                            target="_blank" class="file-link"
                                            title="{{ $files['shipping_instruction']->original_name }}">

                                            <i class="fa fa-file-pdf-o text-danger"></i>

                                            <span>{{ $files['shipping_instruction']->original_name }}</span>

                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Delivery Order --}}
                                <td>
                                    @if (isset($files['delivery_order']))
                                        <a href="{{ asset('storage/' . $files['delivery_order']->file_path) }}"
                                            target="_blank" class="file-link"
                                            title="{{ $files['delivery_order']->original_name }}">

                                            <i class="fa fa-file-pdf-o text-danger"></i>

                                            <span>{{ $files['delivery_order']->original_name }}</span>

                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Invoice --}}
                                <td>
                                    {{ optional($row->invoice)->invoice_no ?? '-' }}
                                </td>

                                {{-- Packing List --}}
                                <td>
                                    {{ optional($row->packingList)->invoice_no ?? '-' }}
                                </td>

                                {{-- BL --}}
                                <td>
                                    @if (isset($files['bl']))
                                        <a href="{{ asset('storage/' . $files['bl']->file_path) }}" target="_blank"
                                            class="file-link" title="{{ $files['bl']->original_name }}">

                                            <i class="fa fa-file-pdf-o text-danger"></i>

                                            <span>{{ $files['bl']->original_name }}</span>

                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- COO --}}
                                <td>
                                    @if (isset($files['coo']))
                                        <a href="{{ asset('storage/' . $files['coo']->file_path) }}" target="_blank"
                                            class="file-link" title="{{ $files['coo']->original_name }}">

                                            <i class="fa fa-file-pdf-o text-danger"></i>

                                            <span>{{ $files['coo']->original_name }}</span>

                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Fumigation --}}
                                <td>
                                    @if (isset($files['fumigation']))
                                        <a href="{{ asset('storage/' . $files['fumigation']->file_path) }}" target="_blank"
                                            class="file-link" title="{{ $files['fumigation']->original_name }}">

                                            <i class="fa fa-file-pdf-o text-danger"></i>

                                            <span>{{ $files['fumigation']->original_name }}</span>

                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- V Legal --}}
                                <td>
                                    @if (isset($files['v_legal']))
                                        <a href="{{ asset('storage/' . $files['v_legal']->file_path) }}" target="_blank"
                                            class="file-link" title="{{ $files['v_legal']->original_name }}">

                                            <i class="fa fa-file-pdf-o text-danger"></i>

                                            <span>{{ $files['v_legal']->original_name }}</span>

                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Phyto --}}
                                <td>
                                    @if (isset($files['phyto']))
                                        <a href="{{ asset('storage/' . $files['phyto']->file_path) }}" target="_blank"
                                            class="file-link" title="{{ $files['phyto']->original_name }}">

                                            <i class="fa fa-file-pdf-o text-danger"></i>

                                            <span>{{ $files['phyto']->original_name }}</span>

                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- ISF --}}
                                <td>
                                    @if (isset($files['isf']))
                                        <a href="{{ asset('storage/' . $files['isf']->file_path) }}" target="_blank"
                                            class="file-link" title="{{ $files['isf']->original_name }}">

                                            <i class="fa fa-file-pdf-o text-danger"></i>

                                            <span>{{ $files['isf']->original_name }}</span>

                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Lacey Plant --}}
                                <td>
                                    @if (isset($files['lacey_plant']))
                                        <a href="{{ asset('storage/' . $files['lacey_plant']->file_path) }}" target="_blank"
                                            class="file-link" title="{{ $files['lacey_plant']->original_name }}">

                                            <i class="fa fa-file-pdf-o text-danger"></i>

                                            <span>{{ $files['lacey_plant']->original_name }}</span>

                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Lacey Animal --}}
                                <td>
                                    @if (isset($files['lacey_animal']))
                                        <a href="{{ asset('storage/' . $files['lacey_animal']->file_path) }}" target="_blank"
                                            class="file-link" title="{{ $files['lacey_animal']->original_name }}">

                                            <i class="fa fa-file-pdf-o text-danger"></i>

                                            <span>{{ $files['lacey_animal']->original_name }}</span>

                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Declaration --}}
                                <td>

                                    @forelse($declarations as $file)
                                        <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank"
                                            class="file-link mb-1" title="{{ $file->original_name }}">

                                            <i class="fa fa-file-pdf-o text-danger"></i>

                                            <span>{{ $file->original_name }}</span>

                                        </a>

                                    @empty

                                        -
                                    @endforelse

                                </td>

                                <td class="text-center">

                                    <a href="#view" class="btn btn-info btn-sm">

                                        <i class="fa fa-eye"></i>

                                    </a>

                                 <a href="{{ route('export.document.edit',$row->id) }}"
                                    class="btn btn-warning btn-sm">

                                        <i class="fa fa-edit"></i>

                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="17" class="text-center">

                                    Belum ada dokumen.

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        <div class="card-footer">


        </div>

    </div>
    <script></script>
    <style>
        .file-link {

            display: flex;
            align-items: center;
            gap: 6px;

            max-width: 140px;

            text-decoration: none;

            color: #dc3545;

        }

        .file-link:hover {

            text-decoration: none;

            color: #b71c1c;

        }

        .file-link i {

            font-size: 18px;

            flex-shrink: 0;

        }

        .file-link span {

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;

            display: block;

        }
    </style>
@endsection
