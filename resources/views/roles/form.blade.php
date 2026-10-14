
@extends('layouts.app')

@section('title', $role->exists ? 'Edit Role' : 'Create Role')

@section('content')
<div class="container-fluid">

    <form method="POST"
          action="{{ $role->exists
              ? route('roles.update', $role)
              : route('roles.store') }}">

        @csrf

        @if($role->exists)
            @method('PUT')
        @endif

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    {{ $role->exists ? 'Edit Role' : 'Create Role' }}
                </h3>
            </div>

            <div class="card-body">

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 pl-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="form-group">
                    <label for="name">Role Name <span class="text-danger">*</span></label>

                    <input type="text"
                           id="name"
                           name="name"
                           class="form-control"
                           value="{{ old('name', $role->name) }}"
                           placeholder="e.g. Local Sales Staff"
                           maxlength="255"
                           required>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Module Permissions</h5>

                    <div>
                        <button type="button"
                                class="btn btn-outline-primary btn-xs"
                                id="select-all">
                            Select All
                        </button>

                        <button type="button"
                                class="btn btn-outline-secondary btn-xs"
                                id="clear-all">
                            Clear All
                        </button>
                    </div>
                </div>

                <p class="text-muted">
                    Select the modules this role is allowed to access.
                </p>

                @php
                    $selectedPermissions = old(
                        'permissions',
                        $role->exists
                            ? $role->permissions->pluck('name')->all()
                            : []
                    );
                @endphp

                @foreach($permissionGroups as $group => $permissions)
                    <div class="card card-light card-outline mb-3">
                        <div class="card-header py-2">
                            <h3 class="card-title font-weight-bold">
                                {{ $group }}
                            </h3>

                            <div class="card-tools">
                                <button type="button"
                                        class="btn btn-tool select-group"
                                        data-group="{{ Str::slug($group) }}"
                                        title="Select or clear this group">
                                    <i class="fas fa-check-square"></i>
                                    Toggle
                                </button>
                            </div>
                        </div>

                        <div class="card-body py-2">
                            <div class="row">
                                @foreach($permissions as $permission)
                                    <div class="col-md-4 col-lg-3 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox"
                                                   class="custom-control-input permission-checkbox group-{{ Str::slug($group) }}"
                                                   id="permission_{{ $loop->parent->index }}_{{ $loop->index }}"
                                                   name="permissions[]"
                                                   value="{{ $permission->name }}"
                                                   {{ in_array($permission->name, $selectedPermissions, true) ? 'checked' : '' }}>

                                            <label class="custom-control-label"
                                                   for="permission_{{ $loop->parent->index }}_{{ $loop->index }}">
                                                {{ $permission->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    {{ $role->exists ? 'Update Role' : 'Save Role' }}
                </button>

                <a href="{{ route('roles.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

@section('js')
<script>
$(function () {
    $('#select-all').on('click', function () {
        $('.permission-checkbox').prop('checked', true);
    });

    $('#clear-all').on('click', function () {
        $('.permission-checkbox').prop('checked', false);
    });

    $('.select-group').on('click', function () {
        const group = $(this).data('group');
        const checkboxes = $('.group-' + group);
        const shouldCheck = checkboxes.filter(':not(:checked)').length > 0;

        checkboxes.prop('checked', shouldCheck);
    });
});
</script>
@endsection
