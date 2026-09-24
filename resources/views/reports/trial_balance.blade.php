@extends('layouts.app')

@section('title', 'Trial Balance')

@section('content')

<div class="container-fluid">

    {{-- =========================================================
         PAGE HEADER
    ========================================================== --}}

    <div class="row mb-3">

        <div class="col-md-6">

            <h4 class="mb-0">
                <i class="fas fa-balance-scale mr-2"></i>
                Trial Balance
            </h4>

            <small class="text-muted">
                Ledger and sub-ledger balances
            </small>

        </div>

        <div class="col-md-6 text-right no-print">

            <button
                type="button"
                onclick="window.print()"
                class="btn btn-dark btn-sm"
            >
                <i class="fas fa-print mr-1"></i>
                Print
            </button>

        </div>

    </div>


    {{-- =========================================================
         FILTER CARD
    ========================================================== --}}

    <div class="card card-outline card-primary no-print">

        <div class="card-header py-2">

            <h3 class="card-title">

                <i class="fas fa-filter mr-1"></i>

                Report Filters

            </h3>

        </div>


        <div class="card-body py-2">

            <form method="GET">

                <div class="row align-items-end">

                    {{-- FROM DATE --}}

                    <div class="col-md-3">

                        <div class="form-group mb-2">

                            <label class="mb-1">
                                From Date
                            </label>

                            <input
                                type="date"
                                name="from_date"
                                value="{{ $fromDate }}"
                                class="form-control form-control-sm"
                            >

                        </div>

                    </div>


                    {{-- TO DATE --}}

                    <div class="col-md-3">

                        <div class="form-group mb-2">

                            <label class="mb-1">
                                To Date
                            </label>

                            <input
                                type="date"
                                name="to_date"
                                value="{{ $toDate }}"
                                class="form-control form-control-sm"
                            >

                        </div>

                    </div>


                    {{-- GENERATE --}}

                    <div class="col-md-2">

                        <div class="form-group mb-2">

                            <button
                                type="submit"
                                class="btn btn-primary btn-sm"
                            >
                                <i class="fas fa-search mr-1"></i>
                                Generate
                            </button>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- =========================================================
         REPORT CARD
    ========================================================== --}}

    <div class="card card-outline card-primary">

        {{-- =====================================================
             REPORT HEADER
        ====================================================== --}}

        <div class="card-header">

            <div class="row">

                <div class="col-md-8">

                    <h3 class="card-title font-weight-bold">

                        TRIAL BALANCE

                    </h3>

                    <div class="text-muted small mt-1">

                        Period:

                        <strong>
                            {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}
                        </strong>

                        &nbsp; to &nbsp;

                        <strong>
                            {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}
                        </strong>

                    </div>

                </div>


                <div class="col-md-4 text-right">

                    <div class="small text-muted">

                        Generated

                    </div>

                    <div class="small font-weight-bold">

                        {{ now()->format('d M Y H:i') }}

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
             TABLE
        ====================================================== --}}

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered table-hover table-sm mb-0 trial-balance-table">

                    <thead class="thead-light">

                        <tr>

                            <th
                                class="account-column"
                            >
                                Account
                            </th>

                            <th
                                class="amount-column text-right"
                            >
                                Debit
                            </th>

                            <th
                                class="amount-column text-right"
                            >
                                Credit
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    @forelse($ledgerGroups as $ledger)

                        {{-- =================================================
                             LEDGER
                        ================================================== --}}

                        <tr class="ledger-row">

                            <td>

                                <span class="ledger-code">
                                    {{ $ledger['code'] ?? $ledger['id'] }}
                                </span>

                                <span class="ledger-name">
                                    {{ $ledger['name'] }}
                                </span>

                            </td>


                            <td class="text-right">

                                @if(($ledger['debit'] ?? 0) != 0)

                                    {{ number_format($ledger['debit'], 2) }}

                                @else

                                    <span class="text-muted">-</span>

                                @endif

                            </td>


                            <td class="text-right">

                                @if(($ledger['credit'] ?? 0) != 0)

                                    {{ number_format($ledger['credit'], 2) }}

                                @else

                                    <span class="text-muted">-</span>

                                @endif

                            </td>

                        </tr>


                        {{-- =================================================
                             SUB LEDGERS
                        ================================================== --}}

                        @if(!empty($ledger['sub_ledgers']))

                            @foreach($ledger['sub_ledgers'] as $subLedger)

                                <tr class="subledger-row">

                                    <td class="subledger-name">

                                        <span class="subledger-indent">
                                            <i class="fas fa-level-up-alt fa-rotate-90 mr-2 text-muted"></i>
                                        </span>

                                        <span class="subledger-code">

                                            {{ $subLedger['code'] ?? $subLedger['id'] }}

                                        </span>

                                        {{ $subLedger['name'] }}

                                    </td>


                                    <td class="text-right">

                                        @if(($subLedger['debit'] ?? 0) != 0)

                                            {{ number_format(
                                                $subLedger['debit'],
                                                2
                                            ) }}

                                        @else

                                            <span class="text-muted">-</span>

                                        @endif

                                    </td>


                                    <td class="text-right">

                                        @if(($subLedger['credit'] ?? 0) != 0)

                                            {{ number_format(
                                                $subLedger['credit'],
                                                2
                                            ) }}

                                        @else

                                            <span class="text-muted">-</span>

                                        @endif

                                    </td>

                                </tr>

                            @endforeach


                            {{-- =================================================
                                 LEDGER TOTAL
                            ================================================== --}}

                            <tr class="ledger-total-row">

                                <td class="text-right">

                                    <span class="text-muted">

                                        {{ $ledger['name'] }} Total

                                    </span>

                                </td>

                                <td class="text-right">

                                    <strong>

                                        {{ number_format(
                                            $ledger['debit'],
                                            2
                                        ) }}

                                    </strong>

                                </td>

                                <td class="text-right">

                                    <strong>

                                        {{ number_format(
                                            $ledger['credit'],
                                            2
                                        ) }}

                                    </strong>

                                </td>

                            </tr>

                        @endif


                    @empty

                        <tr>

                            <td
                                colspan="3"
                                class="text-center text-muted py-5"
                            >

                                <i class="fas fa-info-circle fa-2x mb-2"></i>

                                <br>

                                No accounts found.

                            </td>

                        </tr>

                    @endforelse


                    {{-- =====================================================
                         GRAND TOTAL
                    ====================================================== --}}

                    <tr class="grand-total">

                        <td class="text-right">

                            GRAND TOTAL

                        </td>


                        <td class="text-right">

                            {{ number_format(
                                $totals['debit'] ?? 0,
                                2
                            ) }}

                        </td>


                        <td class="text-right">

                            {{ number_format(
                                $totals['credit'] ?? 0,
                                2
                            ) }}

                        </td>

                    </tr>


                    {{-- =====================================================
                         BALANCE CHECK
                    ====================================================== --}}

                    @php

                        $difference =
                            ($totals['debit'] ?? 0)
                            -
                            ($totals['credit'] ?? 0);

                    @endphp


                    <tr class="balance-check-row">

                        <td class="text-right">

                            <strong>
                                Difference
                            </strong>

                        </td>


                        <td
                            colspan="2"
                            class="text-right"
                        >

                            @if(abs($difference) < 0.01)

                                <span class="text-success font-weight-bold">

                                    <i class="fas fa-check-circle mr-1"></i>

                                    Balanced

                                </span>

                            @else

                                <span class="text-danger font-weight-bold">

                                    {{ number_format(
                                        abs($difference),
                                        2
                                    ) }}

                                </span>

                            @endif

                        </td>

                    </tr>

                    </tbody>

                </table>

            </div>

        </div>


        {{-- =====================================================
             CARD FOOTER
        ====================================================== --}}

        <div class="card-footer bg-white">

            <div class="row">

                <div class="col-md-6">

                    <small class="text-muted">

                        <i class="fas fa-calendar-alt mr-1"></i>

                        {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}

                        -

                        {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}

                    </small>

                </div>


                <div class="col-md-6 text-right">

                    <small class="text-muted">

                        Trial Balance

                    </small>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- =============================================================
     PAGE-SPECIFIC CSS
