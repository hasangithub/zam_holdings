@extends('layouts.app')

@section('title','Edit Category')

@section('content')

<div class="card">
<div class="card-body">

<form method="POST" action="/categories/{{ $category->id }}">
@csrf
@method('PUT')

<input type="text" name="name" class="form-control" value="{{ $category->name }}">

<br>

<button class="btn btn-primary">Update</button>

</form>

</div>
</div>

@endsection