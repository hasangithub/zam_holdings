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
                        <i class="fas fa-shopping-cart"></i> Create Sale
                    </h3>
                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('sales.store') }}">
                        @csrf

                        {{-- CUSTOMER --}}
                        <div class="form-group">
                            <label>Customer</label>
                            <select name="customer_id" class="form-control" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <hr>

                        {{-- ITEMS TABLE --}}
                        <div class="table-responsive">

                            <table class="table table-bordered table-sm table-hover" id="salesTable">

                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th>Item</th>
                                        <th width="120">Qty</th>
                                        <th width="160">Sale Price</th>
                                        <th width="80" class="text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <tr>

                                        <td>
                                            <select name="items[0][group_key]" class="form-control" required>
                                                <option></option>
                                                @foreach($stocks as $s)
                                                <option value="{{ $s->item_id }}|{{ $s->price }}|{{ $s->total_qty }}">
                                                    {{ $s->item_name }}
                                                    | Cost: {{ $s->price }}
                                                    | Stock: {{ $s->total_qty }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td>
                                            <input type="number"
                                                   name="items[0][qty]"
                                                   step="0.01"
                                                   min="0.01"
                                                   class="form-control"
                                                   required>
                                        </td>

                                        <td>
                                            <input type="number"
                                                   name="items[0][sale_price]"
                                                   step="0.01"
                                                   class="form-control"
                                                   required>
                                        </td>

                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm removeRow">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                        {{-- ACTIONS --}}
                        <div class="mt-3">

                            <button type="button" id="addRow" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Item
                            </button>

                            <button type="submit" class="btn btn-success btn-sm float-right">
                                <i class="fas fa-save"></i> Save Sale
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
                        <i class="fas fa-calculator"></i> Sale Summary
                    </h5>
                </div>

                <div class="card-body p-2">

                    <div class="p-2 border rounded mb-2 bg-light">
                        <small>Total Items</small>
                        <h5 class="mb-0" id="totalItems">0</h5>
                    </div>

                    <div class="p-2 border rounded bg-success text-white">
                        <small>Total Amount</small>
                        <h4 class="mb-0" id="totalAmount">Rs 0.00</h4>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function() {

    let i = 1;

    // =========================
    // CALCULATION ENGINE
    // =========================
    function calculateLocalPOS() {

        let totalItems = 0;
        let totalAmount = 0;

        document.querySelectorAll('#salesTable tbody tr').forEach(function(row) {

            let qtyInput = row.querySelector('input[name$="[qty]"]');
            let priceInput = row.querySelector('input[name$="[sale_price]"]');

            if (!qtyInput || !priceInput) return;

            let qty = parseFloat(qtyInput.value || 0);
            let price = parseFloat(priceInput.value || 0);

            if (!isNaN(qty) && !isNaN(price)) {
                totalItems += qty;
                totalAmount += qty * price;
            }

        });

        document.getElementById('totalItems').innerText = totalItems.toFixed(2);
        document.getElementById('totalAmount').innerText = 'Rs ' + totalAmount.toFixed(2);
    }

    // =========================
    // ADD ROW
    // =========================
    document.getElementById('addRow').addEventListener('click', function() {

        let row = `
        <tr>

            <td>
                <select name="items[${i}][group_key]" class="form-control" required>
                    <option value="">Select Item</option>
                    @foreach($stocks as $s)
                    <option value="{{ $s->item_id }}|{{ $s->price }}|{{ $s->total_qty }}">
                        {{ $s->item_name }} | Cost: {{ $s->price }} | Stock: {{ $s->total_qty }}
                    </option>
                    @endforeach
                </select>
            </td>

            <td>
                <input type="number" name="items[${i}][qty]" step="0.01" class="form-control" required>
            </td>

            <td>
                <input type="number" name="items[${i}][sale_price]" step="0.01" class="form-control" required>
            </td>

            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm removeRow">
                    X
                </button>
            </td>

        </tr>`;

        document.querySelector('#salesTable tbody')
            .insertAdjacentHTML('beforeend', row);

        i++;
        calculateLocalPOS();

    });

    // =========================
    // REMOVE ROW
    // =========================
    document.addEventListener('click', function(e) {

        if (e.target.classList.contains('removeRow')) {

            let rows = document.querySelectorAll('#salesTable tbody tr');

            if (rows.length > 1) {
                e.target.closest('tr').remove();
            }

            calculateLocalPOS();
        }

    });

    // =========================
    // LIVE INPUT
    // =========================
    document.addEventListener('input', function(e) {

        if (
            e.target.matches('input[name$="[qty]"]') ||
            e.target.matches('input[name$="[sale_price]"]')
        ) {
            calculateLocalPOS();
        }

    });

    // initial
    calculateLocalPOS();

});
</script>

@endpush