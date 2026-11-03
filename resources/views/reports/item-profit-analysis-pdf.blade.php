<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <title>Item Profit Analysis</title>

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
            font-size: 18px;
        }

        .header p {
            margin: 3px 0;
            font-size: 10px;
        }

        .details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .details td {
            border: 1px solid #ccc;
            padding: 7px;
        }

        .report {
            width: 100%;
            border-collapse: collapse;
        }

        .report th,
        .report td {
            border: 1px solid #777;
            padding: 7px;
        }

        .report th {
            background: #e9ecef;
            text-align: center;
            font-size: 9px;
        }

        .report td {
            text-align: right;
        }

        .report td:first-child {
            text-align: left;
        }

        .report tfoot td {
            background: #e9ecef;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>

<body>

    <div class="header">

        <h2>ITEM PROFIT ANALYSIS REPORT</h2>

        <p>Item-wise Sales and Profitability Summary</p>

    </div>

    <table class="details">

        <tr>
            <td width="50%">
                <strong>From Date:</strong>
                {{ $from ?: 'All Dates' }}
            </td>

            <td width="50%">
                <strong>To Date:</strong>
                {{ $to ?: 'All Dates' }}
            </td>
        </tr>

    </table>

    <table class="report">

        <thead>
            <tr>
                <th width="30%">Item</th>
                <th width="14%">Sold Qty</th>
                <th width="14%">Sales</th>
                <th width="14%">Cost</th>
                <th width="14%">Profit</th>
                <th width="14%">Profit %</th>
            </tr>
        </thead>

        <tbody>

            @forelse($report as $row)

                <tr>

                    <td>{{ $row->item_name }}</td>

                    <td>
                        {{ number_format($row->sold_qty, 2) }}
                    </td>

                    <td>
                        {{ number_format($row->sales_amount, 2) }}
                    </td>

                    <td>
                        {{ number_format($row->cost_amount, 2) }}
                    </td>

                    <td>
                        {{ number_format($row->gross_profit, 2) }}
                    </td>

                    <td>
                        {{ $row->sales_amount > 0
                            ? number_format(($row->gross_profit / $row->sales_amount) * 100, 2)
                            : '0.00'
                        }}%
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="6" style="text-align:center;">
                        No Records Found
                    </td>
                </tr>

            @endforelse

        </tbody>

        <tfoot>

            <tr>

                <td>GRAND TOTAL</td>

                <td>
                    {{ number_format($totals['qty'], 2) }}
                </td>

                <td>
                    {{ number_format($totals['sales'], 2) }}
                </td>

                <td>
                    {{ number_format($totals['cost'], 2) }}
                </td>

                <td>
                    {{ number_format($totals['profit'], 2) }}
                </td>

                <td>
                    {{ number_format($totals['profit_percentage'], 2) }}%
                </td>

            </tr>

        </tfoot>

    </table>

    <div class="footer">
        Generated on {{ now()->format('Y-m-d H:i') }}
    </div>

</body>
</html>