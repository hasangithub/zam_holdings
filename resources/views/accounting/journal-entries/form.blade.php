<div class="row">

    {{-- Date --}}

    <div class="col-md-4">

        <div class="form-group">

            <label>
                Journal Date <span class="text-danger">*</span>
            </label>

            <input type="date"
                   name="journal_date"
                   value="{{ old(
                       'journal_date',
                       isset($journalEntry)
                           ? $journalEntry->journal_date
                           : date('Y-m-d')
                   ) }}"
                   class="form-control">

        </div>

    </div>
</div>


<div class="form-group">

    <label>
        Description
    </label>

    <textarea name="description"
              rows="2"
              class="form-control">{{ old(
                  'description',
                  $journalEntry->description ?? ''
              ) }}</textarea>

</div>


<hr>


<div class="d-flex justify-content-between mb-2">

    <h5>
        Journal Details
    </h5>

    <button type="button"
            id="addRow"
            class="btn btn-success btn-sm">

        <i class="fas fa-plus"></i>
        Add Row

    </button>

</div>


<div class="table-responsive">

<table class="table table-bordered"
       id="journalDetailsTable">

    <thead class="thead-light">

        <tr>

            <th width="18%">
                Account Type
            </th>

            <th width="18%">
                Account Group
            </th>

            <th width="18%">
                Ledger
            </th>

            <th width="18%">
                Sub Ledger
            </th>

            <th width="12%">
                Debit
            </th>

            <th width="12%">
                Credit
            </th>

            <th width="5%">
            </th>

        </tr>

    </thead>


    <tbody id="journalRows">

    @if(isset($journalEntry) && $journalEntry->details->count())

        @foreach($journalEntry->details as $index => $detail)

            <tr class="journal-row">

                <td>

                    <select name="details[{{ $index }}][account_type_id]"
                            class="form-control account-type">

                        <option value="">
                            Select
                        </option>

                        @foreach($accountTypes as $accountType)

                            <option value="{{ $accountType->id }}"
                                {{ $detail->ledger?->accountGroup?->accountType?->id == $accountType->id ? 'selected' : '' }}>

                                {{ $accountType->code }}
                                -
                                {{ $accountType->name }}

                            </option>

                        @endforeach

                    </select>

                </td>


                <td>

                    <select name="details[{{ $index }}][account_group_id]"
                            class="form-control account-group">

                        <option value="">
                            Select
                        </option>

                        @if($detail->ledger?->accountGroup)

                            <option value="{{ $detail->ledger->accountGroup->id }}"
                                    selected>

                                {{ $detail->ledger->accountGroup->code }}
                                -
                                {{ $detail->ledger->accountGroup->name }}

                            </option>

                        @endif

                    </select>

                </td>


                <td>

                    <select name="details[{{ $index }}][ledger_id]"
                            class="form-control ledger">

                        <option value="">
                            Select
                        </option>

                        @if($detail->ledger)

                            <option value="{{ $detail->ledger->id }}"
                                    selected>

                                {{ $detail->ledger->code }}
                                -
                                {{ $detail->ledger->name }}

                            </option>

                        @endif

                    </select>

                </td>


                <td>

                    <select name="details[{{ $index }}][sub_ledger_id]"
                            class="form-control sub-ledger">

                        <option value="">
                            None
                        </option>

                        @if($detail->subLedger)

                            <option value="{{ $detail->subLedger->id }}"
                                    selected>

                                {{ $detail->subLedger->code }}
                                -
                                {{ $detail->subLedger->name }}

                            </option>

                        @endif

                    </select>

                </td>


                <td>

                    <input type="number"
                           step="0.01"
                           min="0"
                           name="details[{{ $index }}][debit]"
                           value="{{ $detail->debit }}"
                           class="form-control debit text-right">

                </td>


                <td>

                    <input type="number"
                           step="0.01"
                           min="0"
                           name="details[{{ $index }}][credit]"
                           value="{{ $detail->credit }}"
                           class="form-control credit text-right">

                </td>


                <td class="text-center">

                    <button type="button"
                            class="btn btn-danger btn-sm remove-row">

                        <i class="fas fa-times"></i>

                    </button>

                </td>

            </tr>

        @endforeach

    @else

        {{-- Initial row 1 --}}

        <tr class="journal-row">

            <td>

                <select name="details[0][account_type_id]"
                        class="form-control account-type">

                    <option value="">
                        Select
                    </option>

                    @foreach($accountTypes as $accountType)

                        <option value="{{ $accountType->id }}">

                            {{ $accountType->code }}
                            -
                            {{ $accountType->name }}

                        </option>

                    @endforeach

                </select>

            </td>


            <td>

                <select name="details[0][account_group_id]"
                        class="form-control account-group">

                    <option value="">
                        Select
                    </option>

                </select>

            </td>


            <td>

                <select name="details[0][ledger_id]"
                        class="form-control ledger">

                    <option value="">
                        Select
                    </option>

                </select>

            </td>


            <td>

                <select name="details[0][sub_ledger_id]"
                        class="form-control sub-ledger">

                    <option value="">
                        None
                    </option>

                </select>

            </td>


            <td>

                <input type="number"
                       step="0.01"
                       min="0"
                       name="details[0][debit]"
                       class="form-control debit text-right">

            </td>


            <td>

                <input type="number"
                       step="0.01"
                       min="0"
                       name="details[0][credit]"
                       class="form-control credit text-right">

            </td>


            <td>

                <button type="button"
                        class="btn btn-danger btn-sm remove-row">

                    <i class="fas fa-times"></i>

                </button>

            </td>

        </tr>


        {{-- Initial row 2 --}}

        <tr class="journal-row">

            <td>

                <select name="details[1][account_type_id]"
                        class="form-control account-type">

                    <option value="">
                        Select
                    </option>

                    @foreach($accountTypes as $accountType)

                        <option value="{{ $accountType->id }}">

                            {{ $accountType->code }}
                            -
                            {{ $accountType->name }}

                        </option>

                    @endforeach

                </select>

            </td>


            <td>

                <select name="details[1][account_group_id]"
                        class="form-control account-group">

                    <option value="">
                        Select
                    </option>

                </select>

            </td>


            <td>

                <select name="details[1][ledger_id]"
                        class="form-control ledger">

                    <option value="">
                        Select
                    </option>

                </select>

            </td>


            <td>

                <select name="details[1][sub_ledger_id]"
                        class="form-control sub-ledger">

                    <option value="">
                        None
                    </option>

                </select>

            </td>


            <td>

                <input type="number"
                       step="0.01"
                       min="0"
                       name="details[1][debit]"
                       class="form-control debit text-right">

            </td>


            <td>

                <input type="number"
                       step="0.01"
                       min="0"
                       name="details[1][credit]"
                       class="form-control credit text-right">

            </td>


            <td>

                <button type="button"
                        class="btn btn-danger btn-sm remove-row">

                    <i class="fas fa-times"></i>

                </button>

            </td>

        </tr>

    @endif

    </tbody>


    <tfoot>

        <tr>

            <th colspan="4"
                class="text-right">

                Total

            </th>

            <th>

                <input type="text"
                       id="totalDebit"
                       class="form-control text-right font-weight-bold"
                       readonly
                       value="0.00">

            </th>

            <th>

                <input type="text"
                       id="totalCredit"
                       class="form-control text-right font-weight-bold"
                       readonly
                       value="0.00">

            </th>

            <th></th>

        </tr>


        <tr>

            <th colspan="4"></th>

            <th colspan="3">

                <div id="balanceStatus"
                     class="text-center font-weight-bold">

                    Not Balanced

                </div>

            </th>

        </tr>

    </tfoot>

</table>

</div>


<div class="mt-3">

    <button type="submit"
            id="saveJournal"
            class="btn btn-primary">

        <i class="fas fa-save"></i>

        Save Journal Entry

    </button>

    <a href="{{ route('accounting.journal-entries.index') }}"
       class="btn btn-secondary">

        Cancel

    </a>

</div>

@push('scripts')

<script>

$(document).ready(function () {

    let rowIndex = $('#journalRows .journal-row').length;


    /*
    |--------------------------------------------------------------------------
    | Account Type -> Account Group
    |--------------------------------------------------------------------------
    */

    $(document).on('change', '.account-type', function () {

        const row = $(this).closest('.journal-row');

        const accountTypeId = $(this).val();

        const groupSelect = row.find('.account-group');
        const ledgerSelect = row.find('.ledger');
        const subLedgerSelect = row.find('.sub-ledger');


        groupSelect.html(
            '<option value="">Loading...</option>'
        );

        ledgerSelect.html(
            '<option value="">Select</option>'
        );

        subLedgerSelect.html(
            '<option value="">None</option>'
        );


        if (!accountTypeId) {

            groupSelect.html(
                '<option value="">Select</option>'
            );

            return;
        }


        $.get(
            '{{ url("accounting/journal-entries/account-groups") }}/'
            + accountTypeId,

            function (data) {

                groupSelect.html(
                    '<option value="">Select Account Group</option>'
                );

                $.each(data, function (key, group) {

                    groupSelect.append(
                        $('<option>', {
                            value: group.id,
                            text: group.name
                        })
                    );

                });

            }
        );

    });


    /*
    |--------------------------------------------------------------------------
    | Account Group -> Ledger
    |--------------------------------------------------------------------------
    */

    $(document).on('change', '.account-group', function () {

        const row = $(this).closest('.journal-row');

        const groupId = $(this).val();

        const ledgerSelect = row.find('.ledger');
        const subLedgerSelect = row.find('.sub-ledger');


        ledgerSelect.html(
            '<option value="">Loading...</option>'
        );

        subLedgerSelect.html(
            '<option value="">None</option>'
        );


        if (!groupId) {

            ledgerSelect.html(
                '<option value="">Select Ledger</option>'
            );

            return;
        }


        $.get(
            '{{ url("accounting/journal-entries/ledgers") }}/'
            + groupId,

            function (data) {

                ledgerSelect.html(
                    '<option value="">Select Ledger</option>'
                );

                $.each(data, function (key, ledger) {

                    ledgerSelect.append(
                        $('<option>', {
                            value: ledger.id,
                            text:  ledger.name
                        })
                    );

                });

            }
        );

    });


    /*
    |--------------------------------------------------------------------------
    | Ledger -> Sub Ledger
    |--------------------------------------------------------------------------
    */

    $(document).on('change', '.ledger', function () {

        const row = $(this).closest('.journal-row');

        const ledgerId = $(this).val();

        const subLedgerSelect = row.find('.sub-ledger');


        subLedgerSelect.html(
            '<option value="">Loading...</option>'
        );


        if (!ledgerId) {

            subLedgerSelect.html(
                '<option value="">None</option>'
            );

            return;
        }


        $.get(
            '{{ url("accounting/journal-entries/sub-ledgers") }}/'
            + ledgerId,

            function (data) {

                subLedgerSelect.html(
                    '<option value="">None</option>'
                );

                $.each(data, function (key, subLedger) {

                    subLedgerSelect.append(
                        $('<option>', {
                            value: subLedger.id,
                            text: subLedger.name
                        })
                    );

                });

            }
        );

    });


    /*
    |--------------------------------------------------------------------------
    | Add Row
    |--------------------------------------------------------------------------
    */

    $('#addRow').click(function () {

        let accountTypeOptions = `
            <option value="">Select</option>
            @foreach($accountTypes as $accountType)
                <option value="{{ $accountType->id }}">
                    {{ $accountType->code }} - {{ $accountType->name }}
                </option>
            @endforeach
        `;


        let row = `

            <tr class="journal-row">

                <td>

                    <select name="details[${rowIndex}][account_type_id]"
                            class="form-control account-type">

                        ${accountTypeOptions}

                    </select>

                </td>


                <td>

                    <select name="details[${rowIndex}][account_group_id]"
                            class="form-control account-group">

                        <option value="">
                            Select
                        </option>

                    </select>

                </td>


                <td>

                    <select name="details[${rowIndex}][ledger_id]"
                            class="form-control ledger">

                        <option value="">
                            Select
                        </option>

                    </select>

                </td>


                <td>

                    <select name="details[${rowIndex}][sub_ledger_id]"
                            class="form-control sub-ledger">

                        <option value="">
                            None
                        </option>

                    </select>

                </td>


                <td>

                    <input type="number"
                           step="0.01"
                           min="0"
                           name="details[${rowIndex}][debit]"
                           class="form-control debit text-right">

                </td>


                <td>

                    <input type="number"
                           step="0.01"
                           min="0"
                           name="details[${rowIndex}][credit]"
                           class="form-control credit text-right">

                </td>


                <td>

                    <button type="button"
                            class="btn btn-danger btn-sm remove-row">

                        <i class="fas fa-times"></i>

                    </button>

                </td>

            </tr>
        `;


        $('#journalRows').append(row);

        rowIndex++;

    });


    /*
    |--------------------------------------------------------------------------
    | Remove Row
    |--------------------------------------------------------------------------
    */

    $(document).on('click', '.remove-row', function () {

        if ($('#journalRows .journal-row').length <= 2) {

            alert('At least two journal rows are required.');

            return;
        }


        $(this)
            .closest('.journal-row')
            .remove();


        calculateTotals();

    });


    /*
    |--------------------------------------------------------------------------
    | Debit / Credit
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'input',
        '.debit, .credit',
        function () {

            const row = $(this).closest('.journal-row');


            if ($(this).hasClass('debit')) {

                if (parseFloat($(this).val()) > 0) {

                    row.find('.credit').val('');

                }

            }


            if ($(this).hasClass('credit')) {

                if (parseFloat($(this).val()) > 0) {

                    row.find('.debit').val('');

                }

            }


            calculateTotals();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Calculate Totals
    |--------------------------------------------------------------------------
    */

    function calculateTotals()
    {
        let debit = 0;
        let credit = 0;


        $('.debit').each(function () {

            debit += parseFloat($(this).val()) || 0;

        });


        $('.credit').each(function () {

            credit += parseFloat($(this).val()) || 0;

        });


        $('#totalDebit').val(
            debit.toFixed(2)
        );


        $('#totalCredit').val(
            credit.toFixed(2)
        );


        const difference =
            Math.abs(debit - credit);


        if (
            debit > 0 &&
            credit > 0 &&
            difference < 0.01
        ) {

            $('#balanceStatus')
                .removeClass('text-danger')
                .addClass('text-success')
                .html(
                    '<i class="fas fa-check-circle"></i> Balanced'
                );


            $('#saveJournal').prop(
                'disabled',
                false
            );

        } else {

            $('#balanceStatus')
                .removeClass('text-success')
                .addClass('text-danger')
                .html(
                    'Not Balanced'
                );


            $('#saveJournal').prop(
                'disabled',
                true
            );

        }

    }


    calculateTotals();

});

</script>

@endpush