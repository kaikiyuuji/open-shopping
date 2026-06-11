<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'OpenShopping')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-black antialiased">
    <header class="border-b border-black">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-5">
            <a href="{{ route('compras.index') }}" class="text-lg font-semibold uppercase tracking-[0.3em]">
                OpenShopping
            </a>
            <nav class="flex gap-8 text-sm uppercase tracking-widest">
                <a href="{{ route('compras.index') }}"
                   class="border-b-2 pb-1 transition {{ request()->routeIs('compras.*') ? 'border-black' : 'border-transparent hover:border-black' }}">
                    Compras
                </a>
                <a href="{{ route('estabelecimentos.index') }}"
                   class="border-b-2 pb-1 transition {{ request()->routeIs('estabelecimentos.*') ? 'border-black' : 'border-transparent hover:border-black' }}">
                    Estabelecimentos
                </a>
                <a href="{{ route('produtos.index') }}"
                   class="border-b-2 pb-1 transition {{ request()->routeIs('produtos.*') ? 'border-black' : 'border-transparent hover:border-black' }}">
                    Produtos
                </a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-6 py-10">
        @if (session('success'))
            <div class="mb-8 border border-black px-4 py-3 text-sm" role="status">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-neutral-200">
        <div class="mx-auto max-w-5xl px-6 py-6 text-xs uppercase tracking-widest text-neutral-400">
            OpenShopping — controle de compras
        </div>
    </footer>
</body>
</html>
