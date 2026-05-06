@extends('layouts.backend')

@section('title', 'Editar rol')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('backend.roles.update', $role) }}">
            @include('backend.roles._form')
        </form>
    </div>
</div>
@endsection
