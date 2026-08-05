<?php

namespace App\Http\Controllers;

use App\Models\PowerOutage;
use App\Models\UsageLog;
use App\Services\EnergyService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(private EnergyService $energy) {}

    public function index()
    {
        $user = Auth::user();
        $devices = DeviceController::visibleQuery()->get();
        $deviceIds = $devices->pluck('id');

        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();

        $outages = PowerOutage::where('user_id', $user->id)->whereNotNull('ended_at')->get();

        // --- Hoy ---
        $todayLogs = UsageLog::with('device')->whereIn('device_id', $deviceIds)
            ->whereDate('log_date', $today)->get();
        $todayActive = round($todayLogs->sum('kwh'), 3);
        $hoursElapsed = max(1, $today->copy()->startOfDay()->diffInMinutes(now()) / 60);
        $todayOutageHours = $this->energy->outageMinutes($outages, $today->copy()->startOfDay(), now()) / 60;
        $todayStandby = $this->energy->standbyKwh($devices, $hoursElapsed, $todayOutageHours);

        // --- Mes ---
        $monthLogs = UsageLog::with('device')->whereIn('device_id', $deviceIds)
            ->whereBetween('log_date', [$monthStart, $today->copy()->endOfDay()])->get();
        $monthActive = round($monthLogs->sum('kwh'), 3);
        $monthHours = max(1, $monthStart->diffInMinutes(now()) / 60);
        $monthOutageHours = $this->energy->outageMinutes($outages, $monthStart, now()) / 60;
        $monthStandby = $this->energy->standbyKwh($devices, $monthHours, $monthOutageHours);

        $ranking = $todayLogs->groupBy('device_id')->map(function ($logs) {
            return [
                'name' => optional($logs->first()->device)->name ?? 'Dispositivo eliminado',
                'kwh' => round($logs->sum('kwh'), 3),
                'hours' => round($logs->sum(fn ($l) => $l->hours()), 2),
                'records' => $logs->count(),
            ];
        })->sortByDesc('kwh')->values();

        $monthRanking = $monthLogs->groupBy('device_id')->map(function ($logs) {
            return [
                'name' => optional($logs->first()->device)->name ?? 'Dispositivo eliminado',
                'kwh' => round($logs->sum('kwh'), 3),
                'hours' => round($logs->sum(fn ($l) => $l->hours()), 2),
            ];
        })->sortByDesc('kwh')->values();

        return view('dashboard.index', [
            'devices' => $devices,
            'pluggedCount' => $devices->where('always_plugged', true)->count(),
            'today' => [
                'active' => $todayActive,
                'standby' => $todayStandby,
                'total' => round($todayActive + $todayStandby, 3),
                'cost' => $this->energy->cost($todayActive + $todayStandby),
                'records' => $todayLogs->count(),
                'hours' => round($todayLogs->sum(fn ($l) => $l->hours()), 2),
                'outageHours' => round($todayOutageHours, 2),
                'ranking' => $ranking,
            ],
            'month' => [
                'active' => $monthActive,
                'standby' => $monthStandby,
                'total' => round($monthActive + $monthStandby, 3),
                'cost' => $this->energy->cost($monthActive + $monthStandby),
                'records' => $monthLogs->count(),
                'days' => max(1, $monthStart->diffInDays($today) + 1),
                'outageHours' => round($monthOutageHours, 2),
                'ranking' => $monthRanking->take(5),
            ],
            'rate' => $this->energy->rate(),
        ]);
    }
}
