<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pickup Points Management Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --warning-color: #f8961e;
            --info-color: #0dcaf0;
            --light-bg: #f8f9fa;
            --dark-bg: #212529;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;

            --purple-color: #6f42c1;
            --teal-color: #20c997;
            --pink-color: #d63384;
            --indigo-color: #6610f2;
            --orange-color: #fd7e14;
            --cyan-color: #0dcaf0;
            --gray-color: #6c757d;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            color: #333;
        }

        .sidebar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            min-height: 100vh;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .sidebar-brand {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 5px;
            margin: 0.2rem 0;
            transition: var(--transition);
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .main-content {
            padding: 2rem;
            background-color: var(--light-bg);
        }

        .dashboard-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }

        .stat-card {
            border: none;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            overflow: hidden;
            margin-bottom: 1.5rem;
            height: 100%;
            border-left: 4px solid;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .stat-card .card-body {
            padding: 1.25rem;
        }

        .card-primary { border-left-color: var(--primary-color); }
        .card-success { border-left-color: var(--success-color); }
        .card-danger { border-left-color: var(--danger-color); }
        .card-warning { border-left-color: var(--warning-color); }
        .card-info { border-left-color: var(--info-color); }
        .card-purple { border-left-color: var(--purple-color); }
        .card-teal { border-left-color: var(--teal-color); }
        .card-pink { border-left-color: var(--pink-color); }
        .card-indigo { border-left-color: var(--indigo-color); }
        .card-orange { border-left-color: var(--orange-color); }
        .card-cyan { border-left-color: var(--cyan-color); }
        .card-gray { border-left-color: var(--gray-color); }

        .text-purple { color: var(--purple-color); }
        .text-teal { color: var(--teal-color); }
        .text-pink { color: var(--pink-color); }
        .text-indigo { color: var(--indigo-color); }
        .text-orange { color: var(--orange-color); }
        .text-cyan { color: var(--cyan-color); }
        .text-gray { color: var(--gray-color); }

        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .table thead th {
            border-bottom: 2px solid #e0e0e0;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            color: #6c757d;
        }

        .badge-pill {
            padding: 5px 10px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .positive {
            color: #28a745;
            font-weight: 600;
        }

        .negative {
            color: #dc3545;
            font-weight: 600;
        }

        .station-card {
            border: none;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .station-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .station-card .card-body {
            padding: 1.25rem;
        }

        .payment-badge {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 0.7rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }

        .search-box {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .search-box input {
            padding-left: 40px;
            border-radius: 20px;
            border: 1px solid #e0e0e0;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 10px;
            color: #6c757d;
        }

        .progress {
            background-color: #e9ecef;
            border-radius: 2px;
            height: 4px;
        }

        .icon-container {
            background-color: rgba(0, 0, 0, 0.05);
            padding: 8px;
            border-radius: 8px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                width: 100%;
            }
            .main-content {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">

            <div class="col-md-2 sidebar p-0">
                <div class="sidebar-brand">
                    <h4 class="text-center mb-0">
                        <i class="fas fa-store-alt"></i> PickupPoints
                    </h4>
                </div>
                <div class="p-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('stations.index') }}">
                                <i class="fas fa-map-marker-alt"></i> Stations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('employees.index') }}">
                                <i class="fas fa-users"></i> Employees
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-laptop"></i> Equipment
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-money-bill-wave"></i> Payments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

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

                <div class="row">
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

                <div class="row">
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
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple animation for cards when they come into view
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card, .station-card');

            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>
