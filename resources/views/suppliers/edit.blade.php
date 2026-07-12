@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')

<div class="row justify-content-center">

    <div class="col-lg-12">

        <div class="card card-default">

            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-1"></i>
                    Edit Supplier
                </h3>
            </div>

            <form method="POST" action="{{ route('suppliers.update', $supplier->id) }}">
                @csrf
                @method('PUT')

                <div class="card-body">

                    <div class="form-group">

                        <label>
                            Supplier Name
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="name"
                               value="{{ old('name', $supplier->name) }}"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Enter Supplier Name"
                               required>

                        @error('name')
                            <span class="invalid-feedback">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>

                    <div class="row">

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>
                                    Phone Number
                                </label>

                                <input type="text"
                                       name="phone"
                                       value="{{ old('phone', $supplier->phone) }}"
                                       class="form-control"
                                       placeholder="0771234567">

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>
                                    Email Address
                                </label>

                                <input type="email"
                                       name="email"
                                       value="{{ old('email', $supplier->email) }}"
                                       class="form-control"
                                       placeholder="supplier@email.com">

                            </div>

                        </div>

                    </div>

                    <div class="form-group">

                        <label>
                            Address
                        </label>

                        <textarea name="address"
                                  rows="3"
                                  class="form-control"
                                  placeholder="Enter Supplier Address">{{ old('address', $supplier->address) }}</textarea>

                    </div>

                </div>

                <div class="card-footer">

                    <a href="{{ route('suppliers.index') }}"
                       class="btn btn-secondary">

                        <i class="fas fa-arrow-left mr-1"></i>
                        Back

                    </a>

                    <button type="submit"
                            class="btn btn-warning float-right">

                        <i class="fas fa-save mr-1"></i>
                        Update Supplier

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection