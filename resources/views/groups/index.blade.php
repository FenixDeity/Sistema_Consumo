@extends('layouts.app')
@section('title', 'Compartir · Sistema Consumo')
@section('content')
<h1 class="text-2xl font-bold">Compartir</h1>
<p class="mt-1 text-sm text-slate-400">Crea una familia y comparte el código para que todos vean el consumo de la casa.</p>

<div class="mt-5 grid gap-5 lg:grid-cols-2">
    <form method="POST" action="{{ route('groups.store') }}" class="card space-y-3">
        @csrf
        <h2 class="text-lg font-bold text-amber-400">Crear familia</h2>
        <div>
            <label class="label" for="name">Nombre de la familia / casa</label>
            <input class="input" id="name" name="name" required minlength="3" maxlength="60" value="{{ old('name') }}">
        </div>
        <button class="btn btn-primary w-full">Crear</button>
    </form>

    <form method="POST" action="{{ route('groups.join') }}" class="card space-y-3">
        @csrf
        <h2 class="text-lg font-bold text-amber-400">Unirme con código</h2>
        <div>
            <label class="label" for="code">Código de 6 caracteres</label>
            <input class="input uppercase" id="code" name="code" required minlength="6" maxlength="6" placeholder="ABC123">
        </div>
        <button class="btn btn-amber w-full">Unirme</button>
    </form>
</div>

<section class="card mt-5">
    <h2 class="text-lg font-bold text-amber-400">Mis familias</h2>
    <div class="mt-3 overflow-x-auto">
        <table class="data">
            <thead><tr><th>Familia</th><th>Código</th><th>Integrantes</th><th>Dispositivos</th><th></th></tr></thead>
            <tbody>
            @forelse ($groups as $group)
                <tr>
                    <td class="font-semibold">{{ $group->name }}</td>
                    <td><code class="rounded bg-slate-800 px-2 py-1 text-amber-300">{{ $group->code }}</code></td>
                    <td>{{ $group->members->pluck('name')->join(', ') }}</td>
                    <td>{{ $group->devices_count }}</td>
                    <td class="text-right">
                        <form method="POST" action="{{ route('groups.leave', $group) }}" onsubmit="return confirm('¿Salir de esta familia?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-ghost">Salir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-slate-400">Aún no perteneces a ninguna familia.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
