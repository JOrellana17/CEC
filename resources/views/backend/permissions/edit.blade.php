@extends('layouts.backend')

@section('title', 'Editar permiso')

@section('content')
<div class="card border-0 shadow-sm"><div class="card-body">
    <form method="POST" action="{{ route('backend.permissions.update', $permission) }}">@include('backend.permissions._form')</form>
</div></div>
@endsection
