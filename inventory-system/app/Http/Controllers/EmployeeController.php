<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public const STATUSES = ['Active', 'On Leave', 'Inactive'];

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $employees = $query->orderBy('name')->get();

        return view('employees.index', compact('employees'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'position' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'hourly_rate' => 'nullable|numeric|min:0',
            'hire_date' => 'nullable|date',
            'employment_status' => 'required|in:' . implode(',', self::STATUSES),
        ]);

        $user->update($data);

        return back()->with('success', "{$user->name}'s employee details updated.");
    }
}
