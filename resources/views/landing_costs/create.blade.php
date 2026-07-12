@extends('layouts.app')

@section('title','Landing Cost')

@section('content')

<div class="container-fluid">

    <form method="POST" action="{{ route('landing-costs.store') }}">
        @csrf

        <div class="card card-primary">

            <div class="card-header">
                <h3 class="card-title">Landing Cost Calculation</h3>
            </div>

            <div class="card-body">

                {{-- ================= INVOICE SECTION ================= --}}
                <div class="card card-outline card-secondary">

                    <div class="card-header">
                        <strong>1. Invoice Selection</strong>
                    </div>

                    <div class="card-body">

                        <select name="invoice_id" id="invoice_id"
                            class="form-control select2" required>

                            <option value="">Select Invoice</option>

                            @foreach($sales as $sale)
                            <option value="{{ $sale->id }}"
                                data-date="{{ $sale->date }}"
                                data-awb="{{ $sale->airway_no }}"
                                data-consignee="{{ $sale->customer->name ?? '' }}"
                                data-net="{{ $sale->items->sum('qty') }}">

                                {{ $sale->invoice_id }}
                            </option>
                            @endforeach

                        </select>

                    </div>

                </div>


                {{-- ================= SHIPMENT INFO ================= --}}
                <div class="card card-outline card-info mt-3">

                    <div class="card-header">
                        <strong>2. Shipment Information</strong>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-2">
                                <label>Date</label>
                                <input type="text" id="date" class="form-control form-control-sm" readonly>
                            </div>

                            <div class="col-md-2">
                                <label>AWB</label>
                                <input type="text" id="awb" class="form-control form-control-sm" readonly>
                            </div>

                            <div class="col-md-3">
                                <label>Consignee</label>
                                <input type="text" id="consignee" class="form-control form-control-sm" readonly>
                            </div>

                            <div class="col-md-2">
                                <label>Net Weight (Kg)</label>
                                <input type="text" id="net_weight" class="form-control form-control-sm" readonly>
                            </div>

                            <div class="col-md-2">
                                <label>Gross Weight</label>
                                <input type="number" step="0.01"
                                    name="gross_weight"
                                    id="gross_weight"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1">
                                <label>Rate</label>
                                <input type="number" step="0.01"
                                    name="exchange_rate"
                                    id="exchange_rate"
                                    class="form-control form-control-sm">
                            </div>

                        </div>

                    </div>

                </div>


                {{-- ================= COST INPUT ================= --}}
                <div class="card card-outline card-warning mt-3">

                    <div class="card-header">
                        <strong>3. Cost Inputs</strong>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-4">
                                <label>Air Freight / Kg</label>
                                <input type="number" step="0.01"
                                    name="air_freight_rate"
                                    id="air_rate"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-4">
                                <label>Logistics Expenses</label>
                                <input type="number" step="0.01"
                                    name="logistics_expenses"
                                    id="logistics"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-4">
                                <label>Packing Cost / Kg</label>
                                <input type="number" step="0.01"
                                    name="packing_cost_perkg"
                                    id="packing"
                                    class="form-control form-control-sm">
                            </div>

                        </div>

                    </div>

                </div>


                {{-- ================= CALCULATION OUTPUT ================= --}}
                <div class="card card-outline card-success mt-3">

                    <div class="card-header">
                        <strong>4. Calculated Results</strong>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-3">
                                <label>Air Freight ($)</label>
                                <input type="text" id="air_usd"
                                    class="form-control form-control-sm bg-light" readonly>
                            </div>

                            <div class="col-md-3">
                                <label>Air Freight (LKR)</label>
                                <input type="text" id="air_lkr"
                                    class="form-control form-control-sm bg-light" readonly>
                            </div>

                            <div class="col-md-3">
                                <label>Freight + Logistics / Kg</label>
                                <input type="text" id="freight_perkg"
                                    name="freight_logistics_perkg"
                                    class="form-control form-control-sm bg-light" readonly>
                            </div>

                            <div class="col-md-3">
                                <label>Total Cost / Kg</label>
                                <input type="text" id="total_perkg"
                                    name="total_cost_perkg"
                                    class="form-control form-control-sm font-weight-bold bg-light"
                                    readonly>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <div class="card-footer text-right">
                <button class="btn btn-success">
                    Save
                </button>
            </div>

        </div>

    </form>

</div>

@endsection

@push('scripts')
<script>
    $(function() {

        function calc() {

            let net = parseFloat($('#net_weight').val()) || 0;
            let gross = parseFloat($('#gross_weight').val()) || 0;
            let rate = parseFloat($('#exchange_rate').val()) || 0;

            let airRate = parseFloat($('#air_rate').val()) || 0;
            let logistics = parseFloat($('#logistics').val()) || 0;
            let packing = parseFloat($('#packing').val()) || 0;

            let airUsd = gross * airRate;
            let airLkr = airUsd * rate;

            let perKg = 0;

            if (net > 0) {
                perKg = airLkr + (logistics / net);
            }

            let total = perKg + packing;

            $('#air_usd').val(airUsd.toFixed(2));
            $('#air_lkr').val(airLkr.toFixed(2));
            $('#freight_perkg').val(perKg.toFixed(2));
            $('#total_perkg').val(total.toFixed(2));
        }

        $('#invoice_id').on('change', function() {

            let o = $(this).find(':selected');

            $('#date').val(o.data('date'));
            $('#awb').val(o.data('awb'));
            $('#consignee').val(o.data('consignee'));
            $('#net_weight').val(o.data('net'));

            calc();
        });

        $('#gross_weight,#exchange_rate,#air_rate,#logistics,#packing')
            .on('keyup change', calc);

    });
</script>
@endpush