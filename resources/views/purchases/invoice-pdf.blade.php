html
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>Purchase Invoice {{ $purchase->invoice_no ?? $purchase->id }}</title>

    <style>
        @page {
            size: A4;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }

        .invoice-wrapper {
            width: 100%;
        }

        /* HEADER */

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
            margin: 0 0 4px;
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
            margin: 0 0 3px;
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

        /* SECTION HEADINGS */

        .section-title {
            background: #343a40;
            color: #fff;
            font-weight: bold;
            padding: 7px 10px;
            font-size: 11px;
            margin-top: 14px;
        }

        /* SUPPLIER DETAILS */

        .supplier-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .supplier-table td {
            padding: 7px 9px;
            border: 1px solid #dee2e6;
            font-size: 10.5px;
            vertical-align: top;
        }

        .supplier-label {
            width: 18%;
            font-weight: bold;
            background: #f8f9fa;
        }

        .supplier-value {
            width: 32%;
            overflow-wrap: break-word;
        }

        /* ITEMS */

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            table-layout: fixed;
        }

        .items-table thead {
            display: table-header-group;
        }

        .items-table tr {
            page-break-inside: avoid;
        }

        .items-table th {
            background: #343a40;
            color: #fff;
            padding: 7px 5px;
            font-size: 9.5px;
            font-weight: bold;
            border: 1px solid #343a40;
            vertical-align: middle;
        }

        .items-table td {
            padding: 7px 5px;
            font-size: 9.5px;
            line-height: 1.4;
            border: 1px solid #dee2e6;
            vertical-align: middle;
            overflow-wrap: break-word;
        }

        .items-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        /* ALIGNMENT */

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* GRAND TOTAL */

        .total-table {
            width: 300px;
            margin-left: auto;
            margin-top: 15px;
            border-collapse: collapse;
        }

        .total-table th,
        .total-table td {
            padding: 9px 10px;
            border: 1px solid #dee2e6;
            font-size: 11px;
        }

        .total-table th {
            width: 58%;
        }

        .grand-total {
            font-size: 13px !important;
            font-weight: bold;
            background: #343a40;
            color: #fff;
            border-color: #343a40 !important;
        }

        /* FOOTER */

        .thank-you {
            margin-top: 28px;
            padding-top: 8px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 9px;
            line-height: 1.6;
            color: #888;
        }
    </style>
</head>

<body>

<div class="invoice-wrapper">

    {{-- COMPANY HEADER --}}

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
                        ->map(fn ($ext) => public_path("images/invoice/purchase/logo.{$ext}"))
                        ->first(fn ($path) => file_exists($path));
                @endphp

                @if ($invoiceLogo)
                    <img
                        src="{{ public_path('images/invoice/purchase/' . basename($invoiceLogo)) }}"
                        alt="Invoice Logo"
                        class="company-logo">
                @endif
            </td>

            <td class="company-cell">
                <div class="company-name">
                    {{ $companyProfile->title ?? 'MR - ZAM HOLDINGS (PVT) LTD' }}
                </div>

                @if(!empty($companyProfile->address))
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
                    <div class="invoice-number">
                        Invoice#:
                        <strong>{{ $purchase->invoice_no ?? $purchase->id }}</strong>
                    </div>

                    <div class="invoice-number">
                        Date:
                        <strong>
                            {{ date('d M Y', strtotime($purchase->purchase_date ?? $purchase->created_at)) }}
                        </strong>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- SUPPLIER DETAILS --}}

    <div class="section-title">
        SUPPLIER DETAILS
    </div>

    <table class="supplier-table">
        <tr>
            <td class="supplier-label">Supplier</td>

            <td class="supplier-value">
                {{ $purchase->supplier->name ?? '-' }}
            </td>

            <td class="supplier-label">Phone</td>

            <td class="supplier-value">
                {{ $purchase->supplier->phone ?? '-' }}
            </td>
        </tr>

        @if(!empty($purchase->supplier->address))
            <tr>
                <td class="supplier-label">Address</td>

                <td colspan="3" class="supplier-value">
                    {!! nl2br(e($purchase->supplier->address)) !!}
                </td>
            </tr>
        @endif
    </table>

    {{-- BILL DETAILS --}}

    <div class="section-title">
        BILL DETAILS
    </div>

    <table class="items-table">
        <thead>
            <tr>
                @if($showRate)
                    <th style="width: 5%;" class="text-center">#</th>
                    <th style="width: 17%;" class="text-left">Item Code</th>
                    <th style="width: 35%;" class="text-left">Item Name</th>
                    <th style="width: 13%;" class="text-right">Qty</th>
                    <th style="width: 14%;" class="text-right">Rate</th>
                    <th style="width: 16%;" class="text-right">Amount</th>
                @else
                    <th style="width: 5%;" class="text-center">#</th>
                    <th style="width: 20%;" class="text-left">Item Code</th>
                    <th style="width: 42%;" class="text-left">Item Name</th>
                    <th style="width: 15%;" class="text-right">Qty</th>
                    <th style="width: 18%;" class="text-right">Amount</th>
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

    {{-- GRAND TOTAL --}}

    <table class="total-table">
        <tr>
            <th class="text-right grand-total">
                GRAND TOTAL
            </th>

            <td class="text-right grand-total">
                {{ number_format($currentPurchase, 2) }}
            </td>
        </tr>
    </table>

    {{-- FOOTER --}}

    <div class="thank-you">
        Thank you for your business!<br>
        This is a computer generated invoice.
    </div>

</div>

</body>
</html>
```