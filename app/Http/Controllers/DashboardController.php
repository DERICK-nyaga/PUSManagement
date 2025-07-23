<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\Employee;
use App\Models\Payment;
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

    $upcomingPayments = Payment::with('station')
        ->upcoming(30)
        ->get()
        ->groupBy(function($payment) {
            if ($payment->due_date->isToday()) {
                return 'Due Today';
            } elseif ($payment->due_date->isPast()) {
                return 'Overdue';
            } else {
                return $payment->due_date->diffForHumans();
            }
        });

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

