<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        // SECURITY: Only admins can view all users
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $users = User::with('location')->get();
        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        // SECURITY: Only admins can create users
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $roles = Role::pluck('name', 'name');
        $locations = Location::active()->orderBy('name')->get();
        return view('users.create', compact('roles', 'locations'));
    }

    public function store(Request $request)
    {
        // SECURITY: Only admins can create users
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|exists:roles,name',
            'location_id' => 'nullable|exists:locations,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'location_id' => $request->location_id,
        ]);

        $user->assignRole($request->role);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? redirect()->route('users.index')->with('success', 'User created and setup email sent!')
            : back()->withInput()->with('error', 'Failed to send setup email.');
    }

    public function edit(User $user): View
    {
        // SECURITY: Only admins can edit users
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $roles = Role::pluck('name', 'name');
        $locations = Location::active()->orderBy('name')->get();
        return view('users.edit', compact('user', 'roles', 'locations'));
    }

    public function update(Request $request, User $user)
    {
        // SECURITY: Only admins can update users
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name',
            'location_id' => 'nullable|exists:locations,id',
            // SECURITY: Strong password requirements (12+ chars, mixed case, numbers, special chars)
            'password' => [
                'nullable',
                'string',
                'min:12',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'location_id' => $request->location_id,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);

        $user->syncRoles($request->role);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // SECURITY: Only admins can delete users
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Cannot delete your own account.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}