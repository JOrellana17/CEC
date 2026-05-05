@extends('layouts.backend')

@section('title', 'Modules')

@section('breadcrumb')
<li class="breadcrumb-item active">Modules</li>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-start border-4 border-primary shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold">Today's Check-ins</div>
                <div class="display-6 fw-bold">{{ $pendingCheckIns }}</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-start border-4 border-success shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold">Today's Check-outs</div>
                <div class="display-6 fw-bold">{{ $pendingCheckOuts }}</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-start border-4 border-info shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold">Available Rooms</div>
                <div class="display-6 fw-bold">{{ $availableRooms }}</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-start border-4 border-warning shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold">Active Reservations</div>
                <div class="display-6 fw-bold">{{ $activeReservations }}</div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="h4 mb-1">Choose a Module</h2>
        <p class="text-muted mb-0">Open the area you want to work with.</p>
    </div>
</div>

<div class="row g-3">
    @foreach($modules as $module)
        @can($module['permission'])
            <div class="col-md-6 col-xl-4">
                <a href="{{ route($module['route']) }}" class="text-decoration-none text-reset">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3">
                                <span class="rounded bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                    <i class="bi {{ $module['icon'] }} fs-4"></i>
                                </span>
                                <div>
                                    <h3 class="h5 mb-1">{{ $module['title'] }}</h3>
                                    <p class="text-muted mb-0">{{ $module['description'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endcan
    @endforeach
</div>
@endsection
