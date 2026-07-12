@extends('layouts.app')

@section('title','Add Purchase')

@section('content')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">Add Purchase</h3>
    </div>

    <form method="POST" action="{{ route('purchase-inventories.store') }}">
        @csrf

        <div class="card-body">

            <div class="row">

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Supplier</label>
                        <select name="supplier_id" class="form-control" required>
                            <option value="">Select Supplier</option>

                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">
                                    {{ $supplier->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Purchase Date</label>
                        <input type="date"
                               name="purchase_date"
                               class="form-control"
                               value="{{ date('Y-m-d') }}"
                               required>
                    </div>
                </div>

            </div>

            <table class="table table-bordered" id="itemsTable">

                <thead>
                    <tr>
                        <th width="35%">Item</th>
                        <th width="15%">Qty</th>
                        <th width="20%">Price</th>
                        <th width="20%">Subtotal</th>
                        <th width="10%">
                            <button type="button"
                                    class="btn btn-success btn-sm"
                                    id="addRow">
                                +
                            </button>
                        </th>
                    </tr>
                </thead>

                <tbody>

                    <tr>

                        <td>
                            <select name="item_id[]"
                                    class="form-control"
                                    required>

                                <option value="">Select Item</option>

                                @foreach($items as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->name }}
                                    </option>
                                @endforeach

                            </select>
                        </td>

                        <td>
                            <input type="number"
                                   name="qty[]"
                                   class="form-control qty"
                                   step="0.01">
                        </td>

                        <td>
                            <input type="number"
                                   name="price[]"
                                   class="form-control price"
                                   step="0.01">
                        </td>

                        <td>
                            <input type="number"
                                   name="subtotal[]"
                                   class="form-control subtotal"
                                   readonly>
                        </td>

                        <td>
                            <button type="button"
                                    class="btn btn-danger removeRow">
                                X
                            </button>
                        </td>

                    </tr>

                </tbody>

            </table>

            <div class="row">

                <div class="col-md-3 offset-md-9">

                    <div class="form-group">
                        <label>Total</label>
                        <input type="number"
                               name="total"
                               id="total"
                               class="form-control"
                               readonly>
                    </div>

                </div>

            </div>

        </div>

        <div class="card-footer">

            <button type="submit"
                    class="btn btn-primary">
                Save Purchase
            </button>

        </div>

    </form>

</div>

@endsection

@push('scripts')

<script>

let itemOptions = `
<option value="">Select Item</option>
@foreach($items as $item)
<option value="{{ $item->id }}">{{ $item->name }}</option>
@endforeach
`;

$('#addRow').click(function(){

    $('#itemsTable tbody').append(`
        <tr>
            <td>
                <select name="item_id[]" class="form-control">
                    ${itemOptions}
                </select>
            </td>

            <td>
                <input type="number"
                       name="qty[]"
                       class="form-control qty"
                       step="0.01">
            </td>

            <td>
                <input type="number"
                       name="price[]"
                       class="form-control price"
                       step="0.01">
            </td>

            <td>
                <input type="number"
                       name="subtotal[]"
                       class="form-control subtotal"
                       readonly>
            </td>

            <td>
                <button type="button"
                        class="btn btn-danger removeRow">
                    X
                </button>
            </td>
        </tr>
    `);

});

$(document).on('click','.removeRow',function(){

    $(this).closest('tr').remove();
    calculateTotal();

});

$(document).on('keyup change','.qty,.price',function(){

    let row = $(this).closest('tr');

    let qty = parseFloat(row.find('.qty').val()) || 0;
    let price = parseFloat(row.find('.price').val()) || 0;

    row.find('.subtotal').val((qty * price).toFixed(2));

    calculateTotal();

});

function calculateTotal()
{
    let total = 0;

    $('.subtotal').each(function(){

        total += parseFloat($(this).val()) || 0;

    });

    $('#total').val(total.toFixed(2));
}

</script>

@endpush