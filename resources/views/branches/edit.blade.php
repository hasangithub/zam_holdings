@extends('layouts.app')

@section('title', 'Edit Branch')

@section('content')

<div class="card">

    <div class="card-header">

        <h3 class="card-title">
            Edit Branch
        </h3>

    </div>

    <form method="POST"
          action="{{ route('branches.update', $branch) }}">

        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="form-group">

                <label>
                    Branch Name
                    <span class="text-danger">*</span>
                </label>

                <input type="text"
                       name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $branch->name) }}"
                       required>

                @error('name')
                    <span class="invalid-feedback">
                        {{ $message }}
                    </span>
                @enderror

            </div>

            <div class="form-group">

                <label>Status</label>

                <div>

                    @if($branch->is_active)

                        <span class="badge badge-success">
                            Active
                        </span>

                    @else

                        <span class="badge badge-danger">
                            Inactive
                        </span>

                    @endif

                </div>

            </div>

        </div>

        <div class="card-footer">

            <button type="submit"
                    class="btn btn-primary">

                <i class="fas fa-save"></i>
                Update

            </button>

            <a href="{{ route('branches.index') }}"
               class="btn btn-secondary">

                Cancel

            </a>

        </div>

    </form>

</div>

@endsection