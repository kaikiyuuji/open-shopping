@extends('layouts.main')

@section('title', 'Editar Item')

@section('content')
    <p class="mb-1 text-xs uppercase tracking-widest text-neutral-500">
        Compra de {{ $compra->data->format('d/m/Y') }} — {{ $compra->estabelecimento->nome }}
    </p>
    <h1 class="mb-8 text-3xl font-light tracking-tight">Editar Item</h1>

    <form action="{{ route('compras.itens.update', ['compra' => $compra, 'item' => $item]) }}" method="POST"
          class="max-w-xl space-y-6">
        @csrf
        @method('PUT')
        @include('itens._form', ['permitirProdutoNovo' => false])

        <div class="flex gap-4 pt-2">
            <button type="submit"
                    class="bg-black px-6 py-2 text-sm uppercase tracking-widest text-white transition hover:bg-neutral-700">
                Atualizar
            </button>
            <a href="{{ route('compras.show', $compra) }}"
               class="border border-black px-6 py-2 text-sm uppercase tracking-widest transition hover:bg-neutral-100">
                Cancelar
            </a>
        </div>
    </form>
@endsection
