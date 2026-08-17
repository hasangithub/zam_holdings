@extends('layouts.app')

@section('title', 'Export Invoice')

@section('content')

<style>

    body {
        background: #f4f6f9;
    }

    .invoice-wrapper {
        max-width: 1000px;
        margin: 20px auto;
        background: #fff;
        padding: 40px;
        box-shadow: 0 2px 10px rgba(0,0,0,.08);
    }

    .company-logo {
        max-height: 75px;
        max-width: 180px;
    }

    .company-name {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: .5px;
    }

    .company-details {
        font-size: 12px;
        line-height: 1.6;
        color: #555;
    }

    .invoice-title {
        font-size: 28px;
        font-weight: 700;
        letter-spacing: 1px;
    }

    .invoice-number {
        font-size: 13px;
        color: #555;
    }

    .section-title {
        background: #343a40;
        color: #fff;
        font-weight: 600;
        padding: 8px 12px;
        font-size: 13px;
    }

    .info-table {
        width: 100%;
        border-collapse: collapse;
    }

    .info-table td {
        padding: 7px 10px;
        border: 1px solid #dee2e6;
        font-size: 13px;
    }

    .info-label {
        font-weight: 600;
        background: #f8f9fa;
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

    .total-table {
        width: 360px;
        margin-left: auto;
        margin-top: 15px;
        border-collapse: collapse;
    }

    .total-table td {
        padding: 9px 12px;
        border: 1px solid #dee2e6;
        font-size: 13px;
    }

    .grand-total {
        font-size: 16px !important;
        font-weight: 700;
        background: #343a40;
        color: #fff;
    }

    .footer-section {
        margin-top: 50px;
        font-size: 12px;
        color: #666;
    }

    .signature {
        margin-top: 60px;
        display: flex;
        justify-content: space-between;
    }

    .signature-box {
        width: 220px;
        text-align: center;
        font-size: 12px;
        border-top: 1px solid #555;
        padding-top: 7px;
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
    {{-- HEADER --}}
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

                <div class="company-details">

                    Address Line<br>
                    Sri Lanka<br>

                    Tel: +94 77XXXXXXX<br>
                    Email: info@company.com

                </div>

            </td>


            {{-- INVOICE --}}
            <td
                style="width:30%; text-align:right; vertical-align:top;">

                <div class="invoice-title">
                    EXPORT INVOICE
                </div>

                <div class="invoice-number">
                    Invoice No:
                    <strong>
                        {{ $sale->invoice_id }}
                    </strong>
                </div>

                <div class="invoice-number">
                    Date:
                    <strong>
                        {{ date(
                            'd M Y',
                            strtotime(
                                $sale->sale_date ?? $sale->created_at
                            )
                        ) }}
                    </strong>
                </div>

            </td>

        </tr>

    </table>


    <hr>


    {{-- ========================================================= --}}
    {{-- EXPORT INFORMATION --}}
    {{-- ========================================================= --}}

    <div class="section-title">
        EXPORT INFORMATION
    </div>

    <table class="info-table">

        <tr>

            <td class="info-label">
                Consignee
            </td>

            <td>
                {{ $sale->customer->consignee_name ?? '-' }}
            </td>

            <td class="info-label">
                Country of Origin
            </td>

            <td>
                {{ $sale->country_of_origin ?? '-' }}
            </td>

        </tr>


        <tr>

            <td class="info-label">
                Port of Loading
            </td>

            <td>
                {{ \App\Models\Sale::PORTOFLOADING[$sale->port_of_loading] ?? '-' }}
            </td>

            <td class="info-label">
                Mode of Payment
            </td>

            <td>
                {{ \App\Models\Sale::MODEOFPAYMENTS[$sale->mode_of_payment] ?? '-' }}
            </td>

        </tr>


        <tr>

            <td class="info-label">
                Mode of Shipping
            </td>

            <td>
                {{ \App\Models\Sale::MODEOFSHIPPING[$sale->mode_of_shipping] ?? '-' }}
            </td>

            <td class="info-label">
                Flight No
            </td>

            <td>
                {{ $sale->flight_no ?? '-' }}
            </td>

        </tr>


        <tr>

            <td class="info-label">
                Airway Bill No
            </td>

            <td colspan="3">
                {{ $sale->airway_no ?? '-' }}
            </td>

        </tr>

    </table>


    {{-- ========================================================= --}}
    {{-- ITEMS --}}
    {{-- ========================================================= --}}

    <table class="items-table">

        <thead>

            <tr>

                <th
                    style="width:5%;"
                    class="text-center">

                    #

                </th>

                <th style="width:15%;">
                    Item Code
                </th>

                <th>
                    Item Description
                </th>

                <th
                    style="width:15%;"
                    class="text-right">

                    Weight / Qty

                </th>

                <th
                    style="width:15%;"
                    class="text-right">

                    Rate (USD)

                </th>

                <th
                    style="width:18%;"
                    class="text-right">

                    Amount (USD)

                </th>

            </tr>

        </thead>


        <tbody>

            @foreach($groupedItems as $key => $row)

                <tr>

                    <td class="text-center">
                        {{ $key + 1 }}
                    </td>

                    <td>
                        {{ $row->item->code ?? '-' }}
                    </td>

                    <td>
                        <strong>
                            {{ $row->item->name ?? '-' }}
                        </strong>
                    </td>

                    <td class="text-right">
                        {{ number_format($row->qty, 3) }}
                    </td>

                    <td class="text-right">

                        {{ number_format(
                            $row->sale_price_foreign,
                            2
                        ) }}

                    </td>

                    <td class="text-right">

                        {{ number_format(
                            $row->sub_total_foreign,
                            2
                        ) }}

                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>


    {{-- ========================================================= --}}
    {{-- TOTAL --}}
    {{-- ========================================================= --}}

    <table class="total-table">

        <tr>

            <td>
                Total Quantity
            </td>

            <td class="text-right">

                {{ number_format(
                    $groupedItems->sum('qty'),
                    3
                ) }}

            </td>

        </tr>


        <tr class="grand-total">

            <td>
                TOTAL USD
            </td>

            <td class="text-right">

                USD
                {{ number_format(
                    $sale->total_foreign ?? 0,
                    2
                ) }}

            </td>

        </tr>

    </table>


    {{-- ========================================================= --}}
    {{-- FOOTER --}}
    {{-- ========================================================= --}}

    <div class="footer-section">

        <strong>Remarks:</strong>

        {{ $sale->remarks ?? '' }}

    </div>


    <div class="signature">

        <div class="signature-box">
            Prepared By
        </div>

        <div class="signature-box">
            Authorized Signature
        </div>

        <div class="signature-box">
            Customer / Consignee
        </div>

    </div>


    <div
        class="text-center"
        style="margin-top:35px; font-size:11px; color:#888;">

        Thank you for your business.

    </div>

</div>

@endsection