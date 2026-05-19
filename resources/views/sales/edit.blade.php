@extends('layouts.app')

@section('title','Edit Sale')

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Sale #{{ $sale->id }}</h3>
    </div>

    <div class="card-body">

        <form method="POST" action="{{ route('sales.update',$sale->id) }}">
            @csrf
            @method('PUT')

            {{-- CUSTOMER --}}
            <div class="form-group">
                <label>Customer</label>
                <select name="customer_id" class="form-control" required>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}"
                            {{ $sale->customer_id == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <hr>

            {{-- ITEMS TABLE --}}
            <table class="table table-bordered" id="salesTable">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th width="100">Qty</th>
                        <th width="130">Base Price</th>
                        <th width="130">Sale Price</th>
                        <th width="80">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($sale->items as $key => $si)
                    <tr>

                        <td>
                            <select name="items[{{ $key }}][item_id]" class="form-control" required>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}"
                                        {{ $si->item_id == $item->id ? 'selected' : '' }}>
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td>
                            <input type="number"
                                   name="items[{{ $key }}][qty]"
                                   value="{{ $si->qty }}"
                                   class="form-control"
                                   required>
                        </td>

                        <td>
                            <input type="number"
                                   name="items[{{ $key }}][base_price]"
                                   value="{{ $si->base_price }}"
                                   class="form-control"
                                   required>
                        </td>

                        <td>
                            <input type="number"
                                   name="items[{{ $key }}][sale_price]"
                                   value="{{ $si->sale_price }}"
                                   class="form-control">
                        </td>

                        <td>
                            <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
                        </td>

                    </tr>
                    @endforeach

                </tbody>
            </table>

            <button type="button" id="addRow" class="btn btn-primary btn-sm">
                + Add Row
            </button>

            <button type="submit" class="btn btn-success btn-sm">
                Update Sale
            </button>

        </form>

    </div>
</div>

@endsection


@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    let i = {{ count($sale->items) }};

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
                <input type="number" name="items[${i}][base_price]" class="form-control" required>
            </td>

            <td>
                <input type="number" name="items[${i}][sale_price]" class="form-control">
            </td>

            <td>
                <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
            </td>

        </tr>`;

        document.querySelector("#salesTable tbody").insertAdjacentHTML('beforeend', row);
        i++;
    });

    // REMOVE ROW
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('removeRow')) {
            e.target.closest('tr').remove();
        }
    });

});
</script>

@endpush