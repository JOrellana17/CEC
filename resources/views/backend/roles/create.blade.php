@extends('layouts.backend')

@section('title', 'Create Role')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('backend.roles.store') }}">
            @include('backend.roles._form')
        </form>
    </div>
</div>
@endsection
