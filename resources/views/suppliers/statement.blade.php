@extends('layouts.app')

@section('title','Supplier Statement')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">{{ $supplier->name }} - Statement</h3>

        <button class="btn btn-primary" data-toggle="modal" data-target="#payModal">
            + Purchase Payment
        </button>
        <button class="btn btn-primary" data-toggle="modal" data-target="#payInventoryModal">
            + Inventory Payment
        </button>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="row mb-4">

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total Purchases</h6>
                    <h4 class="text-dark">
                        {{ number_format($totalPurchase,2) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total Paid</h6>
                    <h4 class="text-success">
                        {{ number_format($totalPaid,2) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Outstanding</h6>
                    <h4 class="text-danger">
                        {{ number_format($balance,2) }}
                    </h4>
                </div>
            </div>
        </div>

    </div>

    {{-- LEDGER TABLE --}}
    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">
            <strong>Supplier Transaction Statement</strong>
        </div>

        <div class="card-body p-0">

            @if($errors->any())

            <div class="alert alert-danger alert-dismissible fade show" role="alert">

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

                <button type="button"
                    class="close"
                    data-dismiss="alert"
                    aria-label="Close">

                    <span aria-hidden="true">&times;</span>

                </button>

            </div>

            @endif

            <div class="table-responsive">

                <table class="table table-bordered mb-0 table-sm">

                    <thead class="thead-light">
                        <tr>
                            <th>Date</th>
                            <th>Module</th>
                            <th>Type</th>
                            <th class="text-right">Debit</th>
                            <th class="text-right">Credit</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($ledger as $row)

                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['module'] }}</td>
                            <td>
                                @if($row['type'] == 'Purchase')
                                <span class="badge badge-danger">Purchase</span>
                                @else
                                <span class="badge badge-success">Payment</span>
                                @endif
                            </td>

                            <td class="text-right text-danger">
                                {{ number_format($row['debit'],2) }}
                            </td>

                            <td class="text-right text-success">
                                {{ number_format($row['credit'],2) }}
                            </td>
                        </tr>

                        @empty

                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No transactions found
                            </td>
                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

{{-- PAYMENT MODAL --}}
<div class="modal fade" id="payModal">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="POST" action="{{ route('suppliers.payment.store', $supplier->id) }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Add Supplier Payment</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" name="amount" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="payment_date" class="form-control"
                            value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="form-group">
                        <label>Method</label>
                        <select name="method" class="form-control">
                            <option>Cash</option>
                            <option>Bank</option>
                            <option>Cheque</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Note</label>
                        <textarea name="note" class="form-control"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Save Payment
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>

<div class="modal fade" id="payInventoryModal">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="POST" action="{{ route('suppliers.inventory.payment.store', $supplier->id) }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Add Supplier Payment</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" name="amount" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="payment_date" class="form-control"
                            value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="form-group">
                        <label>Method</label>
                        <select name="method" class="form-control">
                            <option>Cash</option>
                            <option>Bank</option>
                            <option>Cheque</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Note</label>
                        <textarea name="note" class="form-control"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Save Payment
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>

@endsection