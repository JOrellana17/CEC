<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SIGEH-PHP') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="px-3 mb-4">
                        <h5 class="text-white">
                            <i class="bi bi-building"></i> {{ config('app.name', 'SIGEH-PHP') }}
                        </h5>
                    </div>

                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('backend.dashboard') ? 'active' : 'text-white' }}"
                               href="{{ route('backend.dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>

                        @can('bookings.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('backend/bookings*') ? 'active' : 'text-white' }}"
                               href="{{ route('backend.bookings.index') }}">
                                <i class="bi bi-calendar-check"></i> Bookings
                            </a>
                        </li>
                        @endcan

                        @can('reservations.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('backend/reservations*') ? 'active' : 'text-white' }}"
                               href="{{ route('backend.reservations.index') }}">
                                <i class="bi bi-calendar-event"></i> Reservations
                            </a>
                        </li>
                        @endcan

                        @can('rooms.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('backend/rooms*') ? 'active' : 'text-white' }}"
                               href="{{ route('backend.rooms.index') }}">
                                <i class="bi bi-house-door"></i> Rooms
                            </a>
                        </li>
                        @endcan

                        @can('guests.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('backend/guests*') ? 'active' : 'text-white' }}"
                               href="{{ route('backend.guests.index') }}">
                                <i class="bi bi-people"></i> Guests
                            </a>
                        </li>
                        @endcan

                        @can('invoices.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('backend/invoices*') ? 'active' : 'text-white' }}"
                               href="{{ route('backend.invoices.index') }}">
                                <i class="bi bi-receipt"></i> Invoices
                            </a>
                        </li>
                        @endcan

                        @can('payments.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('backend/payments*') ? 'active' : 'text-white' }}"
                               href="{{ route('backend.payments.index') }}">
                                <i class="bi bi-cash"></i> Payments
                            </a>
                        </li>
                        @endcan

                        @can('services.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('backend/services*') ? 'active' : 'text-white' }}"
                               href="{{ route('backend.services.index') }}">
                                <i class="bi bi-star"></i> Services
                            </a>
                        </li>
                        @endcan

                        @can('reports.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('backend/reports*') ? 'active' : 'text-white' }}"
                               href="{{ route('backend.reports.index') }}">
                                <i class="bi bi-graph-up"></i> Reports
                            </a>
                        </li>
                        @endcan

                        @can('audit.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('backend/audit-logs*') ? 'active' : 'text-white' }}"
                               href="{{ route('backend.audit.index') }}">
                                <i class="bi bi-clipboard-data"></i> Audit Logs
                            </a>
                        </li>
                        @endcan

                        @can('users.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('backend/users*') ? 'active' : 'text-white' }}"
                               href="{{ route('backend.users.index') }}">
                                <i class="bi bi-person-gear"></i> Users
                            </a>
                        </li>
                        @endcan

                        @can('roles.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('backend/roles*') ? 'active' : 'text-white' }}"
                               href="{{ route('backend.roles.index') }}">
                                <i class="bi bi-shield-check"></i> Roles
                            </a>
                        </li>
                        @endcan

                        @can('permissions.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('backend/permissions*') ? 'active' : 'text-white' }}"
                               href="{{ route('backend.permissions.index') }}">
                                <i class="bi bi-key"></i> Permissions
                            </a>
                        </li>
                        @endcan

                        @can('settings.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('backend/settings*') ? 'active' : 'text-white' }}"
                               href="{{ route('backend.settings.index') }}">
                                <i class="bi bi-gear"></i> Settings
                            </a>
                        </li>
                        @endcan
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('title', 'Dashboard')</h1>

                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="sidebarToggle">
                                <i class="bi bi-list"></i>
                            </button>
                        </div>

                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('backend.users.profile') }}">
                                    <i class="bi bi-person"></i> Profile
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a></li>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Breadcrumb -->
                @hasSection('breadcrumb')
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        @yield('breadcrumb')
                    </ol>
                </nav>
                @endif

                <div class="toast-container position-fixed top-0 end-0 p-3">
                    @foreach(['success' => 'text-bg-success', 'error' => 'text-bg-danger', 'warning' => 'text-bg-warning'] as $key => $class)
                        @if(session($key))
                        <div class="toast {{ $class }} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="d-flex">
                                <div class="toast-body">{{ session($key) }}</div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- Main content -->
                @yield('content')

            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="{{ asset('js/app.js') }}"></script>

    @stack('scripts')
</body>
</html>
