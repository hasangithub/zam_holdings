
@extends('layouts.app')

@section('title', 'Employees')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Employees</h3>

        <div class="card-tools">
            <a href="{{ route('employees.create') }}"
               class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Employee
            </a>
        </div>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form method="GET" action="{{ route('employees.index') }}"
              class="form-inline mb-3">
            <input type="text"
                   name="search"
                   class="form-control form-control-sm mr-2"
                   placeholder="Search name or phone"
                   value="{{ request('search') }}">

            <button type="submit" class="btn btn-secondary btn-sm">
                <i class="fas fa-search"></i> Search
            </button>

            @if(request('search'))
                <a href="{{ route('employees.index') }}"
                   class="btn btn-default btn-sm ml-2">Clear</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Employee Name</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th style="width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td>{{ $employees->firstItem() + $loop->index }}</td>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->phone ?: '-' }}</td>
                            <td>{{ $employee->address ?: '-' }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('employees.edit', $employee->id) }}"
                                   class="btn btn-warning btn-xs"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('employees.destroy', $employee->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this employee?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-danger btn-xs"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">
                                No employees found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $employees->links() }}
        </div>
    </div>
</div>
@endsection
