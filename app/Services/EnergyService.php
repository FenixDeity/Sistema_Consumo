<?php

namespace App\Services;

use App\Models\Device;
use App\Models\PowerOutage;
use App\Models\UsageLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Reglas de negocio de consumo eléctrico.
 * Centraliza los cálculos para que controladores y vistas queden limpios.
 */
class EnergyService
{
    public function rate(): float
    {
        return (float) config('energy.rate', 2.85);
    }

    /** Watts reales del dispositivo (si sólo hay volts y amps, se derivan). */
    public function deviceWatts(Device $device): float
    {
        if ($device->watts > 0) {
            return (float) $device->watts;
        }

        return (float) (($device->volts ?? 0) * ($device->amps ?? 0));
    }

    /**
     * Estimación automática del consumo fantasma (standby) en watts.
     * El usuario sólo indica si el aparato queda enchufado; el sistema calcula.
     */
    public function estimateStandbyWatts(Device $device): float
    {
        if (! $device->always_plugged) {
            return 0.0;
        }

        if ($device->standby_watts > 0) {
            return min((float) $device->standby_watts, 15.0);
        }

        $name = mb_strtolower($device->name.' '.$device->brand);
        $table = [
            'refriger' => 2.0, 'nevera' => 2.0, 'congelador' => 2.0,
            'tv' => 1.5, 'televis' => 1.5, 'pantalla' => 1.5, 'monitor' => 1.0,
            'consola' => 3.0, 'playstation' => 3.0, 'xbox' => 3.0,
            'microondas' => 3.0, 'horno' => 2.5, 'cafeter' => 1.5,
            'lavadora' => 1.0, 'secadora' => 1.0, 'lavavaj' => 1.0,
            'modem' => 5.0, 'router' => 5.0, 'wifi' => 5.0,
            'cargador' => 0.5, 'celular' => 0.5, 'laptop' => 1.0,
            'bocina' => 2.0, 'audio' => 2.0, 'equipo de sonido' => 2.5,
            'impresora' => 2.0, 'aire' => 3.0, 'clima' => 3.0, 'venti' => 0.8,
        ];

        foreach ($table as $key => $watts) {
            if (str_contains($name, $key)) {
                return $watts;
            }
        }

        // Valor conservador por defecto para cualquier aparato enchufado
        return 1.0;
    }

    /** kWh de un registro de uso. */
    public function logKwh(UsageLog $log, Device $device): float
    {
        return round($this->deviceWatts($device) * $log->hours() / 1000, 4);
    }

    /** kWh a partir de datos crudos del formulario. */
    public function computeKwh(float $watts, string $mode, ?int $minutes, ?int $cycles, ?int $cycleMinutes): float
    {
        $hours = $mode === 'cycles'
            ? (($cycles ?? 0) * ($cycleMinutes ?? 0)) / 60
            : ($minutes ?? 0) / 60;

        return round($watts * $hours / 1000, 4);
    }

    /** Minutos de apagón registrados en un rango (se descuentan del standby). */
    public function outageMinutes(Collection $outages, Carbon $from, Carbon $to): int
    {
        return (int) $outages
            ->filter(fn (PowerOutage $o) => $o->ended_at && $o->started_at->lt($to) && $o->ended_at->gt($from))
            ->sum(function (PowerOutage $o) use ($from, $to) {
                $start = $o->started_at->greaterThan($from) ? $o->started_at : $from;
                $end = $o->ended_at->lessThan($to) ? $o->ended_at : $to;

                return max(0, $start->diffInMinutes($end));
            });
    }

    /**
     * Consumo fantasma de un conjunto de dispositivos durante N horas,
     * descontando las horas sin luz.
     */
    public function standbyKwh(Collection $devices, float $hours, float $outageHours = 0): float
    {
        $effective = max(0, $hours - $outageHours);
        $watts = $devices->sum(fn (Device $d) => $this->estimateStandbyWatts($d));

        return round($watts * $effective / 1000, 4);
    }

    public function cost(float $kwh): float
    {
        return round($kwh * $this->rate(), 2);
    }
}
