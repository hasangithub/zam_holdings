@extends('layouts.app')

@section('title','Add Freight Record')

@section('content')

<div class="container-fluid">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Add Freight Record (Excel Style)</h3>
            <div class="card-tools"> <a href="/freight-records" class="btn btn-secondary btn-sm"> <i class="fas fa-arrow-left"></i> Back </a> </div>
        </div>
        <div class="card-body table-responsive">
            <form action="{{ route('freight-records.store') }}" method="POST">
                @csrf
                <div class="card card-outline card-primary">

                    <div class="card-header">
                        <h3 class="card-title">Shipment Information</h3>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-3">
                                <label>Date</label>
                                <input type="date" name="date" class="form-control form-control-sm" required>
                            </div>

                            <div class="col-md-3">
                                <label>Airway No</label>
                                <input type="text" name="airway_no" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-3">
                                <label>consignor</label>
                                <select name="consignor" class="form-control form-control-sm">
                                    <option value="">Select Consignor</option>

                                    @foreach(\App\Models\Sale::CONSIGNORS as $id => $name)
                                    <option value="{{ $id }}"
                                        {{ old('consignor', $sale->consignor ?? '') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Consignee</label>
                                <input type="text" name="consignee" class="form-control form-control-sm">
                            </div>

                        </div>

                        <div class="row mt-3">

                            <div class="col-md-3">
                                <label>Net Weight (Kg)</label>
                                <input type="number" step="0.01" name="net_weight" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-3">
                                <label>Gross Weight (Kg)</label>
                                <input type="number" step="0.01" name="gross_weight" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-3">
                                <label>Boxes</label>
                                <input type="number" name="boxes" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-3">
                                <label>Exchange Rate</label>
                                <input type="number" step="0.0001"
                                    id="exchange_rate"
                                    name="exchange_rate"
                                    class="form-control form-control-sm">
                            </div>

                        </div>

                    </div>

                </div>

                <div class="card card-outline card-success mt-3">

                    <div class="card-header">
                        <h3 class="card-title">Invoice Details</h3>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-3">
                                <label>Custom Invoice (USD)</label>
                                <input type="number"
                                    step="0.01"
                                    id="custom_usd"
                                    name="custom_usd"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-3">
                                <label>Custom Invoice (LKR)</label>
                                <input type="number"
                                    step="0.01"
                                    id="custom_lkr"
                                    name="custom_lkr"
                                    class="form-control form-control-sm bg-light"
                                    readonly>
                            </div>

                            <div class="col-md-3">
                                <label>Freight Invoice (USD)</label>
                                <input type="number"
                                    step="0.01"
                                    id="freight_usd"
                                    name="freight_usd"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-3">
                                <label>Freight Invoice (LKR)</label>
                                <input type="number"
                                    step="0.01"
                                    id="freight_lkr"
                                    name="freight_lkr"
                                    class="form-control form-control-sm bg-light"
                                    readonly>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="card card-outline card-warning mt-3">

                    <div class="card-header">
                        <h3 class="card-title">Other Information</h3>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-4">
                                <label>CUSDEC No</label>
                                <input type="text"
                                    name="cusdec_no"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-4">
                                <label>Booking</label>
                                <input type="text"
                                    name="booking"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-4">
                                <label>Bank</label>
                                <input type="text"
                                    name="bank"
                                    class="form-control form-control-sm">
                            </div>

                        </div>

                    </div>

                </div>
                <div class="card-footer text-right"> <button type="submit" class="btn btn-success"> <i class="fas fa-save"></i> Save Record </button> </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function() {

        function calculate() {

            let rate = parseFloat($('#exchange_rate').val()) || 0;

            let custom = parseFloat($('#custom_usd').val()) || 0;

            let freight = parseFloat($('#freight_usd').val()) || 0;

            $('#custom_lkr').val((custom * rate).toFixed(2));

            $('#freight_lkr').val((freight * rate).toFixed(2));

        }

        $('#exchange_rate,#custom_usd,#freight_usd')
            .on('keyup change', calculate);

    });
</script>
@endpush