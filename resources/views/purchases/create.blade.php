
@extends('layouts.app')

<style>
    .ts-dropdown {
        z-index: 99999 !important;
    }

    .card,
    .card-body {
        overflow: visible !important;
    }

    .wizard-step {
        display: none;
    }

    .wizard-step.active {
        display: block;
    }

    .wizard-progress {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
    }

    .wizard-progress .step {
        flex: 1;
        padding: 12px;
        text-align: center;
        border-radius: 4px;
        background: #e9ecef;
        color: #495057;
        font-weight: 600;
    }

    .wizard-progress .step.active {
        background: #343a40;
        color: #fff;
    }

    .wizard-progress .step.completed {
        background: #28a745;
        color: #fff;
    }

    .wizard-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
    }

    .payment-fields {
        display: none;
    }

    .payment-fields.active {
        display: block;
    }
</style>


@section('title', 'Create Purchase')

@section('content')
<div class="container-fluid">

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-dark">
            <h3 class="card-title text-white">
                <i class="fas fa-shopping-cart mr-1"></i>
                Create Purchase
            </h3>
        </div>

        <form action="{{ route('purchases.store') }}"
              method="POST"
              id="purchaseForm">
            @csrf

            <input type="hidden"
                   name="request_token"
                   value="{{ old('request_token', (string) \Illuminate\Support\Str::uuid()) }}">

            <div class="card-body">

                <div class="wizard-progress">
                    <div class="step active" data-step-label="1">
                        1. Supplier & Items
                    </div>
                    <div class="step" data-step-label="2">
                        2. Purchase Details
                    </div>
                    <div class="step" data-step-label="3">
                        3. Payment
                    </div>
                </div>

                {{-- STEP 1 --}}
                <div class="wizard-step active" data-step="1">

                    <h5 class="mb-3">Supplier Information</h5>

                    <div class="form-group">
                        <label>Supplier Type</label>

                        <div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio"
                                       id="existingSupplier"
                                       name="is_new_supplier"
                                       value="0"
                                       class="custom-control-input"
                                       checked>
                                <label class="custom-control-label"
                                       for="existingSupplier">
                                    Existing Supplier
                                </label>
                            </div>

                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio"
                                       id="newSupplier"
                                       name="is_new_supplier"
                                       value="1"
                                       class="custom-control-input"
                                       {{ old('is_new_supplier') == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label"
                                       for="newSupplier">
                                    Create New Supplier
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="existingSupplierFields">
                        <div class="form-group">
                            <label>
                                Select Supplier <span class="text-danger">*</span>
                            </label>

                            <select name="supplier_id"
                                    id="supplier_id"
                                    class="form-control"
                                    required>
                                <option value="">Search supplier...</option>

                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                        {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="newSupplierFields" style="display:none;">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>
                                    Supplier Name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="new_supplier_name"
                                       id="new_supplier_name"
                                       class="form-control"
                                       maxlength="255"
                                       value="{{ old('new_supplier_name') }}">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Phone</label>
                                <input type="text"
                                       name="new_supplier_phone"
                                       id="new_supplier_phone"
                                       class="form-control"
                                       maxlength="30"
                                       value="{{ old('new_supplier_phone') }}">
                            </div>
                        </div>

                        <small class="text-muted">
                            The new supplier will be saved as Trading Goods.
                        </small>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label>
                            Purchase Date <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                               name="purchase_date"
                               id="purchase_date"
                               class="form-control"
                               value="{{ old('purchase_date', date('Y-m-d')) }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label>
                            Select Items <span class="text-danger">*</span>
                        </label>

                        <select id="selected_item_ids"
                                class="form-control"
                                multiple
                                required>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}"
                                    data-name="{{ $item->name }}">
                                    {{ $item->name }}
                                </option>
                            @endforeach
                        </select>

                        <small class="form-text text-muted">
                            Search and select all items for this purchase.
                        </small>
                    </div>

                    <div class="wizard-actions justify-content-end">
                        <button type="button"
                                class="btn btn-primary"
                                id="toStep2">
                            Next: Enter Quantities & Prices
                            <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>

                {{-- STEP 2 --}}
                <div class="wizard-step" data-step="2">

                    <h5 class="mb-3">Purchase Item Details</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm"
                               id="purchaseTable">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th style="width:40%">Item</th>
                                    <th style="width:20%">Quantity</th>
                                    <th style="width:20%">Purchase Price</th>
                                    <th style="width:20%">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class="col-sm-5 col-form-label text-right">
                                    Total
                                </label>
                                <div class="col-sm-7">
                                    <input type="text"
                                           id="total"
                                           class="form-control font-weight-bold"
                                           value="0.00"
                                           readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="wizard-actions">
                        <button type="button"
                                class="btn btn-secondary"
                                data-prev="1">
                            <i class="fas fa-arrow-left mr-1"></i> Back
                        </button>

                        <button type="button"
                                class="btn btn-primary"
                                id="toStep3">
                            Next: Payment
                            <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>

                {{-- STEP 3 --}}
                <div class="wizard-step" data-step="3">

                    <h5 class="mb-3">Payment Information</h5>

                    <div class="alert alert-light border">
                        <div class="d-flex justify-content-between">
                            <span>Purchase Total</span>
                            <strong id="paymentTotal">0.00</strong>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Has this purchase been paid?</label>

                        <div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio"
                                       id="paidYes"
                                       name="is_paid"
                                       value="1"
                                       class="custom-control-input"
                                       {{ old('is_paid', '0') == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="paidYes">
                                    Yes, paid
                                </label>
                            </div>

                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio"
                                       id="paidNo"
                                       name="is_paid"
                                       value="0"
                                       class="custom-control-input"
                                       {{ old('is_paid', '0') == '0' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="paidNo">
                                    No, pay later
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="paymentFields" class="payment-fields">

                        <div class="form-group">
                            <label>
                                Cash / Bank Account <span class="text-danger">*</span>
                            </label>

                            <select name="sub_ledger_id"
                                    id="sub_ledger_id"
                                    class="form-control">
                                <option value="">Select cash/bank account...</option>

                                @foreach($cashSubLedgers as $subLedger)
                                    <option value="{{ $subLedger->id }}"
                                        {{ old('sub_ledger_id') == $subLedger->id ? 'selected' : '' }}>
                                        {{ $subLedger->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                Amount Paid <span class="text-danger">*</span>
                            </label>

                            <input type="number"
                                   name="payment_amount"
                                   id="payment_amount"
                                   class="form-control"
                                   min="0.01"
                                   step="0.01"
                                   value="{{ old('payment_amount') }}">
                            <small class="form-text text-muted">
                                You may enter a partial payment. The remaining amount
                                will stay as the supplier balance.
                            </small>
                        </div>
                    </div>

                    <div class="wizard-actions">
                        <button type="button"
                                class="btn btn-secondary"
                                data-prev="2">
                            <i class="fas fa-arrow-left mr-1"></i> Back
                        </button>

                        <div>
                            <a href="{{ route('purchases.index') }}"
                               class="btn btn-outline-secondary">
                                Cancel
                            </a>

                            <button type="submit"
                                    class="btn btn-success"
                                    id="savePurchase">
                                <i class="fas fa-save mr-1"></i>
                                Save Purchase
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('purchaseForm');
    const steps = document.querySelectorAll('.wizard-step');
    const stepLabels = document.querySelectorAll('.wizard-progress .step');

    const supplierRadios = document.querySelectorAll('[name="is_new_supplier"]');
    const existingSupplierFields = document.getElementById('existingSupplierFields');
    const newSupplierFields = document.getElementById('newSupplierFields');
    const supplierSelect = document.getElementById('supplier_id');
    const newSupplierName = document.getElementById('new_supplier_name');

    const itemPickerElement = document.getElementById('selected_item_ids');
    const itemPicker = new TomSelect(itemPickerElement, {
        plugins: ['remove_button'],
        create: false,
        maxItems: null,
        placeholder: 'Search and select items...',
        dropdownParent: 'body'
    });

    const supplierPicker = new TomSelect(supplierSelect, {
        create: false,
        maxItems: 1,
        allowEmptyOption: true,
        placeholder: 'Search supplier...',
        dropdownParent: 'body'
    });

    let currentStep = 1;

    function showStep(stepNumber) {
        currentStep = stepNumber;

        steps.forEach(function (step) {
            step.classList.toggle(
                'active',
                Number(step.dataset.step) === stepNumber
            );
        });

        stepLabels.forEach(function (label) {
            const number = Number(label.dataset.stepLabel);
            label.classList.toggle('active', number === stepNumber);
            label.classList.toggle('completed', number < stepNumber);
        });

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function updateSupplierFields() {
        const isNew = document.getElementById('newSupplier').checked;

        existingSupplierFields.style.display = isNew ? 'none' : 'block';
        newSupplierFields.style.display = isNew ? 'block' : 'none';

        supplierSelect.required = !isNew;
        newSupplierName.required = isNew;

        if (isNew) {
            supplierPicker.clear();
        } else {
            newSupplierName.value = '';
        }
    }

    supplierRadios.forEach(function (radio) {
        radio.addEventListener('change', updateSupplierFields);
    });

    updateSupplierFields();

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (character) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[character];
        });
    }

    function buildItemRows() {
        const selectedIds = itemPicker.getValue();
        const tbody = document.querySelector('#purchaseTable tbody');

        if (!selectedIds.length) {
            alert('Please select at least one item.');
            return false;
        }

        tbody.innerHTML = '';

        selectedIds.forEach(function (id, index) {
            const option = itemPicker.options[id];

            if (!option) {
                return;
            }

            const itemName = option.text;

            const row = document.createElement('tr');

            row.innerHTML = `
                <td>
                    ${escapeHtml(itemName)}
                    <input type="hidden"
                           name="items[${index}][item_id]"
                           value="${escapeHtml(id)}">
                </td>
                <td>
                    <input type="number"
                           name="items[${index}][qty]"
                           class="form-control form-control-sm qty"
                           min="0.001"
                           step="0.001"
                           value=""
                           required>
                </td>
                <td>
                    <input type="number"
                           name="items[${index}][price]"
                           class="form-control form-control-sm price"
                           min="0"
                           step="0.01"
                           value=""
                           required>
                </td>
                <td>
                    <input type="text"
                           class="form-control form-control-sm subtotal"
                           value="0.00"
                           readonly>
                </td>
            `;

            tbody.appendChild(row);
        });

        calculateTotal();
        return true;
    }

    function calculateRow(row) {
        const qty = parseFloat(row.querySelector('.qty').value) || 0;
        const price = parseFloat(row.querySelector('.price').value) || 0;

        row.querySelector('.subtotal').value = (qty * price).toFixed(2);
    }

    function calculateTotal() {
        let total = 0;

        document.querySelectorAll('#purchaseTable tbody tr').forEach(function (row) {
            calculateRow(row);

            const qty = parseFloat(row.querySelector('.qty').value) || 0;
            const price = parseFloat(row.querySelector('.price').value) || 0;

            total += qty * price;
        });

        document.getElementById('total').value = total.toFixed(2);
        document.getElementById('paymentTotal').textContent = total.toFixed(2);

        const paymentAmount = document.getElementById('payment_amount');

        paymentAmount.max = total.toFixed(2);

        if (
            document.getElementById('paidYes').checked &&
            (!paymentAmount.value || Number(paymentAmount.value) > total)
        ) {
            paymentAmount.value = total > 0 ? total.toFixed(2) : '';
        }
    }

    document.getElementById('toStep2').addEventListener('click', function () {
        const purchaseDate = document.getElementById('purchase_date');

        if (!purchaseDate.reportValidity()) {
            return;
        }

        const isNew = document.getElementById('newSupplier').checked;

        if (isNew && !newSupplierName.reportValidity()) {
            return;
        }

        if (!isNew && !supplierSelect.reportValidity()) {
            return;
        }

        if (!buildItemRows()) {
            return;
        }

        showStep(2);
    });

    document.getElementById('toStep3').addEventListener('click', function () {
        const rows = document.querySelectorAll('#purchaseTable tbody tr');

        if (!rows.length) {
            alert('Please select at least one item.');
            showStep(1);
            return;
        }

        let valid = true;

        rows.forEach(function (row) {
            const qty = row.querySelector('.qty');
            const price = row.querySelector('.price');

            if (!qty.reportValidity()) {
                valid = false;
            } else if (!price.reportValidity()) {
                valid = false;
            }
        });

        if (!valid) {
            return;
        }

        calculateTotal();

        const total = Number(document.getElementById('total').value);

        if (total <= 0) {
            alert('Purchase total must be greater than zero.');
            return;
        }

        const paymentAmount = document.getElementById('payment_amount');

        if (document.getElementById('paidYes').checked) {
            paymentAmount.value = total.toFixed(2);
        } else {
            paymentAmount.value = '';
        }

        updatePaymentFields();
        showStep(3);
    });

    document.querySelectorAll('[data-prev]').forEach(function (button) {
        button.addEventListener('click', function () {
            showStep(Number(button.dataset.prev));
        });
    });

    document.addEventListener('input', function (event) {
        if (
            event.target.classList.contains('qty') ||
            event.target.classList.contains('price')
        ) {
            calculateTotal();
        }
    });

    function updatePaymentFields() {
        const isPaid = document.getElementById('paidYes').checked;
        const paymentFields = document.getElementById('paymentFields');
        const account = document.getElementById('sub_ledger_id');
        const amount = document.getElementById('payment_amount');

        paymentFields.classList.toggle('active', isPaid);

        account.required = isPaid;
        amount.required = isPaid;

        account.disabled = !isPaid;
        amount.disabled = !isPaid;

        if (!isPaid) {
            amount.value = '';
        }
    }

    document.getElementById('paidYes').addEventListener('change', function () {
        updatePaymentFields();

        if (this.checked) {
            const total = Number(document.getElementById('total').value);
            document.getElementById('payment_amount').value = total.toFixed(2);
        }
    });

    document.getElementById('paidNo').addEventListener('change', updatePaymentFields);

    form.addEventListener('submit', function (event) {
        calculateTotal();

        if (currentStep !== 3) {
            event.preventDefault();
            alert('Please complete all wizard steps before saving.');
            return;
        }

        const total = Number(document.getElementById('total').value);
        const isPaid = document.getElementById('paidYes').checked;
        const amount = Number(document.getElementById('payment_amount').value);

        if (isPaid && (!amount || amount <= 0 || amount > total)) {
            event.preventDefault();
            alert('Payment amount must be greater than zero and cannot exceed the purchase total.');
            return;
        }

        document.getElementById('savePurchase').disabled = true;
        document.getElementById('savePurchase').innerHTML =
            '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';
    });

    updatePaymentFields();
});
</script>
@endpush
