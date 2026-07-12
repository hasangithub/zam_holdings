@extends('layouts.app')

@section('title','Expense Details')

@section('content')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">
            Expense #{{ $expense->id }}
        </h3>

        <div class="card-tools">
            <a href="{{ route('expenses.index') }}"
               class="btn btn-secondary btn-sm">
                Back
            </a>
        </div>
    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-3">
                <strong>Date</strong><br>
                {{ \Carbon\Carbon::parse($expense->expense_date)->format('d-m-Y') }}
            </div>

            <div class="col-md-3">
                <strong>Category</strong><br>
                {{ $expense->category->name ?? '' }}
            </div>

            <div class="col-md-3">
                <strong>Type</strong><br>

                @if($expense->category->type == 'fixed')
                    <span class="badge badge-info">Fixed</span>
                @else
                    <span class="badge badge-warning">Packaging</span>
                @endif

            </div>

            <div class="col-md-3">
                <strong>Total Amount</strong><br>
                {{ number_format($expense->total_amount,2) }}
            </div>

        </div>

        @if($expense->remarks)
            <hr>

            <strong>Remarks</strong>

            <p>
                {{ $expense->remarks }}
            </p>
        @endif

        <hr>

        <h5>Expense Details</h5>

        <table class="table table-bordered">

            <thead>
                <tr>
                    <th>Item</th>
                    <th width="120">Qty</th>
                    <th width="150">Amount</th>
                </tr>
            </thead>

            <tbody>

                @forelse($expense->details as $detail)

                    <tr>

                        <td>
                            {{ $detail->item->name ?? $detail->description }}
                        </td>

                        <td>
                            {{ number_format($detail->qty,2) }}
                        </td>

                        <td class="text-right">
                            {{ number_format($detail->amount,2) }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="3" class="text-center">
                            No Details Found
                        </td>
                    </tr>

                @endforelse

            </tbody>

            <tfoot>

                <tr>
                    <th colspan="2" class="text-right">
                        Total
                    </th>

                    <th class="text-right">
                        {{ number_format($expense->total_amount,2) }}
                    </th>
                </tr>

            </tfoot>

        </table>

    </div>

</div>

@endsection