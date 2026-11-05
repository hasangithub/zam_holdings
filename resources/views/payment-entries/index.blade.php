@extends('layouts.app')

@section('title', 'Central Payment Entry')

@section('content')

<div class="container-fluid">

    <div class="card card-primary">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-money-bill-wave mr-1"></i>
                Central Payment Entry
            </h3>
        </div>

        <form action="{{ route('payment-entries.store') }}" method="POST" id="paymentForm">
            @csrf

            <input type="hidden" name="request_token" id="request_token">

            <div class="card-body">

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">

                    {{-- Payment Date --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Payment Date <span class="text-danger">*</span></label>
                            <input type="date"
                                name="payment_date"
                                class="form-control"
                                value="{{ old('payment_date', date('Y-m-d')) }}"
                                required>
                        </div>
                    </div>

                    {{-- Cash Book --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Cash Book / Bank Account <span class="text-danger">*</span></label>

                            <select name="sub_ledger_id" class="form-control" required>
                                <option value="">Select Cash Book</option>

                                @foreach($cashBooks as $cashBook)
                                    <option value="{{ $cashBook->id }}"
                                        {{ old('sub_ledger_id') == $cashBook->id ? 'selected' : '' }}>
                                        {{ $cashBook->name }}
                                    </option>
                                @endforeach

                            </select>
                        </div>
                    </div>

                    {{-- Amount --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Payment Amount (LKR) <span class="text-danger">*</span></label>

                            <input type="number"
                                name="amount"
                                class="form-control"
                                step="0.01"
                                min="0.01"
                                value="{{ old('amount') }}"
                                placeholder="Enter amount"
                                required>
                        </div>
                    </div>

                </div>

                <hr>

                <h5 class="mb-3">
                    <strong>Payment To / From</strong>
                </h5>

                <div class="row">

                    {{-- Module --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Module <span class="text-danger">*</span></label>

                            <select name="type" id="payment_type" class="form-control" required>
                                <option value="">Select Module</option>
                                <option value="sales" {{ old('type') == 'sales' ? 'selected' : '' }}>
                                    Sales - Customer Payment
                                </option>
                                <option value="purchase" {{ old('type') == 'purchase' ? 'selected' : '' }}>
                                    Purchase - Supplier Payment
                                </option>
                                <option value="inventory" {{ old('type') == 'inventory' ? 'selected' : '' }}>
                                    Inventory Purchase Payment
                                </option>
                                <option value="asset" {{ old('type') == 'asset' ? 'selected' : '' }}>
                                    Fixed Asset Payment
                                </option>
                            </select>
                        </div>
                    </div>

                    {{-- Party --}}
                    <div class="col-md-8">
                        <div class="form-group">
                            <label id="party_label">Customer / Supplier <span class="text-danger">*</span></label>

                            <select name="party_id" id="party_id" class="form-control" required disabled>
                                <option value="">Select Module First</option>
                            </select>
                        </div>
                    </div>

                </div>

                {{-- Customer Payment Method --}}
                <div class="row" id="method_container" style="display:none;">

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Payment Method</label>

                            <select name="method" class="form-control">
                                <option value="">Select Method</option>
                                <option value="Cash" {{ old('method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                <option value="Bank Transfer" {{ old('method') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="Cheque" {{ old('method') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                            </select>
                        </div>
                    </div>

                </div>

                <hr>

                {{-- Note --}}
                <div class="form-group">
                    <label>Note / Description</label>

                    <textarea name="note"
                        class="form-control"
                        rows="3"
                        placeholder="Enter payment description">{{ old('note') }}</textarea>
                </div>

            </div>

            <div class="card-footer text-right">

                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-undo"></i> Reset
                </button>

                <button type="submit" class="btn btn-primary" id="savePayment">
                    <i class="fas fa-save"></i> Save Payment
                </button>

            </div>

        </form>

    </div>

</div>

@endsection


@push('scripts')

<script>
$(document).ready(function () {

    const partyUrl = "{{ route('payment-entries.parties') }}";

    let previousParty = "{{ old('party_id') }}";

    function loadParties(type, selectedParty = '') {

        let partySelect = $('#party_id');

        partySelect.html('<option value="">Loading...</option>');
        partySelect.prop('disabled', true);

        if (!type) {
            partySelect.html('<option value="">Select Module First</option>');
            return;
        }

        $.ajax({
            url: partyUrl,
            type: 'GET',
            data: {
                type: type
            },

            success: function (response) {

                partySelect.empty();

                partySelect.append(
                    '<option value="">Select Customer / Supplier</option>'
                );

                $.each(response, function (index, party) {

                    let selected = selectedParty == party.id ? 'selected' : '';

                    partySelect.append(
                        $('<option>', {
                            value: party.id,
                            text: party.name,
                            selected: selectedParty == party.id
                        })
                    );

                });

                partySelect.prop('disabled', false);

                if (response.length === 0) {
                    partySelect.html('<option value="">No records found</option>');
                }
            },

            error: function () {

                partySelect.html('<option value="">Unable to load records</option>');

                toastr.error('Unable to load customers / suppliers.');
            }
        });
    }


    $('#payment_type').on('change', function () {

        let type = $(this).val();

        $('#party_id').val('');

        if (type === 'sales') {

            $('#party_label').html('Customer <span class="text-danger">*</span>');

            $('#method_container').show();

        } else {

            $('#party_label').html('Supplier <span class="text-danger">*</span>');

            $('#method_container').hide();
        }

        loadParties(type);

    });


    // Load old values after validation failure
    if ($('#payment_type').val()) {

        let type = $('#payment_type').val();

        if (type === 'sales') {
            $('#party_label').html('Customer <span class="text-danger">*</span>');
            $('#method_container').show();
        } else {
            $('#party_label').html('Supplier <span class="text-danger">*</span>');
            $('#method_container').hide();
        }

        loadParties(type, previousParty);
    }


    // Generate request token
    function generateToken() {

        if (window.crypto && typeof crypto.randomUUID === 'function') {
            return crypto.randomUUID();
        }

        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            let r = Math.random() * 16 | 0;
            let v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    $('#paymentForm').on('submit', function () {

        if (!$('#request_token').val()) {
            $('#request_token').val(generateToken());
        }

        $('#savePayment').prop('disabled', true);

    });

});
</script>

@endpush