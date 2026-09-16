@extends('layouts.app')

@section('title','Customer Statement')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <h3 class="mb-0">
            {{ $customer->name }} - Statement
        </h3>

        <button class="btn btn-primary"
                data-toggle="modal"
                data-target="#paymentModal">
            + Customer Payment
        </button>

    </div>


    {{-- DATE FILTER --}}
    <div class="card shadow-sm border-0 mb-3">

        <div class="card-body py-2">

            <form method="GET"
                  action="{{ route('customers.statement', $customer->id) }}">

                <div class="form-row align-items-end">

                    <div class="col-md-3">

                        <label class="mb-1">
                            From Date
                        </label>

                        <input type="date"
                               name="from_date"
                               class="form-control"
                               value="{{ $fromDate }}">

                    </div>


                    <div class="col-md-3">

                        <label class="mb-1">
                            To Date
                        </label>

                        <input type="date"
                               name="to_date"
                               class="form-control"
                               value="{{ $toDate }}">

                    </div>


                    <div class="col-md-2">

                        <button type="submit"
                                class="btn btn-primary btn-block">

                            <i class="fas fa-filter"></i>
                            Filter

                        </button>

                    </div>


                    <div class="col-md-2">

                        <a href="{{ route('customers.statement', $customer->id) }}"
                           class="btn btn-secondary btn-block">

                            Last 7 Days

                        </a>

                    </div>


                    <div class="col-md-2">

                        <a href="{{ route('customers.statement', [
                            'id' => $customer->id,
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


    {{-- OPENING BALANCE --}}
    @if(!$all)

        <div class="card shadow-sm border-0 mb-3">

            <div class="card-body py-2">

                <div class="d-flex justify-content-between">

                    <strong>
                        Opening Balance
                    </strong>

                    <strong class="text-danger">
                        {{ number_format($openingBalance, 2) }}
                    </strong>

                </div>

            </div>

        </div>

    @endif


    {{-- SUMMARY --}}
    <div class="row mb-4">

        <div class="col-md-4">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6 class="text-muted">
                        {{ $all ? 'Total Sales' : 'Period Sales' }}
                    </h6>

                    <h4>
                        {{ number_format($periodSales, 2) }}
                    </h4>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6 class="text-muted">
                        {{ $all ? 'Total Paid' : 'Period Paid' }}
                    </h6>

                    <h4 class="text-success">
                        {{ number_format($periodPaid, 2) }}
                    </h4>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6 class="text-muted">
                        Outstanding
                    </h6>

                    <h4 class="text-danger">
                        {{ number_format($balance, 2) }}
                    </h4>

                </div>

            </div>

        </div>

    </div>


    {{-- STATEMENT --}}
    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">

            <div class="d-flex justify-content-between align-items-center">

                <strong>
                    Customer Transaction Statement
                </strong>

                @if(!$all)

                    <span class="text-muted small">
                        {{ $fromDate }} to {{ $toDate }}
                    </span>

                @else

                    <span class="badge badge-info">
                        All Transactions
                    </span>

                @endif

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered table-sm mb-0 table-erp">

                    <thead class="thead-light">

                        <tr>

                            <th>
                                Date
                            </th>

                            <th>
                                Module
                            </th>

                            <th>
                                Type
                            </th>

                            <th class="text-right">
                                Debit
                            </th>

                            <th class="text-right">
                                Credit
                            </th>

                            <th class="text-right">
                                Balance
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        {{-- OPENING --}}
                        @if(!$all)

                            <tr class="bg-light font-weight-bold">

                                <td>
                                    {{ $fromDate }}
                                </td>

                                <td colspan="4">
                                    Opening Balance
                                </td>

                                <td class="text-right">
                                    {{ number_format($openingBalance, 2) }}
                                </td>

                            </tr>

                        @endif


                        {{-- TRANSACTIONS --}}
                        @forelse($ledger as $row)

                            <tr>

                                <td>
                                    {{ $row['date'] }}
                                </td>

                                <td>
                                    {{ $row['module'] }}
                                </td>

                                <td>

                                    @if($row['type'] == 'Invoice')

                                        <span class="badge badge-danger">
                                            Invoice
                                        </span>

                                    @else

                                        <span class="badge badge-success">
                                            Payment
                                        </span>

                                    @endif

                                </td>

                                <td class="text-right text-danger">

                                    @if($row['debit'] > 0)
                                        {{ number_format($row['debit'], 2) }}
                                    @else
                                        -
                                    @endif

                                </td>

                                <td class="text-right text-success">

                                    @if($row['credit'] > 0)
                                        {{ number_format($row['credit'], 2) }}
                                    @else
                                        -
                                    @endif

                                </td>

                                <td class="text-right font-weight-bold">

                                    {{ number_format($row['balance'], 2) }}

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6"
                                    class="text-center text-muted py-4">

                                    No transactions found

                                </td>

                            </tr>

                        @endforelse

                    </tbody>


                    {{-- TOTAL --}}
                    @if($ledger->isNotEmpty())

                        <tfoot class="font-weight-bold bg-light">

                            <tr>

                                <td colspan="3"
                                    class="text-right">

                                    Total

                                </td>

                                <td class="text-right text-danger">

                                    {{ number_format($periodSales, 2) }}

                                </td>

                                <td class="text-right text-success">

                                    {{ number_format($periodPaid, 2) }}

                                </td>

                                <td class="text-right">

                                    {{ number_format($ledger->last()['balance'], 2) }}

                                </td>

                            </tr>

                        </tfoot>

                    @endif

                </table>

            </div>

        </div>

    </div>

</div>


{{-- ========================================================= --}}
{{-- CUSTOMER PAYMENT MODAL --}}
{{-- ========================================================= --}}

@include('customers.payment_modal')
@endsection