@extends('layouts.app')

@section('title', 'Create Sale')


<link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css" rel="stylesheet">

<style>
    /* Keep wizard steps hidden unless active */
    .sale-wizard-step {
        display: none !important;
    }

    .sale-wizard-step.active {
        display: block !important;
    }

    .wizard-progress {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .wizard-progress-item {
        flex: 1;
        padding: 12px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background: #f8f9fa;
        color: #6c757d;
    }

    .wizard-progress-item.active {
        background: #007bff;
        color: #fff;
        border-color: #007bff;
    }

    .wizard-progress-item.completed {
        background: #e8f5e9;
        color: #218838;
        border-color: #28a745;
    }

    .ts-dropdown {
        z-index: 99999 !important;
    }

    .ts-control {
        min-height: 38px;
        padding: 7px 10px;
        font-size: 14px;
    }

    #selectedItemsTable th,
    #selectedItemsTable td {
        vertical-align: middle;
    }

    #selectedItemsTable input {
        min-width: 100px;
    }

    .sale-total-row {
        border-top: 2px solid #dee2e6;
        padding-top: 15px;
    }
</style>


@section('content')

@include('partials.alerts')

<div class="container-fluid">

    <div class="card card-primary card-outline">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-shopping-cart mr-1"></i>
                Create Sale
            </h3>
        </div>

        <div class="card-body">

            <form method="POST"
                  action="{{ route('sales.store') }}"
                  id="saleForm">

                @csrf

                {{-- WIZARD PROGRESS --}}
                <div class="wizard-progress">

                    <div class="wizard-progress-item active" id="progressStep1">
                        <strong>Step 1</strong>
                        
                    </div>

                    <div class="wizard-progress-item" id="progressStep2">
                        <strong>Step 2</strong>
                        
                    </div>

                </div>

                {{-- =====================================================
                     STEP 1: CUSTOMER AND ITEMS
                ====================================================== --}}
                <div id="wizardStep1" class="sale-wizard-step active">

                    <div class="form-group">
                        <label for="customer_id">
                            Customer <span class="text-danger">*</span>
                        </label>

                        <select name="customer_id"
                                id="customer_id"
                                class="form-control"
                                required>

                            <option value="">Select Customer</option>

                            @foreach($customers as $c)
                                <option value="{{ $c->id }}"
                                    {{ (string) old('customer_id') === (string) $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label for="itemPicker">
                            Select Items <span class="text-danger">*</span>
                        </label>

                        <select id="itemPicker"
                                class="form-control"
                                multiple
                                placeholder="Search and select items...">

                            @foreach($stocks as $s)
                                <option
                                    value="{{ $s->item_id }}|{{ $s->price }}"
                                    data-item-name="{{ $s->item_name }}"
                                    data-cost="{{ $s->price }}"
                                    data-stock="{{ $s->total_qty }}">

                                    {{ $s->item_name }}
                                    | Cost: {{ number_format($s->price, 2) }}
                                    | Stock: {{ number_format($s->total_qty, 3) }}

                                </option>
                            @endforeach

                        </select>

                        <small class="form-text text-muted">
                            Search by item name and select all items required for this sale.
                            The same item at different costs appears as a separate option.
                        </small>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">

                        <a href="{{ route('sales.index') }}"
                           class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Cancel
                        </a>

                        <button type="button"
                                class="btn btn-primary"
                                id="nextStep">
                            Next
                            <i class="fas fa-arrow-right ml-1"></i>
                        </button>

                    </div>

                </div>

                {{-- =====================================================
                     STEP 2: SELECTED ITEMS
                ====================================================== --}}
                <div id="wizardStep2" class="sale-wizard-step">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">Sale Items</h5>
                        </div>

                        <span class="badge badge-primary p-2" id="itemCount">
                            0 items
                        </span>
                    </div>

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover table-sm table-erp"
                               id="selectedItemsTable">

                            <thead class="bg-dark text-white">
                                <tr>
                                    <th style="min-width:250px;">Item / Cost / Stock</th>
                                    <th style="width:150px;">Qty</th>
                                    <th style="width:170px;">Sale Price</th>
                                    <th style="width:150px;" class="text-right">Amount</th>
                                    <th style="width:80px;" class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody id="selectedItemsBody">
                                {{-- Selected items are added here by JavaScript --}}
                            </tbody>

                            <tfoot>
                                <tr class="bg-light font-weight-bold">
                                    <td colspan="3" class="text-right">
                                        Total Amount
                                    </td>
                                    <td class="text-right" id="grandTotal">
                                        0.00
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>

                        </table>

                    </div>

                    <div id="noItemsMessage"
                         class="alert alert-warning"
                         style="display:none;">
                        No items selected. Go back and select at least one item.
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">

                        <button type="button"
                                class="btn btn-secondary"
                                id="backStep">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Back
                        </button>

                        <button type="submit"
                                class="btn btn-success"
                                id="saveSale">
                            <i class="fas fa-save mr-1"></i>
                            Create Sale
                        </button>

                    </div>

                </div>

            </form>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>

<script>
(function () {
    'use strict';

    function initializeSaleWizard() {
        const form = document.getElementById('saleForm');

        if (!form || form.dataset.initialized === '1') {
            return;
        }

        form.dataset.initialized = '1';

        const customerSelect = document.getElementById('customer_id');
        const itemPickerElement = document.getElementById('itemPicker');

        const step1 = document.getElementById('wizardStep1');
        const step2 = document.getElementById('wizardStep2');

        const progress1 = document.getElementById('progressStep1');
        const progress2 = document.getElementById('progressStep2');

        const itemsBody = document.getElementById('selectedItemsBody');
        const itemCount = document.getElementById('itemCount');
        const grandTotal = document.getElementById('grandTotal');
        const noItemsMessage = document.getElementById('noItemsMessage');

        let currentStep = 1;
        let itemTomSelect = null;
        let rowIndex = 0;

        /*
         * Initialize Tom Select.
         */
        if (typeof TomSelect !== 'undefined') {
            itemTomSelect = new TomSelect(itemPickerElement, {
                plugins: ['remove_button'],
                create: false,
                maxItems: null,
                hideSelected: true,
                closeAfterSelect: false,
                placeholder: 'Search and select items...',
                dropdownParent: 'body'
            });
        } else {
            console.error('Tom Select failed to load.');
            alert('The item selector could not load. Please refresh the page.');
            return;
        }

        /*
         * Show one wizard step at a time.
         */
        function showStep(stepNumber) {
            currentStep = stepNumber;

            step1.classList.toggle('active', stepNumber === 1);
            step2.classList.toggle('active', stepNumber === 2);

            progress1.classList.toggle('active', stepNumber === 1);
            progress1.classList.toggle('completed', stepNumber === 2);
            progress2.classList.toggle('active', stepNumber === 2);

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        /*
         * Get selected item option data.
         */
        function getSelectedItems() {
            return itemTomSelect.items.map(function (value) {
                const option = itemTomSelect.options[value];

                if (!option) {
                    return null;
                }

                return {
                    key: value,
                    name: option.text.trim(),
                    cost: parseFloat(option.cost) || 0,
                    stock: parseFloat(option.stock) || 0
                };
            }).filter(Boolean);
        }

        /*
         * Preserve quantities and prices when rebuilding rows.
         */
        function getExistingRowValues() {
            const values = {};

            itemsBody.querySelectorAll('tr[data-group-key]').forEach(function (row) {
                const key = row.dataset.groupKey;

                values[key] = {
                    qty: row.querySelector('[name$="[qty]"]').value,
                    salePrice: row.querySelector('[name$="[sale_price]"]').value
                };
            });

            return values;
        }

        /*
         * Build the selected-items table.
         */
        function renderSelectedItems() {
            const previousValues = getExistingRowValues();
            const selectedItems = getSelectedItems();

            itemsBody.innerHTML = '';
            rowIndex = 0;

            selectedItems.forEach(function (item) {
                const previous = previousValues[item.key] || {};
                const index = rowIndex++;

                const row = document.createElement('tr');
                row.dataset.groupKey = item.key;

                const itemCell = document.createElement('td');

                const itemName = document.createElement('div');
                itemName.className = 'font-weight-bold';
                itemName.textContent = item.name.split('|')[0].trim();

                const itemInfo = document.createElement('small');
                itemInfo.className = 'text-muted d-block';
                itemInfo.textContent =
                    'Cost: ' + item.cost.toFixed(2) +
                    ' | Stock: ' + item.stock.toFixed(3);

                itemCell.appendChild(itemName);
                itemCell.appendChild(itemInfo);

                /*
                 * group_key is the original item_id|cost value expected
                 * by the existing sales.store controller.
                 */
                const groupKeyInput = document.createElement('input');
                groupKeyInput.type = 'hidden';
                groupKeyInput.name = 'items[' + index + '][group_key]';
                groupKeyInput.value = item.key;
                itemCell.appendChild(groupKeyInput);

                const qtyCell = document.createElement('td');
                const qtyInput = document.createElement('input');

                qtyInput.type = 'number';
                qtyInput.name = 'items[' + index + '][qty]';
                qtyInput.className = 'form-control form-control-sm qty-input';
                qtyInput.step = '0.001';
                qtyInput.min = '0.001';
                qtyInput.max = item.stock;
                qtyInput.required = true;
                qtyInput.placeholder = 'Quantity';
                qtyInput.value = previous.qty || '';
                qtyInput.setAttribute('aria-label', 'Quantity');

                qtyCell.appendChild(qtyInput);

                const priceCell = document.createElement('td');
                const priceInput = document.createElement('input');

                priceInput.type = 'number';
                priceInput.name = 'items[' + index + '][sale_price]';
                priceInput.className = 'form-control form-control-sm price-input';
                priceInput.step = '0.01';
                priceInput.min = '0';
                priceInput.required = true;
                priceInput.placeholder = 'Sale price';
                priceInput.value = previous.salePrice || '';
                priceInput.setAttribute('aria-label', 'Sale price');

                priceCell.appendChild(priceInput);

                const amountCell = document.createElement('td');
                amountCell.className = 'text-right font-weight-bold amount-cell';
                amountCell.textContent = '0.00';

                const actionCell = document.createElement('td');
                actionCell.className = 'text-center';

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'btn btn-danger btn-sm remove-item';
                removeButton.title = 'Remove item';
                removeButton.innerHTML = '<i class="fas fa-times"></i>';

                actionCell.appendChild(removeButton);

                row.appendChild(itemCell);
                row.appendChild(qtyCell);
                row.appendChild(priceCell);
                row.appendChild(amountCell);
                row.appendChild(actionCell);

                itemsBody.appendChild(row);
            });

            itemCount.textContent =
                selectedItems.length + (selectedItems.length === 1 ? ' item' : ' items');

            noItemsMessage.style.display =
                selectedItems.length === 0 ? 'block' : 'none';

            calculateTotal();
        }

        /*
         * Calculate each line amount and the grand total.
         */
        function calculateTotal() {
            let total = 0;

            itemsBody.querySelectorAll('tr').forEach(function (row) {
                const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                const amount = qty * price;

                row.querySelector('.amount-cell').textContent = amount.toFixed(2);
                total += amount;
            });

            grandTotal.textContent = total.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        /*
         * NEXT: require customer and at least one item.
         */
        document.getElementById('nextStep').addEventListener('click', function () {
            if (!customerSelect.value) {
                alert('Please select a customer.');
                customerSelect.focus();
                return;
            }

            if (itemTomSelect.items.length === 0) {
                alert('Please select at least one item.');
                itemTomSelect.focus();
                return;
            }

            renderSelectedItems();
            showStep(2);
        });

        /*
         * BACK: return to Step 1 without losing entered values.
         */
        document.getElementById('backStep').addEventListener('click', function () {
            showStep(1);
        });

        /*
         * If items are changed after returning to Step 1, refresh the table
         * when moving forward. Existing quantities/prices are preserved.
         */
        itemTomSelect.on('change', function () {
            if (currentStep === 2) {
                renderSelectedItems();
            }
        });

        /*
         * Remove an item from Step 2 and from the Tom Select selection.
         */
        itemsBody.addEventListener('click', function (event) {
            const button = event.target.closest('.remove-item');

            if (!button) {
                return;
            }

            const row = button.closest('tr');
            const key = row.dataset.groupKey;

            itemTomSelect.removeItem(key);
            renderSelectedItems();
        });

        /*
         * Recalculate when quantity or sale price changes.
         */
        itemsBody.addEventListener('input', function (event) {
            if (event.target.matches('.qty-input, .price-input')) {
                calculateTotal();
            }
        });

        /*
         * Validate the form before submitting.
         */
        form.addEventListener('submit', function (event) {
            if (itemTomSelect.items.length === 0) {
                event.preventDefault();
                showStep(1);
                alert('Please select at least one item.');
                return;
            }

            let valid = true;

            itemsBody.querySelectorAll('tr').forEach(function (row) {
                const qtyInput = row.querySelector('.qty-input');
                const priceInput = row.querySelector('.price-input');

                const qty = parseFloat(qtyInput.value);
                const price = parseFloat(priceInput.value);
                const stock = parseFloat(qtyInput.max);

                if (
                    !qtyInput.value ||
                    !Number.isFinite(qty) ||
                    qty <= 0 ||
                    qty > stock ||
                    !priceInput.value ||
                    !Number.isFinite(price) ||
                    price < 0
                ) {
                    valid = false;
                    qtyInput.reportValidity();
                    priceInput.reportValidity();
                }
            });

            if (!valid) {
                event.preventDefault();
            }
        });

        /*
         * Start with only Step 1 visible.
         */
        showStep(1);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeSaleWizard);
    } else {
        initializeSaleWizard();
    }
})();
</script>
@endpush
