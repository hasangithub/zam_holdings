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

        /* ---------------------------------------------------------
           COMPANY HEADER
        --------------------------------------------------------- */

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

        .company-address {
            font-size: 11px;
            line-height: 1.6;
            color: #666;
        }

        .invoice-title {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .invoice-meta {
            font-size: 11px;
            color: #555;
            line-height: 1.8;
        }

        .divider {
            border: 0;
            border-top: 1px solid #999;
            margin: 12px 0;
        }


        /* ---------------------------------------------------------
           SECTION TITLE
        --------------------------------------------------------- */

        .section-title {
            background: #343a40;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            padding: 7px 10px;
            margin-top: 15px;
        }


        /* ---------------------------------------------------------
           CUSTOMER DETAILS
        --------------------------------------------------------- */

        .customer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .customer-table td {
            border: 1px solid #dee2e6;
            padding: 7px 9px;
            font-size: 11px;
        }

        .customer-label {
            background: #f8f9fa;
            font-weight: bold;
            width: 18%;
        }


        /* ---------------------------------------------------------
           ITEMS
        --------------------------------------------------------- */

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


        /* ---------------------------------------------------------
           ALIGNMENTS
        --------------------------------------------------------- */

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }


        /* ---------------------------------------------------------
           SUMMARY
        --------------------------------------------------------- */

        .summary-table {
            width: 380px;
            margin-left: auto;
            margin-top: 15px;
            border-collapse: collapse;
        }

        .summary-table td {
            border: 1px solid #dee2e6;
            padding: 8px 10px;
            font-size: 11px;
        }

        .summary-label {
            font-weight: bold;
            background: #f8f9fa;
        }

        .grand-total {
            font-size: 14px;
            font-weight: bold;
            background: #343a40;
            color: #fff;
        }


        /* ---------------------------------------------------------
           SIGNATURES
        --------------------------------------------------------- */

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 65px;
        }

        .signature-table td {
            width: 33.33%;
            text-align: center;
            padding: 0 15px;
        }

        .signature-line {
            border-top: 1px solid #555;
            padding-top: 7px;
            font-size: 10px;
        }


        /* ---------------------------------------------------------
           FOOTER
        --------------------------------------------------------- */

        .footer {
            margin-top: 35px;
            text-align: center;
            font-size: 10px;
            color: #777;
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
                        class="company-logo">

                </td>


                {{-- COMPANY --}}
                <td class="company-cell">

                    <div class="company-name">

                        {{ $companyProfile->title }}

                    </div>


                    @if($companyProfile->address)

                    <div class="company-address">

                        {!! nl2br(e($companyProfile->address)) !!}

                    </div>

                    @endif

                </td>


                {{-- INVOICE --}}
                <td class="invoice-cell">

                    <div class="invoice-title">

                        INVOICE

                    </div>


                    <div class="invoice-meta">

                        <strong>
                            Invoice No:
                        </strong>

                        {{ $sale->invoice_id }}

                        <br>


                        <strong>
                            Date:
                        </strong>

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

        <table class="summary-table">
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


        {{-- =========================================================
         SIGNATURES
    ========================================================== --}}

        <table class="signature-table">

            <tr>

                <td>

                    <div class="signature-line">
                        Prepared By
                    </div>

                </td>


                <td>

                    <div class="signature-line">
                        Authorized Signature
                    </div>

                </td>


                <td>

                    <div class="signature-line">
                        Customer Signature
                    </div>

                </td>

            </tr>

        </table>


        {{-- =========================================================
         FOOTER
    ========================================================== --}}

        <div class="footer">

            Thank you for your business!

        </div>


    </div>

</body>

</html>