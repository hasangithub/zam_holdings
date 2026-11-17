@extends('layouts.app')

@section('title', 'Freight Service Statement')

@section('content')

<div class="container-fluid">

    @include('partials.alerts')

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h3 class="mb-0">
                {{ $service->name }} - Statement
            </h3>

            <small class="text-muted">
                Freight Service ID: {{ $service->id }}
            </small>
        </div>

        <div>
            <a href="{{ route('freight-services.index') }}"
                class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

    </div>

    {{-- ERRORS --}}
    @if($errors->any())
    <div class="alert alert-danger">
        <strong>Please correct the following errors:</strong>

        <ul class="mb-0 mt-2">
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
                action="{{ route('freight-services.statement', $service->id) }}">

                <div class="form-row align-items-end">

                    <div class="col-md-3">
                        <label class="mb-1">From Date</label>

                        <input type="date"
                            name="from_date"
                            class="form-control"
                            value="{{ $fromDate }}">
                    </div>

                    <div class="col-md-3">
                        <label class="mb-1">To Date</label>

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
                        <a href="{{ route('freight-services.statement', $service->id) }}"
                            class="btn btn-secondary btn-block">
                            Last 7 Days
                        </a>
                    </div>

                    <div class="col-md-2">
                        <a href="{{ route('freight-services.statement', [
                            'id' => $service->id,
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

        <div class="card-body py-2 d-flex justify-content-between">

            <strong>Opening Balance</strong>

            <strong class="{{ $openingBalance > 0 ? 'text-danger' : 'text-success' }}">
                {{ number_format($openingBalance, 2) }}
            </strong>

        </div>

    </div>

    @endif

    {{-- SUMMARY --}}
    <div class="row mb-4">

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <h6 class="text-muted">
                        {{ $all ? 'Total Freight' : 'Period Freight' }}
                    </h6>

                    <h4 class="text-dark">
                        {{ number_format($periodPurchase, 2) }}
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

                    <h6 class="text-muted">Outstanding Balance</h6>

                    <h4 class="{{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($balance, 2) }}
                    </h4>

                </div>
            </div>
        </div>

    </div>

    {{-- FREIGHT SERVICE DETAILS --}}
    <div class="card shadow-sm border-0 mb-3">

        <div class="card-body">

            <div class="row">

                <div class="col-md-4">
                    <strong>Freight Service:</strong>
                    {{ $service->name }}
                </div>

                <div class="col-md-4">
                    <strong>Total Freight Records:</strong>
                    {{ $freights->count() }}
                </div>

                <div class="col-md-4">
                    <strong>Outstanding:</strong>
                    {{ number_format($balance, 2) }}
                </div>

            </div>

        </div>

    </div>

    {{-- STATEMENT TABLE --}}
    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">

            <div class="d-flex justify-content-between align-items-center">

                <strong>Freight Service Transaction Statement</strong>

                @if(!$all)
                <span class="text-muted small">
                    {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}
                    -
                    {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}
                </span>
                @else
                <span class="badge badge-info">All Transactions</span>
                @endif

            </div>

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered table-sm mb-0 table-erp">

                    <thead class="thead-light">

                        <tr>
                            <th>Date</th>
                            <th>Module</th>
                            <th>Reference</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-right">Debit</th>
                            <th class="text-right">Credit</th>
                            <th class="text-right">Balance</th>
                            <th class="text-center" width="80">Action</th>
                        </tr>

                    </thead>

                    <tbody>

                        @if(!$all)

                        <tr class="bg-light font-weight-bold">

                            <td>
                                {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}
                            </td>

                            <td colspan="6">Opening Balance</td>

                            <td class="text-right">
                                {{ number_format($openingBalance, 2) }}
                            </td>

                        </tr>

                        @endif

                        @forelse($ledger as $row)

                        <tr>

                            <td>
                                {{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}
                            </td>

                            <td>{{ $row['module'] }}</td>

                            <td>{{ $row['reference'] }}</td>

                            <td>

                                @if($row['type'] === 'Invoice')
                                <span class="badge badge-danger">Invoice</span>

                                @elseif($row['type'] === 'Payment')
                                <span class="badge badge-success">Payment</span>

                                @elseif($row['type'] === 'Opening Balance')
                                <span class="badge badge-warning">Opening Balance</span>

                                @endif

                            </td>

                            <td>{{ $row['description'] }}</td>

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

                            <td class="text-center">

                                @if($row['type'] === 'Payment' && !empty($row['payment_id']))

                                <button type="button"
                                    class="btn btn-xs btn-primary edit-freight-payment"
                                    data-id="{{ $row['payment_id'] }}"
                                    data-amount="{{ $row['credit'] }}"
                                    data-date="{{ $row['date'] }}"
                                    title="Edit Payment">

                                    <i class="fas fa-edit"></i>

                                </button>

                                @endif

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

                    @if($ledger->count())

                    <tfoot class="font-weight-bold bg-light">

                        <tr>

                            <td colspan="5" class="text-right">
                                Total
                            </td>

                            <td class="text-right text-danger">
                                {{ number_format($periodPurchase, 2) }}
                            </td>

                            <td class="text-right text-success">
                                {{ number_format($periodPaid, 2) }}
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

{{-- EDIT FREIGHT PAYMENT MODAL --}}
<div class="modal fade" id="editFreightPaymentModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <form method="POST"
            id="editFreightPaymentForm"
            action="">

            @csrf
            @method('PUT')

            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Freight Payment</h5>

                    <button type="button"
                        class="close"
                        data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Payment Date</label>

                        <input type="date"
                            name="payment_date"
                            id="edit_payment_date"
                            class="form-control"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Amount (LKR)</label>

                        <input type="number"
                            name="amount"
                            id="edit_payment_amount"
                            class="form-control"
                            step="0.01"
                            min="0.01"
                            required>
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                        class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Payment
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    $('.edit-freight-payment').on('click', function () {

        let paymentId = $(this).data('id');
        let amount = $(this).data('amount');
        let date = $(this).data('date');

        let url = "{{ route('freight-services.payment.update', [
            'serviceId' => $service->id,
            'paymentId' => '__PAYMENT_ID__'
        ]) }}";

        url = url.replace('__PAYMENT_ID__', paymentId);

        $('#editFreightPaymentForm').attr('action', url);

        $('#edit_payment_amount').val(amount);
        $('#edit_payment_date').val(date);

        $('#editFreightPaymentModal').modal('show');
    });

});
</script>
@endpush