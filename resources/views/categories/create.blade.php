@extends('layouts.app')

@section('title','Create Category')

@section('content')

<div class="card">
    <div class="card-header">
        Create Category
    </div>
    <div class="card-body">

        <form method="POST" action="/categories">
            @csrf

            <input type="text" name="name" class="form-control" placeholder="Category Name" required>

            <br>

            <button class="btn btn-success">Save</button>

        </form>

    </div>
</div>

@endsection