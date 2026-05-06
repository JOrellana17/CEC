@extends('layouts.backend')

@section('title', 'Editar servicio')

@section('content')
@include('backend.services.form', ['service' => $service, 'action' => route('backend.services.update', $service), 'method' => 'PATCH'])
@endsection
