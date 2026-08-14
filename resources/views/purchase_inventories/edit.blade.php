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


        <form
            method="POST"
            action="{{ route('purchase-inventories.update', $purchaseInventory->id) }}">

            @csrf
            @method('PUT')


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
                                    {{ old('supplier_id', $purchaseInventory->supplier_id) == $supplier->id ? 'selected' : '' }}>

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
                                value="{{ old('purchase_date', $purchaseInventory->purchase_date) }}"
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
                                value="{{ old('invoice_no', $purchaseInventory->invoice_no) }}"
                                placeholder="Supplier Invoice No">

                        </div>

                    </div>

                </div>


                <hr>


                {{-- ITEMS TABLE --}}
                <div class="table-responsive">

                    <table
                        class="table table-bordered table-sm table-hover"
                        id="itemsTable">

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

                                <th style="width:5%"
                                    class="text-center">

                                    Action

                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($purchaseInventory->items as $index => $purchaseItem)
                            <input
                                type="hidden"
                                name="items[{{ $index }}][id]"
                                value="{{ $purchaseItem->id }}">
                            <tr>

                                <td>

                                    <select
                                        name="items[{{ $index }}][item_id]"
                                        class="form-control form-control-sm"
                                        required>

                                        <option value="">
                                            Select Item
                                        </option>

                                        @foreach($items as $item)

                                        <option
                                            value="{{ $item->id }}"
                                            {{ old("item_id.$index", $purchaseItem->item_id) == $item->id ? 'selected' : '' }}>

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
                                        value="{{ old("qty.$index", $purchaseItem->qty) }}"
                                        required>

                                </td>


                                <td>

                                    <input
                                        type="number"
                                        name="items[{{ $index }}][price]"
                                        class="form-control form-control-sm price"
                                        min="0"
                                        step="0.01"
                                        value="{{ old("price.$index", $purchaseItem->price) }}"
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

                            <label
                                class="col-sm-5 col-form-label text-right">

                                Total

                            </label>

                            <div class="col-sm-7">

                                <input
                                    type="text"
                                    name="total"
                                    id="total"
                                    class="form-control form-control-sm font-weight-bold"
                                    value="0.00"
                                    readonly>

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

                    <i class="fas fa-save"></i>
                    Update Purchase

                </button>


                <a
                    href="{{ route('purchase-inventories.index') }}"
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
    let itemOptions = `
    <option value="">Select Item</option>

    @foreach($items as $item)

        <option value="{{ $item->id }}">
            {{ $item->name }}
        </option>

    @endforeach
`;


    /*
    |--------------------------------------------------------------------------
    | Add Row
    |--------------------------------------------------------------------------
    */

    $('#addRow').click(function() {

        $('#itemsTable tbody').append(`

        <tr>

            <td>

                <select
                    name="item_id[]"
                    class="form-control form-control-sm"
                    required>

                    ${itemOptions}

                </select>

            </td>


            <td>

                <input
                    type="number"
                    name="qty[]"
                    class="form-control form-control-sm qty"
                    min="0.001"
                    step="0.001"
                    required>

            </td>


            <td>

                <input
                    type="number"
                    name="price[]"
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

    `);

    });


    /*
    |--------------------------------------------------------------------------
    | Remove Row
    |--------------------------------------------------------------------------
    */

    $(document).on('click', '.removeRow', function() {

        let rows =
            $('#itemsTable tbody tr');

        if (rows.length <= 1) {

            alert('At least one item is required.');

            return;

        }

        $(this)
            .closest('tr')
            .remove();

        calculateTotal();

    });


    /*
    |--------------------------------------------------------------------------
    | Calculate Subtotal
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'input',
        '.qty, .price',
        function() {

            let row =
                $(this).closest('tr');

            calculateRow(row);

            calculateTotal();

        }
    );


    function calculateRow(row) {
        let qty =
            parseFloat(
                row.find('.qty').val()
            ) || 0;

        let price =
            parseFloat(
                row.find('.price').val()
            ) || 0;

        let subtotal =
            qty * price;

        row.find('.subtotal')
            .val(subtotal.toFixed(2));
    }


    /*
    |--------------------------------------------------------------------------
    | Calculate Total
    |--------------------------------------------------------------------------
    */

    function calculateTotal() {
        let total = 0;

        $('.subtotal').each(function() {

            total +=
                parseFloat($(this).val()) || 0;

        });

        $('#total')
            .val(total.toFixed(2));
    }


    /*
    |--------------------------------------------------------------------------
    | Initial Calculation
    |--------------------------------------------------------------------------
    */

    $(document).ready(function() {

        $('#itemsTable tbody tr').each(function() {

            calculateRow($(this));

        });

        calculateTotal();

    });
</script>

@endpush