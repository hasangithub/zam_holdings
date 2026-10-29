@extends('layouts.app')




<link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css" rel="stylesheet">



<style>
    .ts-dropdown {

        z-index: 99999 !important;

    }



    .ts-control {

        min-height: 31px;

        padding: 4px 8px;

        font-size: 14px;

    }
</style>





@section('title','Create Export Sale')



@section('content')



<div class="container-fluid">



    <div class="row">



        {{-- LEFT PANEL --}}

        <div class="col-md-12">



            <div class="card card-primary card-outline">



                <div class="card-header d-flex justify-content-between align-items-center">

                    <h3 class="card-title mb-0">

                        <i class="fas fa-cash-register"></i> Export POS Terminal (USD)

                    </h3>

                </div>



                <div class="card-body">



                    @if($errors->any())



                    <div class="alert alert-danger alert-dismissible fade show" role="alert">



                        <strong>

                            Please correct the following errors:

                        </strong>



                        <ul class="mb-0 mt-2">



                            @foreach($errors->all() as $error)



                            <li>

                                {{ $error }}

                            </li>



                            @endforeach



                        </ul>



                        <button type="button"

                            class="close"

                            data-dismiss="alert"

                            aria-label="Close">



                            <span aria-hidden="true">&times;</span>



                        </button>



                    </div>



                    @endif



                    <form method="POST" action="{{ route('export-sales.store') }}" id="exportSaleForm" novalidate>

                        @csrf



                        {{-- WIZARD STEP 1: COMMON DETAILS --}}
                        <div id="wizardStep1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0"><span class="badge badge-primary">Step 1</span> Export Sale Details</h5>
                                <small class="text-muted">Common information</small>
                            </div>

                            {{-- SALE DATE --}}
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label for="sale_date">Sale Date</label>
                                    <input type="date" id="sale_date" name="sale_date"
                                        class="form-control form-control-sm"
                                        value="{{ old('sale_date', now()->toDateString()) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="status">Sale Status</label>

                                        <select name="status" id="status" class="form-control" required>
                                            <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>
                                                Save as Draft
                                            </option>
                                            <option value="completed" {{ old('status', 'completed') === 'completed' ? 'selected' : '' }}>
                                                Confirm Sale
                                            </option>
                                        </select>
                                        @error('status')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                            </div>

                            {{-- CUSTOMER + EXCHANGE --}}

                            <div class="row">



                                <div class="col-md-6">

                                    <label>Customer</label>

                                    <select name="customer_id" class="form-control form-control-sm" required>

                                        <option value="">Select Customer</option>

                                        @foreach($customers as $c)

                                        <option value="{{ $c->id }}">{{ $c->name }}</option>

                                        @endforeach

                                    </select>

                                </div>



                                <div class="col-md-6">

                                    <label>Exchange Rate</label>

                                    <input type="number"

                                        step="0.0001"

                                        name="exchange_rate"

                                        class="form-control form-control-sm"

                                        required>

                                </div>



                            </div>



                            <div class="row">



                                <div class="col-md-6">

                                    <label for="airway_no">Airway Bill No</label>

                                    <input

                                        type="text"

                                        name="airway_no"

                                        id="airway_no"

                                        class="form-control form-control-sm"

                                        value="{{ old('airway_no') }}">

                                </div>





                                <div class="col-md-6">

                                    <label>Flight No </label>

                                    <input type="text" name="flight_no" class="form-control form-control-sm">

                                </div>



                            </div>



                            <div class="row">



                                <div class="col-md-6">

                                    <label>Port of Loading</label>

                                    <select name="port_of_loading" class="form-control form-control-sm">

                                        <option value="">Select</option>



                                        @foreach(\App\Models\Sale::PORTOFLOADING as $id => $name)

                                        <option value="{{ $id }}"

                                            {{ old('port_of_loading', $sale->port_of_loading ?? '') == $id ? 'selected' : '' }}>

                                            {{ $name }}

                                        </option>

                                        @endforeach

                                    </select>

                                </div>



                                <div class="col-md-6">

                                    <label>Country of Orgin</label>

                                    <input type="text"

                                        name="country_of_origin"

                                        class="form-control form-control-sm"

                                        required>

                                </div>



                            </div>



                            <div class="row">



                                <div class="col-md-6">

                                    <label>Mode of Payment</label>

                                    <select name="mode_of_payment" class="form-control form-control-sm">

                                        <option value="">Select</option>



                                        @foreach(\App\Models\Sale::MODEOFPAYMENTS as $id => $name)

                                        <option value="{{ $id }}"

                                            {{ old('mode_of_payment', $sale->mode_of_payment ?? '') == $id ? 'selected' : '' }}>

                                            {{ $name }}

                                        </option>

                                        @endforeach

                                    </select>

                                </div>



                                <div class="col-md-6">

                                    <label>Mod of Shipping</label>

                                    <select name="mode_of_shipping" class="form-control form-control-sm">

                                        <option value="">Select</option>



                                        @foreach(\App\Models\Sale::MODEOFSHIPPING as $id => $name)

                                        <option value="{{ $id }}"

                                            {{ old('mode_of_shipping', $sale->mode_of_shipping ?? '') == $id ? 'selected' : '' }}>

                                            {{ $name }}

                                        </option>

                                        @endforeach

                                    </select>

                                </div>



                            </div>



                            <hr>



                            <hr>
                            <div class="form-group mb-3">
                                <label for="wizardItemPicker">Select Items</label>
                                <select id="wizardItemPicker" class="form-control form-control-sm" multiple>
                                    @foreach($stocks as $s)
                                    <option value="{{ $s->item_id }}"
                                        data-name="{{ $s->item_name }}"
                                        data-stock="{{ $s->total_qty }}"
                                        data-cost="{{ $s->average_cost }}">
                                        {{ $s->item_name }} | Average Cost: {{ number_format($s->average_cost, 2) }} | Stock: {{ $s->total_qty }}
                                    </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Selected items remain inside this field. Search and select each item once.</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="default_qty">Default Quantity</label>
                                    <input type="number" id="default_qty" class="form-control form-control-sm"
                                        min="0.001" step="0.001" value="1">
                                    <small class="form-text text-muted">Prefills the quantity for each selected item.</small>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="default_usd_price">Default Sale Price (USD)</label>
                                    <input type="number" id="default_usd_price" class="form-control form-control-sm"
                                        min="0" step="0.01" placeholder="Enter USD price">
                                    <small class="form-text text-muted">Prefills the USD sale price for each selected item.</small>
                                </div>
                            </div>

                            <div class="text-right mt-3 mb-2">
                                <button type="button" class="btn btn-primary btn-sm" id="nextStep">Next: Select Items <i class="fas fa-arrow-right ml-1"></i></button>
                            </div>
                        </div> {{-- /wizardStep1 --}}

                        {{-- WIZARD STEP 2: ITEMS --}}
                        <div id="wizardStep2" style="display:none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0"><span class="badge badge-primary">Step 2</span> Select Items</h5>
                                <small class="text-muted">Enter quantity and sale price in USD</small>
                            </div>

                            {{-- ITEMS TABLE --}}

                            <div class="table-responsive">



                                <table class="table table-bordered table-sm table-hover" id="salesTable">



                                    <thead class="bg-dark text-white">

                                        <tr>

                                            <th style="width:45%">Item</th>

                                            <th style="width:15%">Qty</th>

                                            <th style="width:25%">Sale Price USD</th>

                                            <th style="width:10%" class="text-center">Action</th>

                                        </tr>

                                    </thead>



                                    <tbody></tbody>



                                </table>



                            </div>



                            {{-- ACTION BUTTONS --}}

                            <div class="mt-3">



                                <button type="button"

                                    id="addRow"

                                    class="btn btn-primary btn-sm">

                                    <i class="fas fa-plus"></i> Add More Items

                                </button>



                            </div>



                            <div class="d-flex justify-content-between mt-3 mb-2">
                                <button type="button" class="btn btn-default btn-sm" id="backStep"><i class="fas fa-arrow-left mr-1"></i> Back</button>
                                <button type="submit" class="btn btn-success btn-sm" id="saveExportSale"><i class="fas fa-save"></i> Save Export Sale</button>
                            </div>
                        </div> {{-- /wizardStep2 --}}
                    </form>



                </div>

            </div>



        </div>



    </div>



</div>



@endsection





@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const step1 = document.getElementById('wizardStep1');
        const step2 = document.getElementById('wizardStep2');
        const form = document.getElementById('exportSaleForm');
        const picker = document.getElementById('wizardItemPicker');
        const tbody = document.querySelector('#salesTable tbody');
        const selectedItems = new Map();
        let rowIndex = 0;

        const itemSelect = new TomSelect(picker, {
            plugins: ['remove_button'],
            create: false,
            maxItems: null,
            hideSelected: true,
            placeholder: 'Search and select items...',
            dropdownParent: 'body',
            closeAfterSelect: false
        });

        function syncSelectedItems() {
            selectedItems.clear();
            itemSelect.items.forEach(function(value) {
                const id = String(value);
                const option = picker.querySelector('option[value="' + CSS.escape(id) + '"]');
                if (!option) return;
                selectedItems.set(id, {
                    id: id,
                    name: option.dataset.name || option.textContent.trim(),
                    stock: Number(option.dataset.stock || 0),
                    cost: Number(option.dataset.cost || 0)
                });
            });
        }

        itemSelect.on('item_add', syncSelectedItems);
        itemSelect.on('item_remove', syncSelectedItems);

        function makeItemRow(item) {
            const tr = document.createElement('tr');
            tr.dataset.itemId = item.id;
            tr.innerHTML = `
            <td>
                <input type="hidden" name="items[${rowIndex}][item_id]" value="${item.id}">
                <strong>${escapeHtml(item.name)}</strong>
                <small class="d-block text-muted">Average Cost: ${Number(item.cost).toFixed(2)} | Stock: ${Number(item.stock).toFixed(3)}</small>
            </td>
            <td><input type="number" name="items[${rowIndex}][qty]" class="form-control form-control-sm qty-input" min="0.001" step="0.001" max="${item.stock}" value="${getDefaultQty(item.stock)}" required></td>
            <td><input type="number" name="items[${rowIndex}][sale_price_foreign]" class="form-control form-control-sm price-input" min="0" step="0.01" value="${escapeHtml(document.getElementById('default_usd_price').value)}" required placeholder="USD"></td>
            <td class="text-center"><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>`;
            rowIndex++;
            return tr;
        }

        function getDefaultQty(stock) {
            const entered = Number(document.getElementById('default_qty').value || 1);
            return Math.min(entered > 0 ? entered : 1, Number(stock || 0));
        }

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, function(ch) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                } [ch];
            });
        }

        function buildItemsTable() {
            syncSelectedItems();
            tbody.innerHTML = '';
            rowIndex = 0;
            selectedItems.forEach(function(item) {
                tbody.appendChild(makeItemRow(item));
            });
        }

        function validateStep1() {
            const fields = step1.querySelectorAll('input[required], select[required], textarea[required]');
            for (const field of fields) {
                if (!field.value) {
                    field.classList.add('is-invalid');
                    field.focus();
                    return false;
                }
                field.classList.remove('is-invalid');
            }
            syncSelectedItems();
            if (selectedItems.size === 0) {
                alert('Please select at least one item before continuing.');
                itemSelect.focus();
                return false;
            }
            const defaultQty = Number(document.getElementById('default_qty').value || 0);
            if (defaultQty <= 0) {
                alert('Please enter a default quantity greater than zero.');
                document.getElementById('default_qty').focus();
                return false;
            }
            return true;
        }

        document.getElementById('nextStep').addEventListener('click', function() {
            if (!validateStep1()) {
                alert('Please complete the required export sale details and select at least one item.');
                return;
            }
            buildItemsTable();
            step1.style.display = 'none';
            step2.style.display = 'block';
        });

        document.getElementById('backStep').addEventListener('click', function() {
            step2.style.display = 'none';
            step1.style.display = 'block';
        });

        document.getElementById('addRow').addEventListener('click', function() {
            step2.style.display = 'none';
            step1.style.display = 'block';
            itemSelect.focus();
        });

        document.addEventListener('click', function(event) {
            const button = event.target.closest('.removeRow');
            if (!button) return;
            const row = button.closest('tr');
            const id = String(row.dataset.itemId || '');
            const item = selectedItems.get(id);
            if (item) {
                itemSelect.removeItem(id);
            }
            row.remove();
        });

        document.addEventListener('input', function(event) {
            if (event.target.matches('.qty-input')) {
                const max = Number(event.target.max || 0);
                if (Number(event.target.value) > max) {
                    alert('Quantity cannot exceed stock: ' + max);
                    event.target.value = max;
                }
            }
        });

        form.addEventListener('submit', function(event) {
            if (step2.style.display === 'none') {
                event.preventDefault();
                document.getElementById('nextStep').click();
                return;
            }
            let valid = tbody.querySelectorAll('tr').length > 0;
            tbody.querySelectorAll('tr').forEach(function(row) {
                const qty = row.querySelector('.qty-input');
                const price = row.querySelector('.price-input');
                if (!qty || Number(qty.value) <= 0 || qty.value === '' || Number(qty.value) > Number(qty.max) || !price || price.value === '' || Number(price.value) < 0) valid = false;
            });
            if (!valid) {
                event.preventDefault();
                alert('Please enter a valid quantity within stock and a sale price in USD for every item.');
            }
        });

    });
</script>
@endpush