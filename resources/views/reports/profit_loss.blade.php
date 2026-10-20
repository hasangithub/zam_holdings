@extends('layouts.app')

@section('title','Profit Loss Statement')

@section('content')


<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Report Filters</h3>
    </div>

    <form method="GET" action="{{ route('reports.profit-loss') }}">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label>From Date</label>
                    <input type="date"
                        name="from_date"
                        class="form-control"
                        value="{{ $fromDate }}"
                        required>
                </div>

                <div class="col-md-4">
                    <label>To Date</label>
                    <input type="date"
                        name="to_date"
                        class="form-control"
                        value="{{ $toDate }}"
                        required>
                </div>

                @if((int) auth()->user()->branch_id === 1)
                <div class="col-md-4">
                    <label>Report Scope</label>
                    <select name="branch_scope" class="form-control">
                        <option value="all"
                            {{ request('branch_scope', 'all') === 'all' ? 'selected' : '' }}>
                            All Branches
                        </option>

                        <option value="own"
                            {{ request('branch_scope') === 'own' ? 'selected' : '' }}>
                            Head Office Only (Branch 1)
                        </option>
                    </select>
                </div>
                @endif

                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-search"></i> Generate Report
                    </button>

                    <button type="button"
                        onclick="window.print()"
                        class="btn btn-secondary">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-body">

        <div class="text-center mb-4">
            <h3><strong>PROFIT & LOSS STATEMENT</strong></h3>
            <p>
                Period:
                {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }}
                -
                {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
            </p>
        </div>

        <table class="table table-bordered table-sm">
            <thead class="thead-light">
                <tr>
                    <th>Account / Description</th>
                    <th class="text-right" style="width: 220px;">
                        Amount (LKR)
                    </th>
                </tr>
            </thead>

            <tbody>
                {{-- SALES --}}
                <tr class="table-primary">
                    <td colspan="2"><strong>1. SALES REVENUE</strong></td>
                </tr>

                @foreach($salesGroups as $group)
                <tr class="table-light">
                    <td><strong>{{ $group['name'] }}</strong></td>
                    <td class="text-right">
                        {{ number_format($group['total'], 2) }}
                    </td>
                </tr>

                @foreach($group['ledgers'] as $ledger)
                <tr>
                    <td class="pl-5">{{ $ledger['name'] }}</td>
                    <td class="text-right">
                        {{ number_format($ledger['amount'], 2) }}
                    </td>
                </tr>
                @endforeach
                @endforeach

                <tr>
                    <th>Total Sales</th>
                    <th class="text-right">
                        {{ number_format($salesTotal, 2) }}
                    </th>
                </tr>

                {{-- COST OF GOODS SOLD --}}
                <tr class="table-primary">
                    <td colspan="2">
                        <strong>2. COST OF GOODS SOLD</strong>
                    </td>
                </tr>

                @foreach($cogsGroups as $group)
                <tr class="table-light">
                    <td><strong>{{ $group['name'] }}</strong></td>
                    <td class="text-right">
                        {{ number_format($group['total'], 2) }}
                    </td>
                </tr>

                @foreach($group['ledgers'] as $ledger)
                <tr>
                    <td class="pl-5">{{ $ledger['name'] }}</td>
                    <td class="text-right">
                        {{ number_format($ledger['amount'], 2) }}
                    </td>
                </tr>
                @endforeach
                @endforeach

                <tr>
                    <th>Total Cost of Goods Sold</th>
                    <th class="text-right">
                        {{ number_format($cogsTotal, 2) }}
                    </th>
                </tr>

                <tr class="table-info">
                    <th>GROSS PROFIT / (LOSS)</th>
                    <th class="text-right">
                        {{ number_format($grossProfit, 2) }}
                    </th>
                </tr>

                {{-- OTHER INCOME --}}
                <tr class="table-primary">
                    <td colspan="2"><strong>3. OTHER INCOME</strong></td>
                </tr>

                @foreach($otherIncomeGroups as $group)
                <tr class="table-light">
                    <td><strong>{{ $group['name'] }}</strong></td>
                    <td class="text-right">
                        {{ number_format($group['total'], 2) }}
                    </td>
                </tr>

                @foreach($group['ledgers'] as $ledger)
                <tr>
                    <td class="pl-5">{{ $ledger['name'] }}</td>
                    <td class="text-right">
                        {{ number_format($ledger['amount'], 2) }}
                    </td>
                </tr>
                @endforeach
                @endforeach

                <tr>
                    <th>Total Other Income</th>
                    <th class="text-right">
                        {{ number_format($otherIncomeTotal, 2) }}
                    </th>
                </tr>

                {{-- OTHER EXPENSES --}}
                <tr class="table-primary">
                    <td colspan="2">
                        <strong>4. OTHER EXPENSES</strong>
                    </td>
                </tr>

                @foreach($expenseGroups as $group)
                <tr class="table-light">
                    <td><strong>{{ $group['name'] }}</strong></td>
                    <td class="text-right">
                        {{ number_format($group['total'], 2) }}
                    </td>
                </tr>

                @foreach($group['ledgers'] as $ledger)
                <tr>
                    <td class="pl-5">{{ $ledger['name'] }}</td>
                    <td class="text-right">
                        {{ number_format($ledger['amount'], 2) }}
                    </td>
                </tr>
                @endforeach
                @endforeach

                <tr>
                    <th>Total Other Expenses</th>
                    <th class="text-right">
                        {{ number_format($expensesTotal, 2) }}
                    </th>
                </tr>

                {{-- NET PROFIT / LOSS --}}
                <tr class="{{ $netProfit >= 0 ? 'table-success' : 'table-danger' }}">
                    <th>
                        NET {{ $netProfit >= 0 ? 'PROFIT' : 'LOSS' }}
                    </th>
                    <th class="text-right">
                        {{ number_format(abs($netProfit), 2) }}
                    </th>
                </tr>
            </tbody>
        </table>
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
        .card:first-child,
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

        table {
            width: 100% !important;
        }
    }
</style>
@stop