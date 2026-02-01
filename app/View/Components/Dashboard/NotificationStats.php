<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;

class NotificationStats extends Component
{
    public $stats;

    public function __construct($stats = [])
    {
        $this->stats = $stats;
    }

    public function items()
    {
        return [
            ['key' => 'airtime_expiring_soon', 'icon' => 'phone-alt', 'label' => 'Airtime Expiring', 'color' => 'warning'],
            ['key' => 'internet_due_soon', 'icon' => 'wifi', 'label' => 'Internet Due Soon', 'color' => 'info'],
            ['key' => 'internet_overdue', 'icon' => 'exclamation-triangle', 'label' => 'Overdue Bills', 'color' => 'danger', 'bg_danger' => true],
            ['key' => 'upcoming_schedules', 'icon' => 'calendar-alt', 'label' => 'Upcoming Schedules', 'color' => 'primary']
        ];
    }

    public function value($key)
    {
        return $this->stats[$key] ?? 0;
    }

    public function render()
    {
        return view('components.dashboard.notification-stats');
    }
}
