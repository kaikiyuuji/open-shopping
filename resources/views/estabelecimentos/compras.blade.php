@extends('layouts.main')

@section('title', 'Compras — ' . $estabelecimento->nome)

@section('content')
    <div class="mb-8 flex items-end justify-between">
        <div>
            <p class="mb-1 text-xs uppercase tracking-widest text-neutral-500">Compras de</p>
            <h1 class="text-3xl font-light tracking-tight">{{ $estabelecimento->nome }}</h1>
        </div>
        <a href="{{ route('estabelecimentos.index') }}"
           class="border border-black px-4 py-2 text-xs uppercase tracking-widest transition hover:bg-neutral-100">
            Voltar
        </a>
    </div>

    @if ($compras->isEmpty())
        <p class="border border-dashed border-neutral-300 px-6 py-12 text-center text-sm text-neutral-500">
            Nenhuma compra registrada para este estabelecimento.
        </p>
    @else
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="border-b-2 border-black text-left uppercase tracking-widest">
                    <th class="py-3 pr-4 font-medium">Data</th>
                    <th class="py-3 pr-4 font-medium">Valor</th>
                    <th class="py-3 pr-4 font-medium">Pagamento</th>
                    <th class="py-3 text-right font-medium">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($compras as $compra)
                    <tr class="border-b border-neutral-200 transition hover:bg-neutral-50">
                        <td class="py-3 pr-4">{{ $compra->data->format('d/m/Y') }}</td>
                        <td class="py-3 pr-4">R$ {{ number_format($compra->valor_total, 2, ',', '.') }}</td>
                        <td class="py-3 pr-4 capitalize">
                            {{ $compra->forma_pagamento }}
                            @if ($compra->parcelado)
                                · {{ $compra->numero_parcelas }}x de R$ {{ number_format($compra->valor_parcela, 2, ',', '.') }}
                            @endif
                        </td>
                        <td class="py-3 text-right text-xs uppercase tracking-widest">
                            <a href="{{ route('compras.show', $compra) }}" class="underline-offset-4 hover:underline">Detalhes</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
