@auth
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
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-chart-bar"></i> Reports
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="reportsDropdown">
                                    <li><a class="dropdown-item" href="{{ route('CheckReports') }}">
                                        <i class="fas fa-list me-2"></i> All Reports
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('ViewAll') }}">
                                        <i class="fas fa-tasks me-2"></i> Handle Reports
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('deductions.index') }}">
                                        <i class="fas fa-tasks me-2"></i> Deductions
                                    </a></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('losses.index') }}">
                                            <i class="fas fa-tasks me-2"></i> Loses
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('logout') }}">
                                <i class="fas fa-power-off"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
@endauth
