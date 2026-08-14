@extends('layouts.app')

@section('title','Edit Purchase')

@section('content')

<div class="container-fluid">

    <div class="card">

        <div class="card-header bg-dark">
            <h3 class="card-title text-white">
                <i class="fas fa-edit mr-1"></i>
                Edit Purchase
            </h3>
        </div>

        <form action="{{ route('purchases.update', $purchase->id) }}" method="POST">

            @csrf
            @method('PUT')

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
                                        {{ old('supplier_id', $purchase->supplier_id) == $supplier->id ? 'selected' : '' }}>

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
                                value="{{ old('purchase_date', $purchase->purchase_date) }}"
                                required>

                        </div>

                    </div>


                    <div class="col-md-3">

                        <div class="form-group">

                            <label>
                                Invoice No
                            </label>

                            <input
                                type="text"
                                name="invoice_no"
                                class="form-control form-control-sm"
                                value="{{ old('invoice_no', $purchase->invoice_no) }}"
                                placeholder="Supplier Invoice No">

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

                                <th style="width:5%" class="text-center">
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($purchase->items as $index => $purchaseItem)

                                <tr>
                                     <input
                                type="hidden"
                                name="items[{{ $index }}][id]"
                                value="{{ $purchaseItem->id }}">

                                    <td>

                                        <select
                                            name="items[{{ $index }}][item_id]"
                                            class="form-control form-control-sm item-select"
                                            required>

                                            <option value="">
                                                Select Item
                                            </option>

                                            @foreach($items as $item)

                                                <option
                                                    value="{{ $item->id }}"
                                                    {{ old("items.$index.item_id", $purchaseItem->item_id) == $item->id ? 'selected' : '' }}>

                                                    {{ $item->name }}

                                                </option>

                                            @endforeach

                                        </select>

                                    </td>


                                    <td>

                                        <input
                                            type="number"
                                            name="items[{{ $index }}][qty]"
                                            class="form-control form-control-sm qty"
                                            min="0.001"
                                            step="0.001"
                                            value="{{ old("items.$index.qty", $purchaseItem->qty) }}"
                                            required>

                                    </td>


                                    <td>

                                        <input
                                            type="number"
                                            name="items[{{ $index }}][price]"
                                            class="form-control form-control-sm price"
                                            min="0"
                                            step="0.01"
                                            value="{{ old("items.$index.price", $purchaseItem->price) }}"
                                            required>

                                    </td>


                                    <td>

                                        <input
                                            type="text"
                                            class="form-control form-control-sm subtotal"
                                            value="{{ number_format($purchaseItem->qty * $purchaseItem->price, 2, '.', '') }}"
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

                            @endforeach

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
                    Update Purchase

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

let rowIndex = {{ $purchase->items->count() }};


/*
|--------------------------------------------------------------------------
| Calculate Row
|--------------------------------------------------------------------------
*/

function calculateRow(row)
{
    let qty =
        parseFloat(
            row.querySelector('.qty').value
        ) || 0;

    let price =
        parseFloat(
            row.querySelector('.price').value
        ) || 0;

    let subtotal = qty * price;

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
        .querySelectorAll('#purchaseTable tbody tr')
        .forEach(function(row) {

            let qty =
                parseFloat(
                    row.querySelector('.qty').value
                ) || 0;

            let price =
                parseFloat(
                    row.querySelector('.price').value
                ) || 0;

            total += qty * price;

        });

    document.getElementById('total').value =
        total.toFixed(2);
}


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

                    <option value="">
                        Select Item
                    </option>

                    @foreach($items as $item)

                        <option value="{{ $item->id }}">
                            {{ $item->name }}
                        </option>

                    @endforeach

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

        </tr>

        `;

        document
            .querySelector('#purchaseTable tbody')
            .insertAdjacentHTML('beforeend', row);

        rowIndex++;

    });


/*
|--------------------------------------------------------------------------
| Remove Row
|--------------------------------------------------------------------------
*/

document.addEventListener('click', function(e) {

    if (!e.target.closest('.removeRow')) {
        return;
    }

    let rows =
        document.querySelectorAll(
            '#purchaseTable tbody tr'
        );

    if (rows.length <= 1) {

        alert('At least one item is required.');

        return;
    }

    e.target.closest('tr').remove();

    calculateTotal();

});


/*
|--------------------------------------------------------------------------
| Quantity / Price Change
|--------------------------------------------------------------------------
*/

document.addEventListener('input', function(e) {

    if (
        !e.target.classList.contains('qty') &&
        !e.target.classList.contains('price')
    ) {
        return;
    }

    let row = e.target.closest('tr');

    calculateRow(row);

    calculateTotal();

});


/*
|--------------------------------------------------------------------------
| Initial Total
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', function() {

    document
        .querySelectorAll('#purchaseTable tbody tr')
        .forEach(function(row) {

            calculateRow(row);

        });

    calculateTotal();

});

</script>

@endpush