
@extends('layouts.app')

@section('title', 'Employee Salary Advance Statement')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">{{ $employee->name }} - Statement</h3>
            <small class="text-muted">Employee Salary Advance Transactions</small>
        </div>

        @if(!$employee->opening_journal_entry_id)
            <button class="btn btn-primary"
                    data-toggle="modal"
                    data-target="#openingBalanceModal">
                <i class="fas fa-plus"></i> Opening Balance
            </button>
        @else
            <span class="badge badge-success p-2">
                Opening Balance Entered
            </span>
        @endif
    </div>

    {{-- VALIDATION ERRORS --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- DATE FILTER --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2">
            <form method="GET"
                  action="{{ route('employees.statement', $employee->id) }}">

                <div class="form-row align-items-end">
                    <div class="col-md-3">
                        <label>From Date</label>
                        <input type="date"
                               name="from_date"
                               class="form-control"
                               value="{{ $fromDate }}">
                    </div>

                    <div class="col-md-3">
                        <label>To Date</label>
                        <input type="date"
                               name="to_date"
                               class="form-control"
                               value="{{ $toDate }}">
                    </div>

                    <div class="col-md-2">
                        <button type="submit"
                                class="btn btn-primary btn-block">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>

                    <div class="col-md-2">
                        <a href="{{ route('employees.statement', $employee->id) }}"
                           class="btn btn-secondary btn-block">
                            Last 7 Days
                        </a>
                    </div>

                    <div class="col-md-2">
                        <a href="{{ route('employees.statement', [
                            'id' => $employee->id,
                            'all' => 1
                        ]) }}"
                           class="btn btn-info btn-block">
                            All
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- PERIOD OPENING BALANCE --}}
    @if(!$all)
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-2 d-flex justify-content-between">
                <strong>Opening Balance for Period</strong>
                <strong>{{ number_format($periodOpeningBalance, 2) }}</strong>
            </div>
        </div>
    @endif

    {{-- SUMMARY --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">
                        {{ $all ? 'Total Advances' : 'Period Advances' }}
                    </h6>
                    <h4 class="text-danger">
                        {{ number_format($periodAdvances, 2) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">
                        {{ $all ? 'Total Repayments' : 'Period Repayments' }}
                    </h6>
                    <h4 class="text-success">
                        {{ number_format($periodRepayments, 2) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Outstanding Salary Advance</h6>
                    <h4 class="{{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($balance, 2) }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    {{-- STATEMENT TABLE --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <strong>Employee Transaction Statement</strong>

                @if($all)
                    <span class="badge badge-info">All Transactions</span>
                @else
                    <span class="text-muted small">
                        {{ $fromDate }} to {{ $toDate }}
                    </span>
                @endif
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Date</th>
                            <th>Module</th>
                            <th>Type</th>
                            <th>Reference</th>
                            <th>Description</th>
                            <th class="text-right">Debit</th>
                            <th class="text-right">Credit</th>
                            <th class="text-right">Balance</th>
                        </tr>
                    </thead>

                    <tbody>
                        @if(!$all)
                            <tr class="bg-light font-weight-bold">
                                <td>{{ $fromDate }}</td>
                                <td colspan="6">Opening Balance</td>
                                <td class="text-right">
                                    {{ number_format($periodOpeningBalance, 2) }}
                                </td>
                            </tr>
                        @endif

                        @forelse($ledger as $row)
                            <tr>
                                <td>{{ $row['date'] }}</td>
                                <td>{{ $row['module'] }}</td>
                                <td>
                                    @if($row['type'] === 'Opening Balance')
                                        <span class="badge badge-info">
                                            Opening Balance
                                        </span>
                                    @elseif($row['type'] === 'Advance Issued')
                                        <span class="badge badge-danger">
                                            Advance Issued
                                        </span>
                                    @elseif($row['type'] === 'Repayment')
                                        <span class="badge badge-success">
                                            Repayment
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $row['reference'] ?? '-' }}</td>
                                <td>{{ $row['description'] ?? '-' }}</td>

                                <td class="text-right text-danger">
                                    {{ $row['debit'] > 0
                                        ? number_format($row['debit'], 2)
                                        : '-' }}
                                </td>

                                <td class="text-right text-success">
                                    {{ $row['credit'] > 0
                                        ? number_format($row['credit'], 2)
                                        : '-' }}
                                </td>

                                <td class="text-right font-weight-bold">
                                    {{ number_format($row['balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8"
                                    class="text-center text-muted py-4">
                                    No transactions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($ledger->isNotEmpty())
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td colspan="5" class="text-right">
                                    Period Totals
                                </td>
                                <td class="text-right text-danger">
                                    {{ number_format($periodAdvances, 2) }}
                                </td>
                                <td class="text-right text-success">
                                    {{ number_format($periodRepayments, 2) }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($balance, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

{{-- OPENING BALANCE MODAL --}}
@if(!$employee->opening_journal_entry_id)
    <div class="modal fade" id="openingBalanceModal" tabindex="-1"
         role="dialog" aria-labelledby="openingBalanceModalLabel"
         aria-hidden="true">

        <div class="modal-dialog" role="document">
            <form method="POST"
                  action="{{ route('employees.opening-balance', $employee->id) }}">
                @csrf

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="openingBalanceModalLabel">
                            Employee Salary Advance Opening Balance
                        </h5>

                        <button type="button" class="close"
                                data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Employee</label>
                            <input type="text"
                                   class="form-control"
                                   value="{{ $employee->name }}"
                                   readonly>
                        </div>

                        <div class="form-group">
                            <label>Opening Balance Amount</label>
                            <input type="number"
                                   name="amount"
                                   class="form-control"
                                   min="0.01"
                                   step="0.01"
                                   value="{{ old('amount') }}"
                                   required>
                        </div>

                        <div class="form-group">
                            <label>Opening Balance Date</label>
                            <input type="date"
                                   name="date"
                                   class="form-control"
                                   value="{{ old('date', now()->toDateString()) }}"
                                   required>
                        </div>

                        <small class="text-muted">
                            The opening balance will be recorded in the
                            accounting journal and cannot be entered again
                            after it is saved.
                        </small>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-primary">
                            Save Opening Balance
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
