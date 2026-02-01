<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;

class Header extends Component
{
    public $title;
    public $subtitle;

    public function __construct($title = 'Bill Payment System Dashboard', $subtitle = 'Track and manage all station payments')
    {
        $this->title = $title;
        $this->subtitle = $subtitle;
    }

    public function render()
    {
        return view('components.dashboard.header');
    }
}
