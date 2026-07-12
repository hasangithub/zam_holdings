@extends('layouts.app')

@section('title','Export Sales')

@section('content')

<div class="card">
    <div class="card-header">
        <h3>Export Sales (USD)</h3>
    </div>

    <div class="card-body">

        <table class="table table-bordered table-erp">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Con</th>
                    <th>Customer</th>
                    <th>Total (USD)</th>
                    <th>Total (LKR)</th>
                    <th>Exchange Rate</th>
                    <th>Balance (LKR)</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->invoice_id }}</td>
                    <td>{{ \App\Models\Sale::CONSIGNORS[$sale->consignor] ?? '-' }}</td>
                    <td>{{ $sale->customer->name ?? '' }}</td>

                    <td>{{ $sale->total_foreign }}</td>
                    <td>{{ $sale->total }}</td>
                    <td>{{ $sale->exchange_rate }}</td>
                    <td>{{ $sale->balance_amount }}</td>

                    <td>
                        <a href="{{ route('export-sales.edit',$sale->id) }}"
                            class="btn btn-warning btn-sm">
                            Export Edit
                        </a>
                        <a href="{{ route('export-sales.invoice', $sale->id) }}"
                            class="btn btn-warning btn-sm">
                            Export Invoice
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>

@endsection