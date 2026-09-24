<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        Purchase Invoice - {{ $purchase->invoice_no ?? $purchase->id }}
    </title>

    <style>

        /* =========================================================
           A4
        ========================================================= */

        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #222;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9px;
            line-height: 1.15;
        }


        /* =========================================================
           SAFE A4 CONTENT WIDTH
           
           A4 = 210mm
           15mm left + 15mm right
           = 180mm usable width
        ========================================================= */

        .invoice {
            width: 180mm;
            margin: 0 auto;
            padding: 0;
        }


        /* =========================================================
           HEADER
        ========================================================= */

        .company-header {
            width: 180mm;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .company-name {
            font-size: 18px;
            line-height: 20px;
            font-weight: bold;
            margin: 0;
            padding: 0;
        }

        .company-address {
            font-size: 8px;
            line-height: 10px;
            color: #666;
            margin: 2px 0 0 0;
            padding: 0;
        }

        .header-line {
            width: 180mm;
            border: 0;
            border-top: 1px solid #999;
            margin: 6px 0 7px 0;
            padding: 0;
        }


        /* =========================================================
           INVOICE INFORMATION
        ========================================================= */

        .info-table {
            width: 180mm;
            table-layout: fixed;
            border-collapse: collapse;
            margin: 0 0 7px 0;
            padding: 0;
        }

        .info-table td {
            padding: 0;
            margin: 0;
            vertical-align: top;
        }

        .info-left {
            width: 90mm;
            text-align: left;
            padding-right: 8px !important;
        }

        .info-right {
            width: 90mm;
            text-align: right;
            padding-left: 8px !important;
        }

        .label {
            font-size: 7px;
            line-height: 8px;
            color: #777;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
        }

        .value {
            font-size: 9px;
            line-height: 11px;
            font-weight: bold;
            margin: 0;
            padding: 0;
            word-wrap: break-word;
        }

        .date-section {
            margin-top: 3px;
        }

        .supplier-phone {
            font-size: 8px;
            line-height: 9px;
            color: #666;
            margin-top: 1px;
        }


        /* =========================================================
           ITEMS TABLE
        ========================================================= */

        .items-table {
            width: 180mm;
            table-layout: fixed;
            border-collapse: collapse;
            margin: 0;
            padding: 0;
        }

        .items-table thead {
            display: table-header-group;
        }

        .items-table tr {
            page-break-inside: avoid;
        }

        .items-table th {
            background: #eeeeee;
            color: #222;
            border: 1px solid #999;
            padding: 4px 3px;
            font-size: 8px;
            line-height: 9px;
            font-weight: bold;
            vertical-align: middle;
        }

        .items-table td {
            border: 1px solid #ccc;
            padding: 3px 3px;
            font-size: 8px;
            line-height: 10px;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }


        /* =========================================================
           ALIGNMENT
        ========================================================= */

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }


        /* =========================================================
           SUMMARY
        ========================================================= */

        .summary-wrapper {
            width: 180mm;
            margin: 5px 0 0 0;
            padding: 0;
            text-align: right;
        }

        .summary-table {
            width: 70mm;
            margin: 0 0 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .summary-table th,
        .summary-table td {
            border: 1px solid #999;
            padding: 4px 5px;
            font-size: 9px;
            line-height: 10px;
        }

        .summary-table th {
            width: 38mm;
            background: #eeeeee;
            font-weight: bold;
        }

        .summary-table td {
            width: 32mm;
        }

        .grand-total {
            font-size: 10px !important;
            font-weight: bold;
        }


        /* =========================================================
           FOOTER
        ========================================================= */

        .footer {
            width: 180mm;
            text-align: center;
            margin-top: 12px;
            padding: 0;
            color: #777;
            font-size: 7px;
            line-height: 8px;
        }

        .footer div {
            margin: 0;
            padding: 0;
        }

    </style>

</head>


<body>

<div class="invoice">


    {{-- =========================================================
         COMPANY
    ========================================================== --}}

    <div class="company-header">

        <div class="company-name">
            MR - ZAM HOLDINGS (PVT) LTD
        </div>

        <div class="company-address">
            NO 303, NELUMWILA ROAD, DELATHURA,<br>
            JA-ELA, SRI LANKA
        </div>

    </div>


    <hr class="header-line">


    {{-- =========================================================
         INVOICE / SUPPLIER
    ========================================================== --}}

    <table class="info-table">

        <tr>

            <td class="info-left">

                <div class="label">
                    Purchase Invoice
                </div>

                <div class="value">
                    {{ $purchase->invoice_no ?? $purchase->id }}
                </div>

                <div class="date-section">

                    <div class="label">
                        Date
                    </div>

                    <div class="value">

                        {{ date(
                            'd M Y',
                            strtotime(
                                $purchase->purchase_date ?? $purchase->created_at
                            )
                        ) }}

                    </div>

                </div>

            </td>


            <td class="info-right">

                <div class="label">
                    Supplier
                </div>

                <div class="value">
                    {{ $purchase->supplier->name ?? '-' }}
                </div>

                @if(!empty($purchase->supplier->phone))

                    <div class="supplier-phone">
                        {{ $purchase->supplier->phone }}
                    </div>

                @endif

            </td>

        </tr>

    </table>


    {{-- =========================================================
         ITEMS
    ========================================================== --}}

    <table class="items-table">

        <thead>

        <tr>

            @if($showRate)

                {{--

                    TOTAL = 180mm

                    8 + 27 + 67 + 18 + 27 + 33
                    = 180mm

                --}}

                <th
                    style="width:8mm;"
                    class="text-center"
                >
                    #
                </th>

                <th
                    style="width:27mm;"
                    class="text-left"
                >
                    Item Code
                </th>

                <th
                    style="width:67mm;"
                    class="text-left"
                >
                    Item Name
                </th>

                <th
                    style="width:18mm;"
                    class="text-right"
                >
                    Qty
                </th>

                <th
                    style="width:27mm;"
                    class="text-right"
                >
                    Rate
                </th>

                <th
                    style="width:33mm;"
                    class="text-right"
                >
                    Amount
                </th>

            @else

                {{--

                    TOTAL = 180mm

                    8 + 30 + 97 + 18 + 27
                    = 180mm

                --}}

                <th
                    style="width:8mm;"
                    class="text-center"
                >
                    #
                </th>

                <th
                    style="width:30mm;"
                    class="text-left"
                >
                    Item Code
                </th>

                <th
                    style="width:97mm;"
                    class="text-left"
                >
                    Item Name
                </th>

                <th
                    style="width:18mm;"
                    class="text-right"
                >
                    Qty
                </th>

                <th
                    style="width:27mm;"
                    class="text-right"
                >
                    Amount
                </th>

            @endif

        </tr>

        </thead>


        <tbody>

        @foreach($purchase->items as $key => $item)

            <tr>

                <td class="text-center">
                    {{ $key + 1 }}
                </td>

                <td class="text-left">
                    {{ $item->item->item_code ?? '-' }}
                </td>

                <td class="text-left">
                    {{ $item->item->name ?? '-' }}
                </td>

                <td class="text-right">
                    {{ number_format($item->qty, 2) }}
                </td>

                @if($showRate)

                    <td class="text-right">
                        {{ number_format($item->price, 2) }}
                    </td>

                @endif

                <td class="text-right">
                    {{ number_format($item->subtotal, 2) }}
                </td>

            </tr>

        @endforeach

        </tbody>

    </table>


    {{-- =========================================================
         GRAND TOTAL
    ========================================================== --}}

    <div class="summary-wrapper">

        <table class="summary-table">

            <tr>

                <th class="text-right grand-total">
                    Grand Total
                </th>

                <td class="text-right grand-total">
                    {{ number_format($currentPurchase, 2) }}
                </td>

            </tr>

        </table>

    </div>


    {{-- =========================================================
         FOOTER
    ========================================================== --}}

    <div class="footer">

        <div>
            Thank you for your business!
        </div>

        <div>
            This is a computer generated invoice.
        </div>

    </div>


</div>

</body>

</html>