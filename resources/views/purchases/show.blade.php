@extends('layouts.app')

@section('title','Purchase Details')

@section('content')
<div class="container">

    @if(session('success'))

    <div class="alert alert-success">
        {{ session('success') }}
    </div>

    @endif

    @if(session('error'))

    <div class="alert alert-danger">
        {{ session('error') }}
    </div>

    @endif


    <div class="d-flex justify-content-between mb-3">

        <h3>
            Purchase Details
        </h3>

        @if($purchase->balance_amount > 0)

        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#paymentModal">
            Add Payment
        </button>

        @endif

    </div>


    {{-- Purchase Summary --}}
    <div class="card mb-4">

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 mb-3">

                    <strong>Supplier</strong>

                    <br>

                    {{ $purchase->supplier->name ?? '-' }}

                </div>

                <div class="col-md-4 mb-3">

                    <strong>Total Amount</strong>

                    <br>

                    Rs {{ number_format($purchase->total, 2) }}

                </div>

                <div class="col-md-4 mb-3">

                    <strong>Paid Amount</strong>

                    <br>

                    Rs {{ number_format($purchase->paid_amount, 2) }}

                </div>

                <div class="col-md-4 mb-3">

                    <strong>Balance Amount</strong>

                    <br>

                    <span class="text-danger fw-bold">

                        Rs {{ number_format($purchase->balance_amount, 2) }}

                    </span>

                </div>

                <div class="col-md-4 mb-3">

                    <strong>Status</strong>

                    <br>

                    @if($purchase->payment_status == 'paid')

                    <span class="badge bg-success">
                        Paid
                    </span>

                    @elseif($purchase->payment_status == 'partial')

                    <span class="badge bg-warning">
                        Partial
                    </span>

                    @else

                    <span class="badge bg-danger">
                        Unpaid
                    </span>

                    @endif

                </div>

                <div class="col-md-4 mb-3">

                    <strong>Purchase Date</strong>

                    <br>

                    {{ date('d M Y', strtotime($purchase->created_at)) }}

                </div>

            </div>

        </div>

    </div>


    {{-- Payment History --}}
    <div class="card">

        <div class="card-header">
            Payment History
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered mb-0">

                    <thead>

                        <tr>

                            <th>#</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Note</th>
                            <th>Added By</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($purchase->payments as $key => $payment)

                        <tr>

                            <td>{{ $key + 1 }}</td>

                            <td>
                                {{ date('d M Y', strtotime($payment->payment_date)) }}
                            </td>

                            <td>
                                Rs {{ number_format($payment->amount, 2) }}
                            </td>

                            <td>
                                {{ $payment->payment_method ?? '-' }}
                            </td>

                            <td>
                                {{ $payment->note ?? '-' }}
                            </td>

                            <td>
                                {{ $payment->creator->name ?? '-' }}
                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="6" class="text-center">

                                No payments found

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>



{{-- Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="POST" action="{{ route('purchase.payment.store', $purchase->id) }}">

                @csrf

                <div class="modal-header">

                    <h5 class="modal-title">
                        Add Payment
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label>
                            Remaining Balance
                        </label>

                        <input type="text" class="form-control" readonly
                            value="Rs {{ number_format($purchase->balance_amount, 2) }}">

                    </div>

                    <div class="mb-3">

                        <label>
                            Amount
                        </label>

                        <input type="number" step="0.01" max="{{ $purchase->balance_amount }}" name="amount"
                            class="form-control" required>

                    </div>

                    <div class="mb-3">

                        <label>
                            Payment Date
                        </label>

                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="form-control"
                            required>

                    </div>

                    <div class="mb-3">

                        <label>
                            Payment Method
                        </label>

                        <select name="payment_method" class="form-select">

                            <option value="">
                                Select Method
                            </option>

                            <option value="Cash">
                                Cash
                            </option>

                            <option value="Bank Transfer">
                                Bank Transfer
                            </option>

                            <option value="Cheque">
                                Cheque
                            </option>

                        </select>

                    </div>

                    <div class="mb-3">

                        <label>
                            Note
                        </label>

                        <textarea name="note" class="form-control" rows="3"></textarea>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">

                        Close

                    </button>

                    <button type="submit" class="btn btn-primary">

                        Save Payment

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection