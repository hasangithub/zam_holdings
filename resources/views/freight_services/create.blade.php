@extends('layouts.app')

@section('title','Add Freight Service')

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Freight Service</h3>
    </div>

    <form method="POST" action="{{ route('freight-services.store') }}">
        @csrf

        <div class="card-body">

            <div class="form-group">
                <label>Service Name</label>
                <input type="text"
                       name="name"
                       value="{{ old('name') }}"
                       class="form-control"
                       placeholder="e.g. DHL"
                       required>
            </div>

            <div class="form-check">
                <input type="checkbox"
                       name="active"
                       value="1"
                       class="form-check-input"
                       id="active"
                       checked>

                <label class="form-check-label" for="active">
                    Active
                </label>
            </div>

        </div>

        <div class="card-footer">
            <button class="btn btn-success btn-sm">
                <i class="fas fa-save"></i> Save
            </button>

            <a href="{{ route('freight-services.index') }}"
               class="btn btn-secondary btn-sm">
                Cancel
            </a>
        </div>

    </form>
</div>

@endsection