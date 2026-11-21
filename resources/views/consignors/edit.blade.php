
@extends('layouts.app')

@section('title', 'Edit Consignor')

@section('content')
<div class="container-fluid">

    <div class="card card-primary card-outline">

        <div class="card-header">
            <h3 class="card-title">Edit Consignor</h3>
        </div>

        <form action="{{ route('consignors.update', $consignor->id) }}"
              method="POST">

            @csrf
            @method('PUT')

            <div class="card-body">

                <div class="row">
                    <div class="col-md-6">

                        <div class="form-group">
                            <label>Consignor Name <span class="text-danger">*</span></label>

                            <input type="text"
                                   name="name"
                                   class="form-control form-control-sm @error('name') is-invalid @enderror"
                                   value="{{ old('name', $consignor->name) }}"
                                   required>

                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="form-group">
                            <label>Address</label>

                            <textarea name="address"
                                      rows="3"
                                      class="form-control form-control-sm @error('address') is-invalid @enderror">{{ old('address', $consignor->address) }}</textarea>

                            @error('address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                </div>

            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-save"></i> Update
                </button>

                <a href="{{ route('consignors.index') }}"
                   class="btn btn-secondary btn-sm">
                    Back
                </a>
            </div>

        </form>

    </div>
</div>
@endsection
