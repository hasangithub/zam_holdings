
@extends('layouts.app')

@section('title', 'Cost Statement')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4>Cost Statement #{{ $cost->id }}</h4>
            <small class="text-muted">
                {{ $cost->category->name ?? '-' }}
                /
                {{ $cost->subCategory->name ?? '-' }}
            </small>
        </div>

        <a href="{{ route('costs.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">

        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h4>{{ number_format($cost->amount, 2) }}</h4>
                    <p>Total Cost</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>{{ number_format($cost->paid_amount, 2) }}</h4>
                    <p>Total Paid</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>{{ number_format($cost->balance, 2) }}</h4>
                    <p>Outstanding</p>
                </div>
                <div class="icon"><i class="fas fa-balance-scale"></i></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ $cost->payment_status }}</h4>
                    <p>Payment Status</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>

    </div>


    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Cost Details</h3>

            @if($cost->balance > 0)
                <div class="card-tools">
                    <button class="btn btn-primary btn-sm"
                            data-toggle="modal"
                            data-target="#paymentModal">
                        <i class="fas fa-plus"></i> Add Payment
                    </button>
                </div>
            @endif
        </div>

        <div class="card-body">

            <div class="row">
                <div class="col-md-3">
                    <strong>Cost Date:</strong><br>
                    {{ $cost->cost_date->format('Y-m-d') }}
                </div>

                <div class="col-md-3">
                    <strong>Category:</strong><br>
                    {{ $cost->category->name ?? '-' }}
                </div>

                <div class="col-md-3">
                    <strong>Subcategory:</strong><br>
                    {{ $cost->subCategory->name ?? '-' }}
                </div>

                <div class="col-md-3">
                    <strong>Note:</strong><br>
                    {{ $cost->note ?: '-' }}
                </div>
            </div>

        </div>
    </div>


    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payment History</h3>
        </div>

        <div class="card-body table-responsive p-0">

            <table class="table table-bordered table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Payment Date</th>
                        <th>Cashbook</th>
                        <th>Note</th>
                        <th class="text-right">Amount (LKR)</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($cost->payments->sortBy('payment_date') as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                            <td>{{ $payment->cashbookSubLedger->name ?? '-' }}</td>
                            <td>{{ $payment->note ?? '-' }}</td>
                            <td class="text-right">
                                {{ number_format($payment->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No payments recorded.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if($cost->payments->count())
                    <tfoot>
                        <tr class="font-weight-bold">
                            <td colspan="4" class="text-right">Total Paid</td>
                            <td class="text-right">
                                {{ number_format($cost->paid_amount, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>

        </div>
    </div>

</div>


{{-- ADD PAYMENT MODAL --}}
@if($cost->balance > 0)
<div class="modal fade" id="paymentModal">
    <div class="modal-dialog">
        <form method="POST"
              action="{{ route('costs.payments.store', $cost->id) }}">
            @csrf

            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Add Cost Payment</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        &times;
                    </button>
                </div>

                <div class="modal-body">

                    <div class="alert alert-info">
                        Outstanding Balance:
                        <strong>LKR {{ number_format($cost->balance, 2) }}</strong>
                    </div>

                    <div class="form-group">
                        <label>Payment Date</label>
                        <input type="date" name="payment_date"
                               class="form-control"
                               value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Payment Amount (LKR)</label>
                        <input type="number" name="amount"
                               class="form-control"
                               step="0.01" min="0.01"
                               max="{{ $cost->balance }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Cashbook</label>
                        <select name="cashbook_sub_ledger_id"
                                class="form-control" required>
                            <option value="">Select Cashbook</option>

                            @foreach($cashbooks as $cashbook)
                                <option value="{{ $cashbook->id }}">
                                    {{ $cashbook->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Note</label>
                        <textarea name="note" class="form-control"
                                  rows="2"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Payment
                    </button>

                    <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">Close</button>
                </div>

            </div>
        </form>
    </div>
</div>
@endif

@endsection