============================================================== --}}

<style>

    /*
    |--------------------------------------------------------------------------
    | ACCOUNT COLUMN
    |--------------------------------------------------------------------------
    */

    .account-column {
        width: 65%;
    }

    .amount-column {
        width: 17.5%;
    }


    /*
    |--------------------------------------------------------------------------
    | LEDGER
    |--------------------------------------------------------------------------
    */

    .ledger-row td {
        background-color: #f5f6f8;
        font-weight: 600;
        border-top: 1px solid #cfd4da;
    }

    .ledger-code {
        display: inline-block;
        min-width: 45px;
        color: #6c757d;
        font-size: 12px;
    }

    .ledger-name {
        color: #212529;
        font-weight: 600;
    }


    /*
    |--------------------------------------------------------------------------
    | SUB LEDGER
    |--------------------------------------------------------------------------
    */

    .subledger-row td {
        background-color: #fff;
        font-size: 12px;
    }

    .subledger-name {
        padding-left: 25px !important;
        color: #495057;
    }

    .subledger-indent {
        display: inline-block;
        width: 20px;
    }

    .subledger-code {
        color: #6c757d;
        margin-right: 7px;
        font-size: 11px;
    }


    /*
    |--------------------------------------------------------------------------
    | LEDGER TOTAL
    |--------------------------------------------------------------------------
    */

    .ledger-total-row td {
        background-color: #fafafa;
        color: #495057;
        font-size: 11px;
        border-bottom: 1px solid #dee2e6;
    }


    /*
    |--------------------------------------------------------------------------
    | GRAND TOTAL
    |--------------------------------------------------------------------------
    */

    .grand-total td {
        background-color: #343a40 !important;
        color: #fff !important;
        font-size: 13px;
        font-weight: 700;
        padding-top: 9px !important;
        padding-bottom: 9px !important;
        border-color: #343a40 !important;
    }


    /*
    |--------------------------------------------------------------------------
    | BALANCE CHECK
    |--------------------------------------------------------------------------
    */

    .balance-check-row td {
        background-color: #f8f9fa;
        padding-top: 7px !important;
        padding-bottom: 7px !important;
    }


    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    .trial-balance-table {
        font-size: 12px;
    }

    .trial-balance-table thead th {
        background-color: #e9ecef;
        border-bottom: 2px solid #adb5bd;
        font-size: 12px;
        font-weight: 600;
        padding: 8px 10px;
    }

    .trial-balance-table td {
        padding: 6px 10px;
        vertical-align: middle;
    }


    /*
    |--------------------------------------------------------------------------
    | PRINT
    |--------------------------------------------------------------------------
    */

    @media print {

        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            background: #fff !important;
        }

        .no-print {
            display: none !important;
        }

        .content-wrapper,
        .content,
        .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
        }

        .card {
            border: 0 !important;
            box-shadow: none !important;
        }

        .card-header {
            border-bottom: 1px solid #999 !important;
        }

        .table-responsive {
            overflow: visible !important;
        }

        .trial-balance-table {
            width: 100% !important;
        }

        .trial-balance-table tr {
            page-break-inside: avoid;
        }

    }

</style>

@endsection