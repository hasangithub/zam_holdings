@extends('layouts.app')

@section('title','Purchase Details')

@section('content')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">Purchase #{{ $purchase->id }}</h3>
    </div>

    <div class="card-body">

        {{-- HEADER INFO --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <h5><b>Supplier:</b> {{ $purchase->supplier->name ?? 'N/A' }}</h5>
            </div>

            <div class="col-md-6 text-right">
                <h5><b>Date:</b> {{ $purchase->purchase_date ?? $purchase->created_at->format('Y-m-d') }}</h5>
            </div>
        </div>

        <hr>

        {{-- ITEMS TABLE --}}
        <table class="table table-bordered">

            <thead class="bg-light">
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Remaining</th>
                    <th>Cost Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>

            <tbody>

                @foreach($purchase->items as $pi)
                <tr>
                    <td>{{ $pi->item->name }}</td>

                    <td>{{ $pi->qty }}</td>

                    <td>
                        <span class="badge badge-info">
                            {{ $pi->remaining_qty }}
                        </span>
                    </td>

                    <td>{{ number_format($pi->price,2) }}</td>

                    <td>{{ number_format($pi->qty * $pi->price,2) }}</td>
                </tr>
                @endforeach

            </tbody>

        </table>

        {{-- TOTAL --}}
        <div class="text-right mt-3">
            <h4>
                Total:
                <span class="badge badge-success">
                    {{ number_format($purchase->total,2) }}
                </span>
            </h4>
        </div>

    </div>

</div>

@endsection