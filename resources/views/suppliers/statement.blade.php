@extends('layouts.app')

@section('title', 'Supplier Statement')

@section('content')

<div class="container-fluid">
@include('partials.alerts')
    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h3 class="mb-0">
                {{ $supplier->name }} - Statement
            </h3>

            <small class="text-muted">
                {{ $supplier->supplier_type }}
            </small>
        </div>


        {{-- PAYMENT BUTTON --}}

        <div>

            @if($supplier->supplier_type == 'Trading Goods')

            <button
                class="btn btn-primary"
                data-toggle="modal"
                data-target="#payModal">

                <i class="fas fa-plus"></i>
                Purchase Payment

            </button>

            @elseif($supplier->supplier_type == 'Asset Providers')

            <button
                class="btn btn-primary"
                data-toggle="modal"
                data-target="#payAssetModal">

                <i class="fas fa-plus"></i>
                Fixed Asset Payment

            </button>

            @else

            <button
                class="btn btn-primary"
                data-toggle="modal"
                data-target="#payInventoryModal">

                <i class="fas fa-plus"></i>
                Inventory Payment

            </button>

            @endif

        </div>

    </div>


    {{-- =========================================================
         DATE FILTER
    ========================================================== --}}

    <div class="card shadow-sm border-0 mb-3">

        <div class="card-body py-2">

            <form
                method="GET"
                action="{{ route('suppliers.statement', $supplier->id) }}">

                <div class="form-row align-items-end">

                    {{-- FROM DATE --}}

                    <div class="col-md-3">

                        <label class="mb-1">
                            From Date
                        </label>

                        <input
                            type="date"
                            name="from_date"
                            class="form-control"
                            value="{{ $fromDate }}">

                    </div>


                    {{-- TO DATE --}}

                    <div class="col-md-3">

                        <label class="mb-1">
                            To Date
                        </label>

                        <input
                            type="date"
                            name="to_date"
                            class="form-control"
                            value="{{ $toDate }}">

                    </div>


                    {{-- FILTER --}}

                    <div class="col-md-2">

                        <button
                            type="submit"
                            class="btn btn-primary btn-block">

                            <i class="fas fa-filter"></i>
                            Filter

                        </button>

                    </div>


                    {{-- LAST 7 DAYS --}}

                    <div class="col-md-2">

                        <a
                            href="{{ route('suppliers.statement', $supplier->id) }}"
                            class="btn btn-secondary btn-block">

                            Last 7 Days

                        </a>

                    </div>


                    {{-- ALL --}}

                    <div class="col-md-2">

                        <a
                            href="{{ route('suppliers.statement', [
                                'id' => $supplier->id,
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


    {{-- =========================================================
         OPENING BALANCE
    ========================================================== --}}

    @if(!$all)

    <div class="card shadow-sm border-0 mb-3">

        <div class="card-body py-2">

            <div class="d-flex justify-content-between align-items-center">

                <strong>
                    Opening Balance
                </strong>

                <strong
                    class="{{ $openingBalance >= 0 ? 'text-danger' : 'text-success' }}">

                    {{ number_format($openingBalance, 2) }}

                </strong>

            </div>

        </div>

    </div>

    @endif


    {{-- =========================================================
         SUMMARY
    ========================================================== --}}

    <div class="row mb-4">

        {{-- DEBIT --}}

        <div class="col-md-4">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6 class="text-muted">

                        {{ $all ? 'Total Debits' : 'Period Debits' }}

                    </h6>

                    <h4 class="text-dark">

                        {{ number_format($periodPurchase, 2) }}

                    </h4>

                </div>

            </div>

        </div>


        {{-- CREDIT --}}

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


        {{-- OUTSTANDING --}}

        <div class="col-md-4">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6 class="text-muted">
                        Current Outstanding
                    </h6>

                    <h4
                        class="{{ $balance > 0 ? 'text-danger' : 'text-success' }}">

                        {{ number_format($balance, 2) }}

                    </h4>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         STATEMENT
    ========================================================== --}}

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">

            <div class="d-flex justify-content-between align-items-center">

                <strong>
                    Supplier Transaction Statement
                </strong>


                @if(!$all)

                <span class="text-muted small">

                    {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}

                    -

                    {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}

                </span>

                @else

                <span class="badge badge-info">

                    All Transactions

                </span>

                @endif

            </div>

        </div>


        <div class="card-body p-0">


            {{-- =================================================
                 ERRORS
            ================================================== --}}

            @if($errors->any())

            <div
                class="alert alert-danger alert-dismissible fade show m-3"
                role="alert">

                <strong>
                    Please correct the following errors:
                </strong>

                <ul class="mb-0 mt-2">

                    @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                    @endforeach

                </ul>


                <button
                    type="button"
                    class="close"
                    data-dismiss="alert">

                    <span>
                        &times;
                    </span>

                </button>

            </div>

            @endif


            {{-- =================================================
                 TABLE
            ================================================== --}}

            <div class="table-responsive">

                <table
                    class="table table-bordered table-sm mb-0 table-erp">

                    <thead class="thead-light">

                        <tr>

                            <th>
                                Date
                            </th>

                            <th>
                                Module
                            </th>

                            <th>
                                Reference
                            </th>

                            <th>
                                Type
                            </th>

                            <th>
                                Description
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


                        {{-- =================================================
                             OPENING BALANCE
                        ================================================== --}}

                        @if(!$all)

                        <tr class="bg-light font-weight-bold">

                            <td>
                                {{ $fromDate }}
                            </td>

                            <td colspan="6">
                                Opening Balance
                            </td>

                            <td class="text-right">

                                {{ number_format(
                                        $openingBalance,
                                        2
                                    ) }}

                            </td>

                        </tr>

                        @endif


                        {{-- =================================================
                             TRANSACTIONS
                        ================================================== --}}

                        @forelse($ledger as $row)

                        <tr>


                            {{-- DATE --}}

                            <td>

                                {{ \Carbon\Carbon::parse(
                                        $row['date']
                                    )->format('d M Y') }}

                            </td>


                            {{-- MODULE --}}

                            <td>

                                {{ $row['module'] }}

                            </td>


                            {{-- REFERENCE --}}

                            <td>

                                @if(!empty($row['reference']))

                                @if($row['type'] === 'Amendment')

                                <span
                                    class="text-warning font-weight-bold">

                                    {{ $row['reference'] }}

                                </span>

                                @else

                                {{ $row['reference'] }}

                                @endif

                                @else

                                -

                                @endif

                            </td>


                            {{-- TYPE --}}

                            <td>

                                @if($row['type'] == 'Invoice')

                                <span
                                    class="badge badge-danger">

                                    Invoice

                                </span>


                                @elseif($row['type'] == 'Payment')

                                <span
                                    class="badge badge-success">

                                    Payment

                                </span>


                                @elseif($row['type'] == 'Amendment')

                                <span
                                    class="badge badge-warning">

                                    Amendment

                                </span>


                                @else

                                <span
                                    class="badge badge-secondary">

                                    {{ $row['type'] }}

                                </span>

                                @endif

                            </td>


                            {{-- DESCRIPTION --}}

                            <td>

                                {{ $row['description'] ?? '-' }}

                            </td>


                            {{-- DEBIT --}}

                            <td
                                class="text-right text-danger">

                                @if(($row['debit'] ?? 0) > 0)

                                {{ number_format(
                                            $row['debit'],
                                            2
                                        ) }}

                                @else

                                -

                                @endif

                            </td>


                            {{-- CREDIT --}}

                            <td
                                class="text-right text-success">

                                @if(($row['credit'] ?? 0) > 0)

                                {{ number_format(
                                            $row['credit'],
                                            2
                                        ) }}

                                @else

                                -

                                @endif

                            </td>


                            {{-- BALANCE --}}

                            <td
                                class="text-right font-weight-bold">

                                {{ number_format(
                                        $row['balance'],
                                        2
                                    ) }}

                            </td>

                        </tr>


                        @empty

                        <tr>

                            <td
                                colspan="8"
                                class="text-center text-muted py-4">

                                No transactions found

                            </td>

                        </tr>

                        @endforelse


                    </tbody>


                    {{-- =================================================
                         TOTAL
                    ================================================== --}}

                    @if($ledger->count())

                    <tfoot class="font-weight-bold bg-light">

                        <tr>

                            <td
                                colspan="5"
                                class="text-right">

                                Total

                            </td>


                            <td
                                class="text-right text-danger">

                                {{ number_format(
                                        $periodPurchase,
                                        2
                                    ) }}

                            </td>


                            <td
                                class="text-right text-success">

                                {{ number_format(
                                        $periodPaid,
                                        2
                                    ) }}

                            </td>


                            <td class="text-right">

                                {{ number_format(
                                        $ledger->last()['balance'],
                                        2
                                    ) }}

                            </td>

                        </tr>

                    </tfoot>

                    @endif

                </table>

            </div>

        </div>

    </div>

</div>


{{-- =============================================================
     PURCHASE PAYMENT MODAL
============================================================== --}}

<div
    class="modal fade"
    id="payModal"
    tabindex="-1"
    role="dialog">

    <div
        class="modal-dialog"
        role="document">

        <div class="modal-content">

            <form
                method="POST"
                action="{{ route(
                    'suppliers.payment.store',
                    $supplier->id
                ) }}">

                @csrf


                <div class="modal-header">

                    <h5 class="modal-title">
                        Add Supplier Payment
                    </h5>

                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal">

                        <span>
                            &times;
                        </span>

                    </button>

                </div>


                <div class="modal-body">


                    {{-- AMOUNT --}}

                    <div class="form-group">

                        <label>
                            Amount
                        </label>

                         <input type="text"
                            name="request_token"
                            value="{{ \Illuminate\Support\Str::uuid() }}">

                        <input
                            type="number"
                            name="amount"
                            class="form-control"
                            step="0.01"
                            min="0"
                            required>

                    </div>


                    {{-- DATE --}}

                    <div class="form-group">

                        <label>
                            Date
                        </label>

                        <input
                            type="date"
                            name="payment_date"
                            class="form-control"
                            value="{{ date('Y-m-d') }}"
                            required>

                    </div>


                    {{-- PAYMENT ACCOUNT --}}

                    <div class="form-group">

                        <label>
                            Payment Account
                        </label>

                        <select
                            name="sub_ledger_id"
                            class="form-control"
                            required>

                            <option value="">
                                Select Cash / Bank Account
                            </option>

                            @foreach($paymentSubLedgers as $subLedger)

                            <option
                                value="{{ $subLedger->id }}">

                                {{ $subLedger->name }}

                            </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- NOTE --}}

                    <div class="form-group">

                        <label>
                            Note
                        </label>

                        <textarea
                            name="note"
                            class="form-control"
                            rows="3"></textarea>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal">

                        Close

                    </button>


                    <button
                        type="submit"
                        class="btn btn-primary">

                        Save Payment

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>



