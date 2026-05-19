@extends('layouts.app')

@section('title','Invoice')

@section('content')

<div class="container">

    {{-- ALERTS --}}
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


    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <h3>Sales Details</h3>

        <div>

            <a href="{{ route('sales.invoice', $sale->id) }}" class="btn btn-dark">
                Invoice
            </a>

            @if($sale->balance_amount > 0)

            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#paymentModal">
                Add Payment
            </button>

            @endif

        </div>

    </div>


    {{-- SALES SUMMARY CARD --}}
    <div class="card mb-4">

        <div class="card-header">
            Sale Information
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 mb-3">
                    <strong>Customer</strong><br>
                    {{ $sale->customer->name ?? '-' }}
                </div>

                <div class="col-md-4 mb-3">
                    <strong>Total Amount</strong><br>
                    Rs {{ number_format($sale->total, 2) }}
                </div>

                <div class="col-md-4 mb-3">
                    <strong>Paid Amount</strong><br>
                    Rs {{ number_format($sale->total_paid, 2) }}
                </div>

                <div class="col-md-4 mb-3">
                    <strong>Balance</strong><br>
                    <span class="text-danger fw-bold">
                        Rs {{ number_format($sale->balance_amount, 2) }}
                    </span>
                </div>

                <div class="col-md-4 mb-3">
                    <strong>Status</strong><br>

                    @if($sale->payment_status == 'paid')
                    <span class="badge bg-success">Paid</span>

                    @elseif($sale->payment_status == 'partial')
                    <span class="badge bg-warning">Partial</span>

                    @else
                    <span class="badge bg-danger">Unpaid</span>
                    @endif

                </div>

                <div class="col-md-4 mb-3">
                    <strong>Date</strong><br>
                    {{ date('d M Y', strtotime($sale->created_at)) }}
                </div>

            </div>

        </div>
    </div>


    {{-- PAYMENT HISTORY --}}
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
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($sale->payments as $key => $pay)

                        <tr>
                            <td>{{ $key + 1 }}</td>

                            <td>
                                {{ date('d M Y', strtotime($pay->payment_date)) }}
                            </td>

                            <td>
                                Rs {{ number_format($pay->amount, 2) }}
                            </td>

                            <td>
                                {{ $pay->payment_method ?? '-' }}
                            </td>

                            <td>
                                {{ $pay->note ?? '-' }}
                            </td>
                        </tr>

                        @empty

                        <tr>
                            <td colspan="5" class="text-center">
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

<div class="modal fade" id="paymentModal" tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="POST"
                  action="{{ route('sales.payment.store', $sale->id) }}">

                @csrf

                <div class="modal-header">

                    <h5 class="modal-title">
                        Add Sale Payment
                    </h5>

                    <button type="button"
                            class="close"
                            data-dismiss="modal">
                        <span>&times;</span>
                    </button>

                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Remaining Balance</label>

                        <input type="text"
                               class="form-control"
                               readonly
                               value="Rs {{ number_format($sale->balance_amount, 2) }}">
                    </div>

                    <div class="form-group">
                        <label>Amount</label>

                        <input type="number"
                               step="0.01"
                               max="{{ $sale->balance_amount }}"
                               name="amount"
                               class="form-control"
                               required>
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
                        <label>Payment Method</label>

                        <select name="payment_method"
                                class="form-control">

                            <option value="">Select</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank">Bank</option>
                            <option value="Cheque">Cheque</option>

                        </select>

                    </div>

                    <div class="form-group">
                        <label>Note</label>

                        <textarea name="note"
                                  class="form-control"></textarea>
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal">
                        Close
                    </button>

                    <button type="submit"
                            class="btn btn-primary">
                        Save Payment
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection