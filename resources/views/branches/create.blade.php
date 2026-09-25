@extends('layouts.app')

@section('title', 'Add Branch')

@section('content')

<div class="card">

    <div class="card-header">

        <h3 class="card-title">
            Add Branch
        </h3>

    </div>

    <form method="POST"
          action="{{ route('branches.store') }}">

        @csrf

        <div class="card-body">

            <div class="form-group">

                <label>
                    Branch Name
                    <span class="text-danger">*</span>
                </label>

                <input type="text"
                       name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}"
                       placeholder="Enter branch name"
                       required>

                @error('name')
                    <span class="invalid-feedback">
                        {{ $message }}
                    </span>
                @enderror

            </div>

        </div>

        <div class="card-footer">

            <button type="submit"
                    class="btn btn-primary">

                <i class="fas fa-save"></i>
                Save

            </button>

            <a href="{{ route('branches.index') }}"
               class="btn btn-secondary">

                Cancel

            </a>

        </div>

    </form>

</div>

@endsection