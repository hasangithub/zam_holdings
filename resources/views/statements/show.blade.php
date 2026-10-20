@extends('layouts.app')

@section('title', 'Create Supplier')

@section('content')

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
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
                    <strong>Reference No.</strong>
                    <p>{{ $statement->reference_no }}</p>
                </div>

                <div class="col-md-4">
                    <strong>As of Date</strong>
                    <p>
                        {{ \Carbon\Carbon::parse($statement->as_of_date) }}
                    </p>
                </div>

                <div class="col-md-4">
                    <strong>Submitted At</strong>
                    <p>
                        {{ $statement->submitted_at
                            ? $statement->submitted_at
                            : '-' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

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
                                    {{ number_format($group->ledger_total, 2) }}
                                </td>
                            </tr>

                            @foreach ($group->adjustments as $adjustment)
                                <tr>
                                    <td class="pl-4">
                                        {{ $adjustment->name }}
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($adjustment->amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach

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
        <div class="card-header">
            <h3 class="card-title">Statement Summary</h3>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>Total Assets</strong>
                    <h4>{{ number_format($statement->total_assets, 2) }}</h4>
                </div>

                <div class="col-md-4">
                    <strong>Total Liabilities</strong>
                    <h4>{{ number_format($statement->total_liabilities, 2) }}</h4>
                </div>

                <div class="col-md-4">
                    <strong>Difference</strong>
                    <h4>{{ number_format($statement->difference, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('statements.index') }}"
               class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>

            <a href="{{ route('statements.edit', $statement->id) }}"
               class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>

            @if (
                (int) auth()->user()->branch_id === 2
                && (int) $statement->branch_id === 2
                && $statement->status === 'draft'
            )
                <form action="{{ route('statements.submit', $statement->id) }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Submit this statement to Head Office? You will lose access after submission.');">
                    @csrf

                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane"></i>
                        Submit to Head Office
                    </button>
                </form>
            @endif

            <button type="button"
                    onclick="window.print()"
                    class="btn btn-default">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
@stop

@section('css')
<style>
@media print {
    .main-header,
    .main-sidebar,
    .main-footer,
    .content-header,
    .card-footer,
    .no-print {
        display: none !important;
    }

    .content-wrapper {
        margin-left: 0 !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
    }

    body {
        font-size: 11px;
    }
}
</style>
@stop