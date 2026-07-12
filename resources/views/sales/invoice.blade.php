@extends('layouts.app')

@section('title','Invoice')

@section('content')

<style>
    @media print {
        .no-print { display: none !important; }
        body { background: #fff; }
        .invoice-box { padding: 0; }
    }

    .invoice-title {
        text-align: center;
        margin-bottom: 10px;
    }

    .company-name {
        font-size: 22px;
        font-weight: bold;
    }

    .table th, .table td {
        font-size: 13px;
        padding: 6px;
    }
</style>

<div class="container invoice-box">

    {{-- ACTIONS --}}
    <div class="no-print mb-3">
        <button onclick="window.print()" class="btn btn-dark btn-sm">
            Print Invoice
        </button>
    </div>

    {{-- COMPANY HEADER --}}
    <div class="invoice-title">

        <div class="company-name">
            MR - ZAM HOLDINGS (PVT) LTD
        </div>

        <div>
            NO 303,NELUMWILA ROAD,DELATHURA,<br>
            JA-ELA, SRI LANKA.
        </div>

    </div>

    <hr>

    {{-- INVOICE INFO --}}
    <div class="row mb-3">

        <div class="col-md-6">
            <strong>Invoice No:</strong> {{ $sale->invoice_id }}<br>
            <strong>Date:</strong> {{ date('d M Y', strtotime($sale->sale_date ?? $sale->created_at)) }}
        </div>

        <div class="col-md-6 text-end">
            <strong>Customer Details:</strong><br>
            {{ $sale->customer->name ?? '-' }}<br>
            {{ $sale->customer->phone ?? '-' }}
        </div>

    </div>

    {{-- ITEMS TABLE --}}
    <table class="table table-bordered">

        <thead>
            <tr>
                <th>#</th>
                <th>Item Code</th>
                <th>Item Name</th>
                <th>Qty</th>
                <th class="text-end">Amount</th>
            </tr>
        </thead>

        <tbody>

            @foreach($sale->items as $key => $item)

            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $item->item->code ?? '-' }}</td>
                <td>{{ $item->item->name ?? '-' }}</td>
                <td>{{ $item->qty }}</td>
                <td class="text-end">
                    {{ number_format($item->subtotal, 2) }}
                </td>
            </tr>

            @endforeach

        </tbody>

    </table>

    {{-- SUMMARY --}}
    <table class="table table-bordered">

        <tr>
            <th class="text-end">Total</th>
            <td class="text-end">{{ number_format($sale->total, 2) }}</td>
        </tr>

        <tr>
            <th class="text-end">Less Balance</th>
            <td class="text-end">{{ number_format($previousOutstanding ?? 0, 2) }}</td>
        </tr>

        <tr>
            <th class="text-end">Grand Total</th>
            <td class="text-end">
                {{ number_format(($sale->total + ($previousOutstanding ?? 0)), 2) }}
            </td>
        </tr>

        <tr>
            <th class="text-end">Total Paid Amount</th>
            <td class="text-end">
                {{ number_format($sale->total_paid ?? 0, 2) }}
            </td>
        </tr>

        <tr>
            <th class="text-end"><strong>Total Payable</strong></th>
            <td class="text-end">
                <strong>
                    {{ number_format(($sale->total + ($previousOutstanding ?? 0)) - ($sale->total_paid ?? 0), 2) }}
                </strong>
            </td>
        </tr>

    </table>

    {{-- FOOTER --}}
    <div class="text-center mt-4">
        <small>Thank you for your business!</small>
    </div>

</div>

@endsection