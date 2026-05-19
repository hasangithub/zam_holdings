@extends('layouts.app')

@section('title','Invoice')

@section('content')

<div class="card">

    <div class="card-body">

        {{-- HEADER --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <h2><b>Zam Holdings</b></h2>
                <p>Pettah, Colombo<br>
                    Phone: 077-XXXXXXX</p>
            </div>

            <div class="col-md-6 text-right">
                <h3><b>INVOICE</b></h3>
                <p>
                    Invoice No: <b>{{ $sale->invoice_id }}</b><br>
                    Date: {{ $sale->sale_date ?? $sale->created_at->format('Y-m-d') }}
                </p>
            </div>
        </div>

        <hr>

        {{-- CUSTOMER --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <h5><b>Bill To:</b></h5>
                <p>
                    {{ $sale->customer->name }}<br>
                    {{ $sale->customer->phone ?? '' }}<br>
                    {{ $sale->customer->email ?? '' }}
                </p>
            </div>
        </div>

        {{-- ITEMS --}}
        <table class="table table-bordered table-sm">
            <thead class="bg-light">
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>

            <tbody>
                @foreach($sale->items as $index => $i)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $i->item->name }}</td>
                    <td>{{ $i->qty }}</td>
                    <td>{{ number_format($i->sale_price,2) }}</td>
                    <td>{{ number_format($i->subtotal,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- TOTAL --}}
        {{-- TOTAL --}}
        <div class="row">

            <div class="col-md-6"></div>

            <div class="col-md-6">

                <table class="table table-bordered">

                    <tr>
                        <th>Total Amount:</th>
                        <td class="text-right">
                            {{ number_format($sale->total, 2) }}
                        </td>
                    </tr>

                    <tr>
                        <th>Paid Amount:</th>
                        <td class="text-right text-success">
                            {{ number_format($sale->total_paid ?? 0, 2) }}
                        </td>
                    </tr>

                    <tr>
                        <th>Balance Due:</th>
                        <td class="text-right text-danger">
                            {{ number_format($sale->balance_amount ?? ($sale->total - ($sale->total_paid ?? 0)), 2) }}
                        </td>
                    </tr>

                </table>

            </div>

        </div>

        <hr>

        {{-- FOOTER --}}
        <div class="text-center">
            <p>Thank you for your business!</p>
        </div>

        {{-- PRINT BUTTON --}}
        <div class="text-right">
            <button onclick="window.print()" class="btn btn-primary">
                Print Invoice
            </button>
        </div>

    </div>

</div>

@endsection