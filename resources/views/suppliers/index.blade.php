@extends('layouts.app')

@section('title','Suppliers')

@section('content')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">Suppliers</h3>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm float-right">
            + Add Supplier
        </a>
    </div>

    <div class="card-body table-responsive">

        <table class="table table-bordered table-hover table-sm">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Supplier Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th width="150">Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach($suppliers as $s)
                <tr>
                    <td>{{ $s->id }}</td>
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->phone }}</td>
                    <td>{{ $s->email }}</td>

                    <td>
                        <a href="{{ route('suppliers.edit',$s->id) }}" class="btn btn-warning btn-xs">
                            Edit
                        </a>

                        <form action="{{ route('suppliers.destroy',$s->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-danger btn-xs" onclick="return confirm('Delete?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>

        </table>

    </div>

</div>

@endsection