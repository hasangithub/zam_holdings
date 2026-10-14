
@extends('layouts.app')

@section('title', 'Role Management')

@section('content')
<div class="container-fluid">

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-shield mr-1"></i>
                Role Management
            </h3>

            <div class="card-tools">
                <a href="{{ route('roles.create') }}"
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Create Role
                </a>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 50px">#</th>
                        <th>Role Name</th>
                        <th style="width: 100px">Users</th>
                        <th>Permissions</th>
                        <th style="width: 130px">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($roles as $role)
                    <tr>
                        <td>{{ $roles->firstItem() + $loop->index }}</td>

                        <td>
                            <strong>{{ $role->name }}</strong>

                            @if($role->name === 'Super Admin')
                                <span class="badge badge-primary">
                                    Protected
                                </span>
                            @endif
                        </td>

                        <td class="text-center">
                            {{ $role->users_count }}
                        </td>

                        <td>
                            @forelse($role->permissions as $permission)
                                <span class="badge badge-light border mb-1">
                                    {{ $permission->name }}
                                </span>
                            @empty
                                <span class="text-muted">No permissions</span>
                            @endforelse
                        </td>

                        <td class="text-nowrap">
                            @if($role->name !== 'Super Admin')
                                <a href="{{ route('roles.edit', $role) }}"
                                   class="btn btn-info btn-xs"
                                   title="Edit Role">
                                    <i class="fas fa-edit"></i>
                                </a>

                                @if($role->users_count === 0)
                                    <form action="{{ route('roles.destroy', $role) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Delete this role?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-danger btn-xs"
                                                title="Delete Role">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            @else
                                <span class="text-muted">System role</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted p-3">
                            No roles found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer clearfix">
            {{ $roles->links() }}
        </div>
    </div>

</div>
@endsection
