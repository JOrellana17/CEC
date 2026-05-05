@extends('layouts.backend')

@section('title', 'Create Service')

@section('content')
@include('backend.services.form', ['service' => new \App\Models\Service(), 'action' => route('backend.services.store'), 'method' => 'POST'])
@endsection
