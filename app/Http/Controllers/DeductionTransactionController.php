<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\DeductionTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeductionTransactionController extends Controller
{

public function index(Request $request)
{
    $query = DeductionTransaction::with('employee')
                ->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc');

    if ($request->filled('employee_id')) {
        $query->where('employee_id', $request->employee_id);
    }

    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }

    if ($request->filled('start_date')) {
        $query->where('transaction_date', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->where('transaction_date', '<=', $request->end_date);
    }

    $transactions = $query->paginate(25);

    $totalCredits = DeductionTransaction::where('amount', '<', 0)->sum('amount');
    $totalDebits = DeductionTransaction::where('amount', '>', 0)->sum('amount');
    $netBalance = $totalCredits + $totalDebits;


    $employees = Employee::orderBy('id')->get();

    return view('deductions.index', compact(
        'transactions',
        'employees',
        'totalCredits',
        'totalDebits',
        'netBalance'
    ));
}
    public function create(Employee $employee)
    {
        $types = [
            'initial' => 'Initial Deduction',
            'additional' => 'Additional Deduction',
            'adjustment' => 'Adjustment',
            'payment' => 'Payment (Reduction)'
        ];

        $employees = Employee::all();
        // $employees = Employee::orderBy('name')->get();

        return view('deductions.create', compact('employees', 'types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:initial,additional,adjustment,payment',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'order_number' => 'required',
            'transaction_date' => 'required|date',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        if ($validated['type'] === 'payment') {
            $validated['amount'] = -abs($validated['amount']);
        } else {
            $validated['amount'] = abs($validated['amount']);
        }

        $validated['user_id'] = Auth::id();

        $employee->deductionTransactions()->create($validated);
        $employee->updateBalance();

        return redirect()->route('deductions.index', $employee)
            ->with('success', 'Deduction transaction recorded successfully.');
    }

    public function show(Employee $employee, DeductionTransaction $transaction)
    {
        return view('deductions.show', compact('employee', 'transaction'));
    }

    public function edit(Employee $employee, DeductionTransaction $transaction)
    {
        $types = [
            'initial' => 'Initial Deduction',
            'additional' => 'Additional Deduction',
            'adjustment' => 'Adjustment',
            'payment' => 'Payment (Reduction)'
        ];

        return view('deductions.edit', compact('employee', 'transaction', 'types'));
    }

    public function update(Request $request, Employee $employee, DeductionTransaction $transaction)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:initial,additional,adjustment,payment',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        if ($validated['type'] === 'payment') {
            $validated['amount'] = -abs($validated['amount']);
        } else {
            $validated['amount'] = abs($validated['amount']);
        }

        $transaction->update($validated);
        $employee->updateBalance();

        return redirect()->route('deductions.index', $employee)
            ->with('success', 'Deduction transaction updated successfully.');
    }
    public function employeeTransactions(Employee $employee)
    {
        $transactions = $employee->deductionTransactions()
            ->orderBy('transaction_date', 'desc')
            ->paginate(15);

        $totalCredits = $employee->deductionTransactions()
            ->where('amount', '<', 0)
            ->sum('amount');

        $totalDebits = $employee->deductionTransactions()
            ->where('amount', '>', 0)
            ->sum('amount');

        $netBalance = $totalCredits + $totalDebits;

        return view('deductions.employee_view', compact(
            'employee',
            'transactions',
            'totalCredits',
            'totalDebits',
            'netBalance'
        ));
    }
    public function destroy(Employee $employee, DeductionTransaction $transaction)
    {
        $transaction->delete();
        $employee->updateBalance();

        return back()->with('success', 'Transaction deleted successfully.');
    }
}
