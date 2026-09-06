@extends('layouts.app')

@section('title','Edit Freight Service')

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Freight Service</h3>
    </div>

    <form method="POST"
          action="{{ route('freight-services.update',$service->id) }}">
        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="form-group">
                <label>Service Name</label>

                <input type="text"
                       name="name"
                       value="{{ old('name',$service->name) }}"
                       class="form-control"
                       required>
            </div>

            <div class="form-check">
                <input type="checkbox"
                       name="active"
                       value="1"
                       class="form-check-input"
                       id="active"
                       {{ $service->active ? 'checked' : '' }}>

                <label class="form-check-label" for="active">
                    Active
                </label>
            </div>

        </div>

        <div class="card-footer">
            <button class="btn btn-success btn-sm">
                <i class="fas fa-save"></i> Update
            </button>

            <a href="{{ route('freight-services.index') }}"
               class="btn btn-secondary btn-sm">
                Cancel
            </a>
        </div>

    </form>
</div>

@endsection