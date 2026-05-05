@extends('layouts.backend')

@section('title', 'Edit Service')

@section('content')
@include('backend.services.form', ['service' => $service, 'action' => route('backend.services.update', $service), 'method' => 'PATCH'])
@endsection
