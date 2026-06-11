@extends('layouts.main')

@section('title', 'Revisar Extração')

@section('content')
    <p class="mb-1 text-xs uppercase tracking-widest text-neutral-500">
        Compra de {{ $compra->data->format('d/m/Y') }} — {{ $compra->estabelecimento->nome }}
    </p>
    <h1 class="mb-2 text-3xl font-light tracking-tight">Revisar itens do cupom</h1>
    <p class="mb-8 max-w-2xl text-sm text-neutral-500">
        Confira o que o OCR reconheceu. Ajuste nome, categoria, quantidade e preço de cada produto.
        Desmarque os itens que não devem entrar na compra.
    </p>

    @if (empty($extracao->itens))
        <p class="border border-dashed border-neutral-300 px-6 py-12 text-center text-sm text-neutral-500">
            Nenhum item foi reconhecido nesta imagem. Tente outra foto com mais nitidez.
        </p>
        <div class="mt-6">
            <a href="{{ route('compras.show', $compra) }}"
               class="border border-black px-6 py-2 text-sm uppercase tracking-widest transition hover:bg-neutral-100">
                Voltar
            </a>
        </div>
    @else
        <form action="{{ route('compras.ocr.confirmar', ['compra' => $compra, 'extracao' => $extracao]) }}" method="POST">
            @csrf

            <datalist id="produtos-existentes">
                @foreach ($produtos as $produto)
                    <option value="{{ $produto->nome }}">
                @endforeach
            </datalist>

            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="border-b-2 border-black text-left uppercase tracking-widest">
                        <th class="py-3 pr-4 font-medium">Incluir</th>
                        <th class="py-3 pr-4 font-medium">Produto</th>
                        <th class="py-3 pr-4 font-medium">Categoria</th>
                        <th class="py-3 pr-4 font-medium">Qtd.</th>
                        <th class="py-3 font-medium">Preço (R$)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($extracao->itens as $indice => $item)
                        <tr class="border-b border-neutral-200 align-top">
                            <td class="py-3 pr-4">
                                <input type="hidden" name="itens[{{ $indice }}][incluir]" value="0">
                                <input type="checkbox" name="itens[{{ $indice }}][incluir]" value="1" checked
                                       class="mt-2 border-black text-black focus:ring-black">
                            </td>
                            <td class="py-3 pr-4">
                                <input type="text" name="itens[{{ $indice }}][produto_nome]" list="produtos-existentes"
                                       value="{{ old("itens.$indice.produto_nome", $item['descricao']) }}"
                                       class="w-full border-black text-sm focus:border-black focus:ring-black">
                                @error("itens.$indice.produto_nome")
                                    <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="py-3 pr-4">
                                <input type="text" name="itens[{{ $indice }}][produto_categoria]"
                                       value="{{ old("itens.$indice.produto_categoria", 'Outros') }}"
                                       class="w-full border-black text-sm focus:border-black focus:ring-black">
                            </td>
                            <td class="py-3 pr-4">
                                <input type="number" min="1" name="itens[{{ $indice }}][quantidade]"
                                       value="{{ old("itens.$indice.quantidade", $item['quantidade']) }}"
                                       class="w-24 border-black text-sm focus:border-black focus:ring-black">
                                @error("itens.$indice.quantidade")
                                    <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="py-3">
                                <input type="number" step="0.01" min="0.01" name="itens[{{ $indice }}][preco_pago]"
                                       value="{{ old("itens.$indice.preco_pago", $item['preco_pago']) }}"
                                       class="w-28 border-black text-sm focus:border-black focus:ring-black">
                                @error("itens.$indice.preco_pago")
                                    <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
                                @enderror
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-8 flex gap-4">
                <button type="submit"
                        class="bg-black px-6 py-2 text-sm uppercase tracking-widest text-white transition hover:bg-neutral-700">
                    Confirmar itens
                </button>
                <a href="{{ route('compras.show', $compra) }}"
                   class="border border-black px-6 py-2 text-sm uppercase tracking-widest transition hover:bg-neutral-100">
                    Cancelar
                </a>
            </div>
        </form>
    @endif
@endsection
