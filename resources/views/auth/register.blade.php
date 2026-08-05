@extends('layouts.app')
@section('title', 'Crear cuenta · Sistema Consumo')
@section('content')
<div class="mx-auto max-w-md">
    <h1 class="text-2xl font-bold">Crear cuenta</h1>
    <form method="POST" action="{{ route('register') }}" class="card mt-4 space-y-4">
        @csrf
        <div>
            <label class="label" for="name">Nombre</label>
            <input class="input" id="name" name="name" required minlength="3" maxlength="60" value="{{ old('name') }}">
            <p class="mt-1 text-xs text-slate-400">Solo letras, espacios, puntos y guiones.</p>
        </div>
        <div>
            <label class="label" for="email">Correo</label>
            <input class="input" id="email" name="email" type="email" required value="{{ old('email') }}">
        </div>
        <div>
            <label class="label" for="password">Contraseña</label>
            <input class="input" id="password" name="password" type="password" required minlength="8">
            <p class="mt-1 text-xs text-slate-400">Mínimo 8 caracteres, mayúscula, minúscula, número y símbolo.</p>
        </div>
        <div>
            <label class="label" for="password_confirmation">Confirmar contraseña</label>
            <input class="input" id="password_confirmation" name="password_confirmation" type="password" required>
        </div>
        <button class="btn btn-amber w-full">Registrarme</button>
        <p class="text-center text-sm text-slate-400">
            ¿Ya tienes cuenta? <a class="text-emerald-400 hover:underline" href="{{ route('login') }}">Entrar</a>
        </p>
    </form>
</div>
@endsection
