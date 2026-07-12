@extends('layouts.app')

@section('title','Inventory Summary')

@section('content')

<div class="container-fluid">

    {{-- HEADER CARDS --}}
    <div class="row mb-3">

        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($stocks->sum('purchased_qty'), 2) }}</h4>
                    <p>Total Purchased Qty</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>{{ number_format($stocks->sum('stock_qty'), 2) }}</h4>
                    <p>Available Stock</p>
                </div>
                <div class="icon">
                    <i class="fas fa-warehouse"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>{{ number_format($stocks->avg('avg_price'), 2) }}</h4>
                    <p>Average Cost</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>{{ number_format($stocks->sum('stock_value'), 2) }}</h4>
                    <p>Total Stock Value</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- TABLE --}}
    <div class="card card-outline card-primary">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-warehouse mr-2"></i>
                Inventory Summary
            </h3>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table id="summaryTable" class="table table-bordered table-striped table-erp">

                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th class="text-right">Purchased Qty</th>
                            <th class="text-right">Available Qty</th>
                            <th class="text-right">Avg Cost</th>
                            <th class="text-right">Stock Value</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($stocks as $stock)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <strong>{{ $stock->item->name ?? '' }}</strong>
                            </td>

                            <td class="text-right">
                                {{ number_format($stock->purchased_qty,2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($stock->stock_qty,2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($stock->avg_price,2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($stock->stock_value,2) }}
                            </td>

                            <td>
                                @if($stock->stock_qty <= 0)
                                    <span class="badge badge-danger">Out</span>
                                @elseif($stock->stock_qty < 10)
                                    <span class="badge badge-warning">Low</span>
                                @else
                                    <span class="badge badge-success">Good</span>
                                @endif
                            </td>

                        </tr>

                        @empty

                        <tr>
                            <td colspan="7" class="text-center">
                                No Stock Found
                            </td>
                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection


@push('scripts')
<script>
    $(document).ready(function () {
        $('#summaryTable').DataTable({
            responsive: true,
            pageLength: 10,
            autoWidth: false,
            ordering: true,
            searching: true
        });
    });
</script>
@endpush