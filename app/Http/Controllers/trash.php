<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\DeductionTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\DB;

class DeductionTransactionController extends Controller
{

public function index(Request $request)
{
    $query = DeductionTransaction::with('employee')
                ->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc');

    if ($request->filled('first_name')) {
        $query->where('first_name', $request->first_name);
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
            'refund' => 'Refund',
            'repayment' => 'Payment Made'
        ];

        $typeColors = [
        'initial' => '#007bff',
        'additional' => '#17a2b8',
        'refund' => '#ffc107',
        'repayment' => '#28a745'
    ];
        $employees = Employee::all();;

        return view('deductions.create', compact('employees', 'types'));
    }
public function store(Request $request)
{
    $validated = $request->validate([
        'employee_id' => 'required|string',
        'type' => 'required|string|in:initial,additional,refund,repayment',
        'amount' => 'required|numeric|min:0.01',
        'reason' => 'required|string|max:255',
        'notes' => 'nullable|string',
        'order_number' => 'required|string|max:255',
        'transaction_date' => 'required|date',
    ]);

    // Split the employee_id to get both ID and name
    $employeeData = explode('|', $validated['employee_id']);
    $employeeId = $employeeData[0];

    // Calculate balances
    $previousBalance = $this->getLatestBalance($employeeId);
    $transactionAmount = $validated['amount'];

    // Calculate current balance based on transaction type
    $currentBalance = $this->calculateCurrentBalance(
        $previousBalance,
        $transactionAmount,
        $validated['type']
    );

    $deductionData = [
        'employee_id' => $employeeId,
        'employee_name' => $employeeData[1],
        'type' => $validated['type'],
        'amount' => $transactionAmount,
        'reason' => $validated['reason'],
        'notes' => $validated['notes'],
        'order_number' => $validated['order_number'],
        'transaction_date' => $validated['transaction_date'],
        'previous_balance' => $previousBalance,
        'current_balance' => $currentBalance,
    ];

    DeductionTransaction::create($deductionData);

    return redirect()->route('deductions.index')
        ->with('success', 'Deduction added successfully!');
}

private function getLatestBalance($employeeId)
{
    $latestTransaction = DeductionTransaction::where('employee_id', $employeeId)
        ->latest()
        ->first();

    return $latestTransaction ? $latestTransaction->current_balance : 0;
}

private function calculateCurrentBalance($previousBalance, $amount, $type)
{
    switch ($type) {
        case 'initial':
        case 'additional':
            // For deductions, subtract from balance
            return $previousBalance - $amount;
        case 'refund':
            // For refunds, add to balance
            return $previousBalance + $amount;
        default:
            return $previousBalance;
    }
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

// to reuse

