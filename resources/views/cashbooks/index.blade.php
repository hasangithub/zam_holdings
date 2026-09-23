@extends('layouts.app')

@section('title', 'Cash Book')

@section('content')

<div class="container-fluid">

    <div class="card card-primary card-outline">

        {{-- HEADER --}}
        <div class="card-header">

            <h3 class="card-title">
                <i class="fas fa-cash-register"></i>
                Cash Book
            </h3>

            <div class="card-tools">
                <a href="{{ route('cashbooks.create') }}"
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i>
                    New Transaction
                </a>
            </div>

        </div>

        <div class="card-body">

            {{-- FILTERS --}}
            <form method="GET"
                  action="{{ route('cashbooks.index') }}"
                  class="mb-3">

                <div class="row">

                    {{-- CASH BOOK --}}
                    <div class="col-md-3">
                        <label>Cash Book</label>

                        <select name="cashbook_sub_ledger_id"
                                class="form-control form-control-sm">

                            @foreach($cashBooks as $cashBook)

                                <option value="{{ $cashBook->id }}"
                                    {{ $cashbookId == $cashBook->id ? 'selected' : '' }}>
                                    {{ $cashBook->name }}
                                </option>

                            @endforeach

                        </select>
                    </div>

                    {{-- FROM DATE --}}
                    <div class="col-md-2">
                        <label>From Date</label>

                        <input type="date"
                               name="from_date"
                               value="{{ request('from_date') }}"
                               class="form-control form-control-sm">
                    </div>

                    {{-- TO DATE --}}
                    <div class="col-md-2">
                        <label>To Date</label>

                        <input type="date"
                               name="to_date"
                               value="{{ request('to_date') }}"
                               class="form-control form-control-sm">
                    </div>

                    {{-- BUTTONS --}}
                    <div class="col-md-2">
                        <label>&nbsp;</label>

                        <div>
                            <button type="submit"
                                    class="btn btn-primary btn-sm">
                                <i class="fas fa-filter"></i>
                                Filter
                            </button>

                            <a href="{{ route('cashbooks.index') }}"
                               class="btn btn-secondary btn-sm">
                                Reset
                            </a>
                        </div>
                    </div>

                </div>

            </form>


            {{-- SUMMARY --}}
            <div class="row mb-3">

                <div class="col-md-4">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h4>
                                {{ number_format($totalCashIn, 2) }}
                            </h4>
                            <p>Cash In</p>
                        </div>

                        <div class="icon">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                    </div>
                </div>


                <div class="col-md-4">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h4>
                                {{ number_format($totalCashOut, 2) }}
                            </h4>
                            <p>Cash Out</p>
                        </div>

                        <div class="icon">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                    </div>
                </div>


                <div class="col-md-4">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h4>
                                {{ number_format($totalCashIn - $totalCashOut, 2) }}
                            </h4>
                            <p>Net Cash Movement</p>
                        </div>

                        <div class="icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>

            </div>


            {{-- TRANSACTIONS --}}
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
                            <th width="140" class="text-right">
                                Amount
                            </th>
                            <th width="120">Journal</th>
                            <th width="120">Created By</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse($transactions as $transaction)

                            <tr>

                                <td>
                                    {{ $loop->iteration }}
                                </td>

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

@endsection