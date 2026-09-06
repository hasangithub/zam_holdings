@extends('layouts.app')

@section('title', 'Freight Details')

@section('content')

<div class="card">

    <div class="card-header py-2">
        <h3 class="card-title">
            Freight #{{ $freight->id }}
        </h3>

        <div class="card-tools">
            @if($freight->status == 'active' && $freight->balance_amount > 0)
                <button class="btn btn-success btn-sm"
                        data-toggle="modal"
                        data-target="#paymentModal">
                    <i class="fas fa-money-bill"></i> Payment
                </button>
            @endif

            <a href="{{ route('freights.index') }}"
               class="btn btn-secondary btn-sm">
                Back
            </a>
        </div>
    </div>

    <div class="card-body p-3">

        {{-- FREIGHT DETAILS --}}
        <div class="row">

            <div class="col-md-2">
                <small class="text-muted">Invoice</small>
                <div><strong>{{ $freight->sale->invoice_id }}</strong></div>
            </div>

            <div class="col-md-2">
                <small class="text-muted">Airway No.</small>
                <div>{{ $freight->sale->airway_number }}</div>
            </div>

            <div class="col-md-2">
                <small class="text-muted">Consignor</small>
                <div>{{ $freight->sale->consignor }}</div>
            </div>

            <div class="col-md-2">
                <small class="text-muted">Service</small>
                <div>{{ $freight->service->name }}</div>
            </div>

            <div class="col-md-2">
                <small class="text-muted">Date</small>
                <div>{{ $freight->date }}</div>
            </div>

            <div class="col-md-2">
                <small class="text-muted">Exchange Rate</small>
                <div>{{ number_format($freight->exchange_rate, 4) }}</div>
            </div>

        </div>

        <div class="row mt-3">

            <div class="col-md-3">
                <small class="text-muted">Amount USD</small>
                <div><strong>$ {{ number_format($freight->amount_usd, 2) }}</strong></div>
            </div>

            <div class="col-md-3">
                <small class="text-muted">Total LKR</small>
                <div><strong>{{ number_format($freight->amount_lkr, 2) }}</strong></div>
            </div>

            <div class="col-md-3">
                <small class="text-muted">Paid</small>
                <div class="text-success">
                    <strong>{{ number_format($freight->total_paid, 2) }}</strong>
                </div>
            </div>

            <div class="col-md-3">
                <small class="text-muted">Balance</small>
                <div class="{{ $freight->balance_amount > 0 ? 'text-danger' : 'text-success' }}">
                    <strong>{{ number_format($freight->balance_amount, 2) }}</strong>
                </div>
            </div>

        </div>

        <hr class="my-3">

        {{-- PAYMENTS --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Payments</h5>

            @if($freight->status == 'active')
                <span class="badge badge-{{ $freight->balance_amount > 0 ? 'warning' : 'success' }}">
                    {{ $freight->balance_amount > 0 ? 'Pending' : 'Paid' }}
                </span>
            @else
                <span class="badge badge-secondary">
                    {{ ucfirst($freight->status) }}
                </span>
            @endif
        </div>

        <table class="table table-sm table-bordered mb-0">

            <thead>
                <tr>
                    <th width="15%">Date</th>
                    <th width="25%">Account</th>
                    <th>Reference</th>
                    <th width="20%" class="text-right">Amount</th>
                </tr>
            </thead>

            <tbody>

                @forelse($freight->payments as $payment)

                    <tr>
                        <td>{{ $payment->payment_date }}</td>

                        <td>
                            {{ optional($payment->ledger)->name }}
                        </td>

                        <td>{{ $payment->reference ?? $payment->note }}</td>

                        <td class="text-right">
                            {{ number_format($payment->amount, 2) }}
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            No payments yet.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    {{-- CANCEL --}}
    @if($freight->status == 'active' && $freight->total_paid == 0)
        <div class="card-footer py-2">

            <form method="POST"
                  action="{{ route('freights.cancel', $freight->id) }}"
                  class="d-inline">

                @csrf

                <button type="submit"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('Cancel this freight?')">
                    <i class="fas fa-times"></i>
                    Cancel Freight
                </button>

            </form>

        </div>
    @endif

</div>


{{-- PAYMENT MODAL --}}
<div class="modal fade" id="paymentModal">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="POST"
                  action="{{ route('freights.payment', $freight->id) }}">

                @csrf

                <div class="modal-header py-2">
                    <h5 class="modal-title">Add Freight Payment</h5>

                    <button type="button"
                            class="close"
                            data-dismiss="modal">
                        &times;
                    </button>
                </div>

                <div class="modal-body">

                    <div class="alert alert-info py-2">
                        Balance:
                        <strong>
                            {{ number_format($freight->balance_amount, 2) }}
                        </strong>
                    </div>

                    <div class="form-group">
                        <label>Payment Date</label>
                        <input type="date"
                               name="payment_date"
                               value="{{ date('Y-m-d') }}"
                               class="form-control"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number"
                               name="amount"
                               step="0.01"
                               min="0.01"
                               max="{{ $freight->balance_amount }}"
                               class="form-control"
                               required>
                    </div>

                    <div class="form-group mb-0">
                        <label>Note</label>
                        <textarea name="note"
                                  class="form-control"
                                  rows="2"></textarea>
                    </div>

                </div>

                <div class="modal-footer py-2">

                    <button type="button"
                            class="btn btn-secondary btn-sm"
                            data-dismiss="modal">
                        Close
                    </button>

                    <button type="submit"
                            class="btn btn-success btn-sm">
                        <i class="fas fa-save"></i>
                        Save Payment
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection