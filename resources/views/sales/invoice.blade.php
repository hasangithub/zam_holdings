@extends('layouts.app')

@section('title','Invoice')

@section('content')

<style>
    body {
        background: #f4f6f9;
    }

    .invoice-wrapper {
        max-width: 950px;
        margin: 20px auto;
        background: #fff;
        padding: 40px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
    }

    .company-logo {
        max-height: 75px;
        max-width: 180px;
    }

    .company-name {
        font-size: 25px;
        font-weight: 700;
        letter-spacing: .5px;
    }

    .company-address {
        font-size: 12px;
        line-height: 1.6;
        color: #666;
    }

    .invoice-title {
        font-size: 28px;
        font-weight: 700;
        letter-spacing: 1px;
    }

    .invoice-meta {
        font-size: 13px;
        color: #555;
        line-height: 1.8;
    }

    .section-title {
        background: #343a40;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        padding: 8px 12px;
        margin-top: 20px;
    }

    .customer-table {
        width: 100%;
        border-collapse: collapse;
    }

    .customer-table td {
        border: 1px solid #dee2e6;
        padding: 8px 10px;
        font-size: 13px;
    }

    .customer-label {
        background: #f8f9fa;
        font-weight: 600;
        width: 18%;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .items-table th {
        background: #343a40;
        color: #fff;
        padding: 9px 8px;
        font-size: 12px;
        border: 1px solid #343a40;
    }

    .items-table td {
        padding: 8px;
        font-size: 12px;
        border: 1px solid #dee2e6;
    }

    .items-table tbody tr:nth-child(even) {
        background: #f8f9fa;
    }

    .text-right {
        text-align: right !important;
    }

    .text-center {
        text-align: center !important;
    }

    .summary-table {
        width: 380px;
        margin-left: auto;
        margin-top: 18px;
        border-collapse: collapse;
    }

    .summary-table td {
        border: 1px solid #dee2e6;
        padding: 9px 12px;
        font-size: 13px;
    }

    .summary-label {
        font-weight: 600;
        background: #f8f9fa;
    }

    .grand-total {
        font-size: 16px !important;
        font-weight: 700;
        background: #343a40;
        color: #fff;
    }

    .footer {
        margin-top: 50px;
        font-size: 12px;
        color: #777;
    }

    .signature-row {
        display: flex;
        justify-content: space-between;
        margin-top: 70px;
    }

    .signature-box {
        width: 220px;
        text-align: center;
        border-top: 1px solid #555;
        padding-top: 7px;
        font-size: 12px;
    }

    @media print {

        body {
            background: #fff !important;
        }

        .no-print {
            display: none !important;
        }

        .invoice-wrapper {
            margin: 0;
            padding: 20px;
            max-width: none;
            box-shadow: none;
        }

        @page {
            size: A4;
            margin: 12mm;
        }
    }
</style>


<div class="no-print mb-3">

    <button
        type="button"
        onclick="window.print()"
        class="btn btn-dark btn-sm">

        <i class="fas fa-print"></i>
        Print Invoice

    </button>

</div>


<div class="invoice-wrapper">


    {{-- ========================================================= --}}
    {{-- COMPANY HEADER --}}
    {{-- ========================================================= --}}

    <table style="width:100%; border-collapse:collapse;">

        <tr>

            {{-- LOGO --}}
            <td style="width:20%; vertical-align:top;">

                <img
                    src="{{ asset('logo.png') }}"
                    class="company-logo">

            </td>


            {{-- COMPANY --}}
            <td style="width:50%; vertical-align:top;">

                <div class="company-name">
                    MR - ZAM HOLDINGS (PVT) LTD
                </div>

                <div class="company-address">

                    NO 303, NELUMWILA ROAD, DELATHURA,<br>
                    JA-ELA, SRI LANKA.<br>

                    Tel: +94 77XXXXXXX<br>
                    Email: info@company.com

                </div>

            </td>


            {{-- INVOICE --}}
            <td
                style="width:30%; text-align:right; vertical-align:top;">

                <div class="invoice-title">
                    INVOICE
                </div>

                <div class="invoice-meta">

                    <strong>Invoice No:</strong>
                    {{ $sale->invoice_id }}

                    <br>

                    <strong>Date:</strong>

                    {{ date(
                        'd M Y',
                        strtotime(
                            $sale->sale_date ?? $sale->created_at
                        )
                    ) }}

                </div>

            </td>

        </tr>

    </table>


    <hr>


    {{-- ========================================================= --}}
    {{-- CUSTOMER --}}
    {{-- ========================================================= --}}

    <div class="section-title">
        CUSTOMER DETAILS
    </div>


    <table class="customer-table">

        <tr>

            <td class="customer-label">
                Customer
            </td>

            <td>
                {{ $sale->customer->name ?? '-' }}
            </td>

            <td class="customer-label">
                Phone
            </td>

            <td>
                {{ $sale->customer->phone ?? '-' }}
            </td>

        </tr>


        @if(!empty($sale->customer->address))

        <tr>

            <td class="customer-label">
                Address
            </td>

            <td colspan="3">
                {{ $sale->customer->address }}
            </td>

        </tr>

        @endif

    </table>


    {{-- ========================================================= --}}
    {{-- ITEMS --}}
    {{-- ========================================================= --}}

    <table class="items-table">

        <thead>

            <tr>

                <th width="5%" class="text-center">
                    #
                </th>

                <th width="15%">
                    Item Code
                </th>

                <th>
                    Item Name
                </th>

                <th width="14%" class="text-right">
                    Qty
                </th>

                <th width="17%" class="text-right">
                    Rate
                </th>

                <th width="20%" class="text-right">
                    Amount
                </th>

            </tr>

        </thead>


        <tbody>

            @forelse($groupedItems as $key => $item)

            <tr>

                <td class="text-center">
                    {{ $key + 1 }}
                </td>

                <td>
                    {{ $item->item->item_code ?? '-' }}
                </td>

                <td>

                    <strong>
                        {{ $item->item->name ?? '-' }}
                    </strong>

                </td>

                <td class="text-right">

                    {{ number_format(
                            $item->qty,
                            2
                        ) }}

                </td>

                <td class="text-right">

                    {{ number_format(
                            $item->sale_price,
                            2
                        ) }}

                </td>

                <td class="text-right">

                    {{ number_format(
                            $item->subtotal,
                            2
                        ) }}

                </td>

            </tr>

            @empty

            <tr>

                <td
                    colspan="6"
                    class="text-center text-muted">

                    No items found.

                </td>

            </tr>

            @endforelse

        </tbody>

    </table>


    {{-- ========================================================= --}}
    {{-- SUMMARY --}}
    {{-- ========================================================= --}}

    <div class="invoice-summary">

        <div class="d-flex justify-content-between">
            <span>Previous Outstanding</span>
            <strong>
                {{ number_format($previousOutstanding, 2) }}
            </strong>
        </div>

        <div class="d-flex justify-content-between">
            <span>Current Invoice</span>
            <strong>
                {{ number_format($currentInvoice, 2) }}
            </strong>
        </div>

        <hr>

        <div class="d-flex justify-content-between">
            <strong>Total Payable</strong>
            <strong>
                {{ number_format($totalPayable, 2) }}
            </strong>
        </div>

        <div class="d-flex justify-content-between text-success">
            <span>Payment Received</span>
            <strong>
                {{ number_format($currentPayment, 2) }}
            </strong>
        </div>

        <div class="d-flex justify-content-between">
            <strong>Balance Due</strong>
            <strong>
                {{ number_format($previousOutstanding + $currentBalance, 2) }}
            </strong>
        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- SIGNATURES --}}
    {{-- ========================================================= --}}

    <div class="signature-row">

        <div class="signature-box">
            Prepared By
        </div>

        <div class="signature-box">
            Authorized Signature
        </div>

        <div class="signature-box">
            Customer Signature
        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- FOOTER --}}
    {{-- ========================================================= --}}

    <div class="footer text-center">

        Thank you for your business!

    </div>


</div>

@endsection