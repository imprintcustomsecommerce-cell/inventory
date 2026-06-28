<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('warehouse')->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('users.index', compact('users', 'warehouses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => ['required', Rule::in(['admin', 'staff'])],
            'department' => ['nullable', Rule::in(['materials'])],
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $isMaterials = ($data['role'] === 'staff') && (($data['department'] ?? null) === 'materials');

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'department' => $data['role'] === 'admin' ? null : ($data['department'] ?? null),
            // Admins span all; materials staff work in the stockroom; other staff in their warehouse.
            'warehouse_id' => $data['role'] === 'admin'
                ? null
                : ($isMaterials ? \App\Models\Warehouse::stockrooms()->value('id') : ($data['warehouse_id'] ?? null)),
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'User account created.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['admin', 'staff'])],
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);

        // Don't let an admin demote themselves and risk a lockout.
        if ($user->id === $request->user()->id && $data['role'] !== 'admin') {
            return back()->with('error', "You can't change your own role.");
        }

        $user->update([
            'role' => $data['role'],
            'warehouse_id' => $data['role'] === 'admin' ? null : ($data['warehouse_id'] ?? null),
        ]);

        return back()->with('success', "{$user->name}'s access updated.");
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', "You can't delete your own account.");
        }

        $user->delete();

        return back()->with('success', 'User account deleted.');
    }
}
