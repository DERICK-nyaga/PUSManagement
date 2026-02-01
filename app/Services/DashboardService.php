<?php

namespace App\Services;

use App\Models\Station;
use App\Models\AirtimePayment;
use App\Models\InternetPayment;
use App\Models\InternetProvider;
use Carbon\Carbon;

class DashboardService
{
    public function getDashboardData()
    {
        // Copy the logic from Api/DashboardController index method
        $today = now()->startOfDay();
        $sevenDaysFromNow = now()->addDays(7)->endOfDay();
        $threeDaysFromNow = now()->addDays(3)->endOfDay();

        // Get expiring airtime
        $expiringAirtime = AirtimePayment::with('station')
            ->where('status', 'active')
            ->whereBetween('expected_expiry', [$today, $sevenDaysFromNow])
            ->orderBy('expected_expiry')
            ->get();

        // Get due internet bills
        $dueInternet = InternetPayment::with(['station', 'provider'])
            ->where('status', 'pending')
            ->whereBetween('due_date', [$today, $sevenDaysFromNow])
            ->orderBy('due_date')
            ->get();

        // Get overdue internet bills
        $overdueInternet = InternetPayment::with(['station', 'provider'])
            ->where('status', 'pending')
            ->where('due_date', '<', $today)
            ->orderBy('due_date')
            ->get();

        // Get stations
        $stations = Station::with([
            'internetPayments' => function($query) use ($today, $sevenDaysFromNow) {
                $query->where('status', 'pending')
                    ->whereBetween('due_date', [$today, $sevenDaysFromNow]);
            },
            'airtimePayments' => function($query) use ($today, $sevenDaysFromNow) {
                $query->where('status', 'active')
                    ->whereBetween('expected_expiry', [$today, $sevenDaysFromNow]);
            }
        ])->paginate(10);

        // Top station
        $topStation = Station::withSum(['internetPayments as internet_total' => function($query) {
                $query->where('status', 'paid');
            }], 'total_due')
            ->withSum(['airtimePayments as airtime_total' => function($query) {
                $query->where('status', 'active');
            }], 'amount')
            ->get()
            ->map(function($station) {
                return [
                    'station' => $station,
                    'total_payments' => ($station->internet_total ?? 0) + ($station->airtime_total ?? 0)
                ];
            })
            ->sortByDesc('total_payments')
            ->first();

        $stats = [
            'total_stations' => Station::count(),
            'total_internet_payments' => InternetPayment::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total_due') ?? 0,
            'internet_due_soon' => $dueInternet->count(),
            'internet_overdue' => $overdueInternet->count(),
            'internet_paid' => InternetPayment::where('status', 'paid')->count(),
            'total_internet' => InternetPayment::count(),
            'total_airtime_payments' => AirtimePayment::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount') ?? 0,
            'airtime_expiring_soon' => AirtimePayment::where('status', 'active')
                ->whereBetween('expected_expiry', [$today, $threeDaysFromNow])
                ->count(),
            'airtime_active' => AirtimePayment::where('status', 'active')->count(),
            'total_airtime' => AirtimePayment::count(),
            'total_payments' => InternetPayment::count(),
            'paid_payments' => InternetPayment::where('status', 'paid')->count(),
            'pending_payments' => InternetPayment::where('status', 'pending')->count(),
            'upcoming_payments' => $dueInternet->count(),
            'overdue_payments' => $overdueInternet->count(),
            'top_station' => $topStation['station']->name ?? 'N/A',
            'top_station_total' => $topStation['total_payments'] ?? 0,
            'average_payment' => InternetPayment::where('status', 'paid')->avg('total_due') ?? 0,
            'total_providers' => InternetProvider::count(),
            'scheduled_payments' => 0,
            'scheduled_completed' => 0,
            'upcoming_schedules' => 0,
            'total_pending' => InternetPayment::where('status', 'pending')->count(),
        ];

        // Group data
        $expiringAirtimeGrouped = $expiringAirtime->groupBy(function($item) {
            $days = now()->diffInDays($item->expected_expiry, false);
            if ($days <= 0) return 'Today';
            if ($days == 1) return 'Tomorrow';
            if ($days <= 3) return 'In ' . $days . ' Days';
            return 'Later This Week';
        });

        // Upcoming payments section
        $upcomingInternet = $dueInternet->take(5);
        $upcomingAirtime = $expiringAirtime->take(5);

        // Recent payments
        $recentInternet = InternetPayment::with('station')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentAirtime = AirtimePayment::with('station')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'stats' => $stats,
            'stations' => $stations,
            'expiring_airtime' => $expiringAirtimeGrouped,
            'due_internet' => $dueInternet->groupBy(function($item) {
                $days = now()->diffInDays($item->due_date, false);
                if ($days <= 0) return 'Today';
                if ($days == 1) return 'Tomorrow';
                if ($days <= 3) return 'In ' . $days . ' Days';
                return 'Due Later This Week';
            }),
            'overdue_internet' => $overdueInternet,
            'upcoming_internet' => $upcomingInternet,
            'upcoming_airtime' => $upcomingAirtime,
            'recent_internet' => $recentInternet,
            'recent_airtime' => $recentAirtime
        ];
    }
}
