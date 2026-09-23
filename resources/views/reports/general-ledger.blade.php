@extends('layouts.app')

@section('title', 'General Ledger Report')

@section('content')

<div class="container-fluid">

    {{-- FILTER --}}
    <div class="card card-primary card-outline">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-book"></i>
                General Ledger Report
            </h3>
        </div>

        <form method="POST"
              action="{{ route('reports.general-ledger.report') }}">

            @csrf

            <div class="card-body">

                <div class="row">

                    {{-- FROM DATE --}}
                    <div class="col-md-2">

                        <div class="form-group mb-0">

                            <label>From Date</label>

                            <input type="date"
                                   name="from_date"
                                   class="form-control"
                                   value="{{ isset($fromDate)
                                        ? $fromDate->format('Y-m-d')
                                        : date('Y-m-01') }}"
                                   required>

                        </div>

                    </div>


                    {{-- TO DATE --}}
                    <div class="col-md-2">

                        <div class="form-group mb-0">

                            <label>To Date</label>

                            <input type="date"
                                   name="to_date"
                                   class="form-control"
                                   value="{{ isset($toDate)
                                        ? $toDate->format('Y-m-d')
                                        : date('Y-m-d') }}"
                                   required>

                        </div>

                    </div>


                    {{-- LEDGER --}}
                    <div class="col-md-5">

                        <div class="form-group mb-0">

                            <label>Ledger</label>

                            <select name="ledger_id"
                                    id="ledger_id"
                                    class="form-control"
                                    required>

                                <option value="">
                                    Select Ledger
                                </option>

                                @foreach($ledgers as $item)

                                    <option value="{{ $item->id }}"
                                        @if(isset($ledger) && $ledger->id == $item->id)
                                            selected
                                        @endif>

                                        {{ $item->name }}

                                    </option>

                                @endforeach

                            </select>

                        </div>

                    </div>


                    {{-- GENERATE --}}
                    <div class="col-md-2">

                        <div class="form-group mb-0">

                            <label>&nbsp;</label>

                            <button type="submit"
                                    class="btn btn-primary btn-block">

                                <i class="fas fa-search"></i>
                                Generate

                            </button>

                        </div>

                    </div>


                    {{-- PRINT --}}
                    <div class="col-md-1">

                        <div class="form-group mb-0">

                            <label>&nbsp;</label>

                            <button type="button"
                                    onclick="window.print()"
                                    class="btn btn-secondary btn-block">

                                <i class="fas fa-print"></i>

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </form>

    </div>


    {{-- REPORT --}}
    @isset($groups)

        <div class="card">

            {{-- REPORT HEADER --}}
            <div class="card-header">

                <div class="row">

                    <div class="col-md-8">

                        <h3 class="card-title font-weight-bold">

                            {{ $ledger->name }}

                        </h3>

                    </div>

                    <div class="col-md-4 text-right">

                        <strong>

                            {{ $fromDate->format('d/m/Y') }}

                            -

                            {{ $toDate->format('d/m/Y') }}

                        </strong>

                    </div>

                </div>

            </div>


            <div class="card-body p-0">

                @foreach($groups as $group)

                    <div class="ledger-section">

                        {{-- SUBLEDGER TITLE --}}
                        @if($hasSubLedgers)

                            <div class="bg-light border-top border-bottom px-3 py-2">

                                <strong>

                                    {{ $group['sub_ledger']->name }}

                                </strong>

                            </div>

                        @endif


                        <div class="table-responsive">

                            <table class="table table-bordered table-sm mb-4">

                                <thead class="thead-light">

                                    <tr>

                                        <th width="90">
                                            Date
                                        </th>

                                        <th width="90">
                                            Journal
                                        </th>

                                        <th>
                                            Description
                                        </th>

                                        <th width="130"
                                            class="text-right">

                                            Debit

                                        </th>

                                        <th width="130"
                                            class="text-right">

                                            Credit

                                        </th>

                                        <th width="140"
                                            class="text-right">

                                            Balance

                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    {{-- OPENING --}}
                                    <tr class="font-weight-bold">

                                        <td colspan="3">

                                            Opening Balance

                                        </td>

                                        <td></td>

                                        <td></td>

                                        <td class="text-right">

                                            {{ number_format(
                                                $group['opening'],
                                                2
                                            ) }}

                                        </td>

                                    </tr>


                                    {{-- TRANSACTIONS --}}
                                    @forelse($group['rows'] as $row)

                                        <tr>

                                            <td>

                                                {{ \Carbon\Carbon::parse(
                                                    $row['date']
                                                )->format('d/m/Y') }}

                                            </td>

                                            <td>

                                                JE-{{ $row['journal_id'] }}

                                            </td>

                                            <td>

                                                {{ $row['description'] }}

                                            </td>

                                            <td class="text-right">

                                                @if($row['debit'] != 0)

                                                    {{ number_format(
                                                        $row['debit'],
                                                        2
                                                    ) }}

                                                @endif

                                            </td>

                                            <td class="text-right">

                                                @if($row['credit'] != 0)

                                                    {{ number_format(
                                                        $row['credit'],
                                                        2
                                                    ) }}

                                                @endif

                                            </td>

                                            <td class="text-right font-weight-bold">

                                                {{ number_format(
                                                    $row['balance'],
                                                    2
                                                ) }}

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td colspan="6"
                                                class="text-center text-muted">

                                                No transactions found.

                                            </td>

                                        </tr>

                                    @endforelse

                                </tbody>


                                <tfoot>

                                    <tr class="font-weight-bold bg-light">

                                        <td colspan="3"
                                            class="text-right">

                                            Total

                                        </td>

                                        <td class="text-right">

                                            {{ number_format(
                                                $group['total_debit'],
                                                2
                                            ) }}

                                        </td>

                                        <td class="text-right">

                                            {{ number_format(
                                                $group['total_credit'],
                                                2
                                            ) }}

                                        </td>

                                        <td class="text-right">

                                            {{ number_format(
                                                $group['closing'],
                                                2
                                            ) }}

                                        </td>

                                    </tr>

                                </tfoot>

                            </table>

                        </div>

                    </div>

                @endforeach

            </div>

        </div>

    @endisset

</div>


@push('scripts')

<script>

$(document).ready(function () {

    $('#ledger_id').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Select Ledger',
        allowClear: true
    });

});

</script>

@endpush


@push('styles')

<style>

.table th,
.table td {
    vertical-align: middle;
}

.ledger-section {
    margin-bottom: 5px;
}

@media print {

    .main-header,
    .main-sidebar,
    .main-footer,
    .content-header,
    form,
    .btn {
        display: none !important;
    }

    .content-wrapper {
        margin-left: 0 !important;
        padding: 0 !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
    }

    .card-header {
        display: block !important;
    }

    .table {
        font-size: 11px !important;
    }

}

</style>

@endpush

@endsection