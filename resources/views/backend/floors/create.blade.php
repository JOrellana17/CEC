@extends('layouts.backend')

@section('title', 'Crear piso')

@section('content')
<div class="card border-0 shadow-sm"><div class="card-body">
    <form method="POST" action="{{ route('backend.floors.store') }}">@include('backend.floors._form')</form>
</div></div>
@endsection
