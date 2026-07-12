@extends('layouts.app')

@section('title','Item Profit Analysis')

@section('content')

<div class="container-fluid">

    <div class="card card-primary card-outline">

        <div class="card-header">

            <h3 class="card-title">

                <i class="fas fa-chart-line mr-1"></i>

                Item Profit Analysis

            </h3>

        </div>

        <div class="card-body">

            <form method="GET">

                <div class="row">

                    <div class="col-md-3">

                        <label>From</label>

                        <input
                            type="date"
                            name="from"
                            value="{{ $from }}"
                            class="form-control">

                    </div>

                    <div class="col-md-3">

                        <label>To</label>

                        <input
                            type="date"
                            name="to"
                            value="{{ $to }}"
                            class="form-control">

                    </div>

                    <div class="col-md-2">

                        <label>&nbsp;</label>

                        <button class="btn btn-primary btn-block">

                            <i class="fas fa-search"></i>

                            Filter

                        </button>

                    </div>

                </div>

            </form>

            <hr>

            <div class="table-responsive">

                <table class="table table-bordered table-hover table-sm table-erp">

                    <thead>

                        <tr class="bg-light">

                            <th>Item</th>

                            <th class="text-right">Sold Qty</th>

                            <th class="text-right">Sales</th>

                            <th class="text-right">Cost</th>

                            <th class="text-right">Profit</th>

                            <th class="text-right">Profit %</th>

                        </tr>

                    </thead>

                    <tbody>

                        @php

                        $totalQty=0;
                        $totalSales=0;
                        $totalCost=0;
                        $totalProfit=0;

                        @endphp

                        @forelse($report as $row)

                        @php

                        $totalQty += $row->sold_qty;
                        $totalSales += $row->sales_amount;
                        $totalCost += $row->cost_amount;
                        $totalProfit += $row->gross_profit;

                        @endphp

                        <tr>

                            <td>{{ $row->item_name }}</td>

                            <td class="text-right">

                                {{ number_format($row->sold_qty,2) }}

                            </td>

                            <td class="text-right">

                                {{ number_format($row->sales_amount,2) }}

                            </td>

                            <td class="text-right">

                                {{ number_format($row->cost_amount,2) }}

                            </td>

                            <td class="text-right">

                                {{ number_format($row->gross_profit,2) }}

                            </td>

                            <td class="text-right">

                                {{ $row->sales_amount>0
? number_format(($row->gross_profit/$row->sales_amount)*100,2)
:0 }}%

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="6" class="text-center">

                                No Records Found

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                    <tfoot>

                        <tr class="bg-light">

                            <th>Total</th>

                            <th class="text-right">

                                {{ number_format($totalQty,2) }}

                            </th>

                            <th class="text-right">

                                {{ number_format($totalSales,2) }}

                            </th>

                            <th class="text-right">

                                {{ number_format($totalCost,2) }}

                            </th>

                            <th class="text-right">

                                {{ number_format($totalProfit,2) }}

                            </th>

                            <th class="text-right">

                                {{ $totalSales>0
?number_format(($totalProfit/$totalSales)*100,2)
:0 }}%

                            </th>

                        </tr>

                    </tfoot>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection