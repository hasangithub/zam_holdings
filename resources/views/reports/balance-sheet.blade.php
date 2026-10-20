@extends('layouts.app')

@section('title', 'Customer Summary Report')

@section('content')

<div class="card">
   
    <div class="card-header">
        <form method="GET"
            action="{{ route('reports.balance-sheet') }}"
            class="form-inline">

            <label for="as_of_date" class="mr-2">As of Date</label>

            <input type="date"
                name="as_of_date"
                id="as_of_date"
                class="form-control form-control-sm mr-3"
                value="{{ $asOfDate }}"
                required>

            @if((int) auth()->user()->branch_id === 1)
            <label for="branch_scope" class="mr-2">
                Report Scope
            </label>

            <select name="branch_scope"
                id="branch_scope"
                class="form-control form-control-sm mr-3">
                <option value="all"
                    {{ $branchScope === 'all' ? 'selected' : '' }}>
                    All Branches
                </option>

                <option value="own"
                    {{ $branchScope === 'own' ? 'selected' : '' }}>
                    Head Office Only
                </option>
            </select>
            @endif

            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-search"></i> Generate
            </button>

            <button type="button"
                onclick="window.print()"
                class="btn btn-secondary btn-sm ml-2">
                <i class="fas fa-print"></i> Print
            </button>
        </form>
    </div>


    <div class="card-body">

        <div class="text-center mb-4">
            <h3 class="font-weight-bold">BALANCE SHEET</h3>
            <h5>As at {{ \Carbon\Carbon::parse($asOfDate)->format('d-m-Y') }}</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr>
                        <th>Account Group / Ledger</th>
                        <th class="text-right" style="width: 200px;">
                            Amount (LKR)
                        </th>
                    </tr>
                </thead>

                <tbody>
                    {{-- ASSETS --}}
                    <tr class="table-secondary">
                        <td colspan="2" class="font-weight-bold">
                            ASSETS
                        </td>
                    </tr>

                    @forelse($assets as $row)
                    <tr>
                        <td class="pl-4">
                            {{ $row['group'] }} -
                            {{ $row['ledger'] }}
                        </td>
                        <td class="text-right">
                            {{ number_format($row['balance'], 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="text-center text-muted">
                            No asset balances found.
                        </td>
                    </tr>
                    @endforelse

                    <tr class="font-weight-bold">
                        <td>Total Assets</td>
                        <td class="text-right">
                            {{ number_format($assetsTotal, 2) }}
                        </td>
                    </tr>

                    {{-- LIABILITIES --}}
                    <tr class="table-secondary">
                        <td colspan="2" class="font-weight-bold">
                            LIABILITIES
                        </td>
                    </tr>

                    @forelse($liabilities as $row)
                    <tr>
                        <td class="pl-4">
                            {{ $row['group'] }} -
                            {{ $row['ledger'] }}
                        </td>
                        <td class="text-right">
                            {{ number_format($row['balance'], 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="text-center text-muted">
                            No liability balances found.
                        </td>
                    </tr>
                    @endforelse

                    <tr class="font-weight-bold">
                        <td>Total Liabilities</td>
                        <td class="text-right">
                            {{ number_format($liabilitiesTotal, 2) }}
                        </td>
                    </tr>

                    {{-- EQUITY --}}
                    <tr class="table-secondary">
                        <td colspan="2" class="font-weight-bold">
                            EQUITY
                        </td>
                    </tr>

                    @forelse($equity as $row)
                    <tr>
                        <td class="pl-4">
                            {{ $row['group'] }}
                            @if(!empty($row['ledger']))
                            - {{ $row['ledger'] }}
                            @endif
                        </td>
                        <td class="text-right">
                            {{ number_format($row['balance'], 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="text-center text-muted">
                            No equity balances found.
                        </td>
                    </tr>
                    @endforelse

                    <tr class="font-weight-bold">
                        <td>Total Equity</td>
                        <td class="text-right">
                            {{ number_format($equityTotal, 2) }}
                        </td>
                    </tr>

                    {{-- TOTAL LIABILITIES + EQUITY --}}
                    <tr class="table-primary font-weight-bold">
                        <td>Total Liabilities + Equity</td>
                        <td class="text-right">
                            {{ number_format(
                                $liabilitiesTotal + $equityTotal,
                                2
                            ) }}
                        </td>
                    </tr>

                    {{-- BALANCE CHECK --}}
                    <tr class="font-weight-bold">
                        <td>Difference</td>
                        <td class="text-right
                            {{ abs($assetsTotal - $liabilitiesTotal - $equityTotal) < 0.01
                                ? 'text-success'
                                : 'text-danger' }}">
                            {{ number_format(
                                $assetsTotal - $liabilitiesTotal - $equityTotal,
                                2
                            ) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

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
        .card-header,
        .btn {
            display: none !important;
        }

        .content-wrapper {
            margin-left: 0 !important;
            min-height: auto !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .card-body {
            padding: 0 !important;
        }

        body {
            font-size: 12px;
        }

        table {
            width: 100% !important;
        }
    }
</style>
@stop