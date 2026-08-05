<?php

namespace App\Http\Controllers;

use App\Models\UsageLog;
use App\Services\EnergyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private EnergyService $energy) {}

    private function period(Request $request): array
    {
        $month = $request->input('month', Carbon::today()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfMonth();

        return [$month, $start, $start->copy()->endOfMonth()];
    }

    public function index(Request $request)
    {
        [$month, $start, $end] = $this->period($request);

        $deviceIds = DeviceController::visibleQuery()->withTrashed()->pluck('id');

        $logs = UsageLog::with('device')
            ->whereIn('device_id', $deviceIds)
            ->whereBetween('log_date', [$start, $end])
            ->orderBy('log_date')
            ->get();

        // Agrupado por dispositivo (incluye dispositivos borrados: historial intacto)
        $rows = $logs->groupBy('device_id')->map(function ($items) {
            $device = $items->first()->device;

            return [
                'name' => $device->name ?? 'Dispositivo eliminado',
                'deleted' => (bool) ($device?->deleted_at),
                'watts' => $device ? $this->energy->deviceWatts($device) : 0,
                'hours' => round($items->sum(fn ($l) => $l->hours()), 2),
                'records' => $items->count(),
                'kwh' => round($items->sum('kwh'), 3),
                'cost' => $this->energy->cost($items->sum('kwh')),
            ];
        })->sortByDesc('kwh')->values();

        // Series para las gráficas del modal
        $daily = $logs->groupBy(fn ($l) => $l->log_date->format('d'))
            ->map(fn ($i) => round($i->sum('kwh'), 3))
            ->sortKeys();

        return view('reports.index', [
            'month' => $month,
            'rows' => $rows,
            'totalKwh' => round($rows->sum('kwh'), 3),
            'totalCost' => round($rows->sum('cost'), 2),
            'chartLabels' => $daily->keys()->values()->all(),
            'chartValues' => $daily->values()->all(),
            'deviceLabels' => $rows->pluck('name')->all(),
            'deviceValues' => $rows->pluck('kwh')->all(),
            'rate' => $this->energy->rate(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$month, $start, $end] = $this->period($request);
        $deviceIds = DeviceController::visibleQuery()->withTrashed()->pluck('id');

        $logs = UsageLog::with('device')
            ->whereIn('device_id', $deviceIds)
            ->whereBetween('log_date', [$start, $end])
            ->orderBy('log_date')
            ->get();

        return response()->streamDownload(function () use ($logs) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Fecha', 'Dispositivo', 'Modo', 'Horas', 'Ciclos', 'kWh', 'Costo']);
            foreach ($logs as $log) {
                fputcsv($out, [
                    $log->log_date->format('Y-m-d'),
                    optional($log->device)->name ?? 'Dispositivo eliminado',
                    $log->mode === 'cycles' ? 'Ciclos' : 'Tiempo',
                    round($log->hours(), 2),
                    $log->cycles ?? '',
                    round($log->kwh, 3),
                    $this->energy->cost($log->kwh),
                ]);
            }
            fclose($out);
        }, "reporte-consumo-{$month}.csv", ['Content-Type' => 'text/csv']);
    }
}
