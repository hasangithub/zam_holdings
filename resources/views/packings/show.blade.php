@extends('layouts.app')

@section('title','View Shipment Plan')

@section('content')

<div class="container-fluid">

    <div class="card card-outline card-primary">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list mr-1"></i>
                Shipment Weight #{{ $packing->packing_no }}
            </h3>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-sm table-striped table-erp">

                    <thead class="thead-dark">

                        <tr>

                            <th>Item</th>
                            <th>Actual Weight</th>
                            @foreach($customers as $customer)
                            <th>{{ $customer->name }}</th>
                            @endforeach
                            <th>Customer Total</th>
                            <th>Reject</th>
                            <th>Market</th>
                            <th>Stock</th>
                            <th>Total Allocated</th>
                            <th>Difference</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $currentCategory = null;
                            $catActual = 0;
                            $catCustomer = 0;
                            $catReject = 0;
                            $catMarket = 0;
                            $catStock = 0;
                            $catAllocated = 0;
                            $catDifference = 0;
                        @endphp

                        @foreach($packing->items as $packingItem)


                        {{-- CATEGORY CHANGE --}}
                        @if($currentCategory != $packingItem->item->category_id)


                        {{-- PREVIOUS CATEGORY TOTAL --}}
                        @if($currentCategory != null)

                        <tr class="table-warning font-weight-bold">
                            <td class="text-right">
                                Category Total
                            </td>
                            <td class="text-right">
                                {{ number_format($catActual,2) }}
                            </td>
                            @foreach($customers as $customer)
                            <td></td>
                            @endforeach
                            <td class="text-right">
                                {{ number_format($catCustomer,2) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($catReject,2) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($catMarket,2) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($catStock,2) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($catAllocated,2) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($catDifference,2) }}
                            </td>
                        </tr>

                        @endif

                        @php

                        $currentCategory = $packingItem->item->category_id;

                        $catActual = 0;
                        $catCustomer = 0;
                        $catReject = 0;
                        $catMarket = 0;
                        $catStock = 0;
                        $catAllocated = 0;
                        $catDifference = 0;

                        @endphp

                        <tr class="bg-primary text-white">
                            <td colspan="{{ $customers->count()+8 }}">
                                <i class="fas fa-folder"></i>
                                {{ $packingItem->item->category->name }}
                            </td>
                        </tr>
                        @endif
                        {{-- ITEM CALCULATION --}}
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

                        // CATEGORY TOTALS
                        $catActual += $packingItem->actual_weight;
                        $catCustomer += $customerTotal;
                        $catReject += $packingItem->reject;
                        $catMarket += $packingItem->market;
                        $catStock += $packingItem->stock;
                        $catAllocated += $totalAllocated;
                        $catDifference += $difference;

                        @endphp
                        <tr>
                            <td>
                                <strong>
                                    {{ $packingItem->item->name }}
                                </strong>
                            </td>
                            <td class="text-right">
                                {{ number_format($packingItem->actual_weight,2) }}
                            </td>
                            {{-- CUSTOMER WEIGHTS --}}

                            @foreach($customers as $customer)

                            <td class="text-right">
                                @php

                                $weight = $packingItem->customers
                                ->where('customer_id',$customer->id)
                                ->sum('packed_weight');

                                @endphp


                                @if($weight > 0)

                                {{ number_format($weight,2) }}

                                @else

                                -

                                @endif


                            </td>
                            @endforeach

                            <td class="text-right font-weight-bold">
                                {{ number_format($customerTotal,2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($packingItem->reject,2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($packingItem->market,2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($packingItem->stock,2) }}
                            </td>

                            <td class="text-right font-weight-bold">
                                {{ number_format($totalAllocated,2) }}
                            </td>

                            <td class="text-right font-weight-bold 
    {{ $difference == 0 ? 'text-success' : 'text-danger' }}">

                                {{ number_format($difference,2) }}

                            </td>
                        </tr>
                        @endforeach
                        {{-- LAST CATEGORY TOTAL --}}

                        <tr class="table-warning font-weight-bold">

                            <td class="text-right">
                                Category Total
                            </td>

                            <td class="text-right">
                                {{ number_format($catActual,2) }}
                            </td>

                            @foreach($customers as $customer)

                            <td></td>

                            @endforeach
                            <td class="text-right">
                                {{ number_format($catCustomer,2) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($catReject,2) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($catMarket,2) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($catStock,2) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($catAllocated,2) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($catDifference,2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection