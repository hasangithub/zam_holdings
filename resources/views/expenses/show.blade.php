@extends('layouts.app')

@section('title', 'Expense Details')

@section('content')
<div class="container-fluid">

    <div class="card card-primary card-outline">

        <div class="card-header py-2">
            <h3 class="card-title">
                <i class="fas fa-file-invoice-dollar"></i>
                Expense #{{ $expense->id }}
            </h3>

            <div class="card-tools">
                <a href="{{ route('expenses.index') }}"
                   class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="card-body p-2">

            <div class="row">

                <div class="col-md-6">

                    <table class="table table-sm table-bordered mb-2">

                        <tr>
                            <th width="40%">Expense No</th>
                            <td>#{{ $expense->id }}</td>
                        </tr>

                        <tr>
                            <th>Expense Date</th>
                            <td>
                                {{ \Carbon\Carbon::parse($expense->expense_date)->format('d-m-Y') }}
                            </td>
                        </tr>

                        <tr>
                            <th>Category</th>
                            <td>
                                {{ $expense->expenseCategory->name ?? '-' }}
                            </td>
                        </tr>

                        <tr>
                            <th>Amount</th>
                            <td>
                                <strong>
                                    {{ number_format($expense->total_amount, 2) }}
                                </strong>
                            </td>
                        </tr>

                        <tr>
                            <th>Payment Status</th>
                            <td>
                                @if($expense->is_paid)
                                    <span class="badge badge-success">
                                        Paid
                                    </span>
                                @else
                                    <span class="badge badge-warning">
                                        Unpaid
                                    </span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th>Status</th>
                            <td>
                                @if($expense->status === 'cancelled')
                                    <span class="badge badge-danger">
                                        Cancelled
                                    </span>
                                @else
                                    <span class="badge badge-success">
                                        Active
                                    </span>
                                @endif
                            </td>
                        </tr>

                    </table>

                </div>

                <div class="col-md-6">

                    <table class="table table-sm table-bordered mb-2">

                        <tr>
                            <th width="40%">Payment Account</th>
                            <td>
                                @if($expense->paymentSubLedger)
                                    {{ $expense->paymentSubLedger->name }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th>Remarks</th>
                            <td>
                                {{ $expense->remarks ?: '-' }}
                            </td>
                        </tr>

                        <tr>
                            <th>Created At</th>
                            <td>
                                {{ $expense->created_at->format('d-m-Y H:i') }}
                            </td>
                        </tr>

                        <tr>
                            <th>Updated At</th>
                            <td>
                                {{ $expense->updated_at->format('d-m-Y H:i') }}
                            </td>
                        </tr>

                    </table>

                </div>

            </div>

            @if(!$expense->is_paid && $expense->status !== 'cancelled')

                <div class="card card-warning card-outline mb-0">

                    <div class="card-header py-2">
                        <h3 class="card-title">
                            <i class="fas fa-money-bill-wave"></i>
                            Make Payment
                        </h3>
                    </div>

                    <div class="card-body p-2">

                        <form action="{{ route('expenses.mark-paid', $expense->id) }}"
                              method="POST">

                            @csrf
                            @method('PATCH')

                            <div class="form-row align-items-end">

                                <div class="col-md-5">
                                    <label class="mb-1">
                                        Payment Account
                                    </label>

                                    <select name="payment_sub_ledger_id"
                                            class="form-control form-control-sm"
                                            required>

                                        <option value="">
                                            Select Cash / Bank Account
                                        </option>

                                        @foreach($paymentSubLedgers as $subLedger)
                                            <option value="{{ $subLedger->id }}">
                                                {{ $subLedger->name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="mb-1">
                                        Amount
                                    </label>

                                    <input type="text"
                                           class="form-control form-control-sm"
                                           value="{{ number_format($expense->total_amount, 2) }}"
                                           readonly>
                                </div>

                                <div class="col-md-2">
                                    <button type="submit"
                                            class="btn btn-sm btn-success btn-block"
                                            onclick="return confirm('Mark this expense as paid?')">
                                        <i class="fas fa-check"></i>
                                        Mark Paid
                                    </button>
                                </div>

                            </div>

                        </form>

                    </div>

                </div>

            @elseif($expense->is_paid)

                <div class="alert alert-success py-2 mb-0">
                    <i class="fas fa-check-circle"></i>
                    This expense has been paid.
                </div>

            @elseif($expense->status === 'cancelled')

                <div class="alert alert-danger py-2 mb-0">
                    <i class="fas fa-ban"></i>
                    This expense has been cancelled.
                </div>

            @endif

        </div>

    </div>

</div>
@endsection