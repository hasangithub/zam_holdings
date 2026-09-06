@extends('layouts.app')

@push('css')
<style>
    /* Tom Select dropdown */
    .ts-dropdown {
        z-index: 99999 !important;
    }

    /* Do NOT use overflow hidden on the form/card */
    .card,
    .card-body {
        overflow: visible !important;
    }
</style>
@endpush

@section('title','Create Purchase')

@section('content')

<div class="container-fluid">

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
    <div class="card">

        <div class="card-header bg-dark">
            <h3 class="card-title text-white">
                <i class="fas fa-shopping-cart mr-1"></i>
                Create Purchase
            </h3>
        </div>

        <form action="{{ route('purchases.store') }}" method="POST">

            @csrf

            <div class="card-body">

                {{-- PURCHASE INFORMATION --}}
                <div class="row">

                    <div class="col-md-6">

                        <div class="form-group">

                            <label>
                                Supplier
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                name="supplier_id"
                                class="form-control form-control-sm"
                                required>

                                <option value="">
                                    Select Supplier
                                </option>

                                @foreach($suppliers as $supplier)

                                <option
                                    value="{{ $supplier->id }}"
                                    {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>

                                    {{ $supplier->name }}

                                </option>

                                @endforeach

                            </select>

                        </div>

                    </div>


                    <div class="col-md-3">

                        <div class="form-group">

                            <label>
                                Purchase Date
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="date"
                                name="purchase_date"
                                class="form-control form-control-sm"
                                value="{{ old('purchase_date', date('Y-m-d')) }}"
                                required>

                        </div>
                    </div>
                </div>
                <hr>
                {{-- ITEMS TABLE --}}
                <div class="table-responsive">

                    <table
                        class="table table-bordered table-sm table-hover"
                        id="purchaseTable">

                        <thead class="bg-dark text-white">

                            <tr>

                                <th style="width:45%">
                                    Item
                                </th>

                                <th style="width:15%">
                                    Qty
                                </th>

                                <th style="width:20%">
                                    Purchase Price
                                </th>

                                <th style="width:15%">
                                    Subtotal
                                </th>

                                <th
                                    style="width:5%"
                                    class="text-center">

                                    Action

                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr>

                                <td>

                                    <select
                                        name="items[0][item_id]"
                                        class="form-control form-control-sm item-select"
                                        required>

                                        <option value="">
                                            Select Item
                                        </option>

                                        @foreach($items as $item)

                                        <option
                                            value="{{ $item->id }}">

                                            {{ $item->name }}

                                        </option>

                                        @endforeach

                                    </select>

                                </td>


                                <td>

                                    <input
                                        type="number"
                                        name="items[0][qty]"
                                        class="form-control form-control-sm qty"
                                        min="0.001"
                                        step="0.001"
                                        required>

                                </td>


                                <td>

                                    <input
                                        type="number"
                                        name="items[0][price]"
                                        class="form-control form-control-sm price"
                                        min="0"
                                        step="0.01"
                                        required>

                                </td>


                                <td>

                                    <input
                                        type="text"
                                        class="form-control form-control-sm subtotal"
                                        readonly>

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


                {{-- ADD ITEM --}}
                <button
                    type="button"
                    class="btn btn-primary btn-sm"
                    id="addRow">

                    <i class="fas fa-plus"></i>
                    Add Item

                </button>


                <hr>


                {{-- TOTAL --}}
                <div class="row justify-content-end">

                    <div class="col-md-4">

                        <div class="form-group row">

                            <label class="col-sm-5 col-form-label text-right">
                                Total
                            </label>

                            <div class="col-sm-7">

                                <input
                                    type="text"
                                    id="total"
                                    class="form-control form-control-sm font-weight-bold"
                                    readonly
                                    value="0.00">

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ACTION BUTTONS --}}
            <div class="card-footer">

                <button
                    type="submit"
                    class="btn btn-success">

                    <i class="fas fa-save"></i>
                    Save Purchase

                </button>

                <a
                    href="{{ route('purchases.index') }}"
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

let rowIndex = 1;


/*
|--------------------------------------------------------------------------
| Item Options
|--------------------------------------------------------------------------
*/

const itemOptions = `
    <option value="">Select Item</option>

    @foreach($items as $item)
        <option value="{{ $item->id }}">
            {{ addslashes($item->name) }}
        </option>
    @endforeach
`;


/*
|--------------------------------------------------------------------------
| Add Row
|--------------------------------------------------------------------------
*/

document.getElementById('addRow')
    .addEventListener('click', function () {

        let row = `

        <tr>

            <td>

                <select
                    name="items[${rowIndex}][item_id]"
                    class="form-control form-control-sm item-select"
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
                    name="items[${rowIndex}][price]"
                    class="form-control form-control-sm price"
                    min="0"
                    step="0.01"
                    required>

            </td>


            <td>

                <input
                    type="text"
                    class="form-control form-control-sm subtotal"
                    readonly>

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
            .querySelector('#purchaseTable tbody')
            .insertAdjacentHTML('beforeend', row);

         // Get the newly added select
        const newSelect =
            document.querySelector(
                '#purchaseTable tbody tr:last-child .item-select'
            );

        // Initialize Tom Select
        new TomSelect(newSelect, {
            create: false,
            maxItems: 1,
            allowEmptyOption: true,
            placeholder: 'Search item...',
            dropdownParent: 'body'
        });



        rowIndex++;

    });


/*
|--------------------------------------------------------------------------
| Remove Row
|--------------------------------------------------------------------------
*/

document.addEventListener('click', function(e) {

    const button = e.target.closest('.removeRow');

    if (!button) {
        return;
    }


    const rows =
        document.querySelectorAll(
            '#purchaseTable tbody tr'
        );


    if (rows.length <= 1) {

        alert('At least one item is required.');

        return;
    }


    button.closest('tr').remove();

    calculateTotal();

});


/*
|--------------------------------------------------------------------------
| Calculate Row
|--------------------------------------------------------------------------
*/

document.addEventListener('input', function(e) {

    if (
        !e.target.classList.contains('qty') &&
        !e.target.classList.contains('price')
    ) {
        return;
    }


    const row =
        e.target.closest('tr');


    calculateRow(row);

    calculateTotal();

});


function calculateRow(row)
{
    const qty =
        parseFloat(
            row.querySelector('.qty').value
        ) || 0;


    const price =
        parseFloat(
            row.querySelector('.price').value
        ) || 0;


    const subtotal =
        qty * price;


    row.querySelector('.subtotal').value =
        subtotal.toFixed(2);
}


/*
|--------------------------------------------------------------------------
| Calculate Total
|--------------------------------------------------------------------------
*/

function calculateTotal()
{
    let total = 0;


    document
        .querySelectorAll(
            '#purchaseTable tbody tr'
        )
        .forEach(function(row) {

            const qty =
                parseFloat(
                    row.querySelector('.qty').value
                ) || 0;


            const price =
                parseFloat(
                    row.querySelector('.price').value
                ) || 0;


            total += qty * price;

        });


    document.getElementById('total').value =
        total.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.item-select').forEach(function (element) {

        new TomSelect(element, {
            create: false,
            maxItems: 1,
            allowEmptyOption: true,
            placeholder: 'Search item...',
            dropdownParent: 'body'
        });

    });

});

</script>

@endpush