@extends('layouts.app')

@section('title', 'Account Setup')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Account Setup</h3>
    </div>

    <form action="{{ route('account-setup.store') }}" method="POST">
        @csrf

        <div class="card-body">

            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
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

            {{-- ACCOUNT TYPE --}}
            <div class="form-group">
                <label>Account Type *</label>
                <select name="account_type_id" id="account_type_id" class="form-control" required>
                    <option value="">Select Account Type</option>
                    @foreach($accountTypes as $type)
                    <option value="{{ $type->id }}" @selected(old('account_type_id')==$type->id)>
                        {{ $type->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <hr>
            <h5>Account Group</h5>

            <div class="form-group">
                <label>Action</label>
                <select name="group_action" id="group_action" class="form-control">
                    <option value="existing">Select Existing Group</option>
                    <option value="new" @selected(old('group_action')=='new' )>Create New Group</option>
                </select>
            </div>

            <div id="group_existing">
                <div class="form-group">
                    <label>Account Group *</label>
                    <select name="group_id" id="group_id" class="form-control">
                        <option value="">Select Account Group</option>
                    </select>
                </div>
            </div>

            <div id="group_new" style="display:none">
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label>Group Name *</label>
                        <input name="group_name" class="form-control" value="{{ old('group_name') }}">
                    </div>
                </div>
            </div>

            <hr>
            <h5>Ledger <small class="text-muted">(Optional)</small></h5>

            <div class="form-group">
                <label>Action</label>
                <select name="ledger_action" id="ledger_action" class="form-control">
                    <option value="none">Skip Ledger</option>
                    <option value="existing" @selected(old('ledger_action')=='existing' )>Select Existing Ledger</option>
                    <option value="new" @selected(old('ledger_action')=='new' )>Create New Ledger</option>
                </select>
            </div>

            <div id="ledger_existing" style="display:none">
                <div class="form-group">
                    <label>Ledger *</label>
                    <select name="ledger_id" id="ledger_id" class="form-control">
                        <option value="">Select Ledger</option>
                    </select>
                </div>
            </div>

            <div id="ledger_new" style="display:none">
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label>Ledger Name *</label>
                        <input name="ledger_name" class="form-control" value="{{ old('ledger_name') }}">
                    </div>
                </div>
            </div>


            <hr>

            <h5>Subledger <small class="text-muted">(Optional)</small></h5>

            <div id="subledger_section" class="form-row">

                <div class="form-group col-md-6">
                    <label for="subledger_name">Subledger Name</label>
                    <input
                        type="text"
                        name="subledger_name"
                        id="subledger_name"
                        class="form-control"
                        value="{{ old('subledger_name') }}"
                        placeholder="Enter subledger name">
                </div>

                <div class="form-group col-md-6" id="subledger_branch_section"
                    style="display: none;">
                    <label for="subledger_branch_id">Branch</label>

                    <select
                        name="subledger_branch_id"
                        id="subledger_branch_id"
                        class="form-control">
                        <option value="">Select Branch</option>

                        @foreach($branches as $branch)
                        <option
                            value="{{ $branch->id }}"
                            @selected(old('subledger_branch_id')==$branch->id)
                            >
                            {{ $branch->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

            </div>


        </div>

        <div class="card-footer">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-save"></i> Save Account Setup
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {

        function clearSelect(selector, label) {
            $(selector).empty().append(
                $('<option>', {
                    value: '',
                    text: label
                })
            );
        }

        function loadData(url, selector, label) {
            clearSelect(selector, label);

            if (!url) {
                return $.Deferred().resolve().promise();
            }

            return $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json'
            }).done(function(data) {
                $.each(data, function(i, row) {
                    $(selector).append(
                        $('<option>', {
                            value: row.id,
                            text: row.name
                        })
                    );
                });
            }).fail(function(xhr) {
                console.error('Dropdown loading failed:', xhr.status, xhr.responseText);
            });
        }

        function groupUrl() {
            let id = $('#account_type_id').val();
            return id ?
                "{{ url('/account-setup/account-types') }}/" + id + "/groups" :
                null;
        }

        function ledgerUrl() {
            let id = $('#group_id').val();
            return id ?
                "{{ url('/account-setup/groups') }}/" + id + "/ledgers" :
                null;
        }

        function subledgerUrl() {
            let id = $('#ledger_id').val();
            return id ?
                "{{ url('/account-setup/ledgers') }}/" + id + "/subledgers" :
                null;
        }

        function updateSubledgerBranch() {
            let ledgerAction = $('#ledger_action').val();
            let ledgerId = $('#ledger_id').val();

            let showBranch =
                ledgerAction === 'existing' && String(ledgerId) === '1';

            $('#subledger_branch_section').toggle(showBranch);

            $('#subledger_branch_id').prop('required', showBranch);

            if (!showBranch) {
                $('#subledger_branch_id').val('');
            }
        }

        $('#ledger_action, #ledger_id').on('change', updateSubledgerBranch);

        updateSubledgerBranch();

        function updateVisibility() {
            let groupAction = $('#group_action').val();
            let ledgerAction = $('#ledger_action').val();
            let subAction = $('#subledger_action').val();

            $('#group_existing').toggle(groupAction === 'existing');
            $('#group_new').toggle(groupAction === 'new');

            if (groupAction === 'new') {
                $('#ledger_action option[value="existing"]').prop('disabled', true);
                if (ledgerAction === 'existing') {
                    $('#ledger_action').val('none');
                    ledgerAction = 'none';
                }
            } else {
                $('#ledger_action option[value="existing"]').prop('disabled', false);
            }

            $('#ledger_existing').toggle(ledgerAction === 'existing');
            $('#ledger_new').toggle(ledgerAction === 'new');

            let hasLedger =
                ledgerAction === 'new' ||
                (ledgerAction === 'existing' && $('#ledger_id').val() !== '');

            $('#subledger_action option[value="existing"]').prop(
                'disabled',
                !hasLedger || ledgerAction !== 'existing'
            );

            $('#subledger_action option[value="new"]').prop('disabled', !hasLedger);

            if (!hasLedger && subAction !== 'none') {
                $('#subledger_action').val('none');
                subAction = 'none';
            }

            $('#subledger_action').prop('disabled', !hasLedger);
            $('#subledger_message').toggle(!hasLedger);

            $('#subledger_existing').toggle(subAction === 'existing');
            $('#subledger_new').toggle(subAction === 'new');

            $('input[name="group_name"]')
                .prop('required', groupAction === 'new');

            $('#group_id').prop('required', groupAction === 'existing');

            $('input[name="ledger_name"]')
                .prop('required', ledgerAction === 'new');

            $('#ledger_id').prop('required', ledgerAction === 'existing');

            $('input[name="subledger_name"]')
                .prop('required', subAction === 'new');

            $('#sub_ledger_id').prop('required', subAction === 'existing');
        }

        $('#account_type_id').on('change', function() {
            clearSelect('#group_id', 'Select Account Group');
            clearSelect('#ledger_id', 'Select Ledger');
            clearSelect('#sub_ledger_id', 'Select Subledger');

            loadData(groupUrl(), '#group_id', 'Select Account Group')
                .always(updateVisibility);

            updateVisibility();
        });

        $('#group_action').on('change', function() {
            if ($(this).val() === 'existing') {
                loadData(groupUrl(), '#group_id', 'Select Account Group');
            } else {
                clearSelect('#group_id', 'Select Account Group');
            }

            clearSelect('#ledger_id', 'Select Ledger');
            clearSelect('#sub_ledger_id', 'Select Subledger');
            $('#ledger_action').val('none');
            $('#subledger_action').val('none');

            updateVisibility();
        });

        $('#group_id').on('change', function() {
            clearSelect('#ledger_id', 'Select Ledger');
            clearSelect('#sub_ledger_id', 'Select Subledger');

            $('#ledger_action').val('none');
            $('#subledger_action').val('none');

            updateVisibility();
        });

        $('#ledger_action').on('change', function() {
            clearSelect('#ledger_id', 'Select Ledger');
            clearSelect('#sub_ledger_id', 'Select Subledger');
            $('#subledger_action').val('none');

            if ($(this).val() === 'existing') {
                loadData(ledgerUrl(), '#ledger_id', 'Select Ledger')
                    .always(updateVisibility);
            } else {
                updateVisibility();
            }
        });

        $('#ledger_id').on('change', function() {
            clearSelect('#sub_ledger_id', 'Select Subledger');
            $('#subledger_action').val('none');

            updateVisibility();
        });

        $('#subledger_action').on('change', function() {
            if ($(this).val() === 'existing') {
                loadData(subledgerUrl(), '#sub_ledger_id', 'Select Subledger');
            } else {
                clearSelect('#sub_ledger_id', 'Select Subledger');
            }

            updateVisibility();
        });

        // Initial display.
        updateVisibility();

        // Restore previous values after validation failure.
        const oldType = @json(old('account_type_id'));
        const oldGroup = @json(old('group_id'));
        const oldLedger = @json(old('ledger_id'));
        const oldSubledger = @json(old('sub_ledger_id'));

        if (oldType) {
            $('#account_type_id').val(String(oldType));

            loadData(groupUrl(), '#group_id', 'Select Account Group')
                .done(function() {
                    if (oldGroup) {
                        $('#group_id').val(String(oldGroup));
                    }

                    if ($('#group_action').val() === 'existing' && oldGroup) {
                        loadData(ledgerUrl(), '#ledger_id', 'Select Ledger')
                            .done(function() {
                                if (oldLedger) {
                                    $('#ledger_id').val(String(oldLedger));
                                }

                                if (
                                    $('#ledger_action').val() === 'existing' &&
                                    oldLedger
                                ) {
                                    loadData(
                                        subledgerUrl(),
                                        '#sub_ledger_id',
                                        'Select Subledger'
                                    ).done(function() {
                                        if (oldSubledger) {
                                            $('#sub_ledger_id').val(String(oldSubledger));
                                        }
                                        updateVisibility();
                                    });
                                }

                                updateVisibility();
                            });
                    }

                    updateVisibility();
                });
        }
    });
</script>
@endpush