@extends('layouts.app')
@section('title', 'Sistema Consumo · Control de energía eléctrica del hogar')
@section('content')
<section class="py-10">
    <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight">
        <span class="text-emerald-400">Sistema</span> <span class="text-amber-400">Consumo</span>
    </h1>
    <p class="mt-4 max-w-2xl text-slate-300">
        Registra el uso diario de tus aparatos, descubre el consumo fantasma de lo que dejas enchufado,
        controla apagones y comparte las estadísticas de tu casa con toda la familia.
    </p>
    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('register') }}" class="btn btn-amber">Crear cuenta</a>
        <a href="{{ route('login') }}" class="btn btn-ghost">Ya tengo cuenta</a>
    </div>

    <h2 class="mt-12 text-xl font-bold text-slate-100">Características</h2>
    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ([
            ['Dashboard del día', 'Resumen detallado de consumo activo, fantasma y costo estimado de hoy y del mes.'],
            ['Dispositivos', 'Registra potencia, voltaje y amperaje; el sistema calcula lo que falte.'],
            ['Consumo', 'Registra tiempo de uso o ciclos (por ejemplo 5 veces de 3 horas), sin límite de horas.'],
            ['Consumo fantasma', 'Marca lo que queda enchufado y el sistema estima automáticamente los watts en espera.'],
            ['Apagones', 'Interruptor en tiempo real o registro manual con duración estimada.'],
            ['Reportes y familia', 'Reporte mensual con gráficas, exportación CSV y grupos compartidos por código.'],
        ] as [$t, $d])
        <article class="card">
            <h3 class="font-semibold text-emerald-300">{{ $t }}</h3>
            <p class="mt-2 text-sm text-slate-300">{{ $d }}</p>
        </article>
        @endforeach
    </div>
</section>
@endsection
