
@extends('layouts.app')

@section('title', 'Cost Subcategory Statement')

@section('content')

<div class="container-fluid">

    @include('partials.alerts')

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h3 class="mb-0">
                {{ $subCategory->name }} - Statement
            </h3>

            <small class="text-muted">
                Category: {{ $subCategory->category->name }}
                | Subcategory ID: {{ $subCategory->id }}
            </small>
        </div>

        <div>
            <button type="button" class="btn btn-primary"
                data-toggle="modal" data-target="#payModal">
                <i class="fas fa-plus"></i> Add Payment
            </button>

            <a href="{{ route('costs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

    </div>

    {{-- DATE FILTER --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2">

            <form method="GET"
                action="{{ route('costs.sub-categories.statement', $subCategory->id) }}">

                <div class="form-row align-items-end">

                    <div class="col-md-3">
                        <label class="mb-1">From Date</label>
                        <input type="date" name="from_date"
                            class="form-control" value="{{ $fromDate }}">
                    </div>

                    <div class="col-md-3">
                        <label class="mb-1">To Date</label>
                        <input type="date" name="to_date"
                            class="form-control" value="{{ $toDate }}">
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>

                    <div class="col-md-2">
                        <a href="{{ route('costs.sub-categories.statement', $subCategory->id) }}"
                            class="btn btn-secondary btn-block">
                            Last 7 Days
                        </a>
                    </div>

                    <div class="col-md-2">
                        <a href="{{ route('costs.sub-categories.statement', [
                            'id' => $subCategory->id,
                            'all' => 1
                        ]) }}" class="btn btn-info btn-block">
                            All
                        </a>
                    </div>

                </div>

            </form>

        </div>
    </div>

    {{-- OPENING BALANCE --}}
    @if(!$all)
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-2 d-flex justify-content-between">
                <strong>Opening Balance</strong>

                <strong class="{{ $openingBalance > 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format($openingBalance, 2) }}
                </strong>
            </div>
        </div>
    @endif

    {{-- SUMMARY --}}
    <div class="row mb-4">

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">
                        {{ $all ? 'Total Cost' : 'Period Cost' }}
                    </h6>

                    <h4 class="text-dark">
                        {{ number_format($periodCost, 2) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">
                        {{ $all ? 'Total Paid' : 'Period Paid' }}
                    </h6>

                    <h4 class="text-success">
                        {{ number_format($periodPaid, 2) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Outstanding Balance</h6>

                    <h4 class="{{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($balance, 2) }}
                    </h4>
                </div>
            </div>
        </div>

    </div>

    {{-- SUBCATEGORY DETAILS --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="row">

                <div class="col-md-4">
                    <strong>Category:</strong>
                    {{ $subCategory->category->name }}
                </div>

                <div class="col-md-4">
                    <strong>Subcategory:</strong>
                    {{ $subCategory->name }}
                </div>

                <div class="col-md-4">
                    <strong>Outstanding:</strong>
                    {{ number_format($balance, 2) }}
                </div>

            </div>
        </div>
    </div>

    {{-- STATEMENT TABLE --}}
    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <strong>Cost Transaction Statement</strong>

                @if(!$all)
                    <span class="text-muted small">
                        {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}
                        -
                        {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}
                    </span>
                @else
                    <span class="badge badge-info">All Transactions</span>
                @endif
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">

                <table class="table table-bordered table-sm mb-0 table-erp">

                    <thead class="thead-light">
                        <tr>
                            <th>Date</th>
                            <th>Module</th>
                            <th>Reference</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-right">Debit</th>
                            <th class="text-right">Credit</th>
                            <th class="text-right">Balance</th>
                        </tr>
                    </thead>

                    <tbody>

                        @if(!$all)
                            <tr class="bg-light font-weight-bold">
                                <td>
                                    {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}
                                </td>

                                <td colspan="6">Opening Balance</td>

                                <td class="text-right">
                                    {{ number_format($openingBalance, 2) }}
                                </td>
                            </tr>
                        @endif

                        @forelse($ledger as $row)

                            <tr>
                                <td>
                                    {{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}
                                </td>

                                <td>{{ $row['module'] }}</td>

                                <td>{{ $row['reference'] }}</td>

                                <td>
                                    @if($row['type'] === 'Cost')
                                        <span class="badge badge-danger">Cost</span>
                                    @elseif($row['type'] === 'Payment')
                                        <span class="badge badge-success">Payment</span>
                                    @endif
                                </td>

                                <td>{{ $row['description'] }}</td>

                                <td class="text-right text-danger">
                                    {{ $row['debit'] > 0
                                        ? number_format($row['debit'], 2)
                                        : '-' }}
                                </td>

                                <td class="text-right text-success">
                                    {{ $row['credit'] > 0
                                        ? number_format($row['credit'], 2)
                                        : '-' }}
                                </td>

                                <td class="text-right font-weight-bold">
                                    {{ number_format($row['balance'], 2) }}
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No transactions found.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                    @if($ledger->count())
                        <tfoot class="font-weight-bold bg-light">
                            <tr>
                                <td colspan="5" class="text-right">
                                    Total
                                </td>

                                <td class="text-right text-danger">
                                    {{ number_format($periodCost, 2) }}
                                </td>

                                <td class="text-right text-success">
                                    {{ number_format($periodPaid, 2) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($balance, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif

                </table>

            </div>
        </div>
    </div>

</div>

{{-- ADD COST PAYMENT MODAL --}}
<div class="modal fade" id="payModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">

        <div class="modal-content">

            <form method="POST" id="costPaymentForm" action="">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Add Cost Payment</h5>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Select Cost</label>

                        <select id="payment_cost_id" class="form-control" required>
                            <option value="">Select Cost</option>

                            @foreach($costs as $cost)
                                @if($cost->balance > 0)
                                    <option value="{{ $cost->id }}"
                                        data-balance="{{ $cost->balance }}">
                                        Cost #{{ $cost->id }}
                                        - {{ \Carbon\Carbon::parse($cost->cost_date)->format('d M Y') }}
                                        - Balance: {{ number_format($cost->balance, 2) }}
                                    </option>
                                @endif
                            @endforeach
                        </select>

                        <small class="text-muted" id="costBalanceText"></small>
                    </div>

                    <div class="form-group">
                        <label>Payment Amount</label>

                        <input type="number" name="amount" id="cost_payment_amount"
                            class="form-control" min="0.01" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label>Payment Date</label>

                        <input type="date" name="payment_date"
                            class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Payment Account</label>

                        <select name="cashbook_sub_ledger_id" class="form-control" required>
                            <option value="">Select Cash / Bank Account</option>

                            @foreach($paymentSubLedgers as $subLedger)
                                <option value="{{ $subLedger->id }}">
                                    {{ $subLedger->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Note</label>

                        <textarea name="note" class="form-control" rows="3"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Close
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Payment
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function () {

        let paymentUrl = "{{ route('costs.payments.store', '__COST_ID__') }}";

        $('#payment_cost_id').on('change', function () {

            let costId = $(this).val();
            let balance = parseFloat($(this).find(':selected').data('balance')) || 0;

            if (!costId) {
                $('#costPaymentForm').attr('action', '');
                $('#cost_payment_amount').removeAttr('max');
                $('#costBalanceText').text('');
                return;
            }

            $('#costPaymentForm').attr(
                'action',
                paymentUrl.replace('__COST_ID__', costId)
            );

            $('#cost_payment_amount').attr('max', balance);
            $('#costBalanceText').text(
                'Outstanding balance: ' + balance.toFixed(2)
            );
        });

        $('#costPaymentForm').on('submit', function (e) {

            let selected = $('#payment_cost_id').val();

            if (!selected) {
                e.preventDefault();
                toastr.warning('Please select a cost.');
                return;
            }

            let amount = parseFloat($('#cost_payment_amount').val()) || 0;
            let balance = parseFloat(
                $('#payment_cost_id').find(':selected').data('balance')
            ) || 0;

            if (amount <= 0 || amount > balance) {
                e.preventDefault();
                toastr.warning('Payment amount cannot exceed the outstanding balance.');
            }
        });

    });
</script>
@endpush
