@extends('layouts.app')

@section('title', 'Invoice Logo Settings')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Invoice Logo Settings</h3>
    </div>

    <div class="card-body">

        @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="mb-4">
            <label>Current Invoice Logo</label><br>

            @if ($logo)
            <img src="{{ asset('images/invoice/' . $logo) }}?v={{ filemtime(public_path('images/invoice/' . $logo)) }}"
                alt="Invoice Logo"
                style="max-width: 300px; max-height: 150px;">
            @else
            <p class="text-muted">No invoice logo found.</p>
            @endif
        </div>

        <form action="{{ route('settings.invoice-logo.update') }}"
            method="POST"
            enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="logo">Upload New Logo (JPEG, maximum 2 MB)</label>
                <input type="file"
                    name="logo"
                    class="form-control-file"
                    accept=".png,.jpg,.jpeg,image/png,image/jpeg"
                    required>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-upload"></i> Replace Invoice Logo
            </button>
        </form>

    </div>
</div>
@endsection