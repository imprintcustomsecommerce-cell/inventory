<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    use ExportsCsv;

    public function index(Request $request)
    {
        $query = Expense::with('supplier', 'user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Default to the current month unless a range is given.
        $month = $request->input('month', now()->format('Y-m'));
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            [$y, $m] = explode('-', $month);
            $query->whereYear('expense_date', $y)->whereMonth('expense_date', $m);
        }

        $expenses = $query->latest('expense_date')->paginate(50)->withQueryString();

        $monthTotal = (float) (clone $query)->sum('amount');
        $byCategory = (clone $query)->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')->orderByDesc('total')->get();

        return view('expenses.index', [
            'expenses' => $expenses,
            'categories' => Expense::CATEGORIES,
            'month' => $month,
            'monthTotal' => $monthTotal,
            'byCategory' => $byCategory,
        ]);
    }

    public function create()
    {
        return view('expenses.form', [
            'expense' => new Expense(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function store(StoreExpenseRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        Expense::create($data);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded.');
    }

    public function edit(Expense $expense)
    {
        return view('expenses.form', [
            'expense' => $expense,
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $expense->update($request->validated());

        return redirect()->route('expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }

    public function export(Request $request)
    {
        $query = Expense::with('supplier');

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }
        $month = $request->input('month', now()->format('Y-m'));
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            [$y, $m] = explode('-', $month);
            $query->whereYear('expense_date', $y)->whereMonth('expense_date', $m);
        }

        $rows = $query->latest('expense_date')->get()->map(fn (Expense $e) => [
            $e->expense_date?->format('Y-m-d'),
            $e->category,
            $e->description,
            $e->supplier?->name,
            $e->payment_method,
            number_format((float) $e->amount, 2, '.', ''),
        ]);

        return $this->streamXlsx(
            'expenses-' . ($month ?: now()->format('Y-m')) . '.xlsx',
            ['Date', 'Category', 'Description', 'Supplier', 'Method', 'Amount'],
            $rows
        );
    }
}
