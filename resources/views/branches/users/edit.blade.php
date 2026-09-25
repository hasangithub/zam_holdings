@extends('layouts.app')

@section('title', 'Edit Branch User')

@section('content')

<div class="card">

    <div class="card-header">

        <h3 class="card-title">

            <i class="fas fa-user-edit"></i>

            Edit Branch User

        </h3>

    </div>


    <form method="POST"
          action="{{ route(
              'branches.users.update',
              [$branch, $user]
          ) }}">

        @csrf
        @method('PUT')


        <div class="card-body">

            <div class="alert alert-info">

                <strong>Branch:</strong>
                {{ $branch->name }}

                <br>

                <small>
                    Branch assignment cannot be changed here.
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
                               value="{{ old(
                                   'name',
                                   $user->name
                               ) }}"
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
                               value="{{ old(
                                   'email',
                                   $user->email
                               ) }}"
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
                            New Password
                        </label>

                        <input type="password"
                               name="password"
                               class="form-control
                               @error('password') is-invalid @enderror">

                        <small class="text-muted">
                            Leave blank to keep the current password.
                        </small>

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
                            Confirm New Password
                        </label>

                        <input type="password"
                               name="password_confirmation"
                               class="form-control">

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
                                    {{ old(
                                        'role',
                                        optional($userRole)->name
                                    ) == $role->name
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
                                {{ old(
                                    'is_active',
                                    $user->is_active
                                ) == 1
                                    ? 'selected'
                                    : '' }}>

                                Active

                            </option>

                            <option value="0"
                                {{ old(
                                    'is_active',
                                    $user->is_active
                                ) == 0
                                    ? 'selected'
                                    : '' }}>

                                Inactive

                            </option>

                        </select>

                    </div>

                </div>

            </div>


            <div class="form-group">

                <label>Branch</label>

                <input type="text"
                       class="form-control"
                       value="{{ $branch->name }}"
                       disabled>

            </div>

        </div>


        <div class="card-footer">

            <button type="submit"
                    class="btn btn-primary">

                <i class="fas fa-save"></i>
                Update User

            </button>

            <a href="{{ route(
                'branches.users',
                $branch
            ) }}"
               class="btn btn-secondary">

                Cancel

            </a>

        </div>

    </form>

</div>

@endsection