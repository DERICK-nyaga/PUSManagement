<?php

namespace App\Http\Controllers;

use App\Models\DeductionTransaction;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class DeductionTransactionController extends Controller
{

// index method 5

public function index(Request $request)
{
    $query = DeductionTransaction::with('employee');

    if ($request->filled('employee_first_name')) {
        $query->whereHas('employee', function($q) use ($request) {
            $q->where('first_name', $request->employee_first_name);
        });
    }

    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }

    if ($request->filled('reason')) {
        $query->where('reason', $request->reason);
    }

    if ($request->filled('start_date')) {
        $query->where('transaction_date', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->where('transaction_date', '<=', $request->end_date);
    }

    $transactions = $query->orderBy('transaction_date', 'desc')->paginate(20);

    // Calculate totals properly
    $initialDeductions = $query->clone()->where('type', 'initial')->sum('amount');
    $additionalDeductions = $query->clone()->where('type', 'additional')->sum('amount');
    $adjustments = $query->clone()->where('type', 'refund')->sum('amount');

    // Regular deductions (excluding salary advances)
    $totalDeductions = $initialDeductions + $additionalDeductions + $adjustments;

    // Salary Advances (treated as money employee owes)
    $salaryAdvances = $query->clone()->where('reason', 'salary advance')->sum('amount');
    $salaryAdvanceCount = $query->clone()->where('reason', 'salary advance')->count();

    // Counts for stats
    $regularDeductionCount = $query->clone()
        ->where('reason', '!=', 'salary advance')
        ->where('type', '!=', 'payment')
        ->count();

    $paymentCount = $query->clone()->where('type', 'payment')->count();

    $totalPayments = $query->clone()->where('type', 'payment')->sum('amount');

    // Total Employee Owes = Regular deductions + salary advances
    $totalEmployeeOwes = $totalDeductions + $salaryAdvances;

    // Outstanding Balance = What employee owes minus payments made
    $outstandingBalance = $totalEmployeeOwes - $totalPayments;

    $employees = Employee::orderBy('first_name')->get();

    return view('deductions.index', compact(
        'transactions',
        'employees',
        'initialDeductions',
        'additionalDeductions',
        'adjustments',
        'totalDeductions',
        'salaryAdvances',
        'salaryAdvanceCount',
        'regularDeductionCount',
        'paymentCount',
        'totalEmployeeOwes',
        'totalPayments',
        'outstandingBalance'
    ));
}
    public function create()
    {
        $employees = Employee::all();
        $types = [
            'initial' => 'Initial Deduction',
            'additional' => 'Additional Deduction',
            'refund' => 'Refund',
            'payment' => 'Payment Made',
        ];

        return view('deductions.create', compact('employees', 'types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|string',
            'type' => 'required|string|in:initial,additional,refund,payment',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'order_number' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            // Split the employee_id to get both ID and name
            $employeeData = explode('|', $validated['employee_id']);
            $employeeId = $employeeData[0];
            $employeeName = $employeeData[1];

            // Get the latest transaction to calculate previous balance
            $latestTransaction = $this->getLatestTransaction($employeeId);
            $previousBalance = $latestTransaction ? $latestTransaction->new_balance : 0;

            // Calculate new balance based on transaction type
            $newBalance = $this->calculateNewBalance(
                $previousBalance,
                $validated['amount'],
                $validated['type']
            );

            $deductionData = [
                'employee_id' => $employeeId,
                'employee_name' => $employeeName,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'reason' => $validated['reason'],
                'notes' => $validated['notes'],
                'order_number' => $validated['order_number'],
                'transaction_date' => $validated['transaction_date'],
                'previous_balance' => $previousBalance,
                'new_balance' => $newBalance,
            ];

            $deduction = DeductionTransaction::create($deductionData);

            DB::commit();

            return redirect()->route('deductions.index', $deduction->id)
                ->with('success', 'Deduction transaction recorded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'Error recording transaction: ' . $e->getMessage());
        }
    }

    private function getLatestTransaction($employeeId)
    {
        return DeductionTransaction::where('employee_id', $employeeId)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    private function calculateNewBalance($previousBalance, $amount, $type)
    {
        switch ($type) {
            case 'initial':
            case 'additional':
                // Add to debt (balance becomes more negative)
                return $previousBalance - $amount;

            case 'refund':
                // Reduce debt (balance becomes less negative)
                return $previousBalance + $amount;

            case 'payment':
                // Payment reduces debt (balance becomes less negative)
                return $previousBalance + $amount;

            default:
                return $previousBalance;
        }
    }
    public function show(DeductionTransaction $deduction)
    {
        return view('deductions.show', compact('deduction'));
    }

    public function employeeHistory($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        $transactions = DeductionTransaction::where('employee_id', $employeeId)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $currentBalance = $this->getLatestTransaction($employeeId)?->new_balance ?? 0;

        return view('deductions.history', compact('employee', 'transactions', 'currentBalance'));
    }

public function getCurrentBalance($employeeId)
{
    try {
        Log::info('getCurrentBalance called', ['employeeId' => $employeeId]);

        // Validate employee exists
        $employee = Employee::find($employeeId);
        if (!$employee) {
            Log::warning('Employee not found', ['employeeId' => $employeeId]);
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        $latestTransaction = $this->getLatestTransaction($employeeId);
        $currentBalance = $latestTransaction ? $latestTransaction->new_balance : 0;

        Log::info('Balance fetched successfully', [
            'employeeId' => $employeeId,
            'employee_name' => $employee->first_name . ' ' . $employee->last_name,
            'current_balance' => $currentBalance
        ]);

        return response()->json([
            'success' => true,
            'current_balance' => $currentBalance,
            'formatted_balance' => number_format($currentBalance, 2),
            'employee_name' => $employee->first_name . ' ' . $employee->last_name
        ]);
    } catch (\Exception $e) {
        Log::error('Error in getCurrentBalance', [
            'employeeId' => $employeeId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error fetching balance: ' . $e->getMessage()
        ], 500);
    }
}
}


