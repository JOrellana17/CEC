@extends('layouts.backend')

@section('title', 'Edit Room Type')

@section('content')
<div class="card border-0 shadow-sm"><div class="card-body">
    <form method="POST" action="{{ route('backend.room-types.update', $roomType) }}">@include('backend.room_types._form')</form>
</div></div>
@endsection
