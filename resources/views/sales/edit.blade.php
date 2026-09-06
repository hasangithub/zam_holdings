@extends('layouts.app')

@section('title', 'Edit Sale')

@section('content')

<style>
    .sale-table th,
    .sale-table td {
        vertical-align: middle;
    }

    .sale-table th {
        font-size: 13px;
        white-space: nowrap;
    }

    .sale-table td {
        padding: 6px;
    }

    .sale-total-box {
        max-width: 400px;
        margin-left: auto;
    }

    .sale-total {
        font-size: 18px;
        font-weight: bold;
    }

    .ts-wrapper {
        width: 100%;
    }

    .ts-dropdown {
        z-index: 99999 !important;
    }

    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>

<div class="container-fluid">

    {{-- VALIDATION ERRORS --}}
    @if($errors->any())

    <div class="alert alert-danger alert-dismissible fade show">

        <strong>
            Please correct the following errors:
        </strong>

        <ul class="mb-0 mt-2">

            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach

        </ul>

        <button type="button"
            class="close"
            data-dismiss="alert">

            <span>&times;</span>

        </button>

    </div>

    @endif


    {{-- CARD --}}
    <div class="card">

        {{-- HEADER --}}
        <div class="card-header bg-dark">

            <h3 class="card-title text-white">

                <i class="fas fa-edit mr-1"></i>

                Edit Sale

                <span class="ml-2">
                    #{{ $sale->invoice_id ?? $sale->id }}
                </span>

            </h3>

        </div>


        {{-- FORM --}}
        <form method="POST"
            action="{{ route('sales.update', $sale->id) }}">

            @csrf
            @method('PUT')


            <div class="card-body">


                {{-- SALE INFORMATION --}}
                <div class="row">

                    {{-- CUSTOMER --}}
                    <div class="col-md-6">

                        <div class="form-group">

                            <label>
                                Customer
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                name="customer_id"
                                class="form-control form-control-sm tom-select"
                                required>

                                <option value="">
                                    Select Customer
                                </option>

                                @foreach($customers as $customer)

                                <option
                                    value="{{ $customer->id }}"
                                    {{ $sale->customer_id == $customer->id ? 'selected' : '' }}>

                                    {{ $customer->name }}

                                </option>

                                @endforeach

                            </select>

                        </div>

                    </div>


                    {{-- SALE DATE --}}
                    <div class="col-md-3">

                        <div class="form-group">

                            <label>
                                Sale Date
                            </label>

                            <input
                                type="date"
                                name="sale_date"
                                class="form-control form-control-sm"
                                value="{{ old(
                                    'sale_date',
                                    $sale->sale_date
                                        ? date('Y-m-d', strtotime($sale->sale_date))
                                        : date('Y-m-d')
                                ) }}">

                        </div>

                    </div>

                </div>


                <hr>


                {{-- ITEMS --}}
                <div class="table-responsive">

                    <table
                        class="table table-bordered table-sm table-hover sale-table"
                        id="salesTable">

                        <thead class="bg-dark text-white">

                            <tr>

                                <th style="width:40%">
                                    Item
                                </th>

                                <th style="width:12%">
                                    Qty
                                </th>

                                <th style="width:15%">
                                    Base Price
                                </th>

                                <th style="width:15%">
                                    Sale Price
                                </th>

                                <th style="width:13%"
                                    class="text-right">

                                    Subtotal

                                </th>

                                <th style="width:5%"
                                    class="text-center">

                                    Action

                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($sale->items as $key => $saleItem)

                            <tr>

                                {{-- ITEM --}}
                                <td>

                                    <select
                                        name="items[{{ $key }}][group_key]"
                                        class="form-control form-control-sm item-select tom-select"
                                        required>

                                        <option value="">Select Item</option>

                                        @foreach($items as $item)

                                        <option
                                            value="{{ $item->id }}|{{ $saleItem->base_price }}"
                                            {{ $saleItem->item_id == $item->id ? 'selected' : '' }}>

                                            {{ $item->name }}

                                        </option>

                                        @endforeach

                                    </select>

                                </td>


                                {{-- QTY --}}
                                <td>

                                    <input
                                        type="number"
                                        name="items[{{ $key }}][qty]"
                                        class="form-control form-control-sm qty"
                                        value="{{ $saleItem->qty }}"
                                        min="0.001"
                                        step="0.001"
                                        required>

                                </td>


                                {{-- BASE PRICE --}}
                                <td>

                                    <input
                                        type="number"
                                        name="items[{{ $key }}][base_price]"
                                        class="form-control form-control-sm base-price"
                                        value="{{ $saleItem->base_price }}"
                                        min="0"
                                        step="0.01"
                                        required>

                                </td>


                                {{-- SALE PRICE --}}
                                <td>

                                    <input
                                        type="number"
                                        name="items[{{ $key }}][sale_price]"
                                        class="form-control form-control-sm sale-price"
                                        value="{{ $saleItem->sale_price }}"
                                        min="0"
                                        step="0.01"
                                        required>

                                </td>


                                {{-- SUBTOTAL --}}
                                <td>

                                    <input
                                        type="text"
                                        class="form-control form-control-sm subtotal text-right"
                                        value="{{ number_format($saleItem->subtotal, 2, '.', '') }}"
                                        readonly>

                                </td>


                                {{-- REMOVE --}}
                                <td class="text-center">

                                    <button
                                        type="button"
                                        class="btn btn-danger btn-sm removeRow">

                                        <i class="fas fa-times"></i>

                                    </button>

                                </td>

                            </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>


                {{-- ADD ITEM --}}
                <button
                    type="button"
                    class="btn btn-primary btn-sm"
                    id="addRow">

                    <i class="fas fa-plus mr-1"></i>

                    Add Item

                </button>


                <hr>


                {{-- TOTAL --}}
                <div class="row justify-content-end">

                    <div class="col-md-4">

                        <div class="card card-outline card-dark sale-total-box">

                            <div class="card-body">

                                <div class="row">

                                    <div class="col-6">
                                        <strong>Total</strong>
                                    </div>

                                    <div class="col-6">

                                        <input
                                            type="text"
                                            id="total"
                                            class="form-control form-control-sm text-right sale-total"
                                            value="0.00"
                                            readonly>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- FOOTER --}}
            <div class="card-footer">

                <button
                    type="submit"
                    class="btn btn-success">

                    <i class="fas fa-save mr-1"></i>

                    Update Sale

                </button>


                <a
                    href="{{ route('sales.index') }}"
                    class="btn btn-secondary">

                    <i class="fas fa-times mr-1"></i>

                    Cancel

                </a>

            </div>

        </form>

    </div>

</div>

@endsection


@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function() {

        /*
        |--------------------------------------------------------------------------
        | Row Index
        |--------------------------------------------------------------------------
        */

       let rowIndex = {{ count($sale->items) }};


        /*
        |--------------------------------------------------------------------------
        | Initialize Tom Select
        |--------------------------------------------------------------------------
        */

        function initializeTomSelect(element) {
            if (!element || element.tomselect) {
                return;
            }

            new TomSelect(element, {

                create: false,

                allowEmptyOption: true,

                dropdownParent: 'body',

                maxOptions: 5000

            });
        }


        /*
        |--------------------------------------------------------------------------
        | Existing Selects
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll('.tom-select')
            .forEach(function(element) {

                initializeTomSelect(element);

            });


        /*
        |--------------------------------------------------------------------------
        | Item Options
        |--------------------------------------------------------------------------
        */

        const itemOptions = `
        <option value="">
            Select Item
        </option>

        @foreach($items as $item)

            <option value="{{ $item->id }}|{{$saleItem->base_price}}">
                {{ addslashes($item->name) }}
            </option>

        @endforeach
    `;


        /*
        |--------------------------------------------------------------------------
        | Calculate Row
        |--------------------------------------------------------------------------
        */

        function calculateRow(row) {
            const qty =
                parseFloat(
                    row.querySelector('.qty').value
                ) || 0;

            const salePrice =
                parseFloat(
                    row.querySelector('.sale-price').value
                ) || 0;

            const subtotal =
                qty * salePrice;

            row.querySelector('.subtotal').value =
                subtotal.toFixed(2);
        }


        /*
        |--------------------------------------------------------------------------
        | Calculate Total
        |--------------------------------------------------------------------------
        */

        function calculateTotal() {
            let total = 0;

            document
                .querySelectorAll('#salesTable tbody tr')
                .forEach(function(row) {

                    const qty =
                        parseFloat(
                            row.querySelector('.qty').value
                        ) || 0;

                    const salePrice =
                        parseFloat(
                            row.querySelector('.sale-price').value
                        ) || 0;

                    total += qty * salePrice;

                });


            document.getElementById('total').value =
                total.toFixed(2);
        }


        /*
        |--------------------------------------------------------------------------
        | Initial Total
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll('#salesTable tbody tr')
            .forEach(function(row) {

                calculateRow(row);

            });

        calculateTotal();


        /*
        |--------------------------------------------------------------------------
        | ADD ROW
        |--------------------------------------------------------------------------
        */

        document
            .getElementById('addRow')
            .addEventListener('click', function() {

                const tbody =
                    document.querySelector(
                        '#salesTable tbody'
                    );


                const row = document.createElement('tr');


                row.innerHTML = `

                <td>

                    <select
                        name="items[${rowIndex}][group_key]"
                        class="form-control form-control-sm item-select tom-select"
                        required>

                        ${itemOptions}

                    </select>

                </td>


                <td>

                    <input
                        type="number"
                        name="items[${rowIndex}][qty]"
                        class="form-control form-control-sm qty"
                        min="0.001"
                        step="0.001"
                        required>

                </td>


                <td>

                    <input
                        type="number"
                        name="items[${rowIndex}][base_price]"
                        class="form-control form-control-sm base-price"
                        min="0"
                        step="0.01"
                        required>

                </td>


                <td>

                    <input
                        type="number"
                        name="items[${rowIndex}][sale_price]"
                        class="form-control form-control-sm sale-price"
                        min="0"
                        step="0.01"
                        required>

                </td>


                <td>

                    <input
                        type="text"
                        class="form-control form-control-sm subtotal text-right"
                        value="0.00"
                        readonly>

                </td>


                <td class="text-center">

                    <button
                        type="button"
                        class="btn btn-danger btn-sm removeRow">

                        <i class="fas fa-times"></i>

                    </button>

                </td>

            `;


                tbody.appendChild(row);


                /*
                |--------------------------------------------------------------------------
                | Initialize Tom Select For New Row
                |--------------------------------------------------------------------------
                */

                initializeTomSelect(
                    row.querySelector('.tom-select')
                );


                rowIndex++;

            });


        /*
        |--------------------------------------------------------------------------
        | QTY / SALE PRICE
        |--------------------------------------------------------------------------
        |
        | Event delegation means this works for both existing
        | rows and dynamically added rows.
        |
        */

        document.addEventListener('input', function(e) {

            if (
                e.target.classList.contains('qty') ||
                e.target.classList.contains('sale-price')
            ) {

                const row =
                    e.target.closest('tr');

                if (!row) {
                    return;
                }

                calculateRow(row);

                calculateTotal();

            }

        });


        /*
        |--------------------------------------------------------------------------
        | REMOVE ROW
        |--------------------------------------------------------------------------
        */

        document.addEventListener('click', function(e) {

            const button =
                e.target.closest('.removeRow');

            if (!button) {
                return;
            }


            const rows =
                document.querySelectorAll(
                    '#salesTable tbody tr'
                );


            if (rows.length <= 1) {

                alert(
                    'At least one item is required.'
                );

                return;

            }


            const row =
                button.closest('tr');


            /*
            |--------------------------------------------------------------------------
            | Destroy Tom Select
            |--------------------------------------------------------------------------
            */

            const select =
                row.querySelector('.tom-select');

            if (
                select &&
                select.tomselect
            ) {

                select.tomselect.destroy();

            }


            row.remove();

            calculateTotal();

        });

    });
</script>

@endpush