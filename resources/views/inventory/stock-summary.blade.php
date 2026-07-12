@extends('layouts.app')

@section('title', 'Stock Summary')

@section('content')

<div class="container-fluid">

    {{-- KPI CARDS --}}
    <div class="row mb-3">

        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stocks->count() }}</h3>
                    <p>Total Items</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stocks->where('stock_qty','<=',10)->count() }}</h3>
                    <p>Low Stock</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($stocks->sum('stock_qty'),2) }}</h3>
                    <p>Total Quantity</p>
                </div>
                <div class="icon">
                    <i class="fas fa-warehouse"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- TABLE CARD --}}
    <div class="card card-default">

        {{-- HEADER --}}
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-boxes mr-1"></i>
                Stock Summary
            </h3>
        </div>

        <div class="card-body">

            {{-- DATATABLE --}}
            <table id="stockTable" class="table table-bordered table-striped table-hover table-sm table-erp">

                <thead class="thead-light">
                    <tr>
                        <th width="70">#</th>
                        <th>Item</th>
                        <th class="text-right">Available Qty</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($stocks as $stock)

                    <tr>

                        <td>{{ $loop->iteration }}</td>

                        <td>
                            <i class="fas fa-cube text-primary mr-1"></i>
                            {{ $stock->item->name }}
                        </td>

                        <td class="text-right">
                            <strong>{{ number_format($stock->stock_qty, 2) }}</strong>
                        </td>

                        <td class="text-center">

                            @if($stock->stock_qty <= 10)
                                <span class="badge badge-danger">Low</span>
                            @elseif($stock->stock_qty <= 20)
                                <span class="badge badge-warning">Medium</span>
                            @else
                                <span class="badge badge-success">Good</span>
                            @endif

                        </td>

                    </tr>

                    @empty

                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            No Stock Found
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
$(function () {

    $('#stockTable').DataTable({

        responsive: true,
        pageLength: 10,
        autoWidth: false,

        // IMPORTANT: prevents index mismatch
        columnDefs: [
            { orderable: false, targets: 0 }
        ],

        language: {
            search: "Search Stock:",
            emptyTable: "No stock available"
        }

    });

});
</script>
@endpush