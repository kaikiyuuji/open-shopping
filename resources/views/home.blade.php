@extends('layouts.main')

@section('title', 'OpenShopping')

@section('content')
    <div class="py-10 text-center">
        <h1 class="text-4xl font-light tracking-tight">Controle de compras</h1>
        <p class="mx-auto mt-3 max-w-md text-sm text-neutral-500">
            Registre estabelecimentos, lance compras com seus itens e acompanhe o histórico de preços de cada produto.
        </p>
    </div>

    <div class="grid gap-px border border-black bg-black sm:grid-cols-3">
        <a href="{{ route('compras.index') }}" class="group bg-white p-8 text-center transition hover:bg-black hover:text-white">
            <p class="text-xl font-light">Compras</p>
            <p class="mt-2 text-xs uppercase tracking-widest text-neutral-500 group-hover:text-neutral-300">Lançamentos e itens</p>
        </a>
        <a href="{{ route('estabelecimentos.index') }}" class="group bg-white p-8 text-center transition hover:bg-black hover:text-white">
            <p class="text-xl font-light">Estabelecimentos</p>
            <p class="mt-2 text-xs uppercase tracking-widest text-neutral-500 group-hover:text-neutral-300">Onde você compra</p>
        </a>
        <a href="{{ route('produtos.index') }}" class="group bg-white p-8 text-center transition hover:bg-black hover:text-white">
            <p class="text-xl font-light">Produtos</p>
            <p class="mt-2 text-xs uppercase tracking-widest text-neutral-500 group-hover:text-neutral-300">Histórico de preços</p>
        </a>
    </div>
@endsection
