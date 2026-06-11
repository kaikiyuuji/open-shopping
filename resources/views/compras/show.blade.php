@extends('layouts.main')

@section('title', 'Compra — ' . $compra->estabelecimento->nome)

@section('content')
    <div class="mb-8 flex items-end justify-between">
        <div>
            <p class="mb-1 text-xs uppercase tracking-widest text-neutral-500">
                {{ $compra->data->format('d/m/Y') }} · {{ $compra->categoria }}
            </p>
            <h1 class="text-3xl font-light tracking-tight">{{ $compra->estabelecimento->nome }}</h1>
        </div>
        <div class="flex gap-3 text-xs uppercase tracking-widest">
            <a href="{{ route('compras.edit', $compra) }}"
               class="border border-black px-4 py-2 transition hover:bg-neutral-100">Editar</a>
            <a href="{{ route('compras.index') }}"
               class="border border-black px-4 py-2 transition hover:bg-neutral-100">Voltar</a>
        </div>
    </div>

    <dl class="mb-10 grid grid-cols-2 gap-px border border-black bg-black sm:grid-cols-4">
        <div class="bg-white p-4">
            <dt class="text-xs uppercase tracking-widest text-neutral-500">Valor total</dt>
            <dd class="mt-1 text-xl">R$ {{ number_format($compra->valor_total, 2, ',', '.') }}</dd>
        </div>
        <div class="bg-white p-4">
            <dt class="text-xs uppercase tracking-widest text-neutral-500">Pagamento</dt>
            <dd class="mt-1 text-xl capitalize">{{ $compra->forma_pagamento }}</dd>
        </div>
        <div class="bg-white p-4">
            <dt class="text-xs uppercase tracking-widest text-neutral-500">Parcelamento</dt>
            <dd class="mt-1 text-xl">
                @if ($compra->parcelado)
                    {{ $compra->numero_parcelas }}x
                @else
                    À vista
                @endif
            </dd>
        </div>
        <div class="bg-white p-4">
            <dt class="text-xs uppercase tracking-widest text-neutral-500">Valor da parcela</dt>
            <dd class="mt-1 text-xl">
                @if ($compra->parcelado)
                    R$ {{ number_format($compra->valor_parcela, 2, ',', '.') }}
                @else
                    —
                @endif
            </dd>
        </div>
    </dl>

    <div class="mb-4 flex items-end justify-between border-b-2 border-black pb-2">
        <h2 class="text-sm uppercase tracking-widest">Itens da compra</h2>
        <a href="{{ route('compras.itens.create', $compra) }}"
           class="bg-black px-4 py-1.5 text-xs uppercase tracking-widest text-white transition hover:bg-neutral-700">
            Adicionar item
        </a>
    </div>

    @if ($compra->itens->isEmpty())
        <p class="border border-dashed border-neutral-300 px-6 py-12 text-center text-sm text-neutral-500">
            Nenhum item lançado nesta compra.
        </p>
    @else
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="border-b border-neutral-300 text-left uppercase tracking-widest">
                    <th class="py-3 pr-4 font-medium">Produto</th>
                    <th class="py-3 pr-4 font-medium">Categoria</th>
                    <th class="py-3 pr-4 font-medium">Quantidade</th>
                    <th class="py-3 pr-4 font-medium">Preço pago</th>
                    <th class="py-3 pr-4 font-medium">Subtotal</th>
                    <th class="py-3 text-right font-medium">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($compra->itens as $item)
                    <tr class="border-b border-neutral-200 transition hover:bg-neutral-50">
                        <td class="py-3 pr-4">
                            <a href="{{ route('produtos.show', $item->produto) }}" class="underline-offset-4 hover:underline">
                                {{ $item->produto->nome }}
                            </a>
                        </td>
                        <td class="py-3 pr-4 text-neutral-600">{{ $item->produto->categoria }}</td>
                        <td class="py-3 pr-4">{{ $item->quantidade }}</td>
                        <td class="py-3 pr-4">R$ {{ number_format($item->preco_pago, 2, ',', '.') }}</td>
                        <td class="py-3 pr-4">R$ {{ number_format($item->preco_pago * $item->quantidade, 2, ',', '.') }}</td>
                        <td class="py-3 text-right">
                            <div class="flex justify-end gap-3 text-xs uppercase tracking-widest">
                                <a href="{{ route('compras.itens.edit', ['compra' => $compra, 'item' => $item]) }}"
                                   class="underline-offset-4 hover:underline">Editar</a>
                                <form action="{{ route('compras.itens.destroy', ['compra' => $compra, 'item' => $item]) }}"
                                      method="POST" onsubmit="return confirm('Excluir este item?');">
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

    <div class="mt-12 mb-4 border-b-2 border-black pb-2">
        <h2 class="text-sm uppercase tracking-widest">Importar cupom fiscal (OCR)</h2>
    </div>

    <form action="{{ route('compras.ocr.store', $compra) }}" method="POST" enctype="multipart/form-data"
          class="flex flex-wrap items-end gap-4">
        @csrf
        <div>
            <label for="imagem" class="mb-1 block text-xs uppercase tracking-widest">Foto do cupom ou nota</label>
            <input type="file" name="imagem" id="imagem" accept="image/*"
                   class="block text-sm file:mr-4 file:border file:border-black file:bg-white file:px-4 file:py-2 file:text-xs file:uppercase file:tracking-widest hover:file:bg-neutral-100">
            @error('imagem')
                <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit"
                class="bg-black px-5 py-2 text-xs uppercase tracking-widest text-white transition hover:bg-neutral-700">
            Extrair itens
        </button>
    </form>

    @if ($compra->extracoes->isNotEmpty())
        <table class="mt-6 w-full border-collapse text-sm">
            <thead>
                <tr class="border-b border-neutral-300 text-left uppercase tracking-widest">
                    <th class="py-3 pr-4 font-medium">Enviado em</th>
                    <th class="py-3 pr-4 font-medium">Status</th>
                    <th class="py-3 pr-4 font-medium">Itens reconhecidos</th>
                    <th class="py-3 text-right font-medium">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($compra->extracoes->sortByDesc('id') as $extracao)
                    <tr class="border-b border-neutral-200">
                        <td class="py-3 pr-4">{{ $extracao->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-3 pr-4">
                            <span class="border border-black px-2 py-0.5 text-xs uppercase tracking-widest">
                                {{ $extracao->status }}
                            </span>
                            @if ($extracao->status === 'falhou' && $extracao->erro)
                                <p class="mt-1 text-xs text-neutral-500">{{ Str::limit($extracao->erro, 120) }}</p>
                            @endif
                        </td>
                        <td class="py-3 pr-4">{{ $extracao->itens ? count($extracao->itens) : '—' }}</td>
                        <td class="py-3 text-right">
                            <div class="flex justify-end gap-3 text-xs uppercase tracking-widest">
                                @if ($extracao->status === 'concluida')
                                    <a href="{{ route('compras.ocr.revisar', ['compra' => $compra, 'extracao' => $extracao]) }}"
                                       class="bg-black px-3 py-1 text-white transition hover:bg-neutral-700">Revisar</a>
                                @endif
                                @if ($extracao->status === 'processando')
                                    <a href="{{ route('compras.show', $compra) }}" class="underline-offset-4 hover:underline">Atualizar</a>
                                @endif
                                <form action="{{ route('compras.ocr.destroy', ['compra' => $compra, 'extracao' => $extracao]) }}"
                                      method="POST" onsubmit="return confirm('Descartar esta extração?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="underline-offset-4 hover:underline">Descartar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
