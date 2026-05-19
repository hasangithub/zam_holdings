@extends('layouts.app')

@section('title','Create Purchase')

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Create Purchase</h3>
    </div>

    <div class="card-body">

        <form method="POST" action="{{ route('purchases.store') }}">
            @csrf

            {{-- SUPPLIER --}}
            <div class="form-group">
                <label>Supplier</label>
                <select name="supplier_id" class="form-control" required>
                    <option value="">Select Supplier</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <hr>

            {{-- ITEMS TABLE --}}
            <table class="table table-bordered" id="purchaseTable">
                <thead class="bg-light">
                    <tr>
                        <th>Item</th>
                        <th width="120">Qty</th>
                        <th width="150">Price</th>
                        <th width="80">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>
                            <select name="items[0][item_id]" class="form-control" required>
                                <option value="">Select Item</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </td>

                        <td>
                            <input type="number" name="items[0][qty]" class="form-control" required>
                        </td>

                        <td>
                            <input type="number" name="items[0][price]" class="form-control" required>
                        </td>

                        <td>
                            <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- BUTTONS --}}
            <button type="button" id="addRow" class="btn btn-primary btn-sm">
                + Add Row
            </button>

            <button type="submit" class="btn btn-success btn-sm">
                Save Purchase
            </button>

        </form>

    </div>
</div>

@endsection


@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    let i = 1;

    // ADD ROW
    document.getElementById('addRow').addEventListener('click', function () {

        let row = `
        <tr>
            <td>
                <select name="items[${i}][item_id]" class="form-control" required>
                    <option value="">Select Item</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </td>

            <td>
                <input type="number" name="items[${i}][qty]" class="form-control" required>
            </td>

            <td>
                <input type="number" name="items[${i}][price]" class="form-control" required>
            </td>

            <td>
                <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
            </td>
        </tr>`;

        document.querySelector("#purchaseTable tbody").insertAdjacentHTML('beforeend', row);
        i++;
    });

    // REMOVE ROW (event delegation)
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('removeRow')) {
            e.target.closest('tr').remove();
        }
    });

});
</script>

@endpush