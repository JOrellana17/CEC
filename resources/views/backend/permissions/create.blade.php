@extends('layouts.backend')

@section('title', 'Create Permission')

@section('content')
<div class="card border-0 shadow-sm"><div class="card-body">
    <form method="POST" action="{{ route('backend.permissions.store') }}">@include('backend.permissions._form')</form>
</div></div>
@endsection
