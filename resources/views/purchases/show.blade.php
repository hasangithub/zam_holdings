@extends('layouts.app')

@section('title','Purchase Details')

@section('content')

<div class="container">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between mb-3">

        <h3>
            Supplier: {{ $purchase->supplier->name }}
        </h3>
    </div>

    <button
        type="button"
        class="btn btn-warning btn-sm"
        data-toggle="modal"
        data-target="#purchaseAmendmentModal">
        <i class="fas fa-edit mr-1"></i>
        Add Amendment
    </button>

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

    @if($purchase->amendments->count())

    <div class="card card-outline card-warning mt-3">

        <div class="card-header">

            <h3 class="card-title">

                <i class="fas fa-file-invoice-dollar mr-1"></i>

                Purchase Amendments

            </h3>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-sm table-bordered mb-0">

                    <thead class="thead-light">

                        <tr>

                            <th>
                                Date
                            </th>

                            <th>
                                Reference
                            </th>

                            <th>
                                Reason
                            </th>

                            <th>
                                Notes
                            </th>

                            <th class="text-right">
                                Amount
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($purchase->amendments as $amendment)

                        <tr>

                            <td>
                                {{ $amendment->amendment_date->format('d M Y') }}
                            </td>

                            <td>

                                AMD-{{ $amendment->id }}

                            </td>

                            <td>

                                {{ $amendment->reason }}

                            </td>

                            <td>

                                {{ $amendment->notes ?: '-' }}

                            </td>

                            <td class="text-right">

                                @if($amendment->amount > 0)

                                <span class="text-danger">

                                    +
                                    {{ number_format($amendment->amount, 2) }}

                                </span>

                                @else

                                <span class="text-success">

                                    {{ number_format($amendment->amount, 2) }}

                                </span>

                                @endif

                            </td>

                            <td>

                                @if($amendment->status === 'approved')

                                <span class="badge badge-success">
                                    Approved
                                </span>

                                @elseif($amendment->status === 'draft')

                                <span class="badge badge-warning">
                                    Draft
                                </span>

                                @else

                                <span class="badge badge-danger">
                                    Cancelled
                                </span>

                                @endif

                            </td>

                        </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    @endif

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

{{-- =========================================================
     PURCHASE AMENDMENT MODAL
========================================================= --}}

<div
    class="modal fade"
    id="purchaseAmendmentModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="purchaseAmendmentModalLabel"
    aria-hidden="true"
>

    <div
        class="modal-dialog modal-md"
        role="document"
    >

        <div class="modal-content">


            {{-- HEADER --}}

            <div class="modal-header bg-warning">

                <h5
                    class="modal-title"
                    id="purchaseAmendmentModalLabel"
                >

                    <i class="fas fa-edit mr-1"></i>

                    Add Purchase Amendment

                </h5>

                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close"
                >

                    <span aria-hidden="true">
                        &times;
                    </span>

                </button>

            </div>


            {{-- FORM --}}

            <form
                method="POST"
                action="{{ route(
                    'purchases.amendment.store',
                    $purchase->id
                ) }}"
            >

                @csrf


                <div class="modal-body">


                    {{-- PURCHASE --}}

                    <div class="form-group">

                        <label>
                            Purchase
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $purchase->invoice_no ?? $purchase->id }}"
                            readonly
                        >

                    </div>


                    {{-- SUPPLIER --}}

                    <div class="form-group">

                        <label>
                            Supplier
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $purchase->supplier->name ?? '-' }}"
                            readonly
                        >

                    </div>


                    <div class="row">


                        {{-- DATE --}}

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>
                                    Amendment Date
                                </label>

                                <input
                                    type="date"
                                    name="amendment_date"
                                    class="form-control"
                                    value="{{ old(
                                        'amendment_date',
                                        now()->format('Y-m-d')
                                    ) }}"
                                    required
                                >

                            </div>

                        </div>


                        {{-- AMOUNT --}}

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>
                                    Amount
                                </label>

                                <input
                                    type="number"
                                    name="amount"
                                    class="form-control"
                                    step="0.01"
                                    placeholder="Enter amount"
                                    required
                                >

                                <small class="form-text text-muted">

                                    Positive = increase payable.<br>

                                    Negative = decrease payable.

                                </small>

                            </div>

                        </div>

                    </div>


                    {{-- REASON --}}

                    <div class="form-group">

                        <label>
                            Reason
                        </label>

                        <input
                            type="text"
                            name="reason"
                            class="form-control"
                            placeholder="Reason for amendment"
                            required
                        >

                    </div>


                    {{-- NOTES --}}

                    <div class="form-group">

                        <label>
                            Notes
                        </label>

                        <textarea
                            name="notes"
                            class="form-control"
                            rows="3"
                            placeholder="Additional notes..."
                        ></textarea>

                    </div>


                    {{-- WARNING --}}

                    <div class="alert alert-warning mb-0">

                        <i class="fas fa-exclamation-triangle mr-1"></i>

                        This amendment will affect the supplier
                        outstanding balance.

                    </div>

                </div>


                {{-- FOOTER --}}

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary btn-sm"
                        data-dismiss="modal"
                    >

                        Cancel

                    </button>


                    <button
                        type="submit"
                        class="btn btn-warning btn-sm"
                    >

                        <i class="fas fa-save mr-1"></i>

                        Save Amendment

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection