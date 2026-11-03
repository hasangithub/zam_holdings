
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <title>Sales Profit & Loss Report</title>

    <style>
        @page {
            margin: 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0 0 5px;
            font-size: 19px;
        }

        .header p {
            margin: 0;
            color: #666;
        }

        .details {
            width: 100%;
            margin-bottom: 18px;
        }

        .details td {
            padding: 5px;
            border-bottom: 1px solid #ddd;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        table.report th,
        table.report td {
            border: 1px solid #bbb;
            padding: 6px 4px;
        }

        table.report th {
            background: #e9ecef;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            background: #e9ecef;
            font-weight: bold;
        }

        .profit {
            color: #198754;
        }

        .loss {
            color: #dc3545;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #777;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>SALES PROFIT & LOSS REPORT</h2>
        <p>Sales Invoice Profitability Statement</p>
    </div>

    <table class="details">
        <tr>
            <td width="33%">
                <strong>Invoice No:</strong>
                {{ $sale->invoice_id }}
            </td>

            <td width="34%">
                <strong>Customer:</strong>
                {{ optional($sale->customer)->name ?? '-' }}
            </td>

            <td width="33%">
                <strong>Sale Date:</strong>
                {{ $sale->sale_date
                    ? \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y')
                    : '-' }}
            </td>
        </tr>
    </table>

    <table class="report">

        <thead>
            <tr>
                <th width="4%">#</th>
                <th width="18%">Item</th>
                <th width="9%">Sales Weight</th>
                <th width="9%">Sales Price</th>
                <th width="9%">Purchase Price</th>
                <th width="9%">Other Exp / Kg</th>
                <th width="10%">Purchase Cost / Kg</th>
                <th width="11%">Total Cost</th>
                <th width="11%">Sales Amount</th>
                <th width="10%">Profit / Loss</th>
            </tr>
        </thead>

        <tbody>

            @forelse($rows as $index => $item)

                <tr>
                    <td class="text-center">
                        {{ $index + 1 }}
                    </td>

                    <td>
                        {{ $item['item_name'] }}
                    </td>

                    <td class="text-right">
                        {{ number_format($item['sales_weight'], 3) }}
                    </td>

                    <td class="text-right">
                        {{ number_format($item['sales_price'], 2) }}
                    </td>

                    <td class="text-right">
                        {{ number_format($item['purchase_price'], 2) }}
                    </td>

                    <td class="text-right">
                        {{ number_format($item['other_expense_per_kg'], 2) }}
                    </td>

                    <td class="text-right">
                        {{ number_format($item['purchase_cost_per_kg'], 2) }}
                    </td>

                    <td class="text-right">
                        {{ number_format($item['total_cost'], 2) }}
                    </td>

                    <td class="text-right">
                        {{ number_format($item['sales_amount'], 2) }}
                    </td>

                    <td class="text-right">
                        <strong>
                            {{ number_format($item['profit_loss'], 2) }}
                        </strong>
                    </td>
                </tr>

            @empty

                <tr>
                    <td colspan="10" class="text-center">
                        No Profit & Loss records found.
                    </td>
                </tr>

            @endforelse

        </tbody>

        <tfoot>
            <tr class="total-row">

                <td colspan="2" class="text-right">
                    TOTAL
                </td>

                <td class="text-right">
                    {{ number_format($totals['sales_weight'], 3) }}
                </td>

                <td colspan="4"></td>

                <td class="text-right">
                    {{ number_format($totals['total_cost'], 2) }}
                </td>

                <td class="text-right">
                    {{ number_format($totals['sales_amount'], 2) }}
                </td>

                <td class="text-right">
                    {{ number_format($totals['profit_loss'], 2) }}
                </td>

            </tr>
        </tfoot>

    </table>

    <div class="footer">
        Generated on: {{ now()->format('d/m/Y h:i A') }}
    </div>

</body>
</html>
