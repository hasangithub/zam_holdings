@extends('layouts.app')

@section('title', 'Create Freight')

@section('content')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">Create Freight</h3>
    </div>

    <form method="POST" action="{{ route('freights.store') }}">
        @csrf

        <div class="card-body">

            {{-- Invoice Type --}}
            <div class="row">

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Invoice Type</label>

                        <select name="invoice_type"
                                id="invoice_type"
                                class="form-control"
                                required>

                            <option value="invoice"
                                {{ old('invoice_type', 'invoice') == 'invoice' ? 'selected' : '' }}>
                                Sales Invoice
                            </option>

                            <option value="opening"
                                {{ old('invoice_type') == 'opening' ? 'selected' : '' }}>
                                Opening Balance
                            </option>

                        </select>
                    </div>
                </div>

            </div>

            {{-- Sales Invoice Section --}}
            <div id="sales_invoice_section">

                <div class="row">

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Sales Invoice</label>

                            <select name="sale_id"
                                    id="sale_id"
                                    class="form-control">

                                <option value="">Select Invoice</option>

                                @foreach($sales as $sale)
                                    <option value="{{ $sale->id }}"
                                        data-airway="{{ $sale->airway_no }}"
                                        data-consignor="{{ $sale->consignor }}"
                                        {{ old('sale_id') == $sale->id ? 'selected' : '' }}>

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

            </div>

            {{-- Opening Balance Section --}}
            <div id="opening_invoice_section" style="display:none;">

                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Old Invoice ID</label>

                            <input type="text"
                                   name="invoice_id"
                                   id="invoice_id"
                                   class="form-control"
                                   value="{{ old('invoice_id') }}"
                                   placeholder="Enter old system invoice ID"
                                   maxlength="255">
                        </div>
                    </div>

                </div>

            </div>

            <hr>

            {{-- Freight Details --}}
            <div class="row">

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Date</label>

                        <input type="date"
                               name="date"
                               value="{{ old('date', date('Y-m-d')) }}"
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
                                <option value="{{ $service->id }}"
                                    {{ old('freight_service_id') == $service->id ? 'selected' : '' }}>

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
                               min="0.0001"
                               value="{{ old('exchange_rate') }}"
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
                               min="0.01"
                               value="{{ old('amount_usd') }}"
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

            <button type="submit" class="btn btn-success">
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
$(document).ready(function () {

    // Invoice type selection
    function toggleInvoiceType() {

        let type = $('#invoice_type').val();

        if (type === 'invoice') {

            $('#sales_invoice_section').show();
            $('#opening_invoice_section').hide();

            $('#sale_id')
                .prop('disabled', false)
                .prop('required', true);

            $('#invoice_id')
                .prop('disabled', true)
                .prop('required', false);

        } else {

            $('#sales_invoice_section').hide();
            $('#opening_invoice_section').show();

            $('#sale_id')
                .prop('disabled', true)
                .prop('required', false);

            $('#invoice_id')
                .prop('disabled', false)
                .prop('required', true);

            $('#airway_number').val('');
            $('#consignor').val('');
        }
    }

    // Sales invoice information
    $('#sale_id').on('change', function () {

        let option = $(this).find(':selected');

        $('#airway_number').val(option.data('airway') || '');
        $('#consignor').val(option.data('consignor') || '');

    });

    // Calculate LKR amount
    $('#exchange_rate, #amount_usd').on('input', function () {

        let rate = parseFloat($('#exchange_rate').val()) || 0;
        let usd = parseFloat($('#amount_usd').val()) || 0;

        $('#amount_lkr').val((rate * usd).toFixed(2));

    });

    $('#invoice_type').on('change', toggleInvoiceType);

    // Initial load
    toggleInvoiceType();

    $('#sale_id').trigger('change');

    $('#exchange_rate').trigger('input');

});
</script>

@endpush