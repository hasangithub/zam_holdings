@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
@include('partials.alerts')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">Expenses by Category</h3>

        <div class="card-tools">
            <a href="{{ route('expenses.create') }}"
               class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Expense
            </a>
        </div>
    </div>

    <div class="card-body">

        @php
            $groupedExpenses = $expenses->groupBy(function ($expense) {
                return $expense->category->name ?? 'Uncategorized';
            });
        @endphp

        @forelse($groupedExpenses as $categoryName => $categoryExpenses)

            @php
                // Cancelled expenses are excluded from the category total.
                $categoryTotal = $categoryExpenses
                    ->where('status', '!=', 'cancelled')
                    ->sum('total_amount');

                $categoryType = optional($categoryExpenses->first()->category)->type;
            @endphp

            <div class="card card-outline card-primary mb-4">

                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-folder-open mr-1"></i>
                        {{ $categoryName }}

                        @if($categoryType === 'fixed')
                            <span class="badge badge-info ml-2">Fixed</span>
                        @elseif($categoryType === 'packaging')
                            <span class="badge badge-warning ml-2">Packaging</span>
                        @endif

                        <span class="badge badge-secondary ml-1">
                            {{ $categoryExpenses->count() }} Records
                        </span>
                    </h3>

                    <div class="card-tools">
                        <strong>
                            Category Total:
                            {{ number_format($categoryTotal, 2) }}
                        </strong>
                    </div>
                </div>

                <div class="card-body table-responsive p-0">

                    <table class="table table-bordered table-striped table-erp mb-0">

                        <thead>
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th class="text-right">Total</th>
                                <th>Status</th>
                                <th style="width: 190px;">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($categoryExpenses as $expense)

                                <tr>
                                    <td>{{ $expense->id }}</td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($expense->expense_date)->format('d-m-Y') }}
                                    </td>

                                    <td>
                                        @if(optional($expense->category)->type === 'fixed')
                                            <span class="badge badge-info">Fixed</span>
                                        @else
                                            <span class="badge badge-warning">Packaging</span>
                                        @endif
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($expense->total_amount, 2) }}
                                    </td>

                                    <td>
                                        @if($expense->status === 'cancelled')
                                            <span class="badge badge-danger">
                                                Cancelled
                                            </span>
                                        @else
                                            <span class="badge badge-success">
                                                {{ ucfirst($expense->status) }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-nowrap">

                                        <a href="{{ route('expenses.show', $expense->id) }}"
                                           class="btn btn-info btn-sm"
                                           title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if($expense->status !== 'cancelled')
                                            <a href="{{ route('expenses.edit', $expense->id) }}"
                                               class="btn btn-primary btn-sm"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form action="{{ route('expenses.destroy', $expense->id) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="btn btn-danger btn-sm"
                                                        title="Cancel"
                                                        onclick="return confirm('Cancel this expense?')">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            </form>
                                        @endif

                                    </td>
                                </tr>

                            @endforeach
                        </tbody>

                    </table>

                </div>
            </div>

        @empty

            <div class="alert alert-info text-center">
                No expenses found.
            </div>

        @endforelse

    </div>
</div>

@endsection