<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistema Consumo')</title>
    <meta name="description" content="@yield('description', 'Sistema Consumo: controla y analiza el consumo de energía eléctrica de tu casa.')">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="min-h-dvh bg-[#0b1015] text-slate-100 antialiased">
<div class="min-h-dvh flex flex-col">
    <header class="border-b border-slate-800 bg-[#0e141b]/95 backdrop-blur sticky top-0 z-30">
        <div class="mx-auto max-w-6xl px-4 py-3 flex items-center justify-between gap-4">
            <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="text-lg font-extrabold tracking-tight">
                <span class="text-emerald-400">Sistema</span><span class="text-amber-400">Consumo</span>
            </a>
            @auth
            <nav class="hidden md:flex items-center gap-1 text-sm">
                @foreach ([
                    'dashboard' => 'Dashboard',
                    'consumo.index' => 'Consumo',
                    'devices.index' => 'Dispositivos',
                    'reports.index' => 'Reportes',
                    'groups.index' => 'Compartir',
                ] as $route => $label)
                    <a href="{{ route($route) }}"
                       class="px-3 py-2 rounded-md font-medium transition {{ request()->routeIs($route) ? 'bg-emerald-500/15 text-emerald-300' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">{{ $label }}</a>
                @endforeach
            </nav>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="rounded-md border border-slate-700 px-3 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800">Salir</button>
            </form>
            @else
            <div class="flex gap-2 text-sm">
                <a href="{{ route('login') }}" class="rounded-md px-3 py-2 font-medium text-slate-200 hover:bg-slate-800">Entrar</a>
                <a href="{{ route('register') }}" class="rounded-md bg-amber-500 px-3 py-2 font-semibold text-slate-950 hover:bg-amber-400">Crear cuenta</a>
            </div>
            @endauth
        </div>
        @auth
        <nav class="md:hidden overflow-x-auto border-t border-slate-800 px-3 py-2 flex gap-1 text-sm">
            @foreach ([
                'dashboard' => 'Dashboard',
                'consumo.index' => 'Consumo',
                'devices.index' => 'Dispositivos',
                'reports.index' => 'Reportes',
                'groups.index' => 'Compartir',
            ] as $route => $label)
                <a href="{{ route($route) }}"
                   class="whitespace-nowrap rounded-md px-3 py-2 font-medium {{ request()->routeIs($route) ? 'bg-emerald-500/15 text-emerald-300' : 'text-slate-300' }}">{{ $label }}</a>
            @endforeach
        </nav>
        @endauth
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-6">
        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="border-t border-slate-800 px-4 py-6 text-center text-xs text-slate-400">
        Sistema Consumo · Tarifa configurada: ${{ number_format(config('energy.rate'), 2) }} por kWh
    </footer>
</div>
@stack('scripts')
</body>
</html>
