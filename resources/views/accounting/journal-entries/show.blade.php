@extends('layouts.app')

@section('title', 'Journal Entry')

@section('content')

<div class="container-fluid">

    <div class="card card-dark card-outline">

        {{-- HEADER --}}
        <div class="card-header py-2">
            <h3 class="card-title mb-0">
                <i class="fas fa-book mr-1"></i>
                Journal Entry #{{ $journalEntry->id }}
            </h3>

            <div class="card-tools">
                <a href="{{ url()->previous() }}"
                   class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Back
                </a>
            </div>
        </div>

        <div class="card-body p-2">

            {{-- JOURNAL INFORMATION --}}
            <div class="row mb-2">

                <div class="col-md-2">
                    <small class="text-muted d-block">Journal No</small>
                    <strong>#{{ $journalEntry->id }}</strong>
                </div>

                <div class="col-md-2">
                    <small class="text-muted d-block">Date</small>
                    <strong>
                        {{ \Carbon\Carbon::parse($journalEntry->journal_date)->format('Y-m-d') }}
                    </strong>
                </div>

                <div class="col-md-3">
                    <small class="text-muted d-block">Branch</small>
                    <strong>
                        {{ $journalEntry->branch->name ?? '-' }}
                    </strong>
                </div>

                <div class="col-md-5">
                    <small class="text-muted d-block">Description</small>
                    <strong>
                        {{ $journalEntry->description ?: '-' }}
                    </strong>
                </div>

            </div>

            {{-- JOURNAL DETAILS --}}
            <div class="table-responsive">

                <table class="table table-bordered table-sm mb-0">

                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 5%;" class="text-center">
                                #
                            </th>

                            <th style="width: 25%;">
                                Ledger
                            </th>

                            <th style="width: 30%;">
                                Sub Ledger
                            </th>

                            <th style="width: 20%;" class="text-right">
                                Debit
                            </th>

                            <th style="width: 20%;" class="text-right">
                                Credit
                            </th>
                        </tr>
                    </thead>

                    <tbody>

                        @php
                            $totalDebit = 0;
                            $totalCredit = 0;
                        @endphp

                        @foreach($journalEntry->details as $index => $detail)

                            @php
                                $totalDebit += $detail->debit;
                                $totalCredit += $detail->credit;
                            @endphp

                            <tr>

                                <td class="text-center">
                                    {{ $index + 1 }}
                                </td>

                                <td>
                                    {{ $detail->ledger->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $detail->subLedger->name ?? '-' }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($detail->debit, 2) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($detail->credit, 2) }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                    <tfoot class="font-weight-bold bg-light">

                        <tr>

                            <td colspan="3" class="text-right">
                                Total
                            </td>

                            <td class="text-right">
                                {{ number_format($totalDebit, 2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($totalCredit, 2) }}
                            </td>

                        </tr>

                        <tr>

                            <td colspan="3" class="text-right">
                                Difference
                            </td>

                            <td colspan="2"
                                class="text-right
                                {{ abs($totalDebit - $totalCredit) < 0.01
                                    ? 'text-success'
                                    : 'text-danger' }}">

                                {{ number_format(abs($totalDebit - $totalCredit), 2) }}

                            </td>

                        </tr>

                    </tfoot>

                </table>

            </div>

            {{-- CREATED INFORMATION --}}
            <div class="mt-2 text-muted small">

                <span class="mr-3">
                    <i class="fas fa-user mr-1"></i>
                    Created by:
                    <strong>
                        {{ $journalEntry->creator->name ?? '-' }}
                    </strong>
                </span>

                <span>
                    <i class="fas fa-clock mr-1"></i>
                    Created:
                    <strong>
                        {{ $journalEntry->created_at?->format('Y-m-d H:i') ?? '-' }}
                    </strong>
                </span>

            </div>

        </div>

    </div>

</div>

@endsection