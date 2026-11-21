
@extends('layouts.app')

@section('title', 'Freights')

@section('content')

<div class="card card-outline card-primary">
    <div class="card-header py-2">
        <h3 class="card-title">Freights</h3>

        <div class="card-tools">
            <a href="{{ route('freights.create') }}"
               class="btn btn-primary btn-xs">
                <i class="fas fa-plus"></i> New
            </a>
        </div>
    </div>

    <div class="card-body p-2">

        <form method="GET" class="mb-2">
            <div class="row">
                <div class="col-md-3">
                    <select name="freight_service_id"
                            class="form-control form-control-sm"
                            onchange="this.form.submit()">
                        <option value="">All Freight Services</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}"
                                {{ request('freight_service_id') == $service->id ? 'selected' : '' }}>
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <div class="mb-2">
            <button type="button"
                    class="btn btn-success btn-sm"
                    id="paySelectedBtn"
                    disabled>
                <i class="fas fa-money-bill-wave"></i>
                Pay Selected (<span id="selectedCount">0</span>)
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover table-erp mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="30" class="text-center">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th>Invoice</th>
                        <th>Date</th>
                        <th>Airway No</th>
                        <th>Service</th>
                        <th class="text-right">USD</th>
                        <th class="text-right">LKR</th>
                        <th class="text-right">Paid</th>
                        <th class="text-right">Balance</th>
                        <th>Status</th>
                        <th width="85" class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($freights as $freight)
                        <tr>
                            <td class="text-center">
                                @if($freight->status == 'active' && $freight->balance_amount > 0)
                                    <input type="checkbox"
                                           class="freight-checkbox"
                                           value="{{ $freight->id }}"
                                           data-balance="{{ $freight->balance_amount }}">
                                @endif
                            </td>

                            <td>{{ $freight->invoice_id }}</td>
                            <td>{{ \Carbon\Carbon::parse($freight->date)->format('Y-m-d') }}</td>
                            <td>{{ $freight->sale?->airway_no ?? '-' }}</td>
                            <td>{{ $freight->service?->name ?? '-' }}</td>

                            <td class="text-right">
                                {{ number_format($freight->amount_usd, 2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($freight->amount_lkr, 2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($freight->total_paid, 2) }}
                            </td>

                            <td class="text-right font-weight-bold">
                                {{ number_format($freight->balance_amount, 2) }}
                            </td>

                            <td>
                                @if($freight->status == 'active')
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Cancelled</span>
                                @endif
                            </td>

                            <td class="text-center text-nowrap">
                                <a href="{{ route('freights.show', $freight->id) }}"
                                   class="btn btn-info btn-xs"
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="{{ route('freight-expenses.statement', $freight->id) }}"
                                   class="btn btn-secondary btn-xs"
                                   title="Statement">
                                    <i class="fas fa-file-alt"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center">
                                No freight records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if($freights->count())
                    <tfoot class="font-weight-bold bg-light">
                        <tr>
                            <td colspan="5" class="text-right">Total</td>
                            <td class="text-right">{{ number_format($freights->sum('amount_usd'), 2) }}</td>
                            <td class="text-right">{{ number_format($freights->sum('amount_lkr'), 2) }}</td>
                            <td class="text-right">{{ number_format($freights->sum('total_paid'), 2) }}</td>
                            <td class="text-right">{{ number_format($freights->sum('balance_amount'), 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

{{-- BULK PAYMENT MODAL --}}
<div class="modal fade" id="bulkPaymentModal">
    <div class="modal-dialog modal-md">
        <form method="POST" action="{{ route('freights.bulk-payment') }}" id="bulkPaymentForm">
            @csrf

            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title">Pay Selected Freight Invoices</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="freight_ids" id="freightIds">

                    <div class="form-group">
                        <label>Payment Date</label>
                        <input type="date"
                               name="payment_date"
                               class="form-control form-control-sm"
                               value="{{ now()->toDateString() }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Cashbook</label>
                        <select name="sub_ledger_id"
                                class="form-control form-control-sm"
                                required>
                            <option value="">Select Cashbook</option>
                            @foreach($cashbooks as $cashbook)
                                <option value="{{ $cashbook->id }}">
                                    {{ $cashbook->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="alert alert-info py-2 mb-0">
                        Selected invoices: <strong id="modalSelectedCount">0</strong>
                        <br>
                        Each invoice will be paid for its full outstanding balance.
                    </div>
                </div>

                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-check"></i> Confirm Payment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection


@push('scripts')
<script>
$(function () {

    function updateSelection() {
        let selected = $('.freight-checkbox:checked');
        let count = selected.length;

        $('#selectedCount').text(count);
        $('#paySelectedBtn').prop('disabled', count === 0);

        let total = $('.freight-checkbox').length;

        $('#selectAll').prop(
            'checked',
            total > 0 && count === total
        );
    }

    $('#selectAll').on('change', function () {
        $('.freight-checkbox').prop('checked', this.checked);
        updateSelection();
    });

    $(document).on('change', '.freight-checkbox', function () {
        updateSelection();
    });

    $('#paySelectedBtn').on('click', function () {
        let ids = $('.freight-checkbox:checked').map(function () {
            return this.value;
        }).get();

        if (!ids.length) return;

        $('#freightIds').val(JSON.stringify(ids));
        $('#modalSelectedCount').text(ids.length);

        $('#bulkPaymentModal').modal('show');
    });

    $('#bulkPaymentForm').on('submit', function () {
        if (!$('#freightIds').val()) {
            return false;
        }

        return confirm('Pay the full outstanding balance of all selected invoices?');
    });

});
</script>
@endpush
