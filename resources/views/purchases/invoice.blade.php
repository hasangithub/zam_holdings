@extends('layouts.app')

@section('title', 'Purchase Invoice')

@section('content')

<style>
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background: #fff !important;
        }

        .invoice-box {
            box-shadow: none !important;
            border: none !important;
        }
    }

    .invoice-box {
        max-width: 900px;
        margin: 20px auto;
        background: #fff;
        padding: 35px;
        border: 1px solid #ddd;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
    }

    .invoice-title {
        text-align: center;
        margin-bottom: 15px;
    }

    .company-name {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: .5px;
    }

    .company-address {
        font-size: 13px;
        color: #666;
        line-height: 1.5;
    }

    .invoice-label {
        font-size: 12px;
        color: #777;
        text-transform: uppercase;
    }

    .invoice-value {
        font-size: 14px;
        font-weight: 600;
    }

    .table th,
    .table td {
        font-size: 13px;
        padding: 8px;
        vertical-align: middle;
    }

    .table thead th {
        background: #f5f5f5;
        font-weight: 600;
    }

    .summary-table {
        max-width: 500px;
        margin-left: auto;
    }

    .summary-table td,
    .summary-table th {
        padding: 8px 10px;
    }

    .total-row {
        font-size: 15px;
        font-weight: 700;
    }

    .paid-row {
        font-weight: 600;
    }

    .balance-row {
        font-size: 16px;
        font-weight: 700;
    }

    .balance-due {
        padding: 8px 12px;
        border: 1px solid #ddd;
        display: inline-block;
        font-weight: 700;
    }

    .footer {
        text-align: center;
        margin-top: 35px;
        color: #777;
        font-size: 12px;
    }
</style>

<div class="container">

    {{-- PRINT BUTTON --}}
    <div class="no-print mb-3">
        <button onclick="window.print()" class="btn btn-dark btn-sm">
            <i class="fas fa-print"></i>
            Print Invoice
        </button>
    </div>


    <div class="invoice-box">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}

        <div class="invoice-title">

            <div class="company-name">
                MR - ZAM HOLDINGS (PVT) LTD
            </div>

            <div class="company-address">
                NO 303, NELUMWILA ROAD, DELATHURA,<br>
                JA-ELA, SRI LANKA
            </div>

        </div>

        <hr>


        {{-- ========================================================= --}}
        {{-- PURCHASE / SUPPLIER INFORMATION --}}
        {{-- ========================================================= --}}

        <div class="row mb-4">

            <div class="col-md-6">

                <div class="invoice-label">
                    Purchase Invoice
                </div>

                <div class="invoice-value">
                    {{ $purchase->invoice_no ?? $purchase->id }}
                </div>

                <div class="mt-2">

                    <span class="invoice-label">
                        Date
                    </span>

                    <br>

                    <span class="invoice-value">
                        {{ date(
                            'd M Y',
                            strtotime(
                                $purchase->purchase_date ?? $purchase->created_at
                            )
                        ) }}
                    </span>

                </div>

            </div>


            <div class="col-md-6 text-md-end mt-3 mt-md-0">

                <div class="invoice-label">
                    Supplier
                </div>

                <div class="invoice-value">
                    {{ $purchase->supplier->name ?? '-' }}
                </div>

                @if(!empty($purchase->supplier->phone))

                <div class="text-muted small">
                    {{ $purchase->supplier->phone }}
                </div>

                @endif

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- ITEMS --}}
        {{-- ========================================================= --}}

        <table class="table table-bordered mb-4">

            <thead>

                <tr>
                    <th width="5%">#</th>
                    <th width="15%">Item Code</th>
                    <th>Item Name</th>
                    <th width="10%" class="text-end">Qty</th>
                    <th width="15%" class="text-end">Rate</th>
                    <th width="18%" class="text-end">Amount</th>
                </tr>

            </thead>

            <tbody>

                @foreach($purchase->items as $key => $item)

                <tr>

                    <td>
                        {{ $key + 1 }}
                    </td>

                    <td>
                        {{ $item->item->code ?? '-' }}
                    </td>

                    <td>
                        {{ $item->item->name ?? '-' }}
                    </td>

                    <td class="text-right">
                        {{ number_format($item->qty, 2) }}
                    </td>

                    <td class="text-right">
                        {{ number_format($item->price, 2) }}
                    </td>

                    <td class="text-right">
                        {{ number_format($item->subtotal, 2) }}
                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>


        {{-- ========================================================= --}}
        {{-- PAYMENT / OUTSTANDING SUMMARY --}}
        {{-- ========================================================= --}}

        <table class="table table-bordered summary-table">

            {{-- Previous Outstanding --}}
            <tr>

                <th class="text-end">
                    Previous Outstanding
                </th>

                <td class="text-right">
                    {{ number_format($previousOutstanding, 2) }}
                </td>

            </tr>


            {{-- Current Purchase --}}
            <tr>

                <th class="text-end">
                    Current Purchase
                </th>

                <td class="text-right">
                    {{ number_format($currentPurchase, 2) }}
                </td>

            </tr>


            {{-- Total Payable --}}
            <tr class="total-row">

                <th class="text-end">
                    Total Payable
                </th>

                <td class="text-right">
                    {{ number_format($totalPayable, 2) }}
                </td>

            </tr>


            {{-- Paid --}}
            <tr class="paid-row">

                <th class="text-end">
                    Payment Made
                </th>

                <td class="text-right">
                    {{ number_format($currentPaid, 2) }}
                </td>

            </tr>


            {{-- Current Balance --}}
            <tr class="balance-row">

                <th class="text-end">
                    Outstanding Payable
                </th>

                <td class="text-right">

                    {{ number_format(
                            $previousOutstanding + $currentOutstanding,
                            2
                        ) }}

                </td>

            </tr>

        </table>


        {{-- ========================================================= --}}
        {{-- FOOTER --}}
        {{-- ========================================================= --}}

        <div class="footer">

            <div>
                Thank you for your business!
            </div>

            <div class="mt-1">
                This is a computer generated invoice.
            </div>

        </div>

    </div>

</div>

@endsection