@extends('layouts.app')

@section('title','Edit Export Sale')

@section('content')

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

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Edit Export Sale #{{ $sale->id }}</h3>
    </div>

<form method="POST" action="{{ route('export-sales.update',$sale->id) }}">
    @csrf
    @method('PUT')

    <div class="card-body">

        <div class="row">
            <div class="col-md-4">
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
            </div>

            <div class="col-md-2">
                <div class="form-group">
                    <label>Currency</label>
                    <input type="text" class="form-control"
                        value="{{ $sale->currency }}" readonly>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label>Exchange Rate</label>
                    <input type="number" step="0.0001"
                        name="exchange_rate"
                        value="{{ $sale->exchange_rate }}"
                        class="form-control">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm" id="salesTable">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th width="80">Qty</th>
                        <th width="120">Base Price</th>
                        <th width="120">Sale Price (LKR)</th>

                        @if($sale->currency == 'USD')
                            <th width="120">Sale Price (USD)</th>
                            <th width="120">Subtotal (USD)</th>
                        @endif

                        <th width="60">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($sale->items as $key => $si)
                        <tr>
                            <td>
                                <select name="items[{{ $key }}][item_id]"
                                    class="form-control form-control-sm" required>
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
                                    class="form-control form-control-sm"
                                    min="0.01"
                                    step="0.01"
                                    required>
                            </td>

                            <td>
                                <input type="number"
                                    name="items[{{ $key }}][base_price]"
                                    value="{{ $si->base_price }}"
                                    class="form-control form-control-sm"
                                    step="0.01"
                                    required>
                            </td>

                            <td>
                                <input type="number"
                                    name="items[{{ $key }}][sale_price]"
                                    value="{{ $si->sale_price }}"
                                    class="form-control form-control-sm"
                                    step="0.01">
                            </td>

                            @if($sale->currency == 'USD')
                                <td>
                                    <input type="number"
                                        step="0.01"
                                        name="items[{{ $key }}][sale_price_foreign]"
                                        value="{{ $si->sale_price_foreign }}"
                                        class="form-control form-control-sm">
                                </td>

                                <td>
                                    <input type="number"
                                        value="{{ $si->sub_total_foreign }}"
                                        class="form-control form-control-sm subtotal"
                                        readonly>
                                </td>
                            @endif

                            <td class="text-center">
                                <button type="button"
                                    class="btn btn-danger btn-xs removeRow">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="button" id="addRow"
            class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add Item
        </button>

    </div>

    <div class="card-footer">
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Update Sale
        </button>

        <a href="{{ route('export-sales.index') }}"
            class="btn btn-secondary">
            Cancel
        </a>
    </div>
</form>

</div>
@endsection

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function() {

    let i = {{ $sale->items->count() }};
    let exportMode = "{{ $sale->currency }}" === "USD";

    const itemOptions = `
        <option value="">Select Item</option>
        @foreach($items as $item)
            <option value="{{ $item->id }}">{{ addslashes($item->name) }}</option>
        @endforeach
    `;

    document.getElementById('addRow').addEventListener('click', function() {

        let row = `
            <tr>
                <td>
                    <select name="items[${i}][item_id]"
                        class="form-control form-control-sm" required>
                        ${itemOptions}
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
                        name="items[${i}][base_price]"
                        class="form-control form-control-sm"
                        step="0.01"
                        required>
                </td>

                <td>
                    <input type="number"
                        name="items[${i}][sale_price]"
                        class="form-control form-control-sm"
                        step="0.01">
                </td>

                ${exportMode ? `
                    <td>
                        <input type="number"
                            step="0.01"
                            name="items[${i}][sale_price_foreign]"
                            class="form-control form-control-sm">
                    </td>

                    <td>
                        <input type="number"
                            class="form-control form-control-sm subtotal"
                            readonly>
                    </td>
                ` : ''}

                <td class="text-center">
                    <button type="button"
                        class="btn btn-danger btn-xs removeRow">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;

        document.querySelector('#salesTable tbody')
            .insertAdjacentHTML('beforeend', row);

        i++;
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.removeRow')) {
            e.target.closest('tr').remove();
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.name && e.target.name.includes('sale_price_foreign')) {
            let row = e.target.closest('tr');
            let qty = parseFloat(row.querySelector('input[name*="[qty]"]').value) || 0;
            let price = parseFloat(e.target.value) || 0;
            let subtotal = row.querySelector('.subtotal');

            if (subtotal) {
                subtotal.value = (qty * price).toFixed(2);
            }
        }
    });

});
</script>

@endpush
