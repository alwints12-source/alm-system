<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Show the user management page (list + add-user modal).
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();

        return view('techadmin.users.index', compact('users'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role'       => ['required', 'in:administrative_admin,technical_admin,asset_holder,technician'],
            'department' => ['required', 'string', 'max:100'],
            'password'   => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'employee_id' => 'EMP' . str_pad((User::max('id') + 1), 3, '0', STR_PAD_LEFT),
            'first_name'  => $validated['first_name'],
            'last_name'   => $validated['last_name'],
            'email'       => $validated['email'],
            'password'    => Hash::make($validated['password']),
            'role'        => $validated['role'],
            'department'  => $validated['department'],
            'is_active'   => true,
        ]);

        return redirect()->route('techadmin.users.index')
            ->with('status', 'User created successfully.');
    }

    /**
     * Toggle a user's active status (Deactivate / Activate button).
     */
    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);

        return redirect()->route('techadmin.users.index')
            ->with('status', $user->is_active ? 'User activated.' : 'User deactivated.');
    }
}
