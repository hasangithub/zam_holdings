<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>
        Export Invoice {{ $sale->invoice_id }}
    </title>


    <style>

        @page {
            size: A4;
            margin: 12mm;
        }


        body {

            font-family: DejaVu Sans, sans-serif;

            font-size: 12px;

            color: #222;

            margin: 0;

            padding: 0;

        }


        .invoice-wrapper {

            width: 100%;

        }


        /* =========================================================
           HEADER
        ========================================================= */

        .header-table {

            width: 100%;

            border-collapse: collapse;

        }


        .header-table td {

            vertical-align: top;

        }


        .logo-cell {

            width: 20%;

        }


        .company-cell {

            width: 50%;

        }


        .invoice-cell {

            width: 30%;

            text-align: right;

        }


        .company-logo {

            max-height: 75px;

            max-width: 180px;

        }


        .company-name {

            font-size: 18px;

            font-weight: bold;

            letter-spacing: .5px;

            margin-bottom: 5px;

        }


        .company-details {

            font-size: 11px;

            line-height: 1.6;

            color: #555;

        }


        .invoice-title {

            font-size: 18px;

            font-weight: bold;

            letter-spacing: 1px;

        }


        .invoice-number {

            font-size: 11px;

            color: #555;

            line-height: 1.8;

        }


        .divider {

            border: 0;

            border-top: 1px solid #999;

            margin: 12px 0;

        }


        /* =========================================================
           SECTION TITLE
        ========================================================= */

        .section-title {

            background: #343a40;

            color: #fff;

            font-weight: bold;

            padding: 7px 10px;

            font-size: 11px;

            margin-top: 15px;

        }


        /* =========================================================
           EXPORT INFORMATION
        ========================================================= */

        .info-table {

            width: 100%;

            border-collapse: collapse;

        }


        .info-table td {

            padding: 7px 9px;

            border: 1px solid #dee2e6;

            font-size: 11px;

        }


        .info-label {

            font-weight: bold;

            background: #f8f9fa;

            width: 18%;

        }


        /* =========================================================
           ITEMS
        ========================================================= */

        .items-table {

            width: 100%;

            border-collapse: collapse;

            margin-top: 15px;

        }


        .items-table th {

            background: #343a40;

            color: #fff;

            padding: 7px 6px;

            font-size: 10px;

            border: 1px solid #343a40;

        }


        .items-table td {

            padding: 7px 6px;

            font-size: 10px;

            border: 1px solid #dee2e6;

        }


        .items-table tr:nth-child(even) {

            background: #f8f9fa;

        }


        /* =========================================================
           ALIGNMENT
        ========================================================= */

        .text-right {

            text-align: right;

        }


        .text-center {

            text-align: center;

        }


        /* =========================================================
           TOTAL
        ========================================================= */

        .total-table {

            width: 360px;

            margin-left: auto;

            margin-top: 15px;

            border-collapse: collapse;

        }


        .total-table td {

            padding: 8px 10px;

            border: 1px solid #dee2e6;

            font-size: 11px;

        }


        .grand-total {

            font-size: 14px;

            font-weight: bold;

            background: #343a40;

            color: #fff;

        }


        /* =========================================================
           REMARKS
        ========================================================= */

        .footer-section {

            margin-top: 35px;

            font-size: 11px;

            color: #666;

        }


        /* =========================================================
           SIGNATURES
        ========================================================= */

        .signature-table {

            width: 100%;

            border-collapse: collapse;

            margin-top: 60px;

        }


        .signature-table td {

            width: 33.33%;

            text-align: center;

            padding: 0 15px;

        }


        .signature-box {

            border-top: 1px solid #555;

            padding-top: 7px;

            font-size: 10px;

        }


        /* =========================================================
           FOOTER
        ========================================================= */

        .thank-you {

            margin-top: 35px;

            text-align: center;

            font-size: 10px;

            color: #888;

        }

    </style>

