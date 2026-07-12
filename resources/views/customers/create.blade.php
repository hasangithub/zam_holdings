@extends('layouts.app')

@section('title','Create Customer')

@section('content')

<div class="row justify-content-center">

    <div class="col-lg-10">

        <div class="card card-default">

            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-plus mr-1"></i>
                    Create Customer
                </h3>
            </div>

            <form method="POST" action="{{ route('customers.store') }}">
                @csrf

                <div class="card-body">

                    <div class="row">

                        {{-- Customer Name --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>
                                    Customer Name
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                    name="name"
                                    class="form-control form-control-sm"
                                    value="{{ old('name') }}"
                                    required>
                            </div>
                        </div>

                        {{-- Customer Code --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>
                                    Customer Code
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                    name="customer_code"
                                    class="form-control form-control-sm text-uppercase"
                                    maxlength="3"
                                    placeholder="ABC"
                                    value="{{ old('customer_code') }}"
                                    required>

                                <small class="text-muted">
                                    Example: ABC, ZAM, JHN
                                </small>
                            </div>
                        </div>

                        {{-- Phone --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone</label>

                                <input type="text"
                                    name="phone"
                                    class="form-control form-control-sm"
                                    value="{{ old('phone') }}">
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>

                                <input type="email"
                                    name="email"
                                    class="form-control form-control-sm"
                                    value="{{ old('email') }}">
                            </div>
                        </div>

                        {{-- Customer Type --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Customer Type</label>

                                <select name="customer_type"
                                    class="form-control form-control-sm">

                                    <option value="local"
                                        {{ old('customer_type')=='local' ? 'selected' : '' }}>
                                        Local
                                    </option>

                                    <option value="export"
                                        {{ old('customer_type')=='export' ? 'selected' : '' }}>
                                        Export
                                    </option>

                                </select>
                            </div>
                        </div>

                        {{-- Consignee Name --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Consignee Name</label>

                                <input type="text"
                                    name="consignee_name"
                                    class="form-control form-control-sm"
                                    value="{{ old('consignee_name') }}">
                            </div>
                        </div>

                        {{-- Consignee Address --}}
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Consignee Address</label>

                                <textarea name="consignee_address"
                                    rows="3"
                                    class="form-control form-control-sm">{{ old('consignee_address') }}</textarea>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="card-footer">

                    <a href="{{ route('customers.index') }}"
                        class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>

                    <button type="submit"
                        class="btn btn-success float-right">
                        <i class="fas fa-save"></i> Save Customer
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
@endsection