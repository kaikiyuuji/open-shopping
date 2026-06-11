@extends('layouts.main')

@section('title', 'Adicionar Item')

@section('content')
    <p class="mb-1 text-xs uppercase tracking-widest text-neutral-500">
        Compra de {{ $compra->data->format('d/m/Y') }} — {{ $compra->estabelecimento->nome }}
    </p>
    <h1 class="mb-8 text-3xl font-light tracking-tight">Adicionar Item</h1>

    <form action="{{ route('compras.itens.store', $compra) }}" method="POST" class="max-w-xl space-y-6">
        @csrf
        @include('itens._form', ['item' => null, 'permitirProdutoNovo' => true])

        <div class="flex gap-4 pt-2">
            <button type="submit"
                    class="bg-black px-6 py-2 text-sm uppercase tracking-widest text-white transition hover:bg-neutral-700">
                Salvar
            </button>
            <a href="{{ route('compras.show', $compra) }}"
               class="border border-black px-6 py-2 text-sm uppercase tracking-widest transition hover:bg-neutral-100">
                Cancelar
            </a>
        </div>
    </form>
@endsection
