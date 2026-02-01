<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;

class QuickActions extends Component
{
    public $actions;

    public function __construct()
    {
        $this->actions = [
            ['route' => 'payments.internet.create', 'icon' => 'wifi', 'label' => 'New Internet', 'subtext' => 'Add payment', 'color' => 'primary'],
            ['route' => 'payments.airtime.create', 'icon' => 'phone-alt', 'label' => 'New Airtime', 'subtext' => 'Add topup', 'color' => 'success'],
            ['route' => 'stations.create', 'icon' => 'plus-circle', 'label' => 'Add Station', 'subtext' => 'New station', 'color' => 'info'],
            ['route' => 'internet-providers.create', 'icon' => 'network-wired', 'label' => 'Add Provider', 'subtext' => 'Internet vendor', 'color' => 'warning'],
            ['route' => 'payments.upcoming', 'icon' => 'clock', 'label' => 'Upcoming', 'subtext' => 'View due payments', 'color' => 'danger'],
            ['route' => 'payments.overdue', 'icon' => 'exclamation-triangle', 'label' => 'Overdue', 'subtext' => 'View overdue', 'color' => 'purple']
        ];
    }

    public function render()
    {
        return view('components.dashboard.quick-actions');
    }
}
