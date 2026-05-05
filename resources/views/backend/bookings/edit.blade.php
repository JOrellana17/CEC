@extends('layouts.backend')

@section('title', 'Edit Booking')

@section('content')
<div class="card border-0 shadow-sm"><div class="card-body">
    <form method="POST" action="{{ route('backend.bookings.update', $booking) }}">@include('backend.bookings._form')</form>
</div></div>
@endsection
