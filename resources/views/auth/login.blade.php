@extends('layouts.app')
@section('title', 'Iniciar sesión · Sistema Consumo')
@section('content')
<div class="mx-auto max-w-md">
    <h1 class="text-2xl font-bold">Iniciar sesión</h1>
    <form method="POST" action="{{ route('login') }}" class="card mt-4 space-y-4">
        @csrf
        <div>
            <label class="label" for="email">Correo</label>
            <input class="input" id="email" name="email" type="email" required autocomplete="email" value="{{ old('email') }}">
        </div>
        <div>
            <label class="label" for="password">Contraseña</label>
            <input class="input" id="password" name="password" type="password" required autocomplete="current-password">
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-300">
            <input type="checkbox" name="remember" value="1"> Mantener sesión
        </label>
        <button class="btn btn-primary w-full">Entrar</button>
        <p class="text-center text-sm text-slate-400">
            ¿Sin cuenta? <a class="text-amber-400 hover:underline" href="{{ route('register') }}">Crear cuenta</a>
        </p>
    </form>
</div>
@endsection
