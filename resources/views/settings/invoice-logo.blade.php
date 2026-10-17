@extends('layouts.app')

@section('title', 'Invoice Logo Settings')

@section('content')
<div class="container-fluid">

    <div class="row mb-3">
        <div class="col-12">
            <h4>Invoice Logo Settings</h4>
            <p class="text-muted mb-0">
                Manage the logos used for local sales, export sales, purchases and common invoices.
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i>
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">

        @foreach ($logos as $type => $logo)
            <div class="col-md-6 col-lg-6 mb-4">
                <div class="card card-outline card-primary h-100">

                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-image mr-1"></i>
                            {{ $logo['title'] }}
                        </h3>
                    </div>

                    <div class="card-body">

                        <div class="border rounded bg-light d-flex align-items-center justify-content-center mb-3"
                             style="height: 180px; padding: 15px;">

                            @if ($logo['filename'])
                                <img
                                    src="{{ $logo['url'] }}?v={{ filemtime($logo['path']) }}"
                                    alt="{{ $logo['title'] }}"
                                    style="max-width: 100%; max-height: 150px; object-fit: contain;">
                            @else
                                <div class="text-center text-muted">
                                    <i class="fas fa-image fa-3x mb-2"></i>
                                    <p class="mb-0">No logo uploaded</p>
                                </div>
                            @endif

                        </div>

                        <form action="{{ route('settings.invoice-logo.update', ['type' => $type]) }}"
                              method="POST"
                              enctype="multipart/form-data">

                            @csrf

                            <div class="form-group">
                                <label for="logo_{{ $type }}">Upload {{ $logo['title'] }}</label>

                                <input
                                    type="file"
                                    name="logo"
                                    id="logo_{{ $type }}"
                                    class="form-control-file"
                                    accept=".png,.jpg,.jpeg,image/png,image/jpeg"
                                    required>

                                <small class="form-text text-muted">
                                    PNG, JPG or JPEG. Maximum file size: 2 MB.
                                </small>
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-upload mr-1"></i>
                                {{ $logo['filename'] ? 'Replace Logo' : 'Upload Logo' }}
                            </button>

                        </form>

                    </div>
                </div>
            </div>
        @endforeach

    </div>
</div>
@endsection
