@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')

<div class="row justify-content-center">

    <div class="col-lg-12">

        <div class="card card-default">

            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-edit mr-1"></i>
                    Edit Customer
                </h3>
            </div>

            <form method="POST" action="{{ route('customers.update', $customer->id) }}">
                @csrf
                @method('PUT')

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-8">

                            <div class="form-group">

                                <label>
                                    Customer Name
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="name"
                                       value="{{ old('name', $customer->name) }}"
                                       class="form-control"
                                       placeholder="Enter Customer Name"
                                       required>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="form-group">

                                <label>
                                    Customer Code
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="customer_code"
                                       value="{{ old('customer_code', $customer->customer_code) }}"
                                       class="form-control text-uppercase"
                                       maxlength="3"
                                       placeholder="ABC"
                                       required>

                                <small class="text-muted">
                                    Used for invoice numbering
                                </small>

                            </div>

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>
                                    Phone Number
                                </label>

                                <input type="text"
                                       name="phone"
                                       value="{{ old('phone', $customer->phone) }}"
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
                                       value="{{ old('email', $customer->email) }}"
                                       class="form-control"
                                       placeholder="customer@email.com">

                            </div>

                        </div>

                    </div>

                    <div class="form-group">

                        <label>
                            Customer Type
                            <span class="text-danger">*</span>
                        </label>

                        <select name="customer_type"
                                class="form-control"
                                required>

                            <option value="local"
                                {{ old('customer_type', $customer->customer_type) == 'local' ? 'selected' : '' }}>
                                Local Customer
                            </option>

                            <option value="export"
                                {{ old('customer_type', $customer->customer_type) == 'export' ? 'selected' : '' }}>
                                Export Customer
                            </option>

                        </select>

                    </div>

                </div>

                <div class="card-footer">

                    <a href="{{ route('customers.index') }}"
                       class="btn btn-secondary">

                        <i class="fas fa-arrow-left mr-1"></i>
                        Back

                    </a>

                    <button type="submit"
                            class="btn btn-warning float-right">

                        <i class="fas fa-save mr-1"></i>
                        Update Customer

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection