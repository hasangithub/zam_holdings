@extends('layouts.app')

@section('title','View Shipment Plan')

@section('content')

<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list mr-1"></i>
                 Shipment Plan #{{ $shipmentPlan->id }}
            </h3>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Stock</th>

                            @foreach($customers as $customer)
                            <th>{{ $customer->name }}</th>
                            @endforeach

                            <th>Total</th>
                            <th>Balance</th>

                        </tr>
                    </thead>

                    <tbody>

                        @foreach($shipmentPlan->items as $rows)

                        @php

                        $item = $rows->first()->item;

                        $stock = $item->stock_qty ?? 0; // or calculate from purchase_items

                        $total = $rows->customers->sum('planned_weight');

                        // Key by customer_id for O(1) lookups
                        $customerMap = $rows->customers->keyBy('customer_id');

                        @endphp

                        <tr>

                            <td>{{ $item->name }}</td>

                            <td>{{ $stock }}</td>

                            @foreach($customers as $customer)

                            @php
                            $record = $rows->customers->firstWhere('customer_id', $customer->id);
                            @endphp

                            <td class="text-right">
                                 {{ optional($customerMap->get($customer->id))->planned_weight ?? '' }}
                            </td>

                            @endforeach

                            <td class="text-right">{{ $total }}</td>

                            <td class="text-right">{{ $stock - $total }}</td>

                        </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection