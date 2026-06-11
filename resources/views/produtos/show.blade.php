@extends('layouts.main')

@section('title', $produto->nome)

@section('content')
    <div class="mb-8 flex items-end justify-between">
        <div>
            <p class="mb-1 text-xs uppercase tracking-widest text-neutral-500">{{ $produto->categoria }}</p>
            <h1 class="text-3xl font-light tracking-tight">{{ $produto->nome }}</h1>
        </div>
        <div class="flex gap-3 text-xs uppercase tracking-widest">
            <a href="{{ route('produtos.edit', $produto) }}"
               class="border border-black px-4 py-2 transition hover:bg-neutral-100">Editar</a>
            <a href="{{ route('produtos.index') }}"
               class="border border-black px-4 py-2 transition hover:bg-neutral-100">Voltar</a>
        </div>
    </div>

    <h2 class="mb-4 border-b-2 border-black pb-2 text-sm uppercase tracking-widest">Histórico de compras</h2>

    @if ($historico->isEmpty())
        <p class="border border-dashed border-neutral-300 px-6 py-12 text-center text-sm text-neutral-500">
            Este produto ainda não apareceu em nenhuma compra.
        </p>
    @else
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="border-b border-neutral-300 text-left uppercase tracking-widest">
                    <th class="py-3 pr-4 font-medium">Data</th>
                    <th class="py-3 pr-4 font-medium">Estabelecimento</th>
                    <th class="py-3 pr-4 font-medium">Quantidade</th>
                    <th class="py-3 pr-4 font-medium">Preço pago</th>
                    <th class="py-3 text-right font-medium">Compra</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($historico as $item)
                    <tr class="border-b border-neutral-200 transition hover:bg-neutral-50">
                        <td class="py-3 pr-4">{{ $item->compra->data->format('d/m/Y') }}</td>
                        <td class="py-3 pr-4">{{ $item->compra->estabelecimento->nome }}</td>
                        <td class="py-3 pr-4">{{ $item->quantidade }}</td>
                        <td class="py-3 pr-4">R$ {{ number_format($item->preco_pago, 2, ',', '.') }}</td>
                        <td class="py-3 text-right text-xs uppercase tracking-widest">
                            <a href="{{ route('compras.show', $item->compra) }}" class="underline-offset-4 hover:underline">Ver</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
