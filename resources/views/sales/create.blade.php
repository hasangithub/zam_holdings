@extends('layouts.app')

@section('title','Create Sale')

@section('content')

<div class="container-fluid">

    <div class="row">

        {{-- LEFT SIDE --}}
        <div class="col-md-9">

            <div class="card card-primary card-outline">

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="fas fa-shopping-cart"></i>
                        Create Sale

                    </h3>

                </div>


                <div class="card-body">

                    <form
                        method="POST"
                        action="{{ route('sales.store') }}">

                        @csrf


                        {{-- CUSTOMER --}}

                        <div class="form-group">

                            <label>
                                Customer
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                name="customer_id"
                                class="form-control"
                                required>

                                <option value="">
                                    Select Customer
                                </option>

                                @foreach($customers as $c)

                                    <option
                                        value="{{ $c->id }}"
                                        {{ old('customer_id') == $c->id ? 'selected' : '' }}>

                                        {{ $c->name }}

                                    </option>

                                @endforeach

                            </select>

                        </div>


                        <hr>


                        {{-- ITEMS TABLE --}}

                        <div class="table-responsive">

                            <table
                                class="table table-bordered table-sm table-hover"
                                id="salesTable">

                                <thead class="bg-dark text-white">

                                    <tr>

                                        <th>
                                            Item / Cost / Stock
                                        </th>

                                        <th width="120">
                                            Qty
                                        </th>

                                        <th width="160">
                                            Sale Price
                                        </th>

                                        <th
                                            width="80"
                                            class="text-center">

                                            Action

                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <tr>

                                        <td>

                                            <select
                                                name="items[0][group_key]"
                                                class="form-control"
                                                required>

                                                <option value="">
                                                    Select Item
                                                </option>


                                                @foreach($stocks as $s)

                                                    <option
                                                        value="{{ $s->item_id }}|{{ $s->price }}">

                                                        {{ $s->item_name }}

                                                        |
                                                        Cost:
                                                        {{ number_format($s->price, 2) }}

                                                        |
                                                        Stock:
                                                        {{ number_format($s->total_qty, 3) }}

                                                    </option>

                                                @endforeach

                                            </select>

                                        </td>


                                        <td>

                                            <input
                                                type="number"
                                                name="items[0][qty]"
                                                step="0.001"
                                                min="0.001"
                                                class="form-control"
                                                required>

                                        </td>


                                        <td>

                                            <input
                                                type="number"
                                                name="items[0][sale_price]"
                                                step="0.01"
                                                min="0"
                                                class="form-control"
                                                required>

                                        </td>


                                        <td class="text-center">

                                            <button
                                                type="button"
                                                class="btn btn-danger btn-sm removeRow">

                                                <i class="fas fa-times"></i>

                                            </button>

                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>


                        {{-- ACTIONS --}}

                        <div class="mt-3">

                            <button
                                type="button"
                                id="addRow"
                                class="btn btn-primary btn-sm">

                                <i class="fas fa-plus"></i>
                                Add Item

                            </button>


                            <button
                                type="submit"
                                class="btn btn-success btn-sm float-right">

                                <i class="fas fa-save"></i>
                                Save Sale

                            </button>

                        </div>


                    </form>

                </div>

            </div>

        </div>


        {{-- RIGHT SIDE SUMMARY --}}

        <div class="col-md-3">

            <div class="card card-success card-outline">

                <div class="card-header">

                    <h5 class="mb-0">

                        <i class="fas fa-calculator"></i>
                        Sale Summary

                    </h5>

                </div>


                <div class="card-body p-2">


                    <div
                        class="p-2 border rounded mb-2 bg-light">

                        <small>
                            Total Items
                        </small>

                        <h5
                            class="mb-0"
                            id="totalItems">

                            0

                        </h5>

                    </div>


                    <div
                        class="p-2 border rounded bg-success text-white">

                        <small>
                            Total Amount
                        </small>

                        <h4
                            class="mb-0"
                            id="totalAmount">

                            Rs 0.00

                        </h4>

                    </div>


                </div>

            </div>

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {


        let i = 1;


        /*
        |--------------------------------------------------------------------------
        | Item Options
        |--------------------------------------------------------------------------
        */

        let itemOptions = `

            <option value="">
                Select Item
            </option>

            @foreach($stocks as $s)

                <option
                    value="{{ $s->item_id }}|{{ $s->price }}">

                    {{ $s->item_name }}
                    |
                    Cost:
                    {{ number_format($s->price, 2) }}
                    |
                    Stock:
                    {{ number_format($s->total_qty, 3) }}

                </option>

            @endforeach

        `;


        /*
        |--------------------------------------------------------------------------
        | CALCULATION
        |--------------------------------------------------------------------------
        */

        function calculateLocalPOS()
        {

            let totalItems = 0;

            let totalAmount = 0;


            document
                .querySelectorAll(
                    '#salesTable tbody tr'
                )
                .forEach(function(row) {


                    let qtyInput =
                        row.querySelector(
                            'input[name$="[qty]"]'
                        );


                    let priceInput =
                        row.querySelector(
                            'input[name$="[sale_price]"]'
                        );


                    if (
                        !qtyInput ||
                        !priceInput
                    ) {

                        return;

                    }


                    let qty =
                        parseFloat(
                            qtyInput.value
                        ) || 0;


                    let price =
                        parseFloat(
                            priceInput.value
                        ) || 0;


                    totalItems += qty;

                    totalAmount +=
                        qty * price;

                });


            document.getElementById(
                'totalItems'
            ).innerText =
                totalItems.toFixed(3);


            document.getElementById(
                'totalAmount'
            ).innerText =
                'Rs ' +
                totalAmount.toFixed(2);

        }


        /*
        |--------------------------------------------------------------------------
        | ADD ROW
        |--------------------------------------------------------------------------
        */

        document
            .getElementById('addRow')
            .addEventListener(
                'click',
                function () {


                    let row = `

                    <tr>

                        <td>

                            <select
                                name="items[${i}][group_key]"
                                class="form-control"
                                required>

                                ${itemOptions}

                            </select>

                        </td>


                        <td>

                            <input
                                type="number"
                                name="items[${i}][qty]"
                                step="0.001"
                                min="0.001"
                                class="form-control"
                                required>

                        </td>


                        <td>

                            <input
                                type="number"
                                name="items[${i}][sale_price]"
                                step="0.01"
                                min="0"
                                class="form-control"
                                required>

                        </td>


                        <td class="text-center">

                            <button
                                type="button"
                                class="btn btn-danger btn-sm removeRow">

                                <i class="fas fa-times"></i>

                            </button>

                        </td>

                    </tr>

                    `;


                    document
                        .querySelector(
                            '#salesTable tbody'
                        )
                        .insertAdjacentHTML(
                            'beforeend',
                            row
                        );


                    i++;

                    calculateLocalPOS();

                }
            );


        /*
        |--------------------------------------------------------------------------
        | REMOVE ROW
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'click',
            function(e) {


                let button =
                    e.target.closest(
                        '.removeRow'
                    );


                if (!button) {

                    return;

                }


                let rows =
                    document.querySelectorAll(
                        '#salesTable tbody tr'
                    );


                if (
                    rows.length <= 1
                ) {

                    return;

                }


                button
                    .closest('tr')
                    .remove();


                calculateLocalPOS();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | LIVE CALCULATION
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'input',
            function(e) {


                if (

                    e.target.matches(
                        'input[name$="[qty]"]'
                    )

                    ||

                    e.target.matches(
                        'input[name$="[sale_price]"]'
                    )

                ) {

                    calculateLocalPOS();

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | INITIAL CALCULATION
        |--------------------------------------------------------------------------
        */

        calculateLocalPOS();

    }

);

</script>

@endpush