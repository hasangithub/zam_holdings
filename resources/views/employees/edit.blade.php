
@extends('layouts.app')

@section('title', 'Edit Employee')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Employee</h3>
    </div>

    <form action="{{ route('employees.update', $employee->id) }}"
          method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">
            @include('employees.form')
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-save"></i> Update Employee
            </button>

            <a href="{{ route('employees.index') }}"
               class="btn btn-secondary btn-sm">Cancel</a>
        </div>
    </form>
</div>
@endsection
