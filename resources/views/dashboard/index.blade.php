@extends('layouts.app')
@section('title', 'Dashboard · Sistema Consumo')
@section('content')
<h1 class="text-2xl font-bold"><span class="text-emerald-400">Sistema</span> <span class="text-amber-400">Consumo</span></h1>
<p class="mt-1 text-sm text-slate-400">Resumen de consumo real registrado. Tarifa ${{ number_format($rate, 2) }}/kWh.</p>

<div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ([
        ['Consumo de hoy', $today['total'].' kWh', '$'.number_format($today['cost'], 2).' estimado'],
        ['Consumo del mes', $month['total'].' kWh', '$'.number_format($month['cost'], 2).' estimado'],
        ['Dispositivos', $devices->count(), $pluggedCount.' siempre enchufados'],
        ['Sin luz (mes)', $month['outageHours'].' h', 'descontadas del cálculo'],
    ] as [$t, $v, $s])
    <article class="card">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $t }}</p>
        <p class="mt-2 text-2xl font-bold text-emerald-300">{{ $v }}</p>
        <p class="mt-1 text-xs text-slate-400">{{ $s }}</p>
    </article>
    @endforeach
</div>

<div class="mt-6 grid gap-4 lg:grid-cols-2">
    <section class="card">
        <h2 class="text-lg font-bold text-amber-400">Resumen de hoy</h2>
        <dl class="mt-3 divide-y divide-slate-800 text-sm">
            @foreach ([
                'Consumo en uso' => $today['active'].' kWh',
                'Consumo fantasma (enchufado sin usar)' => $today['standby'].' kWh',
                'Horas de uso registradas' => $today['hours'].' h',
                'Registros capturados' => $today['records'],
                'Horas sin luz' => $today['outageHours'].' h',
                'Costo estimado' => '$'.number_format($today['cost'], 2),
            ] as $k => $v)
            <div class="flex items-center justify-between gap-4 py-2">
                <dt class="text-slate-300">{{ $k }}</dt><dd class="font-semibold text-slate-50">{{ $v }}</dd>
            </div>
            @endforeach
        </dl>
        <h3 class="mt-4 text-sm font-semibold text-slate-200">Mayor consumo hoy</h3>
        @forelse ($today['ranking'] as $r)
            <div class="mt-2 flex items-center justify-between text-sm">
                <span class="text-slate-300">{{ $r['name'] }} <span class="text-slate-500">· {{ $r['hours'] }} h</span></span>
                <span class="font-semibold text-amber-300">{{ $r['kwh'] }} kWh</span>
            </div>
        @empty
            <p class="mt-2 text-sm text-slate-400">Aún no registras uso hoy. Ve a <a class="text-emerald-400 hover:underline" href="{{ route('consumo.index') }}">Consumo</a>.</p>
        @endforelse
    </section>

    <section class="card">
        <h2 class="text-lg font-bold text-amber-400">Resumen del mes</h2>
        <dl class="mt-3 divide-y divide-slate-800 text-sm">
            @foreach ([
                'Consumo en uso' => $month['active'].' kWh',
                'Consumo fantasma' => $month['standby'].' kWh',
                'Promedio diario' => round($month['total'] / $month['days'], 3).' kWh',
                'Días considerados' => $month['days'],
                'Registros del mes' => $month['records'],
                'Costo estimado' => '$'.number_format($month['cost'], 2),
            ] as $k => $v)
            <div class="flex items-center justify-between gap-4 py-2">
                <dt class="text-slate-300">{{ $k }}</dt><dd class="font-semibold text-slate-50">{{ $v }}</dd>
            </div>
            @endforeach
        </dl>
        <h3 class="mt-4 text-sm font-semibold text-slate-200">Top 5 del mes</h3>
        @forelse ($month['ranking'] as $r)
            <div class="mt-2 flex items-center justify-between text-sm">
                <span class="text-slate-300">{{ $r['name'] }} <span class="text-slate-500">· {{ $r['hours'] }} h</span></span>
                <span class="font-semibold text-emerald-300">{{ $r['kwh'] }} kWh</span>
            </div>
        @empty
            <p class="mt-2 text-sm text-slate-400">Sin registros este mes.</p>
        @endforelse
    </section>
</div>
@endsection
