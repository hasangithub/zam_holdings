@extends('layouts.app')

@section('title', 'Cash Book Entry')

@section('content')

<div class="container-fluid">

    <div class="card card-primary card-outline">

        <div class="card-header">

            <h3 class="card-title">
                <i class="fas fa-book"></i>
                Cash Book Entry
            </h3>

            <div class="card-tools">

                <a href="{{ route('cashbooks.index') }}"
                    class="btn btn-secondary btn-sm">

                    <i class="fas fa-arrow-left"></i>
                    Cash Book

                </a>

            </div>

        </div>


        <form action="{{ route('cashbooks.store') }}"
            method="POST">

            @csrf


            <div class="card-body">

                {{-- ERROR --}}
                @if($errors->any())

                <div class="alert alert-danger">

                    <ul class="mb-0">

                        @foreach($errors->all() as $error)

                        <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

                @endif


                {{-- ROW 1 --}}
                <div class="row">


                    {{-- DATE --}}
                    <div class="col-md-3">

                        <div class="form-group">

                            <label>
                                Date
                                <span class="text-danger">*</span>
                            </label>

                            <input type="date"
                                name="transaction_date"
                                class="form-control"
                                value="{{ old('transaction_date', date('Y-m-d')) }}"
                                required>

                        </div>

                    </div>


                    {{-- CASHBOOK --}}
                    <div class="col-md-4">

                        <div class="form-group">

                            <label>
                                Cash Book
                                <span class="text-danger">*</span>
                            </label>

                            <select name="cashbook_sub_ledger_id"
                                class="form-control"
                                required>

                                <option value="">
                                    Select Cash Book
                                </option>

                                @foreach($cashBooks as $cashBook)

                                <option value="{{ $cashBook->id }}"
                                    {{ old('cashbook_sub_ledger_id') == $cashBook->id ? 'selected' : '' }}>

                                    {{ $cashBook->name }}

                                </option>

                                @endforeach

                            </select>

                        </div>

                    </div>


                    {{-- TYPE --}}
                    <div class="col-md-3">

                        <div class="form-group">

                            <label>
                                Type
                                <span class="text-danger">*</span>
                            </label>

                            <select name="type"
                                id="transaction_type"
                                class="form-control"
                                required>

                                <option value="in"
                                    {{ old('type', 'in') == 'in' ? 'selected' : '' }}>
                                    Cash In
                                </option>

                                <option value="out"
                                    {{ old('type') == 'out' ? 'selected' : '' }}>
                                    Cash Out
                                </option>

                            </select>

                        </div>

                    </div>


                    {{-- AMOUNT --}}
                    <div class="col-md-2">

                        <div class="form-group">

                            <label>
                                Amount
                                <span class="text-danger">*</span>
                            </label>

                            <input type="number"
                                name="amount"
                                step="0.01"
                                min="0.01"
                                class="form-control"
                                value="{{ old('amount') }}"
                                required>

                        </div>

                    </div>

                </div>


                <hr>


                {{-- ROW 2 --}}
                <div class="row">


                    {{-- LEDGER --}}
                    <div class="col-md-4">

                        <div class="form-group">

                            <label>
                                Other Ledger
                                <span class="text-danger">*</span>
                            </label>

                            <select name="ledger_id" id="ledger_id" class="form-control">
                                <option value="">Select Ledger</option>

                                @foreach($ledgers as $ledger)
                                <option value="{{ $ledger->id }}">
                                    {{ $ledger->name }}
                                </option>
                                @endforeach
                            </select>

                        </div>

                    </div>


                    {{-- SUBLEDGER --}}
                    <div class="col-md-4">

                        <div class="form-group">

                            <label>
                                Subledger
                            </label>

                            <select name="sub_ledger_id" id="sub_ledger_id" class="form-control">
                                <option value="">Select Subledger</option>
                            </select>

                            <small class="text-muted">
                                Subledger is loaded according to the selected ledger.
                            </small>

                        </div>

                    </div>


                    {{-- REFERENCE --}}
                    <div class="col-md-4">

                        <div class="form-group">

                            <label>
                                Reference
                            </label>

                            <input type="text"
                                name="reference"
                                class="form-control"
                                value="{{ old('reference') }}"
                                placeholder="Receipt / Voucher No.">

                        </div>

                    </div>

                </div>


                {{-- DESCRIPTION --}}
                <div class="form-group">

                    <label>
                        Description
                    </label>

                    <textarea name="description"
                        rows="3"
                        class="form-control"
                        placeholder="Enter description">{{ old('description') }}</textarea>

                </div>


                {{-- ACCOUNTING INFORMATION --}}
                <div class="alert alert-info">

                    <div id="cash_in_text">

                        <i class="fas fa-arrow-down"></i>

                        <strong>Cash In</strong>

                        &nbsp;:

                        Dr Cash Book

                        &nbsp; / &nbsp;

                        Cr Other Account

                    </div>


                    <div id="cash_out_text"
                        style="display:none;">

                        <i class="fas fa-arrow-up"></i>

                        <strong>Cash Out</strong>

                        &nbsp;:

                        Dr Other Account

                        &nbsp; / &nbsp;

                        Cr Cash Book

                    </div>

                </div>

            </div>


            {{-- FOOTER --}}
            <div class="card-footer text-right">

                <a href="{{ route('cashbooks.index') }}"
                    class="btn btn-secondary">

                    Cancel

                </a>

                <button type="submit"
                    class="btn btn-primary">

                    <i class="fas fa-save"></i>

                    Save Transaction

                </button>

            </div>

        </form>

    </div>

</div>

@endsection


@push('scripts')

<script>
    $(document).ready(function() {


        /*
        |--------------------------------------------------------------------------
        | Load Subledgers
        |--------------------------------------------------------------------------
        */

        function loadSubLedgers(ledgerId, selectedId = '') {

            let subLedger = $('#sub_ledger_id');

            subLedger.empty();

            subLedger.append(
                $('<option>', {
                    value: '',
                    text: 'Loading subledgers...'
                })
            );


            if (!ledgerId) {

                subLedger.empty();

                subLedger.append(
                    $('<option>', {
                        value: '',
                        text: 'Select Ledger First'
                    })
                );

                return;
            }


            $.ajax({

                url: "{{ url('cashbooks/ledger') }}/" +
                    ledgerId +
                    "/subledgers",

                type: "GET",

                dataType: "json",

                success: function(data) {

                    subLedger.empty();

                    subLedger.append(
                        $('<option>', {
                            value: '',
                            text: 'Select Subledger'
                        })
                    );


                    if (data.length === 0) {

                        subLedger.empty();

                        subLedger.append(
                            $('<option>', {
                                value: '',
                                text: 'No subledgers available'
                            })
                        );

                        return;
                    }


                    $.each(data, function(index, item) {

                        let option = $('<option>', {
                            value: item.id,
                            text: item.name
                        });


                        if (
                            selectedId &&
                            String(selectedId) === String(item.id)
                        ) {

                            option.prop(
                                'selected',
                                true
                            );

                        }


                        subLedger.append(option);

                    });

                },


                error: function(xhr) {

                    console.log(
                        'Subledger error:',
                        xhr.responseText
                    );

                    subLedger.empty();

                    subLedger.append(
                        $('<option>', {
                            value: '',
                            text: 'Unable to load subledgers'
                        })
                    );

                    if (typeof toastr !== 'undefined') {

                        toastr.error(
                            'Unable to load subledgers.'
                        );

                    }

                }

            });

        }


        /*
        |--------------------------------------------------------------------------
        | Ledger Changed
        |--------------------------------------------------------------------------
        */

        $('#ledger_id').on('change', function() {


            let ledgerId = $(this).val();

            loadSubLedgers(ledgerId);

        });


        /*
        |--------------------------------------------------------------------------
        | Load Old Value After Validation Error
        |--------------------------------------------------------------------------
        */

        let oldLedgerId = "{{ old('ledger_id') }}";

        let oldSubLedgerId = "{{ old('sub_ledger_id') }}";


        if (oldLedgerId) {

            loadSubLedgers(
                oldLedgerId,
                oldSubLedgerId
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Cash In / Cash Out Information
        |--------------------------------------------------------------------------
        */

        function updateTransactionType() {

            let type = $('#transaction_type').val();


            if (type === 'out') {

                $('#cash_in_text').hide();

                $('#cash_out_text').show();

            } else {

                $('#cash_in_text').show();

                $('#cash_out_text').hide();

            }

        }


        $('#transaction_type').on(
            'change',
            updateTransactionType
        );


        updateTransactionType();

    });
</script>

@endpush