@extends('layouts.app')

@section('title','Create Purchase')

@section('content')

<div class="row">

    <div class="col-md-9">

        <div class="card card-outline card-primary">

            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shopping-cart"></i>
                    Create Purchase
                </h3>
            </div>

            <div class="card-body">

                <form method="POST" action="{{ route('purchases.store') }}">
                    @csrf

                    <div class="form-group">
                        <label>Supplier</label>

                        <select name="supplier_id" class="form-control" required>
                            <option value="">Select Supplier</option>

                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover" id="purchaseTable">

                            <thead class="thead-light">

                                <tr>
                                    <th>Item</th>
                                    <th width="120">Qty</th>
                                    <th width="150">Price</th>
                                    <th width="150">Amount</th>
                                    <th width="80">Action</th>
                                </tr>

                            </thead>

                            <tbody>

                                <tr>

                                    <td>
                                        <select name="items[0][item_id]"
                                                class="form-control"
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
                                        <input type="number"
                                               step="0.01"
                                               name="items[0][qty]"
                                               class="form-control qty"
                                               required>
                                    </td>

                                    <td>
                                        <input type="number"
                                               step="0.01"
                                               name="items[0][price]"
                                               class="form-control price"
                                               required>
                                    </td>

                                    <td>
                                        <input type="text"
                                               class="form-control amount bg-light"
                                               value="0.00"
                                               readonly>
                                    </td>

                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-danger btn-sm removeRow">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                    <div class="mt-3">

                        <button type="button"
                                id="addRow"
                                class="btn btn-primary">

                            <i class="fas fa-plus"></i>
                            Add Item

                        </button>

                        <button type="submit"
                                class="btn btn-success float-right">

                            <i class="fas fa-save"></i>
                            Save Purchase

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <div class="col-md-3">

        <div class="card card-outline card-success">

            <div class="card-header">
                <h3 class="card-title">
                    Purchase Summary
                </h3>
            </div>

            <div class="card-body">

                <h5>Total Amount</h5>

                <h2 class="text-success">
                    Rs <span id="grandTotal">0.00</span>
                </h2>

            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    let i = 1;

    function calculatePurchase() {

        let grandTotal = 0;

        document.querySelectorAll('#purchaseTable tbody tr').forEach(function(row) {

            let qty = parseFloat(row.querySelector('.qty')?.value || 0);
            let price = parseFloat(row.querySelector('.price')?.value || 0);

            let amount = qty * price;

            row.querySelector('.amount').value = amount.toFixed(2);

            grandTotal += amount;

        });

        document.getElementById('grandTotal').innerText =
            grandTotal.toFixed(2);
    }

    document.getElementById('addRow').addEventListener('click', function () {

        let row = `
        <tr>

            <td>
                <select name="items[${i}][item_id]"
                        class="form-control"
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
                <input type="number"
                       step="0.01"
                       name="items[${i}][qty]"
                       class="form-control qty"
                       required>
            </td>

            <td>
                <input type="number"
                       step="0.01"
                       name="items[${i}][price]"
                       class="form-control price"
                       required>
            </td>

            <td>
                <input type="text"
                       class="form-control amount bg-light"
                       value="0.00"
                       readonly>
            </td>

            <td class="text-center">
                <button type="button"
                        class="btn btn-danger btn-sm removeRow">
                    <i class="fas fa-trash"></i>
                </button>
            </td>

        </tr>`;

        document.querySelector('#purchaseTable tbody')
            .insertAdjacentHTML('beforeend', row);

        i++;
    });

    document.addEventListener('click', function(e){

        if(e.target.closest('.removeRow')){

            let rows =
                document.querySelectorAll('#purchaseTable tbody tr');

            if(rows.length > 1){
                e.target.closest('tr').remove();
            }

            calculatePurchase();
        }
    });

    document.addEventListener('input', function(e){

        if(
            e.target.classList.contains('qty') ||
            e.target.classList.contains('price')
        ){
            calculatePurchase();
        }
    });

});
</script>

@endpush