@extends('layouts.app')

@section('title','Purchase Details')

@section('content')

<div class="container">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between mb-3">

        <h3>
            Supplier: {{ $purchase->supplier->name }}
        </h3>

        <button class="btn btn-primary" data-toggle="modal" data-target="#paymentModal">
            Add Payment
        </button>

    </div>

    {{-- SUMMARY (SUPPLIER BASED) --}}
    <div class="card mb-4">
        <div class="card-body">

            <div class="row">

                <div class="col-md-4">
                    <strong>Total Purchases</strong><br>
                    Rs {{ number_format($totalPurchase,2) }}
                </div>

                <div class="col-md-4">
                    <strong>Total Paid</strong><br>
                    Rs {{ number_format($totalPaid,2) }}
                </div>

                <div class="col-md-4">
                    <strong>Outstanding</strong><br>
                    <span class="text-danger">
                        Rs {{ number_format($balance,2) }}
                    </span>
                </div>

            </div>

        </div>
    </div>

    {{-- PURCHASE ITEMS (ONLY THIS PURCHASE) --}}
    <div class="card mb-4">
        <div class="card-header">This Purchase Items</div>

        <div class="table-responsive">
            <table class="table table-bordered mb-0 table-erp">

                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($purchase->items as $item)
                    <tr>
                        <td>{{ $item->item->name ?? '-' }}</td>
                        <td>{{ $item->qty }}</td>
                        <td class="text-right">{{ number_format($item->price,2) }}</td>
                        <td class="text-right">{{ number_format($item->subtotal,2) }}</td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>

    {{-- SUPPLIER PURCHASE HISTORY --}}
    <div class="card mb-4">
        <div class="card-header">Supplier Purchase History</div>

        <table class="table table-bordered table-erp">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Total</th>
                </tr>
            </thead>

            <tbody>
                @foreach($purchases as $p)
                <tr>
                    <td>{{ $p->purchase_date }}</td>
                    <td class="text-right">{{ number_format($p->total,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- PAYMENTS --}}
    <div class="card">
        <div class="card-header">Payments</div>

        <table class="table table-bordered table-erp">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Method</th>
                </tr>
            </thead>

            <tbody>
                @foreach($payments as $pay)
                <tr>
                    <td>{{ $pay->payment_date }}</td>
                    <td class="text-right">{{ number_format($pay->amount,2) }}</td>
                    <td>{{ $pay->method }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

{{-- PAYMENT MODAL --}}
<div class="modal fade" id="paymentModal">
    <div class="modal-dialog">

        <form method="POST" action="{{ route('suppliers.payment.store', $purchase->supplier_id) }}">
            @csrf

            <div class="modal-content">

                <div class="modal-header">
                    <h5>Add Payment</h5>
                </div>

                <div class="modal-body">

                    <input type="number" name="amount" class="form-control" placeholder="Amount">

                    <input type="date" name="payment_date" class="form-control mt-2">

                    <input type="text" name="method" class="form-control mt-2" placeholder="Method">

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Save</button>
                </div>

            </div>

        </form>

    </div>
</div>

@endsection