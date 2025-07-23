@extends('layouts.app')

    @section('content')

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 main-content">
                    <div class="dashboard-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h2 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Dashboard Overview</h2>
                            </div>

                            <div class="col-md-6 text-end">
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-calendar-alt me-1"></i> Last 30 Days
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li><a class="dropdown-item" href="#">Today</a></li>
                                        <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                                        <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                                        <li><a class="dropdown-item" href="#">This Month</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="row">
                        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-4">
                            <div class="stat-card card-primary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-subtitle mb-2 text-muted">Total Stations</h6>
                                            <h2 class="mb-0">{{ $totalStations }}</h2>
                                            <p class="card-text text-muted mb-0"><small>2 new this month</small></p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-store text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="progress mt-3">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-4">
                            <div class="stat-card card-success">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-subtitle mb-2 text-muted">Profitable</h6>
                                            <h2 class="mb-0">{{ $profitableStations }}</h2>
                                            <p class="card-text text-muted mb-0"><small>56% of total</small></p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-chart-line text-success"></i>
                                        </div>
                                    </div>
                                    <div class="progress mt-3">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 56%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-4">
                            <div class="stat-card card-danger">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-subtitle mb-2 text-muted">Loss Making</h6>
                                            <h2 class="mb-0">{{ $stationsWithDeductions }}Stations</h2>
                                            <p class="card-text text-muted mb-0"><small></small></p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-chart-line text-danger"></i>
                                        </div>
                                    </div>
                                    <div class="progress mt-3">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 44%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-4">
                            <div class="stat-card card-warning">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-subtitle mb-2 text-muted">Total Employees</h6>
                                            <h2 class="mb-0">{{ $totalEmployees }}</h2>
                                            <p class="card-text text-muted mb-0"><small>Avg 1.3/station</small></p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-users text-warning"></i>
                                        </div>
                                    </div>
                                    <div class="progress mt-3">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 65%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-4">
                            <div class="stat-card card-info">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-subtitle mb-2 text-muted">Monthly Payroll</h6>
                                            <h2 class="mb-0">{{ $totalMonthlyPayroll }}</h2>
                                            <p class="card-text text-muted mb-0"><small>Avg KES 37.5K</small></p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-money-bill-wave text-info"></i>
                                        </div>
                                    </div>
                                    <div class="progress mt-3">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 82%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-4">
                            <div class="stat-card card-purple">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-subtitle mb-2 text-muted">Total Deductions</h6>
                                            <h2 class="mb-0">KES 28K</h2>
                                            <p class="card-text text-muted mb-0"><small>14 stations</small></p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-hand-holding-usd text-purple"></i>
                                        </div>
                                    </div>
                                    <div class="progress mt-3">
                                        <div class="progress-bar bg-purple" role="progressbar" style="width: 28%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-4">
                            <div class="stat-card card-teal">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-subtitle mb-2 text-muted">Pending Payments</h6>
                                            <h2 class="mb-0">5</h2>
                                            <p class="card-text text-muted mb-0"><small>KES 42.5K total</small></p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-exclamation-circle text-teal"></i>
                                        </div>
                                    </div>
                                    <div class="progress mt-3">
                                        <div class="progress-bar bg-teal" role="progressbar" style="width: 15%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-4">
                            <div class="stat-card card-pink">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-subtitle mb-2 text-muted">Equipment Due</h6>
                                            <h2 class="mb-0">7</h2>
                                            <p class="card-text text-muted mb-0"><small>3 maintenance</small></p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-tools text-pink"></i>
                                        </div>
                                    </div>
                                    <div class="progress mt-3">
                                        <div class="progress-bar bg-pink" role="progressbar" style="width: 21%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-4">
                            <div class="stat-card card-indigo">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-subtitle mb-2 text-muted">Avg. Profit</h6>
                                            <h2 class="mb-0">KES 8.2K</h2>
                                            <p class="card-text text-muted mb-0"><small>Per station</small></p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-coins text-indigo"></i>
                                        </div>
                                    </div>
                                    <div class="progress mt-3">
                                        <div class="progress-bar bg-indigo" role="progressbar" style="width: 62%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-4">
                            <div class="stat-card card-orange">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-subtitle mb-2 text-muted">Avg. Loss</h6>
                                            <h2 class="mb-0">KES 5.7K</h2>
                                            <p class="card-text text-muted mb-0"><small>Per station</small></p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-exclamation-triangle text-orange"></i>
                                        </div>
                                    </div>
                                    <div class="progress mt-3">
                                        <div class="progress-bar bg-orange" role="progressbar" style="width: 38%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-4">
                            <div class="stat-card card-cyan">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-subtitle mb-2 text-muted">Top Station</h6>
                                            <h2 class="mb-0">PP-012</h2>
                                            <p class="card-text text-muted mb-0"><small>KES 24.5K profit</small></p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-trophy text-cyan"></i>
                                        </div>
                                    </div>
                                    <div class="progress mt-3">
                                        <div class="progress-bar bg-cyan" role="progressbar" style="width: 95%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-4">
                            <div class="stat-card card-gray">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-subtitle mb-2 text-muted">Worst Station</h6>
                                            <h2 class="mb-0">PP-027</h2>
                                            <p class="card-text text-muted mb-0"><small>KES 12.3K loss</small></p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-exclamation text-gray"></i>
                                        </div>
                                    </div>
                                    <div class="progress mt-3">
                                        <div class="progress-bar bg-gray" role="progressbar" style="width: 15%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5><i class="fas fa-map-marked-alt me-2"></i>All Stations Performance</h5>
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" placeholder="Search stations...">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Station ID</th>
                                        <th>Location</th>
                                        <th>Employees</th>
                                        <th>Monthly P/L</th>
                                        <th>Deductions</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stations as $station)
                                    <tr>
                                        <td>PP-{{ str_pad($station->id, 3, '0', STR_PAD_LEFT) }}</td>
                                        <td>
                                            <small class="text-muted">{{ $station->location }}</small>
                                        </td>
                                        <td>{{ $station->employees_count }}</td>
                                        <td class="{{ $station->monthly_loss >= 0 ? 'positive' : 'negative' }}">
                                            KES {{ number_format($station->monthly_loss, 2) }}
                                        </td>
                                        <td>KES {{ number_format($station->deductions, 2) }}</td>
                                        <td>
                                            <span class="badge rounded-pill {{ $station->monthly_loss >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                {{ $station->monthly_loss >= 0 ? 'Profitable' : 'Loss' }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                                            <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-end mt-3">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>

                    {{-- <div class="row">
                        <div class="col-md-12">
                            <div class="table-container">
                                <h5><i class="fas fa-calendar-check me-2"></i>Upcoming Payments & Bills</h5>
                                <div class="row">
                                    @foreach($upcomingPayments as $payment)
                                    <div class="col-xl-3 col-lg-4 col-md-6">
                                        <div class="station-card card mb-3">
                                            <span class="badge bg-{{ $payment->due_date->isToday() ? 'danger' : ($payment->due_date->isPast() ? 'warning' : 'primary') }} payment-badge">
                                                {{ $payment->due_date->isToday() ? 'Due Today' : ($payment->due_date->isPast() ? 'Overdue' : $payment->due_date->diffForHumans()) }}
                                            </span>
                                            <div class="card-body">
                                                <h6 class="card-subtitle mb-2 text-muted">{{ $payment->station->name }}</h6>
                                                <h5 class="card-title">KES {{ number_format($payment->amount, 2) }}</h5>
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        <i class="fas fa-{{ $payment->type === 'Salary' ? 'user-tie' : ($payment->type === 'Utility' ? 'bolt' : 'file-invoice-dollar') }} me-1"></i>
                                                        {{ $payment->type }}
                                                    </small><br>
                                                    <i class="far fa-calendar-alt me-1"></i>
                                                    Due: {{ $payment->due_date->format('d M Y') }}<br>
                                                    <i class="far fa-user me-1"></i>
                                                    {{ $payment->recipient ?? 'N/A' }}
                                                </p>
                                                <div class="d-flex justify-content-between">
                                                    <button class="btn btn-sm btn-success">Mark Paid</button>
                                                    <button class="btn btn-sm btn-outline-secondary">Details</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div> --}}
                    <!-- resources/views/dashboard.blade.php -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-check me-2"></i>Upcoming Payments & Bills
                        </h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    id="paymentsPeriodDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Next 30 Days
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="paymentsPeriodDropdown">
                                <li><a class="dropdown-item" href="?days=7">Next 7 Days</a></li>
                                <li><a class="dropdown-item" href="?days=14">Next 14 Days</a></li>
                                <li><a class="dropdown-item" href="?days=30">Next 30 Days</a></li>
                                <li><a class="dropdown-item" href="?days=60">Next 60 Days</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        @foreach($upcomingPayments as $group => $payments)
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">{{ $group }}</h6>
                            <div class="row">
                                @foreach($payments as $payment)
                                <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                                    <div class="card h-100 border-start-{{ $payment->due_date->isToday() ? 'danger' : ($payment->due_date->isPast() ? 'warning' : 'primary') }} border-start-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <span class="badge bg-{{ $payment->due_date->isToday() ? 'danger' : ($payment->due_date->isPast() ? 'warning' : 'primary') }}">
                                                        {{ $payment->due_date->isToday() ? 'Due Today' : ($payment->due_date->isPast() ? 'Overdue' : $payment->due_date->diffForHumans()) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="badge bg-light text-dark">
                                                        {{ $payment->type }}
                                                    </span>
                                                </div>
                                            </div>
                                            <h5 class="mt-2">KES {{ number_format($payment->amount, 2) }}</h5>
                                            <p class="mb-1">
                                                <i class="fas fa-store me-1"></i>
                                                {{ $payment->station->name }}
                                            </p>
                                            @if($payment->recipient)
                                            <p class="mb-1">
                                                <i class="fas fa-user me-1"></i>
                                                {{ $payment->recipient }}
                                            </p>
                                            @endif
                                            @if($payment->description)
                                            <p class="text-muted small mb-2">{{ Str::limit($payment->description, 50) }}</p>
                                            @endif
                                            <div class="d-flex justify-content-between mt-3">
                                                <small class="text-muted">
                                                    Due: {{ $payment->due_date->format('M d, Y') }}
                                                </small>
                                                <div>
                                                    <button class="btn btn-sm btn-outline-success mark-paid"
                                                            data-payment-id="{{ $payment->id }}">
                                                        <i class="fas fa-check"></i> Paid
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                        @if($upcomingPayments->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>No upcoming payments</h5>
                            <p class="text-muted">All payments are settled for the selected period</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

                </div>
                </div>
            </div>
    @section('content')
