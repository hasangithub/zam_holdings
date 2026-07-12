@extends('layouts.app')

@section('title','Export Invoice')

@section('content')

<style>
    @media print {
        .no-print { display: none !important; }
        body { background: #fff; }
    }

    .header-table {
        width: 100%;
    }

    .company-title {
        text-align: center;
        font-size: 22px;
        font-weight: bold;
    }

    .sub-text {
        text-align: center;
        font-size: 13px;
    }

    .right-info {
        text-align: right;
        font-size: 13px;
    }

    .table th, .table td {
        font-size: 13px;
        padding: 6px;
    }
</style>

<div class="container">

    {{-- PRINT BUTTON --}}
    <div class="no-print mb-3">
        <button onclick="window.print()" class="btn btn-dark btn-sm">
            Print
        </button>
    </div>

    {{-- HEADER --}}
    <table class="header-table">
        <tr>

            {{-- LEFT LOGO --}}
            <td width="20%">
                <img src="{{ asset('logo.png') }}" style="height:70px;">
            </td>

            {{-- CENTER COMPANY --}}
            <td width="50%">
                <div class="company-title">
                    MR - ZAM HOLDINGS (PVT) LTD
                </div>
                <div class="sub-text">
                    Address Line (Single Line Only)
                </div>
            </td>

            {{-- RIGHT CONTACT --}}
            <td width="30%" class="right-info">
                Tel: +94 77XXXXXXX <br>
                Email: info@company.com <br>
                Web: www.company.com
            </td>

        </tr>
    </table>

    <hr>

    {{-- EXPORT INFO --}}
    <table class="table table-bordered mb-3">

        <tr>
            <th>Invoice No</th>
            <td>{{ $sale->invoice_id }}</td>

            <th>Date</th>
            <td>{{ date('d M Y', strtotime($sale->sale_date ?? $sale->created_at)) }}</td>
        </tr>

        <tr>
            <th>Consignee</th>
            <td>{{ $sale->customer->consignee_name ?? '-' }}</td>

            <th>Country of Origin</th>
            <td>{{ $sale->country_of_origin ?? '-' }}</td>
        </tr>

        <tr>
            <th>Port of Loading</th>
            <td>{{ \App\Models\Sale::PORTOFLOADING[$sale->port_of_loading] ?? '-' }}</td>

            <th>Mode of Payment</th>
            <td>{{ \App\Models\Sale::MODEOFPAYMENTS[$sale->mode_of_payment] ?? '-' }}</td>
        </tr>

        <tr>
            <th>Mode of Shipping</th>
            <td>{{ \App\Models\Sale::MODEOFSHIPPING[$sale->mode_of_shipping] ?? '-' }}</td>

            <th>Flight No</th>
            <td>{{ $sale->flight_no ?? '-' }}</td>
        </tr>

        <tr>
            <th>Airway Bill No</th>
            <td colspan="3">{{ $sale->airway_no ?? '-' }}</td>
        </tr>

    </table>

    {{-- ITEMS TABLE --}}
    <table class="table table-bordered">

        <thead>
            <tr>
                <th>#</th>
                <th>Item Code</th>
                <th>Item Name</th>
                <th>Weight (Qty)</th>
                <th class="text-end">Rate (USD)</th>
                <th class="text-end">Amount (USD)</th>
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
                    {{ number_format($item->sale_price_foreign ?? 0, 2) }}
                </td>
                <td class="text-end">
                    {{ number_format($item->sub_total_foreign ?? 0, 2) }}
                </td>
            </tr>

            @endforeach

        </tbody>

    </table>

    {{-- TOTAL --}}
    <table class="table table-bordered">

        <tr>
            <th class="text-end">Total (USD)</th>
            <td class="text-end">
                {{ number_format($sale->total_foreign ?? 0, 2) }}
            </td>
        </tr>

    </table>

    {{-- FOOTER --}}
    <div class="text-center mt-4">
        <small></small>
    </div>

</div>

@endsection