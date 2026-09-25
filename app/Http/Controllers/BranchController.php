<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    /**
     * Branch list
     */
    public function index()
    {
        $branches = Branch::withCount('users')
            ->orderBy('id')
            ->get();

        return view('branches.index', compact('branches'));
    }


    /**
     * Create branch
     */
    public function create()
    {
        return view('branches.create');
    }


    /**
     * Store branch
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:branches,name',
            ],
        ]);

        Branch::create([
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return redirect()
            ->route('branches.index')
            ->with('success', 'Branch created successfully.');
    }


    /**
     * Edit branch
     */
    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }


    /**
     * Update branch
     */
    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches', 'name')
                    ->ignore($branch->id),
            ],
        ]);

        $branch->update([
            'name' => $validated['name'],
        ]);

        return redirect()
            ->route('branches.index')
            ->with('success', 'Branch updated successfully.');
    }


    /**
     * Activate / deactivate branch
     */
    public function toggleStatus(Branch $branch)
    {
        // Never deactivate Head Office
        if ((int) $branch->id === 1) {
            return back()->with('error', 'Head Office cannot be deactivated.');
        }

        $branch->update([
            'is_active' => !$branch->is_active,
        ]);

        $message = $branch->is_active
            ? 'Branch activated successfully.'
            : 'Branch deactivated successfully.';

        return back()->with('success', $message);
    }


    /**
     * Show users belonging to branch
     */
    public function users(Branch $branch)
    {
        $users = $branch->users()
            ->with('roles')
            ->orderBy('name')
            ->get();

        return view('branches.users.index', compact(
            'branch',
            'users'
        ));
    }


    /**
     * Create branch user
     */
    public function createUser(Branch $branch)
    {
        // Don't allow users to be created in inactive branch
        if (!$branch->is_active) {
            return back()->with(
                'error',
                'Cannot add users to an inactive branch.'
            );
        }

        $roles = \Spatie\Permission\Models\Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        return view('branches.users.create', compact(
            'branch',
            'roles'
        ));
    }


    /**
     * Store branch user
     */
    public function storeUser(Request $request, Branch $branch)
    {
        if (!$branch->is_active) {
            return back()->with(
                'error',
                'Cannot add users to an inactive branch.'
            );
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'name')
                    ->where('guard_name', 'web'),
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],

            // Important:
            // branch comes from route, NOT request
            'branch_id' => $branch->id,

            'password' => Hash::make($validated['password']),

            'is_active' => $validated['is_active'],
        ]);

        $user->assignRole($validated['role']);

        return redirect()
            ->route('branches.users', $branch)
            ->with('success', 'Branch user created successfully.');
    }


    /**
     * Edit branch user
     */
    public function editUser(Branch $branch, User $user)
    {
        // Make sure this user actually belongs to this branch
        abort_unless(
            (int) $user->branch_id === (int) $branch->id,
            404
        );

        $roles = \Spatie\Permission\Models\Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        $userRole = $user->roles->first();

        return view('branches.users.edit', compact(
            'branch',
            'user',
            'roles',
            'userRole'
        ));
    }


    /**
     * Update branch user
     */
    public function updateUser(
        Request $request,
        Branch $branch,
        User $user
    ) {
        // Security check
        abort_unless(
            (int) $user->branch_id === (int) $branch->id,
            404
        );

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user->id),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'name')
                    ->where('guard_name', 'web'),
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->is_active = $validated['is_active'];

        // Never allow branch to be changed from this form
        $user->branch_id = $branch->id;

        if (!empty($validated['password'])) {
            $user->password = Hash::make(
                $validated['password']
            );
        }

        $user->save();

        // Replace existing roles
        $user->syncRoles([
            $validated['role']
        ]);

        return redirect()
            ->route('branches.users', $branch)
            ->with('success', 'Branch user updated successfully.');
    }


    /**
     * Activate / deactivate branch user
     */
    public function toggleUserStatus(
        Branch $branch,
        User $user
    ) {
        abort_unless(
            (int) $user->branch_id === (int) $branch->id,
            404
        );

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $message = $user->is_active
            ? 'User activated successfully.'
            : 'User deactivated successfully.';

        return back()->with('success', $message);
    }


    /**
     * Delete branch user
     */
    public function destroyUser(
        Branch $branch,
        User $user
    ) {
        abort_unless(
            (int) $user->branch_id === (int) $branch->id,
            404
        );

        // Don't allow deleting Head Office user from branch management
        if ((int) $user->branch_id === 1) {
            return back()->with(
                'error',
                'Head Office users cannot be deleted from this screen.'
            );
        }

        $user->delete();

        return back()->with(
            'success',
            'Branch user deleted successfully.'
        );
    }
}