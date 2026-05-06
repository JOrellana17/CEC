@extends('layouts.backend')

@section('title', 'Resumen de pagos')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4"><input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}"></div>
            <div class="col-md-4"><input type="date" name="date_to" class="form-control" value="{{ $dateTo }}"></div>
            <div class="col-md-4"><button class="btn btn-primary w-100">Refresh</button></div>
        </form>
        <table class="table">
            @foreach($summary as $key => $value)
            <tr>
                <th>{{ ucfirst(str_replace('_', ' ', $key)) }}</th>
                <td>{{ $key === 'count' ? $value : '$'.number_format((float) $value, 2) }}</td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection
