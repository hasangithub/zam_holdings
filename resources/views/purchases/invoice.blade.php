@extends('layouts.app')

@section('title','Purchase Invoice')

@section('content')

<style>
    @media print {
        .no-print { display: none !important; }
        body { background: #fff; }
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

    {{-- PRINT --}}
    <div class="no-print mb-3">
        <button onclick="window.print()" class="btn btn-dark btn-sm">
            Print Invoice
        </button>
    </div>

    {{-- HEADER --}}
    <div class="invoice-title">

        <div class="company-name">
            MR - ZAM HOLDINGS (PVT) LTD
        </div>

        <div>
            NO 303, NELUMWILA ROAD, DELATHURA,<br>
            JA-ELA, SRI LANKA
        </div>

    </div>

    <hr>

    {{-- PURCHASE INFO --}}
    <div class="row mb-3">

        <div class="col-md-6">
            <strong>Purchase No:</strong> {{ $purchase->invoice_no ?? $purchase->id }}<br>
            <strong>Date:</strong> {{ date('d M Y', strtotime($purchase->purchase_date ?? $purchase->created_at)) }}
        </div>

        <div class="col-md-6 text-end">
            <strong>Supplier:</strong><br>
            {{ $purchase->supplier->name ?? '-' }}<br>
            {{ $purchase->supplier->phone ?? '-' }}
        </div>

    </div>

    {{-- ITEMS --}}
    <table class="table table-bordered">

        <thead>
            <tr>
                <th>#</th>
                <th>Item Code</th>
                <th>Item Name</th>
                <th>Qty</th>
                <th class="text-end">Rate</th>
                <th class="text-end">Amount</th>
            </tr>
        </thead>

        <tbody>

            @foreach($purchase->items as $key => $item)

            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $item->item->code ?? '-' }}</td>
                <td>{{ $item->item->name ?? '-' }}</td>
                <td>{{ $item->qty }}</td>
                <td class="text-end">{{ number_format($item->price, 2) }}</td>
                <td class="text-end">{{ number_format($item->subtotal, 2) }}</td>
            </tr>

            @endforeach

        </tbody>

    </table>

    {{-- FINANCIAL SUMMARY --}}
    <table class="table table-bordered">

        <tr>
            <th class="text-end">Purchase Total</th>
            <td class="text-end">{{ number_format($purchase->total, 2) }}</td>
        </tr>

        <tr>
            <th class="text-end">Supplier Paid</th>
            <td class="text-end">{{ number_format($totalPaid ?? 0, 2) }}</td>
        </tr>

        <tr>
            <th class="text-end"><strong>Outstanding Payable</strong></th>
            <td class="text-end">
                <strong>
                    {{ number_format($purchase->total - ($totalPaid ?? 0), 2) }}
                </strong>
            </td>
        </tr>

        {{-- OPTIONAL ERP FEATURE --}}
        <tr>
            <th class="text-end">As Of Payable (Supplier Ledger)</th>
            <td class="text-end">
                {{ number_format($asOfPayable ?? ($purchase->total - ($totalPaid ?? 0)), 2) }}
            </td>
        </tr>

    </table>

    {{-- FOOTER --}}
    <div class="text-center mt-4">
        <small>Thank you for your business!</small>
    </div>

</div>

@endsection