{{-- =============================================================
     INVENTORY PAYMENT MODAL
============================================================== --}}

<div
    class="modal fade"
    id="payInventoryModal"
    tabindex="-1"
    role="dialog">

    <div
        class="modal-dialog"
        role="document">

        <div class="modal-content">

            <form
                method="POST"
                action="{{ route(
                    'suppliers.inventory.payment.store',
                    $supplier->id
                ) }}">

                @csrf


                <div class="modal-header">

                    <h5 class="modal-title">
                        Add Supplier Payment
                    </h5>

                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal">

                        <span>
                            &times;
                        </span>

                    </button>

                </div>


                <div class="modal-body">


                    <div class="form-group">

                        <label>
                            Amount
                        </label>

                         <input type="text"
                            name="request_token"
                            value="{{ \Illuminate\Support\Str::uuid() }}">

                        <input
                            type="number"
                            name="amount"
                            class="form-control"
                            step="0.01"
                            min="0"
                            required>

                    </div>


                    <div class="form-group">

                        <label>
                            Date
                        </label>

                        <input
                            type="date"
                            name="payment_date"
                            class="form-control"
                            value="{{ date('Y-m-d') }}"
                            required>

                    </div>


                    <div class="form-group">

                        <label>
                            Payment Account
                        </label>

                        <select
                            name="sub_ledger_id"
                            class="form-control"
                            required>

                            <option value="">
                                Select Cash / Bank Account
                            </option>

                            @foreach($paymentSubLedgers as $subLedger)

                            <option
                                value="{{ $subLedger->id }}">

                                {{ $subLedger->name }}

                            </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="form-group">

                        <label>
                            Note
                        </label>

                        <textarea
                            name="note"
                            class="form-control"
                            rows="3"></textarea>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal">

                        Close

                    </button>


                    <button
                        type="submit"
                        class="btn btn-primary">

                        Save Payment

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>



{{-- =============================================================
     FIXED ASSET PAYMENT MODAL
============================================================== --}}

<div
    class="modal fade"
    id="payAssetModal"
    tabindex="-1"
    role="dialog">

    <div
        class="modal-dialog"
        role="document">

        <div class="modal-content">

            <form
                method="POST"
                action="{{ route(
                    'suppliers.asset.payment.store',
                    $supplier->id
                ) }}">

                @csrf


                <div class="modal-header">

                    <h5 class="modal-title">
                        Add Supplier Payment
                    </h5>

                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal">

                        <span>
                            &times;
                        </span>

                    </button>

                </div>


                <div class="modal-body">


                    <div class="form-group">

                        <label>
                            Amount
                        </label>

                        <input type="text"
                            name="request_token"
                            value="{{ \Illuminate\Support\Str::uuid() }}">

                        <input
                            type="number"
                            name="amount"
                            class="form-control"
                            step="0.01"
                            min="0"
                            required>

                    </div>


                    <div class="form-group">

                        <label>
                            Date
                        </label>

                        <input
                            type="date"
                            name="payment_date"
                            class="form-control"
                            value="{{ date('Y-m-d') }}"
                            required>

                    </div>


                    <div class="form-group">

                        <label>
                            Payment Account
                        </label>

                        <select
                            name="sub_ledger_id"
                            class="form-control"
                            required>

                            <option value="">
                                Select Cash / Bank Account
                            </option>

                            @foreach($paymentSubLedgers as $subLedger)

                            <option
                                value="{{ $subLedger->id }}">

                                {{ $subLedger->name }}

                            </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="form-group">

                        <label>
                            Note
                        </label>

                        <textarea
                            name="note"
                            class="form-control"
                            rows="3"></textarea>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal">

                        Close

                    </button>


                    <button
                        type="submit"
                        class="btn btn-primary">

                        Save Payment

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


@endsection