@extends('layouts.app')

@section('title','Create Item')

@section('content')

<div class="card">
    <div class="card-header">
       Create Item
    </div>
    <div class="card-body">

        <form method="POST" action="/items">
            @csrf

            <select name="category_id" class="form-control" required>
                <option value="">Parent Category</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

            <br>

            <input type="text" name="name" class="form-control" placeholder="Item Name" required>

            <br>

            <button class="btn btn-success">Save</button>

        </form>

    </div>
</div>

@endsection