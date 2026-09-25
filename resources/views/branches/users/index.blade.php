@extends('layouts.app')

@section('title', 'Branch Users')

@section('content')

<div class="card">

    <div class="card-header">

        <h3 class="card-title">

            <i class="fas fa-users"></i>

            {{ $branch->name }}

            <small class="text-muted ml-2">
                Branch Users
            </small>

        </h3>

        <div class="card-tools">

            <a href="{{ route('branches.index') }}"
               class="btn btn-secondary btn-sm">

                <i class="fas fa-arrow-left"></i>
                Branches

            </a>

            @if($branch->is_active)

                <a href="{{ route('branches.users.create', $branch) }}"
                   class="btn btn-primary btn-sm">

                    <i class="fas fa-user-plus"></i>
                    Add User

                </a>

            @endif

        </div>

    </div>


    <div class="card-body p-0">

        @if(session('success'))

            <div class="alert alert-success m-3">
                {{ session('success') }}
            </div>

        @endif

        @if(session('error'))

            <div class="alert alert-danger m-3">
                {{ session('error') }}
            </div>

        @endif


        <div class="px-3 py-2">

            <strong>Branch:</strong>
            {{ $branch->name }}

            <span class="ml-3">

                <strong>Status:</strong>

                @if($branch->is_active)

                    <span class="badge badge-success">
                        Active
                    </span>

                @else

                    <span class="badge badge-danger">
                        Inactive
                    </span>

                @endif

            </span>

        </div>


        <div class="table-responsive">

            <table class="table table-bordered table-hover table-sm mb-0">

                <thead class="thead-light">

                    <tr>

                        <th width="60">#</th>

                        <th>Name</th>

                        <th>Email</th>

                        <th>Role</th>

                        <th width="100"
                            class="text-center">
                            Status
                        </th>

                        <th width="180"
                            class="text-center">
                            Actions
                        </th>

                    </tr>

                </thead>

                <tbody>

                @forelse($users as $user)

                    <tr>

                        <td>
                            {{ $user->id }}
                        </td>

                        <td>
                            <strong>
                                {{ $user->name }}
                            </strong>
                        </td>

                        <td>
                            {{ $user->email }}
                        </td>

                        <td>

                            @forelse($user->roles as $role)

                                <span class="badge badge-info">
                                    {{ $role->name }}
                                </span>

                            @empty

                                <span class="text-muted">
                                    No Role
                                </span>

                            @endforelse

                        </td>

                        <td class="text-center">

                            @if($user->is_active)

                                <span class="badge badge-success">
                                    Active
                                </span>

                            @else

                                <span class="badge badge-danger">
                                    Inactive
                                </span>

                            @endif

                        </td>

                        <td class="text-center">

                            <a href="{{ route(
                                'branches.users.edit',
                                [$branch, $user]
                            ) }}"
                               class="btn btn-warning btn-xs">

                                <i class="fas fa-edit"></i>

                            </a>


                            <form
                                action="{{ route(
                                    'branches.users.toggle-status',
                                    [$branch, $user]
                                ) }}"
                                method="POST"
                                class="d-inline">

                                @csrf

                                <button type="submit"
                                        class="btn btn-xs
                                        {{ $user->is_active
                                            ? 'btn-danger'
                                            : 'btn-success' }}">

                                    @if($user->is_active)

                                        <i class="fas fa-ban"></i>

                                    @else

                                        <i class="fas fa-check"></i>

                                    @endif

                                </button>

                            </form>


                            <form
                                action="{{ route(
                                    'branches.users.destroy',
                                    [$branch, $user]
                                ) }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirm(
                                    'Are you sure you want to delete this user?'
                                );">

                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="btn btn-danger btn-xs">

                                    <i class="fas fa-trash"></i>

                                </button>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6"
                            class="text-center text-muted py-3">

                            No users found for this branch.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection