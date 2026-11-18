@extends('layouts.app')

@section('title','Sale Invoice')

@section('content')

<div class="container">

    {{-- ALERTS --}}
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif


    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <h3>Sale Invoice #{{ $sale->invoice_id }}</h3>

        <div>
            <a href="{{ route('sales.invoice', $sale->id) }}" class="btn btn-dark">
                Print Invoice
            </a>

            <button type="button"
                class="btn btn-warning btn-sm"
                data-toggle="modal"
                data-target="#salesAmendmentModal">
                <i class="fas fa-edit"></i> Add Amendment
            </button>
        </div>

    </div>


    {{-- SALE SUMMARY --}}
    <div class="card mb-4">

        <div class="card-header">
            Sale Information
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 mb-3">
                    <strong>Customer</strong><br>
                    {{ $sale->customer->name ?? '-' }}
                </div>

                <div class="col-md-4 mb-3">
                    <strong>Invoice Date</strong><br>
                    {{ date('d M Y', strtotime($sale->sale_date ?? $sale->created_at)) }}
                </div>

                <div class="col-md-4 mb-3">
                    <strong>Currency</strong><br>
                    {{ $sale->currency }}
                </div>

                <div class="col-md-4 mb-3">
                    <strong>Exchange Rate</strong><br>
                    {{ $sale->exchange_rate }}
                </div>

                <div class="col-md-4 mb-3">
                    <strong>Total Amount</strong><br>
                    <h5 class="text-primary">
                        {{ number_format($sale->total, 2) }}
                    </h5>
                </div>

                @if($sale->currency != 'LKR')
                <div class="col-md-4 mb-3">
                    <strong>Foreign Total</strong><br>
                    <h5 class="text-info">
                        {{ number_format($sale->total_foreign ?? 0, 2) }}
                    </h5>
                </div>
                @endif

            </div>

        </div>
    </div>


    {{-- ITEMS TABLE --}}
    <div class="card">

        <div class="card-header">
            Items
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered mb-0">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Base Price</th>
                            <th>Sale Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($sale->items as $key => $item)

                        <tr>

                            <td>{{ $key + 1 }}</td>

                            <td>{{ $item->item->name ?? '-' }}</td>

                            <td>{{ $item->qty }}</td>

                            <td>{{ number_format($item->base_price, 2) }}</td>

                            <td>{{ number_format($item->sale_price, 2) }}</td>

                            <td class="text-end">
                                {{ number_format($item->subtotal, 2) }}
                            </td>

                        </tr>

                        @empty

                        <tr>
                            <td colspan="6" class="text-center">
                                No items found
                            </td>
                        </tr>

                        @endforelse

                    </tbody>

                    <tfoot>

                        <tr>
                            <th colspan="5" class="text-end">Total</th>
                            <th>{{ number_format($sale->total, 2) }}</th>
                        </tr>

                    </tfoot>

                </table>

            </div>

        </div>

    </div>

</div>

{{-- SALES AMENDMENT MODAL --}}
<div class="modal fade" id="salesAmendmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST"
              action="{{ route('customers.amendments.store', $sale->id) }}">
            @csrf

            <div class="modal-content">

                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> Sales Amendment
                    </h5>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Customer</label>
                        <input type="text"
                               class="form-control"
                               value="{{ $sale->customer->name }}"
                               readonly>
                    </div>

                    <div class="form-group">
                        <label>Amendment Date</label>
                        <input type="date"
                               name="amendment_date"
                               class="form-control"
                               value="{{ old('amendment_date', now()->format('Y-m-d')) }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Amendment Amount</label>
                        <input type="number"
                               name="amount"
                               class="form-control"
                               step="0.01"
                               placeholder="Enter positive or negative amount"
                               required>
                        <small class="text-muted">
                            Positive amount increases customer outstanding.
                            Negative amount decreases customer outstanding.
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Reason</label>
                        <input type="text"
                               name="reason"
                               class="form-control"
                               maxlength="255"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes"
                                  class="form-control"
                                  rows="3"></textarea>
                    </div>

                    <div class="alert alert-warning mb-0">
                        This amendment will update the customer statement
                        and create the corresponding accounting journal.
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal">
                        Close
                    </button>

                    <button type="submit" class="btn btn-warning">
                        Save Amendment
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection