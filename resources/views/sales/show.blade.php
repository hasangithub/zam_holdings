@extends('layouts.app')

@section('title','Sale Invoice')

@section('content')

<div class="container">

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif


    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <h3>Sale Invoice #{{ $sale->invoice_id }}</h3>

        <div>
            <a href="{{ route('sales.invoice', $sale->id) }}" class="btn btn-dark">
                Print Invoice
            </a>
        </div>

    </div>


    {{-- SALE SUMMARY --}}
    <div class="card mb-4">

        <div class="card-header">
            Sale Information
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 mb-3">
                    <strong>Customer</strong><br>
                    {{ $sale->customer->name ?? '-' }}
                </div>

                <div class="col-md-4 mb-3">
                    <strong>Invoice Date</strong><br>
                    {{ date('d M Y', strtotime($sale->sale_date ?? $sale->created_at)) }}
                </div>

                <div class="col-md-4 mb-3">
                    <strong>Currency</strong><br>
                    {{ $sale->currency }}
                </div>

                <div class="col-md-4 mb-3">
                    <strong>Exchange Rate</strong><br>
                    {{ $sale->exchange_rate }}
                </div>

                <div class="col-md-4 mb-3">
                    <strong>Total Amount</strong><br>
                    <h5 class="text-primary">
                        {{ number_format($sale->total, 2) }}
                    </h5>
                </div>

                @if($sale->currency != 'LKR')
                <div class="col-md-4 mb-3">
                    <strong>Foreign Total</strong><br>
                    <h5 class="text-info">
                        {{ number_format($sale->total_foreign ?? 0, 2) }}
                    </h5>
                </div>
                @endif

            </div>

        </div>
    </div>


    {{-- ITEMS TABLE --}}
    <div class="card">

        <div class="card-header">
            Items
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered mb-0">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Base Price</th>
                            <th>Sale Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($sale->items as $key => $item)

                        <tr>

                            <td>{{ $key + 1 }}</td>

                            <td>{{ $item->item->name ?? '-' }}</td>

                            <td>{{ $item->qty }}</td>

                            <td>{{ number_format($item->base_price, 2) }}</td>

                            <td>{{ number_format($item->sale_price, 2) }}</td>

                            <td class="text-end">
                                {{ number_format($item->subtotal, 2) }}
                            </td>

                        </tr>

                        @empty

                        <tr>
                            <td colspan="6" class="text-center">
                                No items found
                            </td>
                        </tr>

                        @endforelse

                    </tbody>

                    <tfoot>

                        <tr>
                            <th colspan="5" class="text-end">Total</th>
                            <th>{{ number_format($sale->total, 2) }}</th>
                        </tr>

                    </tfoot>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection