@extends('layouts.app')

@section('title', 'Create Supplier')

@section('content')

@if (session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

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

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            {{ $statement->reference_no }}
        </h3>

        <div class="card-tools">
            @if ($statement->status === 'submitted')
            <span class="badge badge-success">
                Submitted to Head Office
            </span>
            @else
            <span class="badge badge-secondary">Draft</span>
            @endif
        </div>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label>As of Date</label>
                <input type="text"
                    class="form-control"
                    value="{{ \Carbon\Carbon::parse($statement->as_of_date)->format('d-m-Y') }}"
                    readonly>
            </div>

            <div class="col-md-4">
                <label>Reference No.</label>
                <input type="text"
                    class="form-control"
                    value="{{ $statement->reference_no }}"
                    readonly>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('statements.update', $statement->id) }}"
    method="POST">
    @csrf
    @method('PUT')

    @foreach (['asset' => 'Assets', 'liability' => 'Liabilities'] as $sectionKey => $sectionTitle)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $sectionTitle }}</h3>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Account Group / Ledger</th>
                        <th class="text-right" style="width: 180px;">
                            Amount
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @php
                    $sectionGroups = $statement->groups
                    ->where('section', $sectionKey);
                    @endphp

                    @forelse ($sectionGroups as $group)
                    <tr class="table-secondary">
                        <td colspan="2">
                            <strong>
                                {{ $group->account_type_name }}
                                — {{ $group->account_group_name }}
                            </strong>
                        </td>
                    </tr>

                    @foreach (($group->ledger_balances ?? []) as $ledger)
                    <tr>
                        <td class="pl-4">
                            {{ $ledger['ledger_name'] ?? 'Ledger' }}
                        </td>

                        <td class="text-right">
                            {{ number_format((float) ($ledger['balance'] ?? 0), 2) }}
                        </td>
                    </tr>
                    @endforeach

                    <tr>
                        <td><strong>Ledger Total</strong></td>
                        <td class="text-right">
                            <strong>
                                {{ number_format($group->ledger_total, 2) }}
                            </strong>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <strong>Adjustments</strong>

                            <button type="button"
                                class="btn btn-primary btn-xs float-right add-adjustment"
                                data-target="adjustments-{{ $group->id }}">
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

                                <tbody id="adjustments-{{ $group->id }}">
                                    @php
                                    $existingAdjustments = old(
                                    "adjustments.{$group->id}",
                                    $group->adjustments->map(function ($adjustment) {
                                    return [
                                    'name' => $adjustment->name,
                                    'amount' => $adjustment->amount,
                                    ];
                                    })->toArray()
                                    );

                                    if (count($existingAdjustments) === 0) {
                                    $existingAdjustments = [
                                    ['name' => '', 'amount' => '']
                                    ];
                                    }
                                    @endphp

                                    @foreach ($existingAdjustments as $i => $adjustment)
                                    <tr>
                                        <td>
                                            <input type="text"
                                                name="adjustments[{{ $group->id }}][{{ $i }}][name]"
                                                class="form-control form-control-sm"
                                                value="{{ $adjustment['name'] ?? '' }}"
                                                placeholder="Adjustment name">
                                        </td>

                                        <td>
                                            <input type="number"
                                                step="0.01"
                                                name="adjustments[{{ $group->id }}][{{ $i }}][amount]"
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

                    <tr>
                        <td><strong>Adjusted Total</strong></td>
                        <td class="text-right">
                            <strong>
                                {{ number_format($group->adjusted_total, 2) }}
                            </strong>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="2" class="text-center">
                            No groups available.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Total Assets:</strong>
                    {{ number_format($statement->total_assets, 2) }}
                </div>

                <div class="col-md-4">
                    <strong>Total Liabilities:</strong>
                    {{ number_format($statement->total_liabilities, 2) }}
                </div>

                <div class="col-md-4">
                    <strong>Difference:</strong>
                    {{ number_format($statement->difference, 2) }}
                </div>
            </div>

            <a href="{{ route('statements.show', $statement->id) }}"
                class="btn btn-secondary">
                Cancel
            </a>

            <button type="submit"
                class="btn btn-primary"
                onclick="return confirm('Save your changes?')">
                <i class="fas fa-save"></i> Save Changes
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
            let groupId = targetId.replace('adjustments-', '');
            let nextIndex = 0;

            $tbody.find('input[name]').each(function() {
                let match = $(this).attr('name').match(/\[(\d+)\]\[(name|amount)\]$/);

                if (match) {
                    nextIndex = Math.max(nextIndex, parseInt(match[1], 10) + 1);
                }
            });

            $tbody.append(`
            <tr>
                <td>
                    <input type="text"
                           name="adjustments[${groupId}][${nextIndex}][name]"
                           class="form-control form-control-sm"
                           placeholder="Adjustment name">
                </td>
                <td>
                    <input type="number"
                           step="0.01"
                           name="adjustments[${groupId}][${nextIndex}][amount]"
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
        });

        $(document).on('click', '.remove-adjustment', function() {
            let $tbody = $(this).closest('tbody');

            $(this).closest('tr').remove();

            if ($tbody.find('tr').length === 0) {
                let groupId = $tbody.attr('id').replace('adjustments-', '');

                $tbody.append(`
                <tr>
                    <td>
                        <input type="text"
                               name="adjustments[${groupId}][0][name]"
                               class="form-control form-control-sm"
                               placeholder="Adjustment name">
                    </td>
                    <td>
                        <input type="number"
                               step="0.01"
                               name="adjustments[${groupId}][0][amount]"
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