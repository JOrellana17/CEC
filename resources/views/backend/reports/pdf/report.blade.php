<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #172033; font-size: 12px; }
        h1 { margin: 0 0 4px; font-size: 24px; }
        .muted { color: #667085; }
        .summary { margin: 18px 0; padding: 12px; background: #f3f6f9; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        th { background: #172033; color: #fff; }
    </style>
</head>
<body>
    <h1>{{ $report['title'] }}</h1>
    <div class="muted">Generated {{ $generatedAt->format('Y-m-d H:i') }} | {{ $filters['date_from'] }} to {{ $filters['date_to'] }}</div>

    <div class="summary">
        @if($type === 'operational')
            Ocupación: {{ number_format($report['occupancy_rate'], 2) }}% | Habitaciones disponibles: {{ $report['available_rooms']->count() }} | Reservaciones activas: {{ $report['active_reservations']->count() }}
        @elseif($type === 'financial')
            Revenue: {{ number_format($report['total_revenue'], 2) }} | Invoiced: {{ number_format($report['total_invoiced'], 2) }} | Outstanding: {{ number_format($report['total_outstanding'], 2) }}
        @else
            Huéspedes frecuentes: {{ $report['most_frequent_guests']->count() }} | Cancelaciones: {{ $report['total_cancellations'] }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                @foreach(array_keys($rows->first() ?? ['Sin datos' => '']) as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td>No hay datos disponibles para este reporte.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
