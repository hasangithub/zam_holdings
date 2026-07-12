@extends('layouts.app')

@section('title','Edit Expense')

@section('content')

<div class="container-fluid">

    <div class="row">

        <div class="col-md-12">

            <div class="card card-warning card-outline">

                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit"></i> Edit Expense
                    </h3>
                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('expenses.update', $expense->id) }}">
                        @csrf
                        @method('PUT')

                        {{-- CATEGORY --}}
                        <div class="form-group">
                            <label>Expense Category</label>
                            <select name="expense_category_id" class="form-control" required>
                                <option value="">Select Category</option>

                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}"
                                        {{ $expense->expense_category_id == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        {{-- DATE --}}
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date"
                                   name="expense_date"
                                   value="{{ $expense->expense_date }}"
                                   class="form-control"
                                   required>
                        </div>

                        {{-- AMOUNT --}}
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number"
                                   step="0.01"
                                   name="amount"
                                   value="{{ $expense->total_amount }}"
                                   class="form-control">
                        </div>

                        {{-- NOTE --}}
                        <div class="form-group">
                            <label>Note</label>
                            <textarea name="note" class="form-control">{{ $expense->remarks }}</textarea>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                Update Expense
                            </button>

                            <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection