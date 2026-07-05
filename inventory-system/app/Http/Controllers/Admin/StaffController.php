<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('warehouse');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"));
        }
        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $staff = $query->orderBy('name')->paginate(50)->withQueryString();
        $warehouses = Warehouse::orderBy('name')->get();

        return role_view('admin.staff.index', [
            'staff' => $staff,
            'warehouses' => $warehouses,
            'roles' => User::ROLES,
            'roleLabels' => User::ROLE_LABELS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateStaff($request);
        $data['password'] = Hash::make($data['password']);
        $data['warehouse_id'] = $data['role'] === 'admin' ? null : ($data['warehouse_id'] ?? null);

        User::create($data);

        return back()->with('success', 'Staff account created.');
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validateStaff($request, $user);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        // Don't strip the last admin of their admin role.
        if ($user->isAdmin() && $data['role'] !== 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'You cannot change the role of the last remaining admin.');
        }

        $data['warehouse_id'] = $data['role'] === 'admin' ? null : ($data['warehouse_id'] ?? null);

        $user->update($data);

        return back()->with('success', 'Staff account updated.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'You cannot delete the last remaining admin.');
        }

        $user->delete();

        return back()->with('success', 'Staff account removed.');
    }

    private function validateStaff(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::in(User::ROLES)],
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'password' => $user ? 'nullable|string|min:8' : 'required|string|min:8',
        ]);
    }
}
