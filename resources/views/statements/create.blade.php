@extends('layouts.app')

@section('title', 'Create Supplier')

@section('content')

@if ($errors->any())
<div class="alert alert-danger">
    <strong>Please correct the following errors:</strong>
    <ul class="mb-0 mt-2">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('statements.store') }}" method="POST">
    @csrf

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Statement Details</h3>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label for="as_of_date">As of Date</label>

                    <input type="date"
                        id="as_of_date"
                        name="as_of_date"
                        value="{{ old('as_of_date', $selectedDate) }}"
                        class="form-control"
                        required>
                </div>

                <div class="col-md-4">
                    <label>Report Date Used</label>

                    <input type="text"
                        class="form-control"
                        value="{{ $asOfDate }}"
                        readonly>
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit"
                        class="btn btn-success"
                        onclick="return confirm('Save this statement as a draft?')">
                        <i class="fas fa-save"></i> Save Draft
                    </button>
                </div>
            </div>
        </div>
    </div>

    @php
    $displayTotals = [
    'asset' => 0,
    'liability' => 0,
    ];
    @endphp

    @foreach (['asset' => 'Assets', 'liability' => 'Liabilities'] as $sectionKey => $sectionTitle)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $sectionTitle }}</h3>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-sm soa-table">
                <thead>
                    <tr>
                        <th>Account Group / Ledger</th>
                        <th class="text-right" style="width: 180px;">
                            Amount
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse (($report[$sectionKey] ?? []) as $group)
                    @php
                    $groupKey = $group['account_group_id'];
                    $displayTotals[$sectionKey] += $group['ledger_total'];
                    @endphp

                    <tr class="table-secondary">
                        <td colspan="2">
                            <strong>
                                {{ $group['account_type_name'] }}
                                — {{ $group['account_group_name'] }}
                            </strong>
                        </td>
                    </tr>

                    @foreach ($group['ledger_balances'] as $ledger)
                    <tr>
                        <td class="pl-4">
                            {{ $ledger['ledger_name'] }}
                        </td>

                        <td class="text-right">
                            {{ number_format($ledger['balance'], 2) }}
                        </td>
                    </tr>
                    @endforeach

                    <tr>
                        <td>
                            <strong>Ledger Total</strong>
                        </td>

                        <td class="text-right">
                            <strong>
                                {{ number_format($group['ledger_total'], 2) }}
                            </strong>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <strong>Adjustments</strong>

                            <button type="button"
                                class="btn btn-xs btn-primary float-right add-adjustment"
                                data-target="adjustments-{{ $groupKey }}">
                                <i class="fas fa-plus"></i> Add Row
                            </button>

                            <table class="table table-sm table-bordered mt-2 mb-1">
                                <thead>
                                    <tr>
                                        <th>Adjustment Name</th>
                                        <th style="width: 180px;">Amount</th>
                                        <th style="width: 70px;">Action</th>
                                    </tr>
                                </thead>

                                <tbody id="adjustments-{{ $groupKey }}">
                                    @foreach (old("adjustments.$groupKey", [['name' => '', 'amount' => '']]) as $i => $adjustment)
                                    <tr>
                                        <td>
                                            <input type="text"
                                                name="adjustments[{{ $groupKey }}][{{ $i }}][name]"
                                                class="form-control form-control-sm"
                                                value="{{ $adjustment['name'] ?? '' }}"
                                                placeholder="Adjustment name">
                                        </td>

                                        <td>
                                            <input type="number"
                                                step="0.01"
                                                name="adjustments[{{ $groupKey }}][{{ $i }}][amount]"
                                                class="form-control form-control-sm"
                                                value="{{ $adjustment['amount'] ?? '' }}"
                                                placeholder="0.00">
                                        </td>

                                        <td class="text-center">
                                            <button type="button"
                                                class="btn btn-danger btn-xs remove-adjustment">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="2" class="text-center">
                            No account groups found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    <div class="card">
        <div class="card-body text-right">
            <a href="{{ route('statements.index') }}"
                class="btn btn-secondary">
                Cancel
            </a>

            <button type="submit"
                class="btn btn-success"
                onclick="return confirm('Save this statement as a draft?')">
                <i class="fas fa-save"></i> Save Draft
            </button>
        </div>
    </div>
</form>
@stop

@push('scripts')
<script>
    $(function() {
        $('.add-adjustment').on('click', function() {
            let targetId = $(this).data('target');
            let $tbody = $('#' + targetId);
            let groupKey = targetId.replace('adjustments-', '');
            let nextIndex = $tbody.find('tr').length;

            let html = `
            <tr>
                <td>
                    <input type="text"
                           name="adjustments[${groupKey}][${nextIndex}][name]"
                           class="form-control form-control-sm"
                           placeholder="Adjustment name">
                </td>
                <td>
                    <input type="number"
                           step="0.01"
                           name="adjustments[${groupKey}][${nextIndex}][amount]"
                           class="form-control form-control-sm"
                           placeholder="0.00">
                </td>
                <td class="text-center">
                    <button type="button"
                            class="btn btn-danger btn-xs remove-adjustment">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;

            $tbody.append(html);
        });

        $(document).on('click', '.remove-adjustment', function() {
            let $tbody = $(this).closest('tbody');

            $(this).closest('tr').remove();

            if ($tbody.find('tr').length === 0) {
                let targetId = $tbody.attr('id');
                let groupKey = targetId.replace('adjustments-', '');

                $tbody.append(`
                <tr>
                    <td>
                        <input type="text"
                               name="adjustments[${groupKey}][0][name]"
                               class="form-control form-control-sm"
                               placeholder="Adjustment name">
                    </td>
                    <td>
                        <input type="number"
                               step="0.01"
                               name="adjustments[${groupKey}][0][amount]"
                               class="form-control form-control-sm"
                               placeholder="0.00">
                    </td>
                    <td class="text-center">
                        <button type="button"
                                class="btn btn-danger btn-xs remove-adjustment">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `);
            }
        });
    });
</script>
@endpush