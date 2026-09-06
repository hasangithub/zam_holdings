@extends('layouts.app')

@section('title','Create Expense')

@section('content')
<div class="container-fluid">

    <div class="card card-primary card-outline">

        <div class="card-header py-2">
            <h3 class="card-title">
                <i class="fas fa-shopping-cart mr-1"></i> Create Expense
            </h3>
        </div>

        <form method="POST" action="{{ route('expenses.store') }}">
            @csrf

            <div class="card-body py-3">

                <div class="row">

                    <div class="col-md-4">
                        <div class="form-group mb-2">
                            <label class="mb-1">Expense Category</label>
                            <select name="expense_category_id"
                                    id="expense_category"
                                    class="form-control"
                                    required>
                                <option value="">Select Category</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}">
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
                                   value="{{ date('Y-m-d') }}"
                                   class="form-control"
                                   required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="mb-1">Amount</label>
                            <input type="number"
                                   name="amount"
                                   step="0.01"
                                   min="0.01"
                                   class="form-control"
                                   placeholder="0.00"
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
                                <option value="">Select Status</option>
                                <option value="paid">Paid</option>
                                <option value="unpaid">Unpaid / Accrued</option>
                            </select>
                        </div>
                    </div>

                </div>

                {{-- Paid Account --}}
                <div class="form-group mb-2"
                     id="cash_bank_box"
                     style="display:none;">

                    <label class="mb-1">Cash / Bank Account</label>

                    <select name="payment_sub_ledger_id"
                            id="payment_sub_ledger_id"
                            class="form-control">

                        <option value="">Select Cash / Bank Account</option>

                        @foreach($cashBankSubLedgers as $subLedger)
                            <option value="{{ $subLedger->id }}">
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
                              placeholder="Optional"></textarea>
                </div>

            </div>

            <div class="card-footer py-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save mr-1"></i> Save Expense
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

    $('#payment_status').on('change', function () {

        let status = $(this).val();

        $('#cash_bank_box').hide();

        $('#payment_sub_ledger_id')
            .prop('required', false);

        if (status === 'paid') {
            $('#cash_bank_box').show();
            $('#payment_sub_ledger_id').prop('required', true);
        }

        if (status === 'unpaid') {
           
        }

    });

});
</script>
@endpush

