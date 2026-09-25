@extends('layouts.app')

@section('title', 'Add Branch User')

@section('content')

<div class="card">

    <div class="card-header">

        <h3 class="card-title">

            <i class="fas fa-user-plus"></i>

            Add Branch User

        </h3>

    </div>


    <form method="POST"
          action="{{ route('branches.users.store', $branch) }}">

        @csrf

        <div class="card-body">

            <div class="alert alert-info">

                <strong>Branch:</strong>
                {{ $branch->name }}

                <br>

                <small>
                    The user will automatically be assigned
                    to this branch.
                </small>

            </div>


            <div class="row">

                <div class="col-md-6">

                    <div class="form-group">

                        <label>
                            Name
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="name"
                               class="form-control
                               @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               required>

                        @error('name')
                            <span class="invalid-feedback">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>

                </div>


                <div class="col-md-6">

                    <div class="form-group">

                        <label>
                            Email
                            <span class="text-danger">*</span>
                        </label>

                        <input type="email"
                               name="email"
                               class="form-control
                               @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               required>

                        @error('email')
                            <span class="invalid-feedback">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>

                </div>

            </div>


            <div class="row">

                <div class="col-md-6">

                    <div class="form-group">

                        <label>
                            Password
                            <span class="text-danger">*</span>
                        </label>

                        <input type="password"
                               name="password"
                               class="form-control
                               @error('password') is-invalid @enderror"
                               required>

                        @error('password')
                            <span class="invalid-feedback">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>

                </div>


                <div class="col-md-6">

                    <div class="form-group">

                        <label>
                            Confirm Password
                            <span class="text-danger">*</span>
                        </label>

                        <input type="password"
                               name="password_confirmation"
                               class="form-control"
                               required>

                    </div>

                </div>

            </div>


            <div class="row">

                <div class="col-md-6">

                    <div class="form-group">

                        <label>
                            Role
                            <span class="text-danger">*</span>
                        </label>

                        <select name="role"
                                class="form-control
                                @error('role') is-invalid @enderror"
                                required>

                            <option value="">
                                Select Role
                            </option>

                            @foreach($roles as $role)

                                <option value="{{ $role->name }}"
                                    {{ old('role') == $role->name
                                        ? 'selected'
                                        : '' }}>

                                    {{ $role->name }}

                                </option>

                            @endforeach

                        </select>

                        @error('role')
                            <span class="invalid-feedback">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>

                </div>


                <div class="col-md-6">

                    <div class="form-group">

                        <label>Status</label>

                        <select name="is_active"
                                class="form-control">

                            <option value="1"
                                {{ old('is_active', 1) == 1
                                    ? 'selected'
                                    : '' }}>

                                Active

                            </option>

                            <option value="0"
                                {{ old('is_active') === '0'
                                    ? 'selected'
                                    : '' }}>

                                Inactive

                            </option>

                        </select>

                    </div>

                </div>

            </div>

        </div>


        <div class="card-footer">

            <button type="submit"
                    class="btn btn-primary">

                <i class="fas fa-save"></i>
                Create User

            </button>

            <a href="{{ route('branches.users', $branch) }}"
               class="btn btn-secondary">

                Cancel

            </a>

        </div>

    </form>

</div>

@endsection