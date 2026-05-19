@extends('layouts.app')

@section('title','Edit Customer')

@section('content')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">Edit Customer</h3>
    </div>

    <div class="card-body">

        <form method="POST" action="{{ route('customers.update',$customer->id) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Customer Name</label>
                <input type="text"
                       name="name"
                       class="form-control"
                       value="{{ $customer->name }}"
                       required>
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text"
                       name="phone"
                       class="form-control"
                       value="{{ $customer->phone }}">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email"
                       name="email"
                       class="form-control"
                       value="{{ $customer->email }}">
            </div>

            <button class="btn btn-primary">Update</button>

        </form>

    </div>

</div>

@endsection