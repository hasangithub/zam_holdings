@extends('layouts.app')

@section('title','Expenses')

@section('content')

<div class="card">

    <div class="card-header">

        <h3 class="card-title">Packing Usages</h3>

        <div class="card-tools">
            <a href="{{ route('packing-usages.create') }}"
               class="btn btn-primary btn-sm">
                Add Usages
            </a>
        </div>

    </div>

    <div class="card-body">

        <table class="table table-bordered table-striped table-erp">

            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Total</th>
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
                            @if($expense->expense_category_id !== null)
                                <span class="badge badge-info">Fixed</span>
                            @else
                                <span class="badge badge-warning">Packaging</span>
                            @endif
                        </td>

                        <td class="text-right">
                            {{ number_format($expense->total_amount, 2) }}
                        </td>

                        <td>

                            <a href="{{ route('expenses.show',$expense->id) }}"
                               class="btn btn-info btn-sm">
                                View
                            </a>

                            <a href="{{ route('packing-usages.edit',$expense->id) }}"
                               class="btn btn-primary btn-sm">
                                Edit
                            </a>

                            <form method="POST"
                                  action="{{ route('packing-usages.destroy',$expense->id) }}"
                                  style="display:inline-block">

                                @csrf
                                @method('DELETE')

                                <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Delete this expense?')">
                                    Delete
                                </button>

                            </form>

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