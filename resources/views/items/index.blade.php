@extends('layouts.app')

@section('title','Items')

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Items</h3>
        <a href="{{ route('items.create') }}" class="btn btn-primary btn-sm float-right">
            + Add Item
        </a>
    </div>

    <div class="card-body table-responsive">

        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category</th>
                    <th>Name</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->category->name }}</td>
                    <td>{{ $item->name }}</td>
                    <td>
                        <a href="/items/{{ $item->id }}/edit" class="btn btn-warning btn-xs">Edit</a>
                    </td>
                </tr>
                @endforeach
            </tbody>

        </table>

    </div>
</div>

@endsection