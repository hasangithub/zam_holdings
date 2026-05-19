@extends('layouts.app')

@section('title','Edit Supplier')

@section('content')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">Edit Supplier</h3>
    </div>

    <div class="card-body">

        <form method="POST" action="{{ route('suppliers.update',$supplier->id) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Supplier Name</label>
                <input type="text" name="name" class="form-control"
                       value="{{ $supplier->name }}" required>
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control"
                       value="{{ $supplier->phone }}">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control"
                       value="{{ $supplier->email }}">
            </div>

            <button class="btn btn-primary">Update</button>

        </form>

    </div>

</div>

@endsection