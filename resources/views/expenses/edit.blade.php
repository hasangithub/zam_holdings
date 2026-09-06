@extends('layouts.app')

@section('title','Edit Expense')

@section('content')
<div class="container-fluid">

    <div class="card card-primary card-outline">

        <div class="card-header py-2">
            <h3 class="card-title">
                <i class="fas fa-edit mr-1"></i> Edit Expense
            </h3>
        </div>

        <form method="POST" action="{{ route('expenses.update', $expense->id) }}">
            @csrf
            @method('PUT')

            <div class="card-body py-3">

                <div class="row">

                    <div class="col-md-4">
                        <div class="form-group mb-2">
                            <label class="mb-1">Expense Category</label>

                            <select name="expense_category_id"
                                    class="form-control"
                                    required>

                                <option value="">Select Category</option>

                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}"
                                        {{ $expense->expense_category_id == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach

                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label class="mb-1">Date</label>

                            <input type="date"
                                   name="expense_date"
                                   value="{{ $expense->expense_date }}"
                                   class="form-control"
                                   required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="mb-1">Amount</label>

                            <input type="number"
                                   name="amount"
                                   value="{{ $expense->amount }}"
                                   step="0.01"
                                   min="0.01"
                                   class="form-control"
                                   required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="mb-1">Payment Status</label>

                            <select name="payment_status"
                                    id="payment_status"
                                    class="form-control"
                                    required>

                                <option value="paid"
                                    {{ $expense->payment_status == 'paid' ? 'selected' : '' }}>
                                    Paid
                                </option>

                                <option value="unpaid"
                                    {{ $expense->payment_status == 'unpaid' ? 'selected' : '' }}>
                                    Unpaid / Accrued
                                </option>

                            </select>
                        </div>
                    </div>

                </div>

                {{-- Cash / Bank Account --}}
                <div class="form-group mb-2"
                     id="cash_bank_box"
                     style="{{ $expense->payment_status == 'paid' ? '' : 'display:none;' }}">

                    <label class="mb-1">Cash / Bank Account</label>

                    <select name="payment_sub_ledger_id"
                            id="payment_sub_ledger_id"
                            class="form-control">

                        <option value="">Select Cash / Bank Account</option>

                        @foreach($cashBankSubLedgers as $subLedger)
                            <option value="{{ $subLedger->id }}"
                                {{ $expense->payment_sub_ledger_id == $subLedger->id ? 'selected' : '' }}>
                                {{ $subLedger->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div class="form-group mb-0">
                    <label class="mb-1">Note</label>

                    <textarea name="remarks"
                              class="form-control"
                              rows="2"
                              placeholder="Optional">{{ $expense->remarks }}</textarea>
                </div>

            </div>

            <div class="card-footer py-2">

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save mr-1"></i> Update Expense
                </button>

                <a href="{{ route('expenses.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>
@endsection

@push('scripts')
<script>
$(function () {

    function updatePaymentBox() {

        let status = $('#payment_status').val();

        $('#cash_bank_box').hide();
        $('#payment_sub_ledger_id').prop('required', false);

        if (status === 'paid') {
            $('#cash_bank_box').show();
            $('#payment_sub_ledger_id').prop('required', true);
        }
    }

    $('#payment_status').on('change', updatePaymentBox);

    updatePaymentBox();

});
</script>
@endpush

