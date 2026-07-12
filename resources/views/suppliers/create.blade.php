@extends('layouts.app')

@section('title', 'Create Supplier')

@section('content')

<div class="row justify-content-center">

    <div class="col-lg-12">

        <div class="card card-default">

            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-truck mr-1"></i>
                    Create Supplier
                </h3>
            </div>

            <form method="POST" action="{{ route('suppliers.store') }}">
                @csrf

                <div class="card-body">

                    <div class="form-group">

                        <label>
                            Supplier Name
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
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
                                       value="{{ old('phone') }}"
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
                                       value="{{ old('email') }}"
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
                                  placeholder="Enter Supplier Address">{{ old('address') }}</textarea>

                    </div>

                </div>

                <div class="card-footer">

                    <a href="{{ route('suppliers.index') }}"
                       class="btn btn-secondary">

                        <i class="fas fa-arrow-left mr-1"></i>
                        Back

                    </a>

                    <button type="submit"
                            class="btn btn-success float-right">

                        <i class="fas fa-save mr-1"></i>
                        Save Supplier

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection