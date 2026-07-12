@extends('layouts.app')

@section('title','Shipment Plan Step 2')

@section('content')

<div class="container-fluid">

    <div class="card card-success card-outline">

        <div class="card-header">
            <h3 class="card-title">Step 2: Allocate Shipment Weights</h3>
        </div>

        <div class="card-body">

            <form method="POST" action="{{ route('shipment-plans.store') }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-4"> <label>Date</label> <input type="date" name="shipment_date" value="{{ date('Y-m-d') }}" class="form-control" required> </div>
                </div>

                <div class="table-responsive">

                    <table class="table table-bordered table-sm">

                        <thead class="bg-light">

                            <tr>
                                <th>Item</th>
                                <th>Weight</th>

                                @foreach($customers as $customer)
                                <th>{{ $customer->name }}</th>
                                @endforeach

                                <th>Total Allocated</th>
                                <th>Balance</th>
                            </tr>

                        </thead>

                        <tbody>

                            @foreach($items as $item)

                            <tr data-item="{{ $item->id }}">

                                <input type="hidden"
                                    name="allocations[{{ $item->id }}][item_id]"
                                    value="{{ $item->id }}">

                                {{-- ITEM --}}
                                <td>
                                    <strong>{{ $item->name }}</strong>
                                </td>

                                {{-- STOCK --}}
                                <td>
                                    <input type="hidden"
                                        class="stock"
                                        value="{{ $item->stock_qty ?? 0 }}">

                                    <span class="stock-label">
                                        {{ $item->stock_qty ?? 0 }}
                                    </span>
                                </td>

                                {{-- CUSTOMER INPUTS --}}
                                @foreach($customers as $customer)

                                <td>
                                    <input type="number"
                                        step="0.01"
                                        class="form-control qty-input"
                                        data-item="{{ $item->id }}"
                                        name="allocations[{{ $item->id }}][customers][{{ $customer->id }}]">
                                </td>

                                @endforeach

                                {{-- TOTAL ALLOCATED --}}
                                <td>
                                    <span class="allocated">0</span>
                                </td>

                                {{-- BALANCE --}}
                                <td>
                                    <span class="balance">0</span>
                                </td>

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

        if (!e.target.classList.contains('qty-input')) return;

        let row = e.target.closest('tr');

        let stock = parseFloat(row.querySelector('.stock').value || 0);

        let inputs = row.querySelectorAll('.qty-input');

        let total = 0;

        inputs.forEach(function(inp) {
            total += parseFloat(inp.value || 0);
        });

        let balance = stock - total;

        row.querySelector('.allocated').innerText = total.toFixed(2);
        row.querySelector('.balance').innerText = balance.toFixed(2);

        // ❗ Validation
        if (balance < 0) {
            e.target.value = 0;

            alert("Cannot exceed stock limit!");

            // recalc again
            let total2 = 0;

            inputs.forEach(function(inp) {
                total2 += parseFloat(inp.value || 0);
            });

            row.querySelector('.allocated').innerText = total2.toFixed(2);
            row.querySelector('.balance').innerText = (stock - total2).toFixed(2);
        }

    });
</script>
@endpush