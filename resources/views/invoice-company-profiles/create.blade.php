
@extends('layouts.app')

@section('title', 'Add Invoice Company Profile')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Invoice Company Profile</h3>
    </div>

    <form action="{{ route('invoice-company-profiles.store') }}" method="POST">
        @csrf

        <div class="card-body">
            @include('invoice-company-profiles.form', ['profile' => null])
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-save"></i> Save
            </button>

            <a href="{{ route('invoice-company-profiles.index') }}"
               class="btn btn-secondary btn-sm">Cancel</a>
        </div>
    </form>
</div>
@endsection
