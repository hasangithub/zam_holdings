@extends('layouts.app')

@section('title','Create Expense')

@section('content')

<div class="container-fluid">

    <div class="row">

        {{-- LEFT SIDE --}}
        <div class="col-md-12">

            <div class="card card-primary card-outline">

                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart"></i> Create Expense
                    </h3>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('expenses.store') }}">
                        @csrf

                        {{-- CATEGORY --}}
                        <div class="form-group">
                            <label>Expense Category</label>
                            <select name="expense_category_id" id="expense_category" class="form-control">
                                <option value="">Select Category</option>
                                @foreach($categories as $c)
                                <option value="{{ $c->id }}" data-packing="{{ $c->type }}">
                                    {{ $c->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4"> <label>Date</label> <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" class="form-control" required> </div>
                        </div> 

                         <div class="form-group">
                                <label>Amount</label>
                                <input type="number" name="amount" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>Note</label>
                                <textarea name="note" class="form-control"></textarea>
                            </div>

                        <button type="submit" class="btn btn-success">
                            Save Expense
                        </button>

                    </form>
                </div>
            </div>

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>

</script>

@endpush