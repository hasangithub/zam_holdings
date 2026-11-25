
@extends('layouts.app')

@section('title', 'Customer Currency Statement')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h4 class="mb-1">
                Freight Currency Statement
            </h4>

            <div class="text-muted">
                {{ $freightService->name }}
                |
                {{ $currency }} - {{ $currencyOptions[$currency] }}
            </div>
        </div>

        <div>
            <a href="{{ route('freight-services.statement', $freightService->id) }}"
               class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> LKR Statement
            </a>
        </div>

    </div>

    {{-- SUMMARY --}}
    <div class="row">

        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($openingBalance, 2) }}</h4>
                    <p>Opening Balance</p>
                </div>
                <div class="icon">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>{{ number_format($periodSales, 2) }}</h4>
                    <p>Invoice Total ({{ $currency }})</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>{{ number_format($periodPaid, 2) }}</h4>
                    <p>Payments ({{ $currency }})</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h4>{{ number_format($balance, 2) }}</h4>
                    <p>Closing Balance ({{ $currency }})</p>
                </div>
                <div class="icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- ACTIONS --}}
    <div class="card card-default">

        <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between">

                <div class="mb-2">
                    <button type="button"
                            class="btn btn-success btn-sm"
                            data-toggle="modal"
                            data-target="#currencyPaymentModal">
                        <i class="fas fa-plus"></i>
                        Add Payment
                    </button>

                </div>

                <div class="mb-2">

                    <button type="button"
                            class="btn btn-info btn-sm"
                            data-toggle="modal"
                            data-target="#paymentHistoryModal">
                        <i class="fas fa-history"></i>
                        Payment History
                    </button>

                </div>

            </div>

        </div>

    </div>

    {{-- DATE FILTER --}}
    <div class="card card-default">

        <div class="card-body">

            <form method="GET"
                  action="{{ route('freight-expenses.currency-statement', [$freightService->id, $currency]) }}">

                <div class="row align-items-end">

                    <div class="col-md-3">
                        <label>From Date</label>
                        <input type="date"
                               name="from_date"
                               class="form-control"
                               value="{{ $fromDate }}">
                    </div>

                    <div class="col-md-3">
                        <label>To Date</label>
                        <input type="date"
                               name="to_date"
                               class="form-control"
                               value="{{ $toDate }}">
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>

                    <div class="col-md-2">
                        <a href="{{ route('freight-expenses.currency-statement', [$freightService->id, $currency]) }}?all=1"
                           class="btn btn-secondary btn-block">
                            All Transactions
                        </a>
                    </div>

                    <div class="col-md-2">
                        <a href="{{ route('freight-expenses.currency-statement', [$freightService->id, $currency]) }}"
                           class="btn btn-default btn-block">
                            Reset
                        </a>
                    </div>

                </div>

            </form>

        </div>

    </div>

    {{-- STATEMENT TABLE --}}
    <div class="card card-default">

        <div class="card-header">
            <h3 class="card-title">
                {{ $currency }} Transactions
            </h3>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered table-hover table-sm mb-0 table-erp">

                    <thead class="thead-light">
                        <tr>
                            <th width="100">Date</th>
                            <th width="120">Reference</th>
                            <th width="130">Type</th>
                            <th>Description</th>
                            <th class="text-right" width="140">Debit ({{ $currency }})</th>
                            <th class="text-right" width="140">Credit ({{ $currency }})</th>
                            <th class="text-right" width="150">Balance ({{ $currency }})</th>
                        </tr>
                    </thead>

                    <tbody>

                        @if(!$all && $openingBalance != 0)
                            <tr class="bg-light font-weight-bold">
                                <td colspan="6" class="text-right">
                                    Opening Balance
                                </td>

                                <td class="text-right">
                                    {{ number_format($openingBalance, 2) }}
                                </td>
                            </tr>
                        @endif

                        @forelse($ledger as $row)

                            <tr>

                                <td>
                                    {{ \Carbon\Carbon::parse($row['date'])->format('Y-m-d') }}
                                </td>

                                <td>
                                    {{ $row['reference'] ?? '-' }}
                                </td>

                                <td>
                                    @if($row['type'] == 'Invoice')
                                        <span class="badge badge-primary">Invoice</span>
                                    @elseif($row['type'] == 'Payment')
                                        <span class="badge badge-success">Payment</span>
                                    @else
                                        <span class="badge badge-warning">
                                            Opening Balance
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $row['description'] ?? '-' }}
                                </td>

                                <td class="text-right">
                                    {{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '-' }}
                                </td>

                                <td class="text-right">
                                    {{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '-' }}
                                </td>

                                <td class="text-right font-weight-bold">
                                    {{ number_format($row['balance'], 2) }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="text-center text-muted p-3">
                                    No transactions found.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                    <tfoot class="font-weight-bold bg-light">

                        <tr>
                            <td colspan="4" class="text-right">
                                Period Total
                            </td>

                            <td class="text-right">
                                {{ number_format($periodSales, 2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($periodPaid, 2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($balance, 2) }}
                            </td>
                        </tr>

                    </tfoot>

                </table>

            </div>

        </div>

    </div>

</div>

{{-- ADD PAYMENT MODAL --}}
<div class="modal fade" id="currencyPaymentModal" tabindex="-1">

    <div class="modal-dialog">

        <form method="POST"
              action="{{ route('freight-expenses.currency-payments.store', [$freightService->id, $currency]) }}">

            @csrf

            <div class="modal-content">

                <div class="modal-header bg-success">
                    <h5 class="modal-title">
                        Add {{ $currency }} Payment
                    </h5>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Customer</label>
                        <input type="text"
                               class="form-control"
                               value="{{ $freightService->name }}"
                               readonly>
                    </div>

                    <div class="form-group">
                        <label>Currency</label>
                        <input type="text"
                               class="form-control"
                               value="{{ $currency }}"
                               readonly>
                    </div>

                    <div class="form-group">
                        <label>Freight Payment</label>
                        <select name="freight_id" class="form-control" required>
                            <option value="">Select Freights</option>
                            @foreach($freights as $freight)
                            <option value="{{ $freight->id }}">
                                {{ $freight->invoice_id }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Payment Date</label>
                        <input type="date"
                               name="payment_date"
                               class="form-control"
                               value="{{ now()->format('Y-m-d') }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Payment Amount ({{ $currency }})</label>
                        <input type="number"
                               name="amount"
                               step="0.01"
                               min="0.01"
                               class="form-control"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Reference</label>
                        <input type="text"
                               name="reference"
                               class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Note</label>
                        <textarea name="note"
                                  class="form-control"
                                  rows="2"></textarea>
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal">
                        Close
                    </button>

                    <button type="submit" class="btn btn-success">
                        Save Payment
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

{{-- PAYMENT HISTORY MODAL --}}
<div class="modal fade" id="paymentHistoryModal" tabindex="-1">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header bg-info">
                <h5 class="modal-title">
                    {{ $currency }} Payment History
                </h5>

                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-sm table-erp">

                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Note</th>
                                <th class="text-right">Amount</th>
                                <th width="100">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($currencyPayments as $payment)

                                <tr>

                                    <td>
                                        {{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}
                                    </td>

                                    <td>{{ $payment->reference ?? '-' }}</td>

                                    <td>{{ $payment->note ?? '-' }}</td>

                                    <td class="text-right">
                                        {{ number_format($payment->amount, 2) }}
                                    </td>

                                    <td>

                                        <button type="button"
                                                class="btn btn-xs btn-primary"
                                                data-toggle="modal"
                                                data-target="#editPaymentModal{{ $payment->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <form method="POST"
                                              action="{{ route('freight-expenses.currency-payments.delete', [$freightService->id, $currency, $payment->id]) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Delete this payment?')">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="btn btn-xs btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                                {{-- EDIT PAYMENT MODAL --}}
                                <div class="modal fade"
                                     id="editPaymentModal{{ $payment->id }}"
                                     tabindex="-1">

                                    <div class="modal-dialog">

                                        <form method="POST"
                                              action="{{ route('freight-expenses.currency-payments.update', [$freightService->id, $currency, $payment->id]) }}">

                                            @csrf
                                            @method('PUT')

                                            <div class="modal-content">

                                                <div class="modal-header bg-primary">
                                                    <h5 class="modal-title">
                                                        Edit {{ $currency }} Payment
                                                    </h5>

                                                    <button type="button"
                                                            class="close"
                                                            data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>

                                                <div class="modal-body">

                                                    <div class="form-group">
                                                        <label>Payment Date</label>

                                                        <input type="date"
                                                               name="payment_date"
                                                               class="form-control"
                                                               value="{{ $payment->payment_date }}"
                                                               required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Amount ({{ $currency }})</label>

                                                        <input type="number"
                                                               name="amount"
                                                               step="0.01"
                                                               min="0.01"
                                                               class="form-control"
                                                               value="{{ $payment->amount }}"
                                                               required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Reference</label>

                                                        <input type="text"
                                                               name="reference"
                                                               class="form-control"
                                                               value="{{ $payment->reference }}">
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Note</label>

                                                        <textarea name="note"
                                                                  class="form-control"
                                                                  rows="2">{{ $payment->note }}</textarea>
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
                                                        Update Payment
                                                    </button>

                                                </div>

                                            </div>

                                        </form>

                                    </div>

                                </div>

                            @empty

                                <tr>
                                    <td colspan="5" class="text-center">
                                        No currency payments found.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal">
                    Close
                </button>
            </div>

        </div>

    </div>

</div>

@endsection
