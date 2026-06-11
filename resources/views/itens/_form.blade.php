@php
    $produtoNovoInicial = (bool) old('produto_novo', false);
@endphp

<div x-data="{ produtoNovo: {{ $produtoNovoInicial ? 'true' : 'false' }} }" class="space-y-6">
    @if ($permitirProdutoNovo ?? false)
        <div>
            <input type="hidden" name="produto_novo" value="0">
            <label class="flex items-center gap-3 text-sm">
                <input type="checkbox" name="produto_novo" value="1" x-model="produtoNovo"
                       class="border-black text-black focus:ring-black">
                <span class="text-xs uppercase tracking-widest">Cadastrar produto novo</span>
            </label>
        </div>
    @endif

    <div x-show="!produtoNovo">
        <label for="produto_id" class="mb-1 block text-xs uppercase tracking-widest">Produto</label>
        <select name="produto_id" id="produto_id" :disabled="produtoNovo"
                class="w-full border-black focus:border-black focus:ring-black">
            <option value="">Selecione…</option>
            @foreach ($produtos as $produto)
                <option value="{{ $produto->id }}"
                        @selected((int) old('produto_id', $item?->produto_id) === $produto->id)>
                    {{ $produto->nome }} — {{ $produto->categoria }}
                </option>
            @endforeach
        </select>
        @error('produto_id')
            <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
        @enderror
    </div>

    @if ($permitirProdutoNovo ?? false)
        <div x-show="produtoNovo" x-cloak class="space-y-6 border border-neutral-300 p-4">
            <div>
                <label for="produto_nome" class="mb-1 block text-xs uppercase tracking-widest">Nome do novo produto</label>
                <input type="text" name="produto_nome" id="produto_nome" value="{{ old('produto_nome') }}"
                       class="w-full border-black focus:border-black focus:ring-black">
                @error('produto_nome')
                    <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="produto_categoria" class="mb-1 block text-xs uppercase tracking-widest">Categoria do novo produto</label>
                <input type="text" name="produto_categoria" id="produto_categoria" value="{{ old('produto_categoria') }}"
                       class="w-full border-black focus:border-black focus:ring-black">
                @error('produto_categoria')
                    <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    @endif

    <div class="grid grid-cols-2 gap-6">
        <div>
            <label for="quantidade" class="mb-1 block text-xs uppercase tracking-widest">Quantidade</label>
            <input type="number" min="1" name="quantidade" id="quantidade"
                   value="{{ old('quantidade', $item?->quantidade) }}"
                   class="w-full border-black focus:border-black focus:ring-black">
            @error('quantidade')
                <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="preco_pago" class="mb-1 block text-xs uppercase tracking-widest">Preço pago (R$)</label>
            <input type="number" step="0.01" min="0.01" name="preco_pago" id="preco_pago"
                   value="{{ old('preco_pago', $item?->preco_pago) }}"
                   class="w-full border-black focus:border-black focus:ring-black">
            @error('preco_pago')
                <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
