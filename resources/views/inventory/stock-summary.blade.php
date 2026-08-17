@extends('layouts.app')

@section('title', 'Stock Summary')

@section('content')

<div class="container-fluid">

    {{-- ========================================================= --}}
    {{-- SUMMARY CARDS --}}
    {{-- ========================================================= --}}

    <div class="row mb-3">

        {{-- TOTAL PURCHASED --}}
        <div class="col-md-3 col-sm-6">

            <div class="small-box bg-info">

                <div class="inner">

                    <h4>
                        {{ number_format($stocks->sum('purchased_qty'), 2) }}
                    </h4>

                    <p>Total Purchased Qty</p>

                </div>

                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>

            </div>

        </div>


        {{-- AVAILABLE STOCK --}}
        <div class="col-md-3 col-sm-6">

            <div class="small-box bg-success">

                <div class="inner">

                    <h4>
                        {{ number_format($stocks->sum('stock_qty'), 2) }}
                    </h4>

                    <p>Available Stock</p>

                </div>

                <div class="icon">
                    <i class="fas fa-warehouse"></i>
                </div>

            </div>

        </div>


        {{-- AVERAGE COST --}}
        <div class="col-md-3 col-sm-6">

            <div class="small-box bg-warning">

                <div class="inner">

                    <h4>
                        {{ number_format($stocks->avg('avg_price'), 2) }}
                    </h4>

                    <p>Average Cost</p>

                </div>

                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>

            </div>

        </div>


        {{-- STOCK VALUE --}}
        <div class="col-md-3 col-sm-6">

            <div class="small-box bg-danger">

                <div class="inner">

                    <h4>
                        {{ number_format($stocks->sum('stock_value'), 2) }}
                    </h4>

                    <p>Total Stock Value</p>

                </div>

                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- STOCK TABLE --}}
    {{-- ========================================================= --}}

    <div class="card card-outline card-primary">

        <div class="card-header">

            <h3 class="card-title">

                <i class="fas fa-warehouse mr-2"></i>

                Stock Summary

            </h3>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table
                    id="stockTable"
                    class="table table-bordered table-striped table-hover table-erp">

                    <thead class="bg-light">

                        <tr>

                            <th>
                                
                            </th>

                            <th>
                                Item
                            </th>

                            <th class="text-right">
                                Purchased Qty
                            </th>

                            <th class="text-right">
                                Available Qty
                            </th>

                            <th class="text-right">
                                Avg Cost
                            </th>

                            <th class="text-right">
                                Stock Value
                            </th>

                            <th class="text-center">
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($stocks as $stock)

                            <tr>
                                <td>
                                    {{ $stock->item->item_code ?? '-' }}
                                </td>


                                {{-- ITEM --}}
                                <td>

                                    <i class="fas fa-cube text-primary mr-1"></i>

                                    <strong>
                                        {{ $stock->item->name ?? '-' }}
                                    </strong>

                                </td>


                                {{-- PURCHASED QTY --}}
                                <td class="text-right">

                                    {{ number_format(
                                        $stock->purchased_qty,
                                        2
                                    ) }}

                                </td>


                                {{-- AVAILABLE QTY --}}
                                <td class="text-right">

                                    <strong>

                                        {{ number_format(
                                            $stock->stock_qty,
                                            2
                                        ) }}

                                    </strong>

                                </td>


                                {{-- AVERAGE COST --}}
                                <td class="text-right">

                                    {{ number_format(
                                        $stock->avg_price,
                                        2
                                    ) }}

                                </td>


                                {{-- STOCK VALUE --}}
                                <td class="text-right">

                                    <strong>

                                        {{ number_format(
                                            $stock->stock_value,
                                            2
                                        ) }}

                                    </strong>

                                </td>


                                {{-- STATUS --}}
                                <td class="text-center">

                                    @if($stock->stock_qty <= 0)

                                        <span class="badge badge-danger">
                                            Out
                                        </span>

                                    @elseif($stock->stock_qty < 10)

                                        <span class="badge badge-warning">
                                            Low
                                        </span>

                                    @elseif($stock->stock_qty < 20)

                                        <span class="badge badge-info">
                                            Medium
                                        </span>

                                    @else

                                        <span class="badge badge-success">
                                            Good
                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="7"
                                    class="text-center text-muted">

                                    <i class="fas fa-box-open mr-1"></i>

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

    $('#stockTable').DataTable({

        responsive: true,

        pageLength: 10,

        autoWidth: false,

        ordering: true,

        searching: true,

        language: {

            search: "Search Stock:",

            emptyTable: "No stock available",

            zeroRecords: "No matching stock found"

        }

    });

});

</script>

@endpush