</head>


<body>


<div class="invoice-wrapper">


    {{-- =========================================================
         COMPANY HEADER
    ========================================================== --}}

    <table class="header-table">

        <tr>


            {{-- LOGO --}}

            <td class="logo-cell">

                <img
                    src="{{ public_path('logo.png') }}"
                    class="company-logo"
                >

            </td>


            {{-- COMPANY --}}

            <td class="company-cell">

                <div class="company-name">

                    {{ $companyProfile->title ?? 'MR - ZAM HOLDINGS (PVT) LTD' }}

                </div>


                @if($companyProfile->address)

                    <div class="company-details">

                        {!! nl2br(
                            e($companyProfile->address)
                        ) !!}

                    </div>

                @endif

            </td>


            {{-- INVOICE --}}

            <td class="invoice-cell">

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
                                $sale->sale_date
                                ?? $sale->created_at
                            )
                        ) }}

                    </strong>

                </div>

            </td>

        </tr>

    </table>


    <hr class="divider">


    {{-- =========================================================
         EXPORT INFORMATION
    ========================================================== --}}

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

                {{ \App\Models\Sale::PORTOFLOADING[
                    $sale->port_of_loading
                ] ?? '-' }}

            </td>


            <td class="info-label">
                Mode of Payment
            </td>

            <td>

                {{ \App\Models\Sale::MODEOFPAYMENTS[
                    $sale->mode_of_payment
                ] ?? '-' }}

            </td>

        </tr>


        <tr>

            <td class="info-label">
                Mode of Shipping
            </td>

            <td>

                {{ \App\Models\Sale::MODEOFSHIPPING[
                    $sale->mode_of_shipping
                ] ?? '-' }}

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


    {{-- =========================================================
         ITEMS
    ========================================================== --}}

    <table class="items-table">

        <thead>

            <tr>

                <th
                    style="width:5%;"
                    class="text-center"
                >
                    #
                </th>


                <th style="width:15%;">

                    Item Code

                </th>


                <th style="width:27%;">

                    Item Description

                </th>


                <th
                    style="width:15%;"
                    class="text-right"
                >

                    Weight / Qty

                </th>


                <th
                    style="width:18%;"
                    class="text-right"
                >

                    Rate (USD)

                </th>


                <th
                    style="width:20%;"
                    class="text-right"
                >

                    Amount (USD)

                </th>

            </tr>

        </thead>


        <tbody>


            @forelse($groupedItems as $key => $row)

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

                        {{ number_format(
                            $row->qty,
                            3
                        ) }}

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


            @empty

                <tr>

                    <td
                        colspan="6"
                        class="text-center"
                    >

                        No items found.

                    </td>

                </tr>

            @endforelse


        </tbody>

    </table>


    {{-- =========================================================
         TOTAL
    ========================================================== --}}

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


        <tr>

            <td class="grand-total">

                TOTAL USD

            </td>


            <td class="grand-total text-right">

                USD

                {{ number_format(
                    $sale->total_foreign ?? 0,
                    2
                ) }}

            </td>

        </tr>


    </table>


    {{-- =========================================================
         REMARKS
    ========================================================== --}}

    <div class="footer-section">

        <strong>
            Remarks:
        </strong>

        {{ $sale->remarks ?? '' }}

    </div>


    {{-- =========================================================
         SIGNATURES
    ========================================================== --}}

    <table class="signature-table">

        <tr>

            <td>

                <div class="signature-box">

                    Prepared By

                </div>

            </td>


            <td>

                <div class="signature-box">

                    Authorized Signature

                </div>

            </td>


            <td>

                <div class="signature-box">

                    Customer / Consignee

                </div>

            </td>

        </tr>

    </table>


    {{-- =========================================================
         FOOTER
    ========================================================== --}}

    <div class="thank-you">

        Thank you for your business.

    </div>


</div>

</body>

</html>