@extends('layouts.app')

@section('title','Branch Comparison Report')

@section('content')

<div class="container-fluid">

    <div class="card">

        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Branch Sales vs Purchase Comparison Report</h5>
        </div>

        <div class="card-body">

            <div class="row mb-3">

                <div class="col-md-6">

                    <table class="table table-sm table-borderless">

                        <tr>
                            <th width="180">Sales Branch</th>
                            <td>: {{ $branch->name }}</td>
                        </tr>

                        <tr>
                            <th>Sales Date</th>
                            <td>
                                : {{ \Carbon\Carbon::parse($requestData['sales_from'])->format('d-m-Y') }}
                                -
                                {{ \Carbon\Carbon::parse($requestData['sales_to'])->format('d-m-Y') }}
                            </td>
                        </tr>

                    </table>

                </div>

                <div class="col-md-6">

                    <table class="table table-sm table-borderless">

                        <tr>
                            <th width="180">Purchase Supplier</th>
                            <td>: {{ $supplier->name }}</td>
                        </tr>

                        <tr>
                            <th>Purchase Date</th>
                            <td>
                                : {{ \Carbon\Carbon::parse($requestData['purchase_from'])->format('d-m-Y') }}
                                -
                                {{ \Carbon\Carbon::parse($requestData['purchase_to'])->format('d-m-Y') }}
                            </td>
                        </tr>

                    </table>

                </div>

            </div>

            <div class="table-responsive">

                <table class="table table-bordered table-erp table-sm">

                    <thead class="table-dark">

                        <tr>

                            <th width="40%">Item</th>

                            <th class="text-end">Purchase Qty</th>

                            <th class="text-end">Sales Qty</th>

                            <th class="text-end">Difference Qty</th>

                        </tr>

                    </thead>

                    <tbody>

                        @php

                        $grandPurchase=0;
                        $grandSales=0;
                        $grandDifference=0;

                        @endphp


                        @foreach($report as $category=>$rows)

                        @php

                        $categoryPurchase=0;
                        $categorySales=0;
                        $categoryDifference=0;

                        @endphp

                        @foreach($rows as $row)

                        @php

                        $categoryPurchase += $row['purchase_qty'];
                        $categorySales += $row['sales_qty'];
                        $categoryDifference += $row['difference_qty'];

                        @endphp

                        <tr>

                            <td>

                                {{ $row['item_name'] }}

                            </td>

                            <td class="text-end">

                                {{ number_format($row['purchase_qty'],2) }}

                            </td>

                            <td class="text-end">

                                {{ number_format($row['sales_qty'],2) }}

                            </td>

                            <td class="text-end">

                                @if($row['difference_qty']==0)

                                <span class="text-success fw-bold">

                                    {{ number_format($row['difference_qty'],2) }}

                                </span>

                                @else

                                <span class="text-danger fw-bold">

                                    {{ number_format($row['difference_qty'],2) }}

                                </span>

                                @endif

                            </td>

                        </tr>

                        @endforeach


                        <tr class="table-warning fw-bold">

                            <td>

                                {{ $category }} Total

                            </td>

                            <td class="text-end">

                                {{ number_format($categoryPurchase,2) }}

                            </td>

                            <td class="text-end">

                                {{ number_format($categorySales,2) }}

                            </td>

                            <td class="text-end">

                                {{ number_format($categoryDifference,2) }}

                            </td>

                        </tr>


                        @php

                        $grandPurchase += $categoryPurchase;
                        $grandSales += $categorySales;
                        $grandDifference += $categoryDifference;

                        @endphp

                        @endforeach


                        <tr class="table-dark">

                            <td>

                                GRAND TOTAL

                            </td>

                            <td class="text-end">

                                {{ number_format($grandPurchase,2) }}

                            </td>

                            <td class="text-end">

                                {{ number_format($grandSales,2) }}

                            </td>

                            <td class="text-end">

                                {{ number_format($grandDifference,2) }}

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection