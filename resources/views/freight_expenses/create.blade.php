@extends('layouts.app')

@section('title','Create Freight')

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Create Freight</h3>
    </div>

    <form method="POST" action="{{ route('freights.store') }}">
        @csrf

        <div class="card-body">

            <div class="row">

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Sales Invoice</label>
                        <select name="sale_id"
                                id="sale_id"
                                class="form-control"
                                required>
                            <option value="">Select Invoice</option>

                            @foreach($sales as $sale)
                                <option value="{{ $sale->id }}"
                                    data-airway="{{ $sale->airway_number }}"
                                    data-consignor="{{ $sale->consignor }}">
                                    {{ $sale->invoice_id }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Airway Number</label>
                        <input type="text"
                               id="airway_number"
                               class="form-control"
                               readonly>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Consignor</label>
                        <input type="text"
                               id="consignor"
                               class="form-control"
                               readonly>
                    </div>
                </div>

            </div>

            <div class="row">

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date"
                               name="date"
                               value="{{ date('Y-m-d') }}"
                               class="form-control"
                               required>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Freight Service</label>
                        <select name="freight_service_id"
                                class="form-control"
                                required>
                            <option value="">Select Service</option>

                            @foreach($services as $service)
                                <option value="{{ $service->id }}">
                                    {{ $service->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Exchange Rate</label>
                        <input type="number"
                               name="exchange_rate"
                               id="exchange_rate"
                               step="0.0001"
                               class="form-control"
                               required>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Freight Amount (USD)</label>
                        <input type="number"
                               name="amount_usd"
                               id="amount_usd"
                               step="0.01"
                               class="form-control"
                               required>
                    </div>
                </div>

            </div>

            <div class="row">

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Amount (LKR)</label>
                        <input type="text"
                               id="amount_lkr"
                               class="form-control"
                               readonly>
                    </div>
                </div>

            </div>

        </div>

        <div class="card-footer">
            <button class="btn btn-success">
                <i class="fas fa-save"></i> Save
            </button>

            <a href="{{ route('freights.index') }}"
               class="btn btn-secondary">
                Cancel
            </a>
        </div>

    </form>
</div>

@endsection

@push('scripts')
<script>
$('#sale_id').change(function () {
    let option = $(this).find(':selected');

    $('#airway_number').val(option.data('airway') || '');
    $('#consignor').val(option.data('consignor') || '');
});

$('#exchange_rate, #amount_usd').on('input', function () {
    let rate = parseFloat($('#exchange_rate').val()) || 0;
    let usd = parseFloat($('#amount_usd').val()) || 0;

    $('#amount_lkr').val((rate * usd).toFixed(2));
});
</script>
@endpush