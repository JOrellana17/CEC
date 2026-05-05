@extends('layouts.backend')

@section('title', 'Edit Floor')

@section('content')
<div class="card border-0 shadow-sm"><div class="card-body">
    <form method="POST" action="{{ route('backend.floors.update', $floor) }}">@include('backend.floors._form')</form>
</div></div>
@endsection
