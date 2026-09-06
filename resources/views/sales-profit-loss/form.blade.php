@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h4>Sales Profit & Loss</h4>

        <a href="{{ route('sales-profit-loss.index') }}"
            class="btn btn-secondary">

            Back

        </a>

    </div>


    {{-- =========================================================
         SELECT SALES INVOICE
    ========================================================== --}}

    @if(!$sale)

    <div class="card">

        <div class="card-header">

            <strong>Select Sales Invoice</strong>

        </div>


        <div class="card-body">

            <form method="GET"
                action="{{ route('sales-profit-loss.create') }}">

                <div class="row">

                    <div class="col-md-8">

                        <label class="form-label">
                            Sales Invoice
                        </label>


                        <select name="sale_id"
                            class="form-control"
                            required>

                            <option value="">
                                -- Select Invoice --
                            </option>


                            @foreach($sales as $item)

                            <option value="{{ $item->id }}">

                                {{ $item->invoice_no }}

                                -

                                {{ optional(
                                            $item->customer
                                        )->name }}

                            </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="col-md-4 d-flex align-items-end">

                        <button type="submit"
                            class="btn btn-primary">

                            Load Invoice

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    @else


    {{-- =====================================================
             EXISTING SALE
        ====================================================== --}}

    @php
    $profitLoss = $sale->profitLoss;
    @endphp


    <div class="card">


        {{-- Invoice header --}}

        <div class="card-header">

            <div class="row">

                <div class="col-md-4">

                    <strong>Invoice:</strong>

                    {{ $sale->invoice_no }}

                </div>


                <div class="col-md-4">

                    <strong>Customer:</strong>

                    {{ optional(
                            $sale->customer
                        )->name }}

                </div>


                <div class="col-md-4">

                    <strong>Date:</strong>

                    {{ $sale->sale_date
                            ? \Carbon\Carbon::parse(
                                $sale->sale_date
                              )->format('d/m/Y')
                            : '-' }}

                </div>

            </div>

        </div>


        <div class="card-body">


            <form method="POST"

                action="{{
                        $profitLoss

                            ? route(
                                'sales-profit-loss.update',
                                $profitLoss
                            )

                            : route(
                                'sales-profit-loss.store'
                            )
                      }}">

                @csrf


                @if($profitLoss)

                @method('PUT')

                @else

                <input type="hidden"
                    name="sale_id"
                    value="{{ $sale->id }}">

                @endif


                <div class="table-responsive">

                    <table class="table table-bordered table-erp" id="profitLossTable">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th width="120">
                                    Sales Weight
                                </th>
                                <th width="120">
                                    Sales Price
                                </th>
                                <th width="120">
                                    Purchase Price
                                </th>
                                <th width="120">
                                    Other Exp / Kg
                                </th>
                                <th width="130">
                                    Purchase Cost / Kg
                                </th>
                                <th width="130">
                                    Total Cost
                                </th>
                                <th width="130">
                                    Sales Amount
                                </th>
                                <th width="130">
                                    Profit / Loss
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                            @if($profitLoss)

                            {{-- Existing Profit & Loss --}}
                            @foreach($profitLoss->items as $index => $item)

                            <tr>
                                <td>
                                    {{ $item->item_name }}

                                    <input type="hidden"
                                        name="items[{{ $index }}][product_id]"
                                        value="{{ $item->product_id }}">

                                    <input type="hidden"
                                        name="items[{{ $index }}][item_name]"
                                        value="{{ $item->item_name }}">
                                </td>

                                <td>
                                    <input type="number"
                                        step="0.001"
                                        name="items[{{ $index }}][sales_weight]"
                                        value="{{ $item->sales_weight }}"
                                        class="form-control sales-weight">
                                </td>

                                <td>
                                    <input type="number"
                                        step="0.01"
                                        name="items[{{ $index }}][sales_price]"
                                        value="{{ $item->sales_price }}"
                                        class="form-control sales-price">
                                </td>

                                <td>
                                    <input type="number"
                                        step="0.01"
                                        name="items[{{ $index }}][purchase_price]"
                                        value="{{ $item->purchase_price }}"
                                        class="form-control purchase-price">
                                </td>

                                <td>
                                    <input type="number"
                                        step="0.01"
                                        name="items[{{ $index }}][other_expense_per_kg]"
                                        value="{{ $item->other_expense_per_kg }}"
                                        class="form-control other-expense">
                                </td>

                                <td class="purchase-cost">
                                    {{ number_format($item->purchase_cost_per_kg, 2) }}
                                </td>

                                <td class="total-cost">
                                    {{ number_format($item->total_cost, 2) }}
                                </td>

                                <td class="sales-amount">
                                    {{ number_format($item->sales_amount, 2) }}
                                </td>

                                <td class="profit-loss">
                                    {{ number_format($item->profit_loss, 2) }}
                                </td>
                            </tr>

                            @endforeach

                            @else

                            {{-- New Profit & Loss: Load from Sale Items --}}
                            @foreach($sale->items as $index => $saleItem)

                            <tr>
                                <td>
                                    {{ optional($saleItem->item)->name }}

                                    <input type="hidden"
                                        name="items[{{ $index }}][product_id]"
                                        value="{{ $saleItem->item_id }}">

                                    <input type="hidden"
                                        name="items[{{ $index }}][item_name]"
                                        value="{{ optional($saleItem->item)->name }}">
                                </td>

                                <td>
                                    <input type="number"
                                        step="0.001"
                                        name="items[{{ $index }}][sales_weight]"
                                        value="{{ $saleItem->qty }}"
                                        class="form-control sales-weight">
                                </td>

                                <td>
                                    <input type="number"
                                        step="0.01"
                                        name="items[{{ $index }}][sales_price]"
                                        value="{{ $saleItem->sale_price }}"
                                        class="form-control sales-price">
                                </td>

                                <td>
                                    <input type="number"
                                        step="0.01"
                                        name="items[{{ $index }}][purchase_price]"
                                        value="{{ $saleItem->base_price }}"
                                        class="form-control purchase-price">
                                </td>

                                <td>
                                    <input type="number"
                                        step="0.01"
                                        name="items[{{ $index }}][other_expense_per_kg]"
                                        value="0"
                                        class="form-control other-expense">
                                </td>

                                <td class="purchase-cost">0.00</td>
                                <td class="total-cost">0.00</td>
                                <td class="sales-amount">0.00</td>
                                <td class="profit-loss">0.00</td>
                            </tr>

                            @endforeach

                            @endif

                        </tbody>


                        <tfoot>

                            <tr>

                                <th>TOTAL</th>


                                <th id="totalSalesWeight">
                                    0.000
                                </th>


                                <th></th>

                                <th></th>

                                <th></th>

                                <th></th>


                                <th id="totalCost">
                                    0.00
                                </th>


                                <th id="totalSalesAmount">
                                    0.00
                                </th>


                                <th id="totalProfitLoss">
                                    0.00
                                </th>

                            </tr>

                        </tfoot>

                    </table>

                </div>


                <div class="text-end mt-3">

                    <button type="submit"
                        class="btn btn-success">

                        @if($profitLoss)

                        Update

                        @else

                        Save

                        @endif

                    </button>

                </div>


            </form>

        </div>

    </div>

    @endif

</div>

@endsection

@push('scripts')
<script>
function calculateProfitLoss() {

    let totalWeight = 0;
    let totalCost = 0;
    let totalSales = 0;
    let totalProfitLoss = 0;

    document.querySelectorAll('#profitLossTable tbody tr').forEach(function(row) {

        let weight = parseFloat(
            row.querySelector('.sales-weight')?.value
        ) || 0;

        let salesPrice = parseFloat(
            row.querySelector('.sales-price')?.value
        ) || 0;

        let purchasePrice = parseFloat(
            row.querySelector('.purchase-price')?.value
        ) || 0;

        let otherExpense = parseFloat(
            row.querySelector('.other-expense')?.value
        ) || 0;


        // Purchase Cost / Kg
        let purchaseCost = purchasePrice + otherExpense;

        // Total Cost
        let cost = weight * purchaseCost;

        // Sales Amount
        let salesAmount = weight * salesPrice;

        // Profit / Loss
        let profitLoss = salesAmount - cost;


        // Display values
        row.querySelector('.purchase-cost').innerText =
            purchaseCost.toFixed(2);

        row.querySelector('.total-cost').innerText =
            cost.toFixed(2);

        row.querySelector('.sales-amount').innerText =
            salesAmount.toFixed(2);

        row.querySelector('.profit-loss').innerText =
            profitLoss.toFixed(2);


        // Totals
        totalWeight += weight;
        totalCost += cost;
        totalSales += salesAmount;
        totalProfitLoss += profitLoss;
    });


    document.getElementById('totalSalesWeight').innerText =
        totalWeight.toFixed(3);

    document.getElementById('totalCost').innerText =
        totalCost.toFixed(2);

    document.getElementById('totalSalesAmount').innerText =
        totalSales.toFixed(2);

    document.getElementById('totalProfitLoss').innerText =
        totalProfitLoss.toFixed(2);
}


// Recalculate when values change
document.addEventListener('input', function(e) {

    if (
        e.target.classList.contains('sales-weight') ||
        e.target.classList.contains('sales-price') ||
        e.target.classList.contains('purchase-price') ||
        e.target.classList.contains('other-expense')
    ) {
        calculateProfitLoss();
    }

});


// Calculate when page loads
document.addEventListener('DOMContentLoaded', function() {
    calculateProfitLoss();
});
</script>
@endpush