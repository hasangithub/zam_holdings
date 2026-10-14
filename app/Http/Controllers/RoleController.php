<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    private function permissionGroups(): array
    {
        return Permission::where('guard_name', 'web')
            ->whereNotIn('name', [
                'roles.manage',
                'users.roles.manage',
            ])
            ->orderBy('name')
            ->get()
            ->groupBy(function ($permission) {
                $parts = explode('.', $permission->name, 2);

                return count($parts) === 2
                    ? ucfirst(str_replace('-', ' ', $parts[0]))
                    : 'Other Modules';
            })
            ->all();
    }

    public function index()
    {
        $roles = Role::where('guard_name', 'web')
            ->with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->paginate(20);

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $role = new Role();

        $permissionGroups = $this->permissionGroups();

        return view('roles.form', compact(
            'role',
            'permissionGroups'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9 _-]+$/',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web'),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'required',
                'string',
                'distinct',
                Rule::exists('permissions', 'name')
                    ->where('guard_name', 'web'),
                Rule::notIn([
                    'roles.manage',
                    'users.roles.manage',
                ]),
            ],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions(
            $validated['permissions'] ?? []
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        abort_unless($role->guard_name === 'web', 404);

        // Super Admin is managed by the seeder.
        abort_if($role->name === 'Super Admin', 403,
            'Super Admin is a protected role.');

        $role->load('permissions');

        $permissionGroups = $this->permissionGroups();

        return view('roles.form', compact(
            'role',
            'permissionGroups'
        ));
    }

    public function update(Request $request, Role $role)
    {
        abort_unless($role->guard_name === 'web', 404);

        abort_if($role->name === 'Super Admin', 403,
            'Super Admin is a protected role.');

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9 _-]+$/',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web')
                    ->ignore($role->id),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'required',
                'string',
                'distinct',
                Rule::exists('permissions', 'name')
                    ->where('guard_name', 'web'),
                Rule::notIn([
                    'roles.manage',
                    'users.roles.manage',
                ]),
            ],
        ]);

        $role->update([
            'name' => $validated['name'],
        ]);

        $role->syncPermissions(
            $validated['permissions'] ?? []
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        abort_unless($role->guard_name === 'web', 404);

        abort_if($role->name === 'Super Admin', 403,
            'Super Admin cannot be deleted.');

        if ($role->users()->exists()) {
            return back()->with(
                'error',
                'This role is assigned to users. Remove those assignments first.'
            );
        }

        $role->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}