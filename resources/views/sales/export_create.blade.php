@extends('layouts.app')

@push('css')
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
@endpush

@section('title','Create Export Sale')

@section('content')

<div class="container-fluid">

    <div class="row">

        {{-- LEFT PANEL --}}
        <div class="col-md-9">

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

                    <form method="POST" action="{{ route('export-sales.store') }}">
                        @csrf

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
                                <label>consignor</label>
                                <select name="consignor" class="form-control form-control-sm">
                                    <option value="">Select Consignor</option>

                                    @foreach($freightServices as $freightService)
                                    <option value="{{ $freightService->id }}">
                                        {{ $freightService->name }}
                                    </option>
                                    @endforeach
                                </select>
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
                                    name="country_of_orgin"
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

                                <tbody>

                                    <tr>

                                        <td>
                                            <select name="items[0][group_key]"
                                                class="form-control form-control-sm item-select"
                                                required>
                                                <option value=""></option>
                                                @foreach($stocks as $s)
                                                <option value="{{ $s->item_id }}|{{ $s->price }}|{{ $s->total_qty }}">
                                                    {{ $s->item_name }}
                                                    | Cost: {{ $s->price }}
                                                    | Stock: {{ $s->total_qty }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td>
                                            <input type="number"
                                                min="0.01"
                                                step="0.01"
                                                name="items[0][qty]"
                                                class="form-control form-control-sm"
                                                required>
                                        </td>

                                        <td>
                                            <input type="number"
                                                name="items[0][sale_price_foreign]"
                                                class="form-control form-control-sm"
                                                required>
                                        </td>

                                        <td class="text-center">
                                            <button type="button"
                                                class="btn btn-danger btn-sm removeRow">
                                                X
                                            </button>
                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                        {{-- ACTION BUTTONS --}}
                        <div class="mt-3">

                            <button type="button"
                                id="addRow"
                                class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Item
                            </button>

                            <button type="submit"
                                class="btn btn-success btn-sm float-right">
                                <i class="fas fa-save"></i> Save Export Sale
                            </button>

                        </div>

                    </form>

                </div>
            </div>

        </div>

        {{-- RIGHT SIDE (FUTURE EXTENSION PANEL) --}}
        {{-- RIGHT SIDE TOTAL PANEL --}}
        <div class="col-md-3">

            <div class="card card-success card-outline">

                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calculator"></i> Sale Summary
                    </h5>
                </div>

                <div class="card-body p-2">

                    <div class="p-2 border rounded mb-2 bg-light">
                        <small>Total Items</small>
                        <h5 class="mb-0" id="totalItems">0</h5>
                    </div>

                    <div class="p-2 border rounded mb-2 bg-light">
                        <small>Subtotal (USD)</small>
                        <h5 class="mb-0" id="totalUsd">$ 0.00</h5>
                    </div>

                    <div class="p-2 border rounded mb-2 bg-light">
                        <small>Exchange Rate</small>
                        <h5 class="mb-0" id="exchangeDisplay">-</h5>
                    </div>

                    <div class="p-2 border rounded bg-success text-white">
                        <small>Total (LKR)</small>
                        <h4 class="mb-0" id="totalLkr">Rs 0.00</h4>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        let i = 1;

        /*
        |--------------------------------------------------------------------------
        | Initialize Tom Select
        |--------------------------------------------------------------------------
        */
        function initItemSelect(select) {

            if (select.tomselect) {
                return select.tomselect;
            }

            return new TomSelect(select, {
                create: false,
                maxItems: 1,
                allowEmptyOption: true,
                placeholder: 'Search item...',
                dropdownParent: 'body',
                closeAfterSelect: true
            });
        }


        /*
        |--------------------------------------------------------------------------
        | FIRST ROW
        |--------------------------------------------------------------------------
        */
        document.querySelectorAll('.item-select').forEach(function(select) {
            initItemSelect(select);
        });


        /*
        |--------------------------------------------------------------------------
        | Update disabled options
        |--------------------------------------------------------------------------
        */
        function updateItemOptions() {

            let selectedValues = [];

            /*
            | Get selected values from every row
            */
            document.querySelectorAll(
                '#salesTable select[name$="[group_key]"]'
            ).forEach(function(select) {

                if (select.value) {
                    selectedValues.push(select.value);
                }
            });


            /*
            | Update every Tom Select
            */
            document.querySelectorAll(
                '#salesTable select[name$="[group_key]"]'
            ).forEach(function(select) {

                let currentValue = select.value;
                let ts = select.tomselect;

                /*
                | Disable options in the original select
                */
                select.querySelectorAll('option').forEach(function(option) {

                    if (!option.value) {
                        return;
                    }

                    if (
                        selectedValues.includes(option.value) &&
                        option.value !== currentValue
                    ) {
                        option.disabled = true;
                    } else {
                        option.disabled = false;
                    }

                });


                /*
                |--------------------------------------------------------------------------
                | IMPORTANT:
                | Rebuild Tom Select options so disabled state is reflected
                |--------------------------------------------------------------------------
                */
                if (ts) {

                    ts.clearOptions();

                    Array.from(select.options).forEach(function(option) {

                        ts.addOption({
                            value: option.value,
                            text: option.text,
                            disabled: option.disabled
                        });

                    });

                    /*
                    | Restore current selected value
                    */
                    if (currentValue) {
                        ts.setValue(currentValue, true);
                    }

                    ts.refreshOptions(false);
                }

            });
        }


        /*
        |--------------------------------------------------------------------------
        | ADD ROW
        |--------------------------------------------------------------------------
        */
        document.getElementById('addRow').addEventListener('click', function() {

            let row = `
        <tr>

            <td>
                <select name="items[${i}][group_key]"
                        class="form-control form-control-sm item-select"
                        required>

                    <option value="">Select Item</option>

                    @foreach($stocks as $s)
                        <option value="{{ $s->item_id }}|{{ $s->price }}|{{ $s->total_qty }}">
                            {{ $s->item_name }}
                            | Cost: {{ $s->price }}
                            | Stock: {{ $s->total_qty }}
                        </option>
                    @endforeach

                </select>
            </td>

            <td>
                <input type="number"
                       name="items[${i}][qty]"
                       class="form-control form-control-sm"
                       min="0.01"
                       step="0.01"
                       required>
            </td>

            <td>
                <input type="number"
                       name="items[${i}][sale_price_foreign]"
                       class="form-control form-control-sm"
                       min="0"
                       step="0.01"
                       required>
            </td>

            <td class="text-center">
                <button type="button"
                        class="btn btn-danger btn-sm removeRow">
                    X
                </button>
            </td>

        </tr>
        `;

            document.querySelector('#salesTable tbody')
                .insertAdjacentHTML('beforeend', row);


            /*
            | Initialize Tom Select for new row
            */
            let newSelect = document.querySelector(
                '#salesTable tbody tr:last-child .item-select'
            );

            initItemSelect(newSelect);


            /*
            | Disable items already selected
            */
            updateItemOptions();

            i++;
        });


        /*
        |--------------------------------------------------------------------------
        | ITEM CHANGED
        |--------------------------------------------------------------------------
        */
        document.addEventListener('change', function(e) {

            if (e.target.matches(
                    '#salesTable select[name$="[group_key]"]'
                )) {

                updateItemOptions();

                validateStock(
                    e.target.closest('tr')
                );

                calculatePOS();
            }

        });


        /*
        |--------------------------------------------------------------------------
        | REMOVE ROW
        |--------------------------------------------------------------------------
        */
        document.addEventListener('click', function(e) {

            let button = e.target.closest('.removeRow');

            if (!button) {
                return;
            }

            let rows = document.querySelectorAll(
                '#salesTable tbody tr'
            );

            if (rows.length > 1) {

                let row = button.closest('tr');

                let select = row.querySelector(
                    'select[name$="[group_key]"]'
                );

                /*
                | Destroy Tom Select
                */
                if (select && select.tomselect) {
                    select.tomselect.destroy();
                }

                row.remove();
            }

            updateItemOptions();
            calculatePOS();
        });


        /*
        |--------------------------------------------------------------------------
        | QUANTITY
        |--------------------------------------------------------------------------
        */
        document.addEventListener('input', function(e) {

            if (e.target.matches(
                    '#salesTable input[name$="[qty]"]'
                )) {

                let row = e.target.closest('tr');

                validateStock(row);
                calculatePOS();
            }


            if (
                e.target.matches(
                    '#salesTable input[name$="[sale_price_foreign]"]'
                ) ||
                e.target.matches(
                    'input[name="exchange_rate"]'
                )
            ) {
                calculatePOS();
            }

        });


        /*
        |--------------------------------------------------------------------------
        | STOCK VALIDATION
        |--------------------------------------------------------------------------
        */
        function validateStock(row) {

            if (!row) {
                return;
            }

            let select = row.querySelector(
                'select[name$="[group_key]"]'
            );

            let qtyInput = row.querySelector(
                'input[name$="[qty]"]'
            );

            if (!select || !qtyInput || !select.value) {
                return;
            }

            let stock = parseFloat(
                select.value.split('|')[2] || 0
            );

            let qty = parseFloat(
                qtyInput.value || 0
            );

            if (qty > stock) {

                alert(
                    'Quantity cannot exceed stock: ' + stock
                );

                qtyInput.value = stock;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | CALCULATE POS
        |--------------------------------------------------------------------------
        */
        function calculatePOS() {

            let totalItems = 0;
            let totalUsd = 0;

            let exchangeRate = parseFloat(
                document.querySelector(
                    'input[name="exchange_rate"]'
                ).value || 0
            );


            document.querySelectorAll(
                '#salesTable tbody tr'
            ).forEach(function(row) {

                let qtyInput = row.querySelector(
                    'input[name$="[qty]"]'
                );

                let priceInput = row.querySelector(
                    'input[name$="[sale_price_foreign]"]'
                );

                if (!qtyInput || !priceInput) {
                    return;
                }

                let qty = parseFloat(
                    qtyInput.value || 0
                );

                let price = parseFloat(
                    priceInput.value || 0
                );

                if (qty > 0 && price > 0) {

                    totalItems += qty;
                    totalUsd += qty * price;
                }

            });


            let totalLkr = totalUsd * exchangeRate;


            document.getElementById('totalItems').innerText =
                totalItems;

            document.getElementById('totalUsd').innerText =
                '$ ' + totalUsd.toFixed(2);

            document.getElementById('exchangeDisplay').innerText =
                exchangeRate || '-';

            document.getElementById('totalLkr').innerText =
                'Rs ' + totalLkr.toFixed(2);
        }


        /*
        |--------------------------------------------------------------------------
        | INITIAL
        |--------------------------------------------------------------------------
        */
        updateItemOptions();
        calculatePOS();

    });
</script>
@endpush