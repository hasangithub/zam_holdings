@extends('layouts.app')

@section('title','Invoice Profit Analysis')

@section('content')

<div class="container-fluid">

    <div class="card card-primary card-outline">

        <div class="card-header py-2">
            <h3 class="card-title">
                <i class="fas fa-chart-line mr-1"></i>
                Invoice Profit Analysis  @if($report)
                <small class="text-muted d-block">
                    Snapshot:
                    <strong>
                        {{ $report->updated_at->format('Y-m-d H:i') }}
                    </strong>
                </small>
                @endif

            </h3>
            <div class="d-flex justify-content-end">
                @if($sale)
                <button type="button"
                    id="saveReportBtn"
                    class="btn btn-success btn-sm">
                    <i class="fas fa-save"></i>
                    Save / Update Report
                </button>
                @endif
            </div>
        </div>

        <div class="card-body">

            {{-- =========================
                INVOICE SELECT
            ========================== --}}

            <form method="GET"
                action="{{ route('reports.invoice-profit-analysis') }}">

                <div class="row">

                    <div class="col-md-6">

                        <label>Invoice</label>

                        <select
                            name="sale_id"
                            class="form-control select2"
                            required>

                            <option value="">Select Invoice</option>

                            @foreach($sales as $saleOption)

                            <option
                                value="{{ $saleOption->id }}"
                                {{ request('sale_id')==$saleOption->id ? 'selected':'' }}>

                                {{ $saleOption->invoice_id }}
                                -
                                {{ $saleOption->customer->name }}

                            </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-2">

                        <label>&nbsp;</label>

                        <button
                            type="submit"
                            class="btn btn-primary btn-block">

                            <i class="fas fa-search"></i>

                            Load

                        </button>

                    </div>

                </div>

            </form>


            @if($sale)

            <hr>


            {{-- =========================
                HEADER
            ========================== --}}

            <div class="row mb-3">

                <div class="col-md-2">

                    <label>Invoice No</label>

                    <input
                        class="form-control form-control-sm"
                        readonly
                        value="{{ $sale->invoice_id }}">

                </div>

                <div class="col-md-3">

                    <label>Customer</label>

                    <input
                        class="form-control form-control-sm"
                        readonly
                        value="{{ $sale->customer->name }}">

                </div>

                <div class="col-md-2">

                    <label>Date</label>

                    <input
                        class="form-control form-control-sm"
                        readonly
                        value="{{ $sale->sale_date }}">

                </div>

                <div class="col-md-2">

                    <label>Exchange Rate</label>

                    <input
                        type="number"
                        step="0.01"
                        id="exchange_rate"
                        value="{{ $report->exchange_rate ?? 330 }}"
                        class="form-control form-control-sm">

                </div>

                <div class="col-md-2">

                    <label>F & P Per Kg</label>

                    <input
                        type="number"
                        step="0.01"
                        id="fp_perkg"
                        value="{{ $report->fp_perkg ?? 0 }}"
                        class="form-control form-control-sm">

                </div>

            </div>


            {{-- =========================
                TABLE
            ========================== --}}

            <div class="table-responsive">

                <table
                    class="table table-bordered table-sm table-hover table-erp"
                    id="analysisTable">

                    <thead>

                        <tr class="bg-light">

                            <th width="220">Item</th>

                            <th width="220">Qty</th>

                            <th width="220">Sales Price ($)</th>

                            <th width="220">Market Price</th>

                            <th width="220">Avg Cost</th>

                            <th width="220">Sales Price <br> (LKR)</th>

                            <th width="220">
                                Sales Price
                                <br>
                                With Cost (LKR)
                            </th>

                            <th width="220">
                                Total Purchase
                                <br>
                                Amount
                            </th>

                            <th width="220">
                                Total Cost
                            </th>

                            <th width="220">
                                Total Sales
                                <br>
                                Amount (LKR)
                            </th>

                            <th width="220">
                                Total Sales
                                <br>
                                Amount ($)
                            </th>

                            <th width="220">
                                Profit /
                                Loss
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($items as $row)

                        <tr>

                            <td>

                                {{ $row->name }}
                                <input type="hidden" class="item_id" value="{{ $row->item_id }}">

                            </td>

                            <td>

                                <input
                                    type="number"
                                    class="form-control form-control-sm qty"

                                    value="{{ number_format($row->qty,2,'.','') }}"

                                    readonly>

                            </td>

                            <td>

                                <input
                                    type="number"
                                    step="0.01"
                                    class="form-control form-control-sm sale_price" value="{{ $row->sale_price_usd ?? 0 }}">

                            </td>

                            <td>

                                <input
                                    type="number"
                                    step="0.01"
                                    class="form-control form-control-sm market_price" value="{{ $row->market_price ?? 0 }}">

                            </td>

                            <td>

                                <input
                                    type="number"
                                    class="form-control form-control-sm avg_cost"

                                    value="{{ number_format($row->avg_cost,2,'.','') }}"

                                    readonly>

                            </td>

                            <td class="sales_lkr text-right align-middle">

                                0.00

                            </td>

                            <td class="sales_after_fp text-right align-middle">

                                0.00

                            </td>

                            <td class="purchase_amount text-right align-middle">

                                0.00

                            </td>

                            <td class="total_cost text-right align-middle">

                                0.00

                            </td>

                            <td class="sales_total_lkr text-right align-middle">

                                0.00

                            </td>

                            <td class="sales_total_usd text-right align-middle">

                                0.00

                            </td>

                            <td class="profit text-right align-middle font-weight-bold">

                                0.00

                            </td>

                        </tr>

                        @endforeach

                    </tbody>

                    <tfoot>

                        <tr class="bg-light font-weight-bold">

                            <td colspan="7" class="text-right">

                                GRAND TOTAL

                            </td>

                            <td id="grand_purchase" class="text-right">

                                0.00

                            </td>

                            <td id="grand_cost" class="text-right">

                                0.00

                            </td>

                            <td id="grand_sales_lkr" class="text-right">

                                0.00

                            </td>

                            <td id="grand_sales_usd" class="text-right">

                                0.00

                            </td>

                            <td id="grand_profit" class="text-right">

                                0.00

                            </td>

                        </tr>

                    </tfoot>

                </table>

            </div>

            @endif

        </div>

    </div>

</div>

@endsection

@push('scripts')

<script>
    $(function() {

        calculate();

        $("#saveReportBtn").on("click", function() {
            saveInvoiceProfitReport();
        });

    });


    $(document).on(
        'keyup change',
        '#exchange_rate,#fp_perkg,.sale_price,.market_price',
        function() {

            calculate();

        }
    );


    function calculate() {

        let exchangeRate = parseFloat($("#exchange_rate").val()) || 0;

        let fpPerKg = parseFloat($("#fp_perkg").val()) || 0;


        let grandPurchase = 0;
        let grandCost = 0;
        let grandSalesLkr = 0;
        let grandSalesUsd = 0;
        let grandProfit = 0;


        $("#analysisTable tbody tr").each(function() {

            let row = $(this);


            let qty = parseFloat(
                row.find(".qty").val()
            ) || 0;


            let avgCost = parseFloat(
                row.find(".avg_cost").val()
            ) || 0;


            let salesPriceUSD = parseFloat(
                row.find(".sale_price").val()
            ) || 0;


            /*
            ============================================
            Sales Price (LKR)

            Sales Price ($)
            ×
            Exchange Rate
            ============================================
            */

            let salesPriceLKR =
                salesPriceUSD * exchangeRate;


            /*
            ============================================
            Sales Price With Cost (LKR)

            Sales Price (LKR)
            -
            F&P Per Kg
            ============================================
            */

            let salesAfterFP =
                salesPriceLKR - fpPerKg;


            /*
            ============================================
            Total Purchase Amount

            Qty
            ×
            Avg Cost
            ============================================
            */

            let purchaseAmount =
                qty * avgCost;


            /*
            ============================================
            Total Cost

            Purchase Amount
            +
            Qty × F&P
            ============================================
            */

            let totalCost =
                purchaseAmount + (qty * fpPerKg);


            /*
            ============================================
            Total Sales Amount (LKR)

            Sales Price With Cost
            ×
            Qty
            ============================================
            */

            let totalSalesLKR =
                salesAfterFP * qty;


            /*
            ============================================
            Total Sales Amount ($)

            Sales Price ($)
            ×
            Qty
            ============================================
            */

            let totalSalesUSD =
                salesPriceUSD * qty;


            /*
            ============================================
            Profit

            Sales LKR
            -
            Total Cost
            ============================================
            */

            let profit =
                totalSalesLKR - totalCost;


            row.find(".sales_lkr")
                .text(
                    numberFormat(salesPriceLKR)
                );


            row.find(".sales_after_fp")
                .text(
                    numberFormat(salesAfterFP)
                );


            row.find(".purchase_amount")
                .text(
                    numberFormat(purchaseAmount)
                );


            row.find(".total_cost")
                .text(
                    numberFormat(totalCost)
                );


            row.find(".sales_total_lkr")
                .text(
                    numberFormat(totalSalesLKR)
                );


            row.find(".sales_total_usd")
                .text(
                    numberFormat(totalSalesUSD)
                );


            row.find(".profit")
                .text(
                    numberFormat(profit)
                );


            grandPurchase += purchaseAmount;

            grandCost += totalCost;

            grandSalesLkr += totalSalesLKR;

            grandSalesUsd += totalSalesUSD;

            grandProfit += profit;

        });


        $("#grand_purchase")
            .text(
                numberFormat(grandPurchase)
            );


        $("#grand_cost")
            .text(
                numberFormat(grandCost)
            );


        $("#grand_sales_lkr")
            .text(
                numberFormat(grandSalesLkr)
            );


        $("#grand_sales_usd")
            .text(
                numberFormat(grandSalesUsd)
            );


        $("#grand_profit")
            .text(
                numberFormat(grandProfit)
            );

    }



    function numberFormat(number) {

        return parseFloat(number)
            .toLocaleString(
                undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );

    }

    function saveInvoiceProfitReport() {

        let items = [];

        // =========================
        // COLLECT TABLE DATA
        // =========================
        $("#analysisTable tbody tr").each(function() {

            items.push({
                item_id: $(this).find(".item_id").val(),
                sale_price_usd: $(this).find(".sale_price").val() || 0,
                market_price: $(this).find(".market_price").val() || 0
            });

        });

        // =========================
        // BUTTON LOADING STATE
        // =========================
        let btn = $("#saveReportBtn");
        btn.prop("disabled", true);
        btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        // =========================
        // AJAX REQUEST
        // =========================
        $.ajax({
            url: "{{ route('reports.invoice-profit-analysis-save') }}", // change if needed
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                sale_id: "{{ $sale->id ?? '' }}",
                exchange_rate: $("#exchange_rate").val(),
                fp_perkg: $("#fp_perkg").val(),
                items: items
            },
            success: function(res) {

                if (res.status) {
                    toastr.success(res.message);
                } else {
                    toastr.error(res.message);
                }

            },
            error: function(xhr) {

                let msg = "Something went wrong";

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }

                 toastr.error(msg);
            },
            complete: function() {

                btn.prop("disabled", false);
                btn.html('<i class="fas fa-save"></i> Save / Update Report');

            }
        });
    }
</script>

@endpush