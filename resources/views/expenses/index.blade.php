@extends('layouts.app')

@section('title','Expenses')

@section('content')
@include('partials.alerts')
<div class="card">

    <div class="card-header">

        <h3 class="card-title">Expenses</h3>

        <div class="card-tools">
            <a href="{{ route('expenses.create') }}"
                class="btn btn-primary btn-sm">
                Add Expense
            </a>
        </div>

    </div>

    <div class="card-body">

        <table class="table table-bordered table-striped table-erp">

            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th width="180">Action</th>
                </tr>
            </thead>

            <tbody>

                @forelse($expenses as $expense)

                <tr>

                    <td>{{ $expense->id }}</td>

                    <td>
                        {{ \Carbon\Carbon::parse($expense->expense_date)->format('d-m-Y') }}
                    </td>

                    <td>
                        {{ $expense->category->name ?? '' }}
                    </td>

                    <td>
                        @if($expense->category->type == 'fixed')
                        <span class="badge badge-info">Fixed</span>
                        @else
                        <span class="badge badge-warning">Packaging</span>
                        @endif
                    </td>

                    <td class="text-right">
                        {{ number_format($expense->total_amount, 2) }}
                    </td>

                    <td>
                    <td>
                        @if($expense->status === 'cancelled')
                        <span class="badge badge-danger">Cancelled</span>
                        @else
                        <span class="badge badge-success">{{ ucfirst($expense->status) }}</span>
                        @endif
                    </td>
                    </td>

                    <td>

                        <a href="{{ route('expenses.show',$expense->id) }}"
                            class="btn btn-info btn-sm">
                            View
                        </a>

                        <a href="{{ route('expenses.edit',$expense->id) }}"
                            class="btn btn-primary btn-sm">
                            Edit
                        </a>

                        @if($expense->status !== 'cancelled')
                        <form action="{{ route('expenses.destroy', $expense->id) }}"
                            method="POST"
                            class="d-inline">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('Cancel this expense?')">
                                <i class="fas fa-ban"></i> Cancel
                            </button>
                        </form>
                        @endif
                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="6" class="text-center">
                        No Expenses Found
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection