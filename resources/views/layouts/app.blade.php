<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pickup Points - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
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
                            <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}"
               href="{{ route('dashboard') }}">
                        </li>
                        <li class="nav-item">
                    <a class="nav-link {{ request()->is('stations*') ? 'active' : '' }}"
               href="{{ route('stations.index') }}">
                <i class="fas fa-map-marker-alt"></i> Stations
            </a>
                        </li>
    <li class="nav-item">
    <a class="nav-link {{ request()->is('employees*') ? 'active' : '' }}"
       href="{{ route('employees.index') }}">
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
                    </ul>
                </div>
            </div>

            <div class="col-md-10 main-content p-4">
                @yield('content')
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
