@extends('layouts.app')

@section('title', 'Add Cost')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Add New Cost</h4>
        <a href="{{ route('costs.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('costs.store') }}" method="POST">
        @csrf

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Cost Details</h3>
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Cost Date <span class="text-danger">*</span></label>
                            <input type="date" name="cost_date"
                                class="form-control"
                                value="{{ old('cost_date', date('Y-m-d')) }}"
                                required>
                            @error('cost_date') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Category <span class="text-danger">*</span></label>

                            <div class="input-group">
                                <select name="cost_category_id" id="cost_category_id"
                                    class="form-control" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('cost_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </select>

                                <div class="input-group-append">
                                    <button type="button" class="btn btn-success"
                                        data-toggle="modal"
                                        data-target="#categoryModal">
                                        <i class="fas fa-plus"></i> Add
                                    </button>
                                </div>
                            </div>

                            @error('cost_category_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Subcategory <span class="text-danger">*</span></label>

                            <div class="input-group">
                                <select name="cost_sub_category_id"
                                    id="cost_sub_category_id"
                                    class="form-control" required>
                                    <option value="">Select Subcategory</option>
                                </select>

                                <div class="input-group-append">
                                    <button type="button" class="btn btn-success"
                                        data-toggle="modal"
                                        data-target="#subCategoryModal">
                                        <i class="fas fa-plus"></i> Add
                                    </button>
                                </div>
                            </div>

                            @error('cost_sub_category_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Amount (LKR) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01"
                                name="amount" id="amount"
                                class="form-control"
                                value="{{ old('amount') }}"
                                required>
                            @error('amount') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Note</label>
                            <textarea name="note" class="form-control"
                                rows="3">{{ old('note') }}</textarea>
                        </div>
                    </div>

                </div>

                <hr>

                <h5>Payment Details</h5>

                <div class="form-group">
                    <label>Payment Status</label>

                    <div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input class="custom-control-input payment-status"
                                type="radio" id="status_unpaid"
                                name="payment_status" value="unpaid"
                                {{ old('payment_status', 'unpaid') == 'unpaid' ? 'checked' : '' }}>
                            <label class="custom-control-label" for="status_unpaid">Unpaid</label>
                        </div>

                        <div class="custom-control custom-radio custom-control-inline">
                            <input class="custom-control-input payment-status"
                                type="radio" id="status_paid"
                                name="payment_status" value="paid"
                                {{ old('payment_status') == 'paid' ? 'checked' : '' }}>
                            <label class="custom-control-label" for="status_paid">Fully Paid</label>
                        </div>

                        <div class="custom-control custom-radio custom-control-inline">
                            <input class="custom-control-input payment-status"
                                type="radio" id="status_partial"
                                name="payment_status" value="partial"
                                {{ old('payment_status') == 'partial' ? 'checked' : '' }}>
                            <label class="custom-control-label" for="status_partial">Partially Paid</label>
                        </div>
                    </div>
                </div>

                <div id="paymentFields" style="display:none;">
                    <div class="row">

                        <div class="col-md-4" id="paymentAmountField">
                            <div class="form-group">
                                <label>Payment Amount (LKR)</label>
                                <input type="number" step="0.01" min="0.01"
                                    name="payment_amount"
                                    id="payment_amount"
                                    class="form-control"
                                    value="{{ old('payment_amount') }}">
                                @error('payment_amount') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Payment Date</label>
                                <input type="date" name="payment_date"
                                    class="form-control"
                                    value="{{ old('payment_date', date('Y-m-d')) }}">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Cashbook</label>
                                <select name="cashbook_sub_ledger_id" class="form-control">
                                    <option value="">Select Cashbook</option>
                                    @foreach($cashbooks as $cashbook)
                                    <option value="{{ $cashbook->id }}"
                                        {{ old('cashbook_sub_ledger_id') == $cashbook->id ? 'selected' : '' }}>
                                        {{ $cashbook->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('cashbook_sub_ledger_id') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Payment Note</label>
                                <input type="text" name="payment_note"
                                    class="form-control"
                                    value="{{ old('payment_note') }}">
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <div class="card-footer text-right">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Cost
                </button>
                <a href="{{ route('costs.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>


{{-- CATEGORY MODAL --}}
<div class="modal fade" id="categoryModal">
    <div class="modal-dialog">
        <form action="{{ route('costs.categories.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Cost Category</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <label>Category Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Save Category</button>
                </div>
            </div>
        </form>
    </div>
</div>


{{-- SUBCATEGORY MODAL --}}
<div class="modal fade" id="subCategoryModal">
    <div class="modal-dialog">
        <form action="{{ route('costs.sub-categories.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Cost Subcategory</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">

                    <div class="form-group">
                        <label>Category</label>
                        <select name="cost_category_id" id="modal_category_id"
                            class="form-control" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Subcategory Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <small class="text-muted">
                        An expense ledger and accrued expense subledger will be created automatically.
                    </small>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Save Subcategory</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function() {

        function loadSubCategories(categoryId, selectedId = null) {

            $('#cost_sub_category_id').html(
                '<option value="">Loading...</option>'
            );

            if (!categoryId) {
                $('#cost_sub_category_id').html(
                    '<option value="">Select Subcategory</option>'
                );
                return;
            }

            $.get("{{ url('costs/sub-categories') }}/" + categoryId, function(data) {

                let options = '<option value="">Select Subcategory</option>';

                data.forEach(function(item) {
                    let selected = selectedId == item.id ? 'selected' : '';

                    options += `<option value="${item.id}" ${selected}>${item.name}</option>`;
                });

                $('#cost_sub_category_id').html(options);
            });
        }

        $('#cost_category_id').on('change', function() {
            loadSubCategories($(this).val());
        });

        $('#modal_category_id').on('change', function() {
            $('#cost_category_id').val($(this).val()).trigger('change');
        });

        let oldCategory = "{{ old('cost_category_id') }}";
        let oldSubCategory = "{{ old('cost_sub_category_id') }}";

        if (oldCategory) {
            loadSubCategories(oldCategory, oldSubCategory);
        }

        function togglePaymentFields() {
            let status = $('input[name="payment_status"]:checked').val();

            if (status === 'paid' || status === 'partial') {
                $('#paymentFields').show();
                $('#payment_amount').prop('required', status === 'partial');

                if (status === 'paid') {
                    $('#paymentAmountField').hide();
                    $('#payment_amount').val('');
                } else {
                    $('#paymentAmountField').show();
                }
            } else {
                $('#paymentFields').hide();
                $('#payment_amount').prop('required', false);
            }
        }

        $('.payment-status').on('change', togglePaymentFields);

        togglePaymentFields();
    });
</script>
@endpush