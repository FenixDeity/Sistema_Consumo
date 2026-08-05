@extends('layouts.app')
@section('title', 'Dispositivos · Sistema Consumo')
@section('content')
<h1 class="text-2xl font-bold">Dispositivos</h1>
<p class="mt-1 text-sm text-slate-400">Registra o elimina aparatos. El uso diario se captura en la sección Consumo.</p>

<div class="mt-5 grid gap-5 lg:grid-cols-[380px_1fr]">
    <form method="POST" action="{{ route('devices.store') }}" class="card space-y-4">
        @csrf
        <h2 class="text-lg font-bold text-amber-400">Nuevo dispositivo</h2>
        <div>
            <label class="label" for="name">Nombre</label>
            <input class="input" id="name" name="name" required maxlength="60" value="{{ old('name') }}">
        </div>
        <div>
            <label class="label" for="brand">Marca / modelo (opcional)</label>
            <input class="input" id="brand" name="brand" maxlength="60" value="{{ old('brand') }}">
        </div>
        <div class="grid grid-cols-3 gap-2">
            <div>
                <label class="label" for="watts">Watts</label>
                <input class="input" id="watts" name="watts" type="number" step="0.01" min="0" value="{{ old('watts') }}">
            </div>
            <div>
                <label class="label" for="volts">Volts</label>
                <input class="input" id="volts" name="volts" type="number" step="0.01" min="0" value="{{ old('volts', 127) }}">
            </div>
            <div>
                <label class="label" for="amps">Amperes</label>
                <input class="input" id="amps" name="amps" type="number" step="0.01" min="0" value="{{ old('amps') }}">
            </div>
        </div>
        <p class="text-xs text-slate-400">Si no conoces los watts, captura volts y amperes: el sistema los calcula (W = V × A).</p>
        @if ($groups->count())
        <div>
            <label class="label" for="group_id">Compartir con familia</label>
            <select class="input" id="group_id" name="group_id">
                <option value="">Dispositivo personal</option>
                @foreach ($groups as $g)
                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <label class="flex items-start gap-2 text-sm text-slate-200">
            <input type="checkbox" name="always_plugged" value="1" class="mt-1">
            <span>Queda enchufado todo el día (consumo fantasma). El sistema estima automáticamente los watts en espera.</span>
        </label>
        <div>
            <label class="label" for="notes">Notas (opcional)</label>
            <textarea class="input" id="notes" name="notes" rows="2" maxlength="300">{{ old('notes') }}</textarea>
        </div>
        <button class="btn btn-primary w-full">Registrar dispositivo</button>
    </form>

    <section class="card">
        <h2 class="text-lg font-bold text-amber-400">Registrados ({{ $devices->count() }})</h2>
        <div class="mt-3 overflow-x-auto">
            <table class="data">
                <thead>
                <tr><th>Dispositivo</th><th>Potencia</th><th>Fantasma</th><th>Pertenece a</th><th></th></tr>
                </thead>
                <tbody>
                @forelse ($devices as $device)
                    <tr>
                        <td>
                            <span class="font-semibold">{{ $device->name }}</span>
                            @if ($device->brand)<span class="block text-xs text-slate-400">{{ $device->brand }}</span>@endif
                        </td>
                        <td>{{ round($energy->deviceWatts($device), 2) }} W</td>
                        <td>
                            @if ($device->always_plugged)
                                <span class="text-amber-300">{{ round($energy->estimateStandbyWatts($device), 2) }} W en espera</span>
                            @else
                                <span class="text-slate-500">—</span>
                            @endif
                        </td>
                        <td>{{ $device->group?->name ? 'Familia: '.$device->group->name : 'Dispositivo personal' }}</td>
                        <td class="text-right">
                            <form method="POST" action="{{ route('devices.destroy', $device) }}"
                                  onsubmit="return confirm('¿Eliminar {{ $device->name }}? Su historial se conserva en reportes.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-ghost">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-slate-400">Todavía no hay dispositivos registrados.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
