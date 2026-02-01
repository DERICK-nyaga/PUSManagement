<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;

class StatCard extends Component
{
    public $key;
    public $label;
    public $icon;
    public $color;
    public $subtext;
    public $format;
    public $progress;
    public $stats;

    public function __construct($key, $label, $icon, $color = 'primary', $subtext = '', $format = 'number', $progress = 75, $stats = [])
    {
        $this->key = $key;
        $this->label = $label;
        $this->icon = $icon;
        $this->color = $color;
        $this->subtext = $subtext;
        $this->format = $format;
        $this->progress = $progress;
        $this->stats = $stats;
    }

    public function value()
    {
        if ($this->format === 'currency') {
            return 'KES ' . number_format($this->stats[$this->key] ?? 0, 0);
        } elseif ($this->key === 'top_station') {
            return $this->stats[$this->key] ?? 'N/A';
        } else {
            return $this->stats[$this->key] ?? 0;
        }
    }

    public function render()
    {
        return view('components.dashboard.stat-card');
    }
}
