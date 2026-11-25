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
            @if(!$subCategory->opening_journal_entry_id)

            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#openingBalanceModal">

                <i class="fas fa-balance-scale"></i>
                Add Opening Balance

            </button>

            @else

            <span class="badge badge-success p-2">
                Opening Balance Added
            </span>

            @endif

            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#payModal">
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

            <form method="GET" action="{{ route('costs.sub-categories.statement', $subCategory->id) }}">

                <div class="form-row align-items-end">

                    <div class="col-md-3">
                        <label class="mb-1">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                    </div>

                    <div class="col-md-3">
                        <label class="mb-1">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
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
                            <th class="text-center" width="80">Action</th>
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
                            <td class="text-center">

                                @if($row['type'] === 'Cost')

                                <button type="button" class="btn btn-xs btn-warning edit-cost-btn"
                                    data-id="{{ $row['id'] }}" data-date="{{ $row['date'] }}"
                                    data-amount="{{ $row['debit'] }}" title="Edit Cost">

                                    <i class="fas fa-edit"></i>

                                </button>

                                @elseif($row['type'] === 'Payment')

                                @php
                                $paymentRecord = $payments->firstWhere('id', $row['payment_id']);
                                @endphp

                                @if($paymentRecord)

                                <button type="button" class="btn btn-xs btn-warning edit-payment-btn"
                                    data-id="{{ $paymentRecord->id }}"
                                    data-date="{{ \Carbon\Carbon::parse($paymentRecord->payment_date)->format('Y-m-d') }}"
                                    data-amount="{{ $paymentRecord->amount }}"
                                    data-cashbook="{{ $paymentRecord->cashbook_sub_ledger_id }}" title="Edit Payment">

                                    <i class="fas fa-edit"></i>

                                </button>

                                @endif

                                @else

                                <span class="text-muted">-</span>

                                @endif

                            </td>
                        </tr>

                        @empty

                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
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
                            <option value="{{ $cost->id }}" data-balance="{{ $cost->balance }}">
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

                        <input type="number" name="amount" id="cost_payment_amount" class="form-control" min="0.01"
                            step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label>Payment Date</label>

                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}"
                            required>
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

{{-- OPENING BALANCE MODAL --}}
<div class="modal fade" id="openingBalanceModal" tabindex="-1" role="dialog">

    <div class="modal-dialog" role="document">

        <div class="modal-content">

            <form method="POST" action="{{ route('costs.sub-categories.opening-balance', $subCategory->id) }}">

                @csrf

                <div class="modal-header bg-warning">

                    <h5 class="modal-title">
                        <i class="fas fa-balance-scale"></i>
                        Add Opening Balance
                    </h5>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>

                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Cost Category</label>

                        <input type="text" class="form-control" value="{{ $subCategory->category->name }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Cost Subcategory</label>

                        <input type="text" class="form-control" value="{{ $subCategory->name }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Opening Balance Date</label>

                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Opening Balance Amount</label>

                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01"
                            placeholder="Enter opening balance" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>

                        <textarea name="description" class="form-control" rows="2"
                            placeholder="Opening balance description"></textarea>
                    </div>

                    <div class="alert alert-info mb-0">
                        <small>
                            This will create an opening balance journal
                            for this subcategory.
                        </small>
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Close
                    </button>

                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i>
                        Save Opening Balance
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

{{-- EDIT COST MODAL --}}
<div class="modal fade" id="editCostModal" tabindex="-1" role="dialog">

    <div class="modal-dialog" role="document">

        <div class="modal-content">

            <form method="POST" id="editCostForm">

                @csrf
                @method('PUT')

                <div class="modal-header bg-warning">

                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> Edit Cost
                    </h5>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>

                </div>

                <div class="modal-body">

                    <input type="hidden" id="edit_cost_id">

                    <div class="form-group">
                        <label>Cost Date</label>

                        <input type="date" name="cost_date" id="edit_cost_date" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Cost Amount</label>

                        <input type="number" name="amount" id="edit_cost_amount" class="form-control" min="0.01"
                            step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label>Note</label>

                        <input type="text" name="note" id="edit_cost_note" class="form-control">
                    </div>

                    <small class="text-muted">
                        The cost amount cannot be less than the total payments already made.
                    </small>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Cost
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

{{-- EDIT PAYMENT MODAL --}}
<div class="modal fade" id="editPaymentModal" tabindex="-1" role="dialog">

    <div class="modal-dialog" role="document">

        <div class="modal-content">

            <form method="POST" id="editPaymentForm">

                @csrf
                @method('PUT')

                <div class="modal-header bg-warning">

                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> Edit Cost Payment
                    </h5>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>

                </div>

                <div class="modal-body">

                    <input type="hidden" id="edit_payment_id">

                    <div class="form-group">
                        <label>Payment Date</label>

                        <input type="date" name="payment_date" id="edit_payment_date" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Payment Amount</label>

                        <input type="number" name="amount" id="edit_payment_amount" class="form-control" min="0.01"
                            step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label>Cash / Bank Account</label>

                        <select name="cashbook_sub_ledger_id" id="edit_payment_cashbook" class="form-control" required>

                            <option value="">Select Account</option>

                            @foreach($paymentSubLedgers as $subLedger)

                            <option value="{{ $subLedger->id }}">
                                {{ $subLedger->name }}
                            </option>

                            @endforeach

                        </select>
                    </div>

                    <div class="form-group">
                        <label>Note</label>

                        <input type="text" name="note" id="edit_cost_note" class="form-control">
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Payment
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {

    let paymentUrl = "{{ route('costs.payments.store', '__COST_ID__') }}";

    $('#payment_cost_id').on('change', function() {

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

    $('#costPaymentForm').on('submit', function(e) {

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

    /*
    |--------------------------------------------------------------------------
    | Edit Cost
    |--------------------------------------------------------------------------
    */

    $('.edit-cost-btn').on('click', function() {

        let id = $(this).data('id');
        let date = $(this).data('date');
        let amount = $(this).data('amount');

        $('#edit_cost_id').val(id);
        $('#edit_cost_date').val(date);
        $('#edit_cost_amount').val(amount);

        let url = "{{ route('costs.update', ':id') }}";
        url = url.replace(':id', id);

        $('#editCostForm').attr('action', url);

        $('#editCostModal').modal('show');
    });


    /*
    |--------------------------------------------------------------------------
    | Edit Payment
    |--------------------------------------------------------------------------
    */

    $('.edit-payment-btn').on('click', function() {

        let id = $(this).data('id');
        let date = $(this).data('date');
        let amount = $(this).data('amount');
        let cashbook = $(this).data('cashbook');

        $('#edit_payment_id').val(id);
        $('#edit_payment_date').val(date);
        $('#edit_payment_amount').val(amount);
        $('#edit_payment_cashbook').val(cashbook);

        let url = "{{ route('costs.payments.update', ':id') }}";
        url = url.replace(':id', id);

        $('#editPaymentForm').attr('action', url);

        $('#editPaymentModal').modal('show');
    });

});
</script>
@endpush