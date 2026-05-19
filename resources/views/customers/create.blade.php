@extends('layouts.app')

@section('title','Create Customer')

@section('content')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">Create Customer</h3>
    </div>

    <div class="card-body">

        <form method="POST" action="{{ route('customers.store') }}">
            @csrf

            <div class="form-group">
                <label>Customer Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control">
            </div>

            <button class="btn btn-success">Save</button>

        </form>

    </div>

</div>

@endsection