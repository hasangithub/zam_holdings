<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Packing Report</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 12px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h2 {
            font-size: 15px;
            margin: 0 0 4px;
        }

        .header p {
            font-size: 9px;
            margin: 2px 0;
        }

        .details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .details td {
            border: 1px solid #aaa;
            padding: 4px;
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
            font-size: 7px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        table.report th {
            background: #343a40;
            color: #fff;
            text-align: center;
            font-weight: bold;
        }

        table.report td {
            text-align: right;
        }

        table.report td.item {
            text-align: left;
            font-weight: bold;
        }

        .category-row td {
            background: #286090;
            color: #fff;
            text-align: left !important;
            font-weight: bold;
            padding: 4px;
        }

        .total-row td {
            background: #fff2cc;
            font-weight: bold;
        }

        .footer {
            margin-top: 8px;
            text-align: right;
            font-size: 7px;
            color: #666;
        }

        .negative {
            color: #dc3545;
        }

        .positive {
            color: #218838;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>PACKING WEIGHT REPORT</h2>
        <p>Packing No: {{ $packing->packing_no }}</p>
    </div>

    <table class="details">
        <tr>
            <td>
                <strong>Packing No:</strong>
                {{ $packing->packing_no }}
            </td>
            <td>
                <strong>Generated Date:</strong>
                {{ now()->format('Y-m-d') }}
            </td>
        </tr>
    </table>

    <table class="report">

        <thead>
            <tr>
                <th style="width: 11%;">Item</th>
                <th style="width: 8%;">Actual Weight</th>

                @foreach($customers as $customer)
                    <th>{{ $customer->name }}</th>
                @endforeach

                <th style="width: 8%;">Customer Total</th>
                <th style="width: 6%;">Reject</th>
                <th style="width: 6%;">Market</th>
                <th style="width: 6%;">Stock</th>
                <th style="width: 8%;">Total Allocated</th>
                <th style="width: 7%;">Difference</th>
            </tr>
        </thead>

        <tbody>

            @php
                $categoryGroups = $packing->items->groupBy(function ($item) {
                    return $item->item->category_id;
                });
            @endphp

            @forelse($categoryGroups as $categoryItems)

                @php
                    $category = $categoryItems->first()->item->category;

                    $catActual = 0;
                    $catCustomer = 0;
                    $catReject = 0;
                    $catMarket = 0;
                    $catStock = 0;
                    $catAllocated = 0;
                    $catDifference = 0;
                @endphp

                <tr class="category-row">
                    <td colspan="{{ $customers->count() + 8 }}">
                        {{ $category->name ?? 'Uncategorized' }}
                    </td>
                </tr>

                @foreach($categoryItems as $packingItem)

                    @php
                        $customerTotal = $packingItem->customers->sum('packed_weight');

                        $totalAllocated =
                            $customerTotal +
                            $packingItem->reject +
                            $packingItem->market +
                            $packingItem->stock;

                        $difference =
                            $packingItem->actual_weight -
                            $totalAllocated;

                        $catActual += $packingItem->actual_weight;
                        $catCustomer += $customerTotal;
                        $catReject += $packingItem->reject;
                        $catMarket += $packingItem->market;
                        $catStock += $packingItem->stock;
                        $catAllocated += $totalAllocated;
                        $catDifference += $difference;
                    @endphp

                    <tr>
                        <td class="item">
                            {{ $packingItem->item->name ?? '' }}
                        </td>

                        <td>
                            {{ number_format($packingItem->actual_weight, 2) }}
                        </td>

                        @foreach($customers as $customer)

                            @php
                                $weight = $packingItem->customers
                                    ->where('customer_id', $customer->id)
                                    ->sum('packed_weight');
                            @endphp

                            <td>
                                {{ $weight > 0 ? number_format($weight, 2) : '-' }}
                            </td>

                        @endforeach

                        <td>
                            {{ number_format($customerTotal, 2) }}
                        </td>

                        <td>
                            {{ number_format($packingItem->reject, 2) }}
                        </td>

                        <td>
                            {{ number_format($packingItem->market, 2) }}
                        </td>

                        <td>
                            {{ number_format($packingItem->stock, 2) }}
                        </td>

                        <td>
                            {{ number_format($totalAllocated, 2) }}
                        </td>

                        <td class="{{ $difference == 0 ? 'positive' : 'negative' }}">
                            {{ number_format($difference, 2) }}
                        </td>
                    </tr>

                @endforeach

                {{-- CATEGORY TOTAL --}}

                <tr class="total-row">
                    <td>Category Total</td>

                    <td>{{ number_format($catActual, 2) }}</td>

                    @foreach($customers as $customer)
                        <td></td>
                    @endforeach

                    <td>{{ number_format($catCustomer, 2) }}</td>
                    <td>{{ number_format($catReject, 2) }}</td>
                    <td>{{ number_format($catMarket, 2) }}</td>
                    <td>{{ number_format($catStock, 2) }}</td>
                    <td>{{ number_format($catAllocated, 2) }}</td>
                    <td>{{ number_format($catDifference, 2) }}</td>
                </tr>

            @empty

                <tr>
                    <td colspan="{{ $customers->count() + 8 }}"
                        style="text-align:center;">
                        No packing items found
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