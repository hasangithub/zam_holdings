<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>
        Invoice {{ $sale->invoice_id }}
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
            table-layout: fixed;
        }

        .header-table td {
            padding: 3px 5px;
            vertical-align: middle;
        }

        .logo-cell {
            width: 20%;
            text-align: left;
            vertical-align: top !important;
            padding: 2px 10px 8px 0 !important;
        }

        .company-cell {
            width: 60%;
            text-align: center;
            vertical-align: top !important;
            padding: 2px 4px 8px !important;
        }

        .header-spacer-cell {
            width: 20%;
        }

        .company-logo {
            display: block;
            max-width: 145px;
            max-height: 78px;
            width: auto;
            height: auto;
        }

        .company-name {
            font-size: 17px;
            font-weight: bold;
            letter-spacing: .25px;
            line-height: 1.3;
            margin: 0 0 4px 0;
            text-align: center;
        }

        .company-details {
            font-size: 9.5px;
            line-height: 1.4;
            color: #444;
            text-align: center;
        }

        .company-subtitle-1 {
            font-size: 9.5px;
            line-height: 1.4;
            color: #333;
            text-align: center;
            font-weight: 600;
        }

        .company-subtitle-2 {
            font-size: 9.5px;
            line-height: 1.4;
            color: #444;
            text-align: center;
        }

        .invoice-cell {
            text-align: right;
            vertical-align: middle;
        }

        .invoice-details-row {
            padding: 8px 5px 9px !important;
            text-align: right;
            border-top: 1px solid #b8b8b8;
            border-bottom: 1px solid #b8b8b8;
        }

        .invoice-details-line {
            width: 100%;
            text-align: right;
            font-size: 10.5px;
            line-height: 1.6;
        }

        .invoice-title {
            display: block;
            margin: 0 0 3px 0;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: .8px;
            color: #222;
            text-align: right;
        }

        .invoice-number {
            display: block;
            margin: 0;
            font-size: 10.5px;
            line-height: 1.6;
            color: #444;
            text-align: right;
            white-space: nowrap;
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
            <colgroup>
                <col style="width: 20%;">
                <col style="width: 60%;">
                <col style="width: 20%;">
            </colgroup>
            <tr>
                <td class="logo-cell">
                    @php
                    $invoiceLogo = collect(['png', 'jpg', 'jpeg'])
                    ->map(fn ($ext) => public_path("images/invoice/local-sales/logo.{$ext}"))
                    ->first(fn ($path) => file_exists($path));
                    @endphp

                    @if ($invoiceLogo)
                    <img src="{{ public_path('images/invoice/local-sales/' . basename($invoiceLogo)) }}"
                        alt="Invoice Logo"
                        class="company-logo">
                    @endif
                </td>

                <td class="company-cell">
                    <div class="company-name">
                        {{ $companyProfile->title ?? 'MR - ZAM HOLDINGS (PVT) LTD' }}
                    </div>

                    @if($companyProfile->address)
                    <div class="company-details">
                        {!! nl2br(e($companyProfile->address)) !!}
                    </div>
                    @endif

                    @if(!empty($companyProfile->sub_title_1))
                    <div class="company-subtitle-1">
                        {{ $companyProfile->sub_title_1 }}
                    </div>
                    @endif

                    @if(!empty($companyProfile->sub_title_2))
                    <div class="company-subtitle-2">
                        {{ $companyProfile->sub_title_2 }}
                    </div>
                    @endif
                </td>

                <td class="header-spacer-cell"></td>
            </tr>

            <tr>
                <td colspan="3" class="invoice-cell invoice-details-row">
                    <div class="invoice-details-line">
                        <div class="invoice-title"></div>
                        <div class="invoice-number">Invoice#: <strong>{{ $sale->invoice_id }}</strong></div>
                        <div class="invoice-number">Date: <strong>{{ date('d M Y', strtotime($sale->sale_date ?? $sale->created_at)) }}</strong></div>
                    </div>
                </td>
            </tr>
        </table>


        <hr class="divider">


        {{-- =========================================================
         CUSTOMER DETAILS
    ========================================================== --}}

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

                    {!! nl2br(
                    e($sale->customer->address)
                    ) !!}

                </td>

            </tr>

            @endif

        </table>


        {{-- =========================================================
         BILL DETAILS / ITEMS
    ========================================================== --}}

        <div class="section-title">

            BILL DETAILS

        </div>


        <table class="items-table">

            <thead>

                <tr>

                    <th width="5%" class="text-center">
                        #
                    </th>

                    <th width="15%">
                        Item Code
                    </th>

                    <th width="29%">
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
                        class="text-center">

                        No items found.

                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>


        {{-- =========================================================
         BILL SUMMARY
    ========================================================== --}}

        <table class="total-table">
            <tr>

                <td class="grand-total">
                    GRAND TOTAL
                </td>

                <td class="grand-total text-right">

                    {{ number_format(
                    $currentInvoice,
                    2
                ) }}

                </td>

            </tr>

        </table>
    </div>

</body>

</html>