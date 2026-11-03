<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <title>Invoice Profit Analysis</title>

    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h2 {
            font-size: 17px;
            margin: 0 0 5px;
        }

        .header p {
            margin: 3px 0;
            font-size: 10px;
        }

        .details {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .details td {
            padding: 5px;
            border: 1px solid #ccc;
        }

        .details strong {
            color: #333;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.report th,
        table.report td {
            border: 1px solid #777;
            padding: 5px 3px;
            text-align: right;
            vertical-align: middle;
            word-wrap: break-word;
        }

        table.report th {
            background: #e9ecef;
            font-weight: bold;
            text-align: center;
            font-size: 8px;
        }

        table.report td:first-child {
            text-align: left;
            width: 12%;
        }

        table.report tfoot td {
            background: #e9ecef;
            font-weight: bold;
        }

        .text-center {
            text-align: center !important;
        }

        .profit {
            font-weight: bold;
        }

        .footer {
            margin-top: 15px;
            text-align: right;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>INVOICE PROFIT ANALYSIS REPORT</h2>
        <p>Invoice Profitability Summary</p>
    </div>

    <table class="details">
        <tr>
            <td width="20%">
                <strong>Invoice No:</strong>
                {{ $sale->invoice_id }}
            </td>

            <td width="35%">
                <strong>Customer:</strong>
                {{ $sale->customer->name ?? '' }}
            </td>

            <td width="20%">
                <strong>Date:</strong>
                {{ $sale->sale_date }}
            </td>

            <td width="25%">
                <strong>Exchange Rate:</strong>
                {{ number_format($exchangeRate, 2) }}
            </td>
        </tr>

        <tr>
            <td colspan="4">
                <strong>F & P Per Kg:</strong>
                {{ number_format($fpPerKg, 2) }} LKR
            </td>
        </tr>
    </table>

    <table class="report">

        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Sales Price ($)</th>
                <th>Market Price</th>
                <th>Avg Cost</th>
                <th>Sales Price (LKR)</th>
                <th>Sales Price With Cost (LKR)</th>
                <th>Total Purchase Amount</th>
                <th>Total Cost</th>
                <th>Total Sales (LKR)</th>
                <th>Total Sales ($)</th>
                <th>Profit / Loss</th>
            </tr>
        </thead>

        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item['name'] }}</td>

                <td>{{ number_format($item['qty'], 2) }}</td>

                <td>{{ number_format($item['sale_price_usd'], 2) }}</td>

                <td>{{ number_format($item['market_price'], 2) }}</td>

                <td>{{ number_format($item['avg_cost'], 2) }}</td>

                <td>{{ number_format($item['sales_price_lkr'], 2) }}</td>

                <td>{{ number_format($item['sales_after_fp'], 2) }}</td>

                <td>{{ number_format($item['purchase_amount'], 2) }}</td>

                <td>{{ number_format($item['total_cost'], 2) }}</td>

                <td>{{ number_format($item['total_sales_lkr'], 2) }}</td>

                <td>{{ number_format($item['total_sales_usd'], 2) }}</td>

                <td class="profit">
                    {{ number_format($item['profit'], 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr>
                <td>GRAND TOTAL</td>

                <td>{{ number_format($totals['qty'], 2) }}</td>

                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>

                <td>{{ number_format($totals['purchase_amount'], 2) }}</td>

                <td>{{ number_format($totals['total_cost'], 2) }}</td>

                <td>{{ number_format($totals['total_sales_lkr'], 2) }}</td>

                <td>{{ number_format($totals['total_sales_usd'], 2) }}</td>

                <td>{{ number_format($totals['profit'], 2) }}</td>
            </tr>
        </tfoot>

    </table>

    <div class="footer">
        Generated on {{ now()->format('Y-m-d H:i') }}
    </div>

</body>

</html>