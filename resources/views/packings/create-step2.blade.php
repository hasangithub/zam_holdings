@extends('layouts.app')

@section('title','Shipment Plan Step 2')

@section('content')

<div class="container-fluid">

    <div class="card card-success card-outline">

        <div class="card-header">
            <h3 class="card-title">Step 2: Allocate Shipment Weights</h3>
        </div>

        <div class="card-body">

            <form method="POST" action="{{ route('packings.store') }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-4"> <label>Date</label> <input type="date" name="shipment_date" value="{{ date('Y-m-d') }}" class="form-control" required> </div>
                </div>

                <div class="table-responsive">

                    <table class="table table-bordered table-sm table-erp">

                        <thead class="bg-light">

                            <tr>
                                <th>Item</th>
                                <th>Weight</th>

                                @foreach($customers as $customer)
                                <th>{{ $customer->name }}</th>
                                @endforeach

                                <th>Customer Total</th>
                                <th>Reject</th>
                                <th>Market</th>
                                <th>Stock</th>
                                <th>Total Allocated</th>
                                <th>Difference</th>
                            </tr>

                        </thead>

                        <tbody>

                            @foreach($categories as $category)

                
                            @foreach($category->items as $item)

                            <tr class="item-row" data-category="{{ $category->id }}">

                                <input type="hidden"
                                    name="items[{{ $item->id }}][item_id]"
                                    value="{{ $item->id }}">

                                {{-- ITEM --}}
                                <td>
                                    <strong>{{ $item->name }}</strong>
                                </td>

                                {{-- WEIGHT --}}
                                <td>

                                    <input type="hidden"
                                        class="stock"
                                        name="items[{{ $item->id }}][actual_weight]"
                                        value="{{ $item->stock_qty ?? 0 }}">

                                    <span class="stock-label">
                                        {{ number_format($item->stock_qty ?? 0,2) }}
                                    </span>

                                </td>

                                {{-- CUSTOMER WEIGHTS --}}
                                @foreach($customers as $customer)

                                <td>

                                    <input type="number"
                                        step="0.01"
                                        class="form-control form-control-sm qty-input customer-input"
                                        data-category="{{ $category->id }}"
                                        name="items[{{ $item->id }}][customers][{{ $customer->id }}][packed_weight]">

                                </td>

                                @endforeach

                                {{-- CUSTOMER TOTAL --}}
                                <td>

                                    <span class="customer-total font-weight-bold">
                                        0.00
                                    </span>

                                </td>

                                {{-- REJECT --}}
                                <td>

                                    <input type="number"
                                        step="0.01"
                                        class="form-control form-control-sm qty-input reject-input"
                                        data-category="{{ $category->id }}"
                                        name="items[{{ $item->id }}][reject]">

                                </td>

                                {{-- MARKET --}}
                                <td>

                                    <input type="number"
                                        step="0.01"
                                        class="form-control form-control-sm qty-input market-input"
                                        data-category="{{ $category->id }}"
                                        name="items[{{ $item->id }}][market]">

                                </td>

                                {{-- STOCK --}}
                                <td>

                                    <input type="number"
                                        step="0.01"
                                        class="form-control form-control-sm qty-input stock-input"
                                        data-category="{{ $category->id }}"
                                        name="items[{{ $item->id }}][stock]">

                                </td>

                                {{-- TOTAL ALLOCATED --}}
                                <td>

                                    <span class="allocated font-weight-bold text-primary">
                                        0.00
                                    </span>

                                </td>

                                {{-- DIFFERENCE --}}
                                <td>

                                    <span class="difference font-weight-bold text-success">
                                        0.00
                                    </span>

                                </td>

                            </tr>

                            @endforeach

                            {{-- CATEGORY TOTAL --}}
                            <tr class="table-warning category-total"
                                data-category="{{ $category->id }}">

                                <th colspan="{{ $customers->count() + 7 }}"
                                    class="text-right">

                                    Total Difference - {{ $category->name }}

                                </th>

                                <th>

                                    <span class="category-difference">
                                        0.00
                                    </span>

                                </th>

                            </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

                <div class="text-right mt-3">

                    <button class="btn btn-success">
                        Save Shipment Plan
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('input', function(e) {

        if (!e.target.classList.contains('qty-input'))
            return;

        calculateRow(e.target.closest('tr'));
        calculateCategory(e.target.closest('tr').dataset.category);

    });

    function calculateRow(row) {
        let weight = parseFloat(row.querySelector('.stock').value) || 0;

        // Customer Total
        let customerTotal = 0;

        row.querySelectorAll('.customer-input').forEach(function(input) {

            customerTotal += parseFloat(input.value) || 0;

        });

        // Reject
        let reject = parseFloat(row.querySelector('.reject-input').value) || 0;

        // Market
        let market = parseFloat(row.querySelector('.market-input').value) || 0;

        // Stock
        let stock = parseFloat(row.querySelector('.stock-input').value) || 0;

        // Total Allocated
        let allocated = customerTotal + reject + market + stock;

        // Validation
        if (allocated > weight) {

            alert('Total allocated cannot exceed available weight.');

            event.target.value = '';

            customerTotal = 0;

            row.querySelectorAll('.customer-input').forEach(function(input) {

                customerTotal += parseFloat(input.value) || 0;

            });

            reject = parseFloat(row.querySelector('.reject-input').value) || 0;
            market = parseFloat(row.querySelector('.market-input').value) || 0;
            stock = parseFloat(row.querySelector('.stock-input').value) || 0;

            allocated = customerTotal + reject + market + stock;

        }

        let difference = weight - allocated;

        row.querySelector('.customer-total').innerHTML = customerTotal.toFixed(2);

        row.querySelector('.allocated').innerHTML = allocated.toFixed(2);

        let diff = row.querySelector('.difference');

        diff.innerHTML = difference.toFixed(2);

        if (difference < 0) {
            diff.classList.remove('text-success');
            diff.classList.add('text-danger');
        } else {
            diff.classList.remove('text-danger');
            diff.classList.add('text-success');
        }
    }

    function calculateCategory(categoryId) {
        let totalDifference = 0;

        document.querySelectorAll('tr.item-row[data-category="' + categoryId + '"]')
            .forEach(function(row) {

                totalDifference += parseFloat(
                    row.querySelector('.difference').innerText
                ) || 0;

            });

        document.querySelector(
            '.category-total[data-category="' + categoryId + '"] .category-difference'
        ).innerHTML = totalDifference.toFixed(2);
    }
</script>
@endpush