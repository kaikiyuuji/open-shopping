@extends('layouts.main')

@section('title', 'Compras')

@section('content')
    <div class="mb-8 flex items-end justify-between">
        <h1 class="text-3xl font-light tracking-tight">Compras</h1>
        <a href="{{ route('compras.create') }}"
           class="bg-black px-5 py-2 text-sm uppercase tracking-widest text-white transition hover:bg-neutral-700">
            Nova
        </a>
    </div>

    @if ($compras->isEmpty())
        <p class="border border-dashed border-neutral-300 px-6 py-12 text-center text-sm text-neutral-500">
            Nenhuma compra registrada.
        </p>
    @else
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="border-b-2 border-black text-left uppercase tracking-widest">
                    <th class="py-3 pr-4 font-medium">Data</th>
                    <th class="py-3 pr-4 font-medium">Estabelecimento</th>
                    <th class="py-3 pr-4 font-medium">Categoria</th>
                    <th class="py-3 pr-4 font-medium">Valor</th>
                    <th class="py-3 pr-4 font-medium">Pagamento</th>
                    <th class="py-3 text-right font-medium">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($compras as $compra)
                    <tr class="border-b border-neutral-200 transition hover:bg-neutral-50">
                        <td class="py-3 pr-4">{{ $compra->data->format('d/m/Y') }}</td>
                        <td class="py-3 pr-4">
                            <a href="{{ route('compras.show', $compra) }}" class="underline-offset-4 hover:underline">
                                {{ $compra->estabelecimento->nome }}
                            </a>
                        </td>
                        <td class="py-3 pr-4 text-neutral-600">{{ $compra->categoria }}</td>
                        <td class="py-3 pr-4">R$ {{ number_format($compra->valor_total, 2, ',', '.') }}</td>
                        <td class="py-3 pr-4 capitalize">
                            {{ $compra->forma_pagamento }}
                            @if ($compra->parcelado)
                                · {{ $compra->numero_parcelas }}x
                            @endif
                        </td>
                        <td class="py-3 text-right">
                            <div class="flex justify-end gap-3 text-xs uppercase tracking-widest">
                                <a href="{{ route('compras.show', $compra) }}" class="underline-offset-4 hover:underline">Detalhes</a>
                                <a href="{{ route('compras.edit', $compra) }}" class="underline-offset-4 hover:underline">Editar</a>
                                <form action="{{ route('compras.destroy', $compra) }}" method="POST"
                                      onsubmit="return confirm('Excluir esta compra?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="underline-offset-4 hover:underline">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
