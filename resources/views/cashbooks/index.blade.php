@extends('layouts.app')

@section('title', 'Cash Book')

@section('content')

<div class="container-fluid">

    <div class="card card-primary card-outline">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-cash-register"></i>
                Cash Book
            </h3>

            <div class="card-tools">
                <a href="{{ route('payment-entries.index') }}"
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Cash Entry
                </a>

                <a href="{{ route('cashbooks.create') }}"
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> New Transaction
                </a>
            </div>
        </div>

        <div class="card-body">

            {{-- FILTERS --}}
            <form method="GET"
                  action="{{ route('cashbooks.index') }}"
                  class="mb-3">

                <div class="row">

                    <div class="col-md-3">
                        <label>Cash Book</label>

                        <select name="cashbook_sub_ledger_id"
                                class="form-control form-control-sm"
                                required>

                            @foreach($cashBooks as $cashBook)
                                <option value="{{ $cashBook->id }}"
                                    {{ $cashbookId == $cashBook->id ? 'selected' : '' }}>
                                    {{ $cashBook->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>From Date</label>

                        <input type="date"
                               name="from_date"
                               value="{{ request('from_date') }}"
                               class="form-control form-control-sm">
                    </div>

                    <div class="col-md-2">
                        <label>To Date</label>

                        <input type="date"
                               name="to_date"
                               value="{{ request('to_date') }}"
                               class="form-control form-control-sm">
                    </div>

                    <div class="col-md-2">
                        <label>&nbsp;</label>

                        <div>
                            <button type="submit"
                                    class="btn btn-primary btn-sm">
                                <i class="fas fa-filter"></i> Filter
                            </button>

                            <a href="{{ route('cashbooks.index') }}"
                               class="btn btn-secondary btn-sm">
                                Reset
                            </a>
                        </div>
                    </div>

                </div>
            </form>

            {{-- JOURNAL SUMMARY --}}
            <div class="card card-outline card-info">

                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-book"></i>
                        Cash Book Journal Summary
                    </h3>

                    <div class="card-tools">
                        <span class="badge badge-info">
                            {{ optional($cashBooks->firstWhere('id', $cashbookId))->name ?? 'Cash Book' }}
                        </span>
                    </div>
                </div>

                <div class="card-body">

                    {{-- JOURNAL TOTALS --}}
                    <div class="row mb-3">

                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-secondary">
                                    <i class="fas fa-wallet"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Opening Balance</span>
                                    <span class="info-box-number">
                                        {{ number_format($journalOpeningBalance, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-arrow-down"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Debit</span>
                                    <span class="info-box-number text-success">
                                        {{ number_format($journalTotalDebit, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger">
                                    <i class="fas fa-arrow-up"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Credit</span>
                                    <span class="info-box-number text-danger">
                                        {{ number_format($journalTotalCredit, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-balance-scale"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Closing Balance</span>
                                    <span class="info-box-number text-info">
                                        {{ number_format($journalClosingBalance, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- JOURNAL TABLE --}}
                    <div class="table-responsive">

                        <table class="table table-bordered table-hover table-sm">

                            <thead class="thead-light">
                                <tr>
                                    <th width="45">#</th>
                                    <th width="105">Date</th>
                                    <th width="100">Journal No.</th>
                                    <th>Description</th>
                                    <th width="140" class="text-right">Debit</th>
                                    <th width="140" class="text-right">Credit</th>
                                    <th width="150" class="text-right">Balance</th>
                                </tr>
                            </thead>

                            <tbody>

                                {{-- OPENING BALANCE --}}
                                <tr class="bg-light font-weight-bold">
                                    <td colspan="4">
                                        Opening Balance
                                    </td>

                                    <td class="text-right">
                                        -
                                    </td>

                                    <td class="text-right">
                                        -
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($journalOpeningBalance, 2) }}
                                    </td>
                                </tr>

                                @forelse($journalEntries as $journal)

                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($journal->journal_date)->format('Y-m-d') }}
                                        </td>

                                        <td>
                                            <span class="badge badge-info">
                                                JE #{{ $journal->journal_entry_id }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ $journal->description ?: '-' }}
                                        </td>

                                        <td class="text-right text-success">
                                            {{ number_format($journal->debit, 2) }}
                                        </td>

                                        <td class="text-right text-danger">
                                            {{ number_format($journal->credit, 2) }}
                                        </td>

                                        <td class="text-right font-weight-bold">
                                            {{ number_format($journal->running_balance, 2) }}
                                        </td>
                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="7"
                                            class="text-center text-muted py-3">
                                            No journal entries found.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                            <tfoot class="bg-light font-weight-bold">
                                <tr>
                                    <td colspan="4" class="text-right">
                                        Period Total
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($journalTotalDebit, 2) }}
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($journalTotalCredit, 2) }}
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($journalClosingBalance, 2) }}
                                    </td>
                                </tr>
                            </tfoot>

                        </table>

                    </div>

                </div>
            </div>

            {{-- CASH BOOK TRANSACTIONS --}}
            <div class="card card-outline card-primary">

                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i>
                        Cash Book Transactions
                    </h3>
                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover table-sm">

                            <thead class="thead-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th width="110">Date</th>
                                    <th width="100">Type</th>
                                    <th width="120">Reference</th>
                                    <th>Ledger</th>
                                    <th>Subledger</th>
                                    <th>Description</th>
                                    <th width="140" class="text-right">Amount</th>
                                    <th width="120">Journal</th>
                                    <th width="120">Created By</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($transactions as $transaction)

                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d') }}
                                        </td>

                                        <td>
                                            @if($transaction->type === 'in')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-arrow-down"></i>
                                                    Cash In
                                                </span>
                                            @else
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-arrow-up"></i>
                                                    Cash Out
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            {{ $transaction->reference ?: '-' }}
                                        </td>

                                        <td>
                                            {{ $transaction->ledger->name ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $transaction->subLedger->name ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $transaction->description ?: '-' }}
                                        </td>

                                        <td class="text-right font-weight-bold">
                                            {{ number_format($transaction->amount, 2) }}
                                        </td>

                                        <td>
                                            @if($transaction->journal_entry_id)
                                                <span class="badge badge-info">
                                                    JE #{{ $transaction->journal_entry_id }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td>
                                            {{ $transaction->creator->name ?? '-' }}
                                        </td>
                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="10"
                                            class="text-center text-muted py-4">
                                            No cash book transactions found.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>
            </div>

        </div>
    </div>

</div>

@endsection