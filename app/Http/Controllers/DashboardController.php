<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\Payment;
use App\Models\Employee;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stations = Station::withCount('employees')->get();

        $totalStations = Station::count();
        $totalEmployees = Employee::count();
        $profitableStations = Station::where('monthly_loss', '>=', 0)->count();
        $stationsWithDeductions = Station::where('deductions', '>', 0)->count();
        $totalMonthlyPayroll = Employee::sum('salary');
        // upcoming payments within next 7 days
        $upcomingPayments = Payment::with('station')
            ->where('due_date', '<=', Carbon::now()->addDays(7))
            ->orderBy('due_date')
            ->get();

        return view('dashboard', [
            'totalStations' => $totalStations,
            'totalEmployees' => $totalEmployees,
            'profitableStations' => $profitableStations,
            'stationsWithDeductions' => $stationsWithDeductions,
            'totalMonthlyPayroll' => $totalMonthlyPayroll,
            'stations' => $stations,
            'upcomingPayments' => $upcomingPayments
        ]);

    }
}
