
@extends('layouts.app')

@section('title', 'Add Employee')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Employee</h3>
    </div>

    <form action="{{ route('employees.store') }}" method="POST">
        @csrf

        <div class="card-body">
            @include('employees.form')
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-save"></i> Save Employee
            </button>

            <a href="{{ route('employees.index') }}"
               class="btn btn-secondary btn-sm">Cancel</a>
        </div>
    </form>
</div>
@endsection
