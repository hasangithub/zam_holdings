<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <title>Shipment Plan</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 15px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .header h2 {
            margin: 0 0 4px;
            font-size: 15px;
        }

        .header p {
            margin: 2px 0;
            font-size: 9px;
        }

        .details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .details td {
            padding: 5px;
            border: 1px solid #bbb;
            font-size: 8px;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.report th,
        table.report td {
            border: 1px solid #777;
            padding: 3px 2px;
            vertical-align: middle;
            word-wrap: break-word;
            font-size: 7px;
        }

        table.report td:first-child {
            text-align: left;
            font-weight: bold;
        }

        table.report tfoot td {
            background: #e9ecef;
            font-weight: bold;
        }

        .footer {
            margin-top: 12px;
            text-align: right;
            font-size: 7px;
            color: #666;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>SHIPMENT PLAN</h2>
        <p>Shipment Plan #{{ $shipmentPlan->id }}</p>
    </div>

    <table class="details">
        <tr>
            <td>
                <strong>Shipment Plan No:</strong>
                {{ $shipmentPlan->id }}
            </td>

            <td>
                <strong>Date:</strong>
                {{ now()->format('Y-m-d') }}
            </td>
        </tr>
    </table>

    <table class="report">

        <thead>
            <tr>
                <th style="width:12%;">Item</th>
                <th style="width:9%;">Stock</th>

                @foreach($customers as $customer)
                <th>{{ $customer->name }}</th>
                @endforeach

                <th style="width:9%;">Total</th>
                <th style="width:9%;">Balance</th>
            </tr>
        </thead>

        <tbody>

            @forelse($shipmentPlan->items as $rows)

            @php
            $item = $rows->first()->item;
            $stock = $item->stock_qty ?? 0;
            $total = $rows->customers->sum('planned_weight');
            $customerMap = $rows->customers->keyBy('customer_id');
            @endphp

            <tr>
                <td>{{ $item->name ?? '' }}</td>

                <td>{{ number_format($stock, 2) }}</td>

                @foreach($customers as $customer)
                @php
                $record = $customerMap->get($customer->id);
                @endphp

                <td>
                    {{ $record ? number_format($record->planned_weight, 2) : '' }}
                </td>
                @endforeach

                <td>{{ number_format($total, 2) }}</td>

                <td>{{ number_format($stock - $total, 2) }}</td>
            </tr>

            @empty

            <tr>
                <td colspan="{{ $customers->count() + 4 }}"
                    style="text-align:center;">
                    No shipment items found
                </td>
            </tr>

            @endforelse

        </tbody>

    </table>

    <div class="footer">
        Generated on {{ now()->format('Y-m-d H:i') }}
    </div>

</body>

</html>