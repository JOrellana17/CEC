@extends('layouts.backend')

@section('title', 'Create Booking')

@section('content')
<div class="card border-0 shadow-sm"><div class="card-body">
    <form method="POST" action="{{ route('backend.bookings.store') }}">@include('backend.bookings._form')</form>
</div></div>
@endsection
