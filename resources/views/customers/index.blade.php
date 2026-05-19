@extends('layouts.app')

@section('title','Customers')

@section('content')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">Customers</h3>

        <a href="{{ route('customers.create') }}"
           class="btn btn-primary btn-sm float-right">
            + Add Customer
        </a>
    </div>

    <div class="card-body table-responsive">

        <table class="table table-bordered table-hover table-sm">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th width="150">Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach($customers as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td>{{ $c->name }}</td>
                    <td>{{ $c->phone }}</td>
                    <td>{{ $c->email }}</td>

                    <td>
                        <a href="{{ route('customers.edit',$c->id) }}"
                           class="btn btn-warning btn-xs">
                            Edit
                        </a>

                        <form action="{{ route('customers.destroy',$c->id) }}"
                              method="POST"
                              style="display:inline;">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-danger btn-xs"
                                    onclick="return confirm('Delete this customer?')">
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