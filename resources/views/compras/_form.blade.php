@php
    $formaInicial = old('forma_pagamento', $compra?->forma_pagamento ?? '');
    $parceladoInicial = (bool) old('parcelado', $compra?->parcelado ?? false);
@endphp

<div x-data="{ forma: '{{ $formaInicial }}', parcelado: {{ $parceladoInicial ? 'true' : 'false' }} }" class="space-y-6">
    <div>
        <label for="estabelecimento_id" class="mb-1 block text-xs uppercase tracking-widest">Estabelecimento</label>
        <select name="estabelecimento_id" id="estabelecimento_id" class="w-full border-black focus:border-black focus:ring-black">
            <option value="">Selecione…</option>
            @foreach ($estabelecimentos as $estabelecimento)
                <option value="{{ $estabelecimento->id }}"
                        @selected((int) old('estabelecimento_id', $compra?->estabelecimento_id) === $estabelecimento->id)>
                    {{ $estabelecimento->nome }} — {{ $estabelecimento->categoria }}
                </option>
            @endforeach
        </select>
        @error('estabelecimento_id')
            <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-2 gap-6">
        <div>
            <label for="data" class="mb-1 block text-xs uppercase tracking-widest">Data</label>
            <input type="date" name="data" id="data"
                   value="{{ old('data', $compra?->data?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                   class="w-full border-black focus:border-black focus:ring-black">
            @error('data')
                <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="valor_total" class="mb-1 block text-xs uppercase tracking-widest">Valor total (R$)</label>
            <input type="number" step="0.01" min="0.01" name="valor_total" id="valor_total"
                   value="{{ old('valor_total', $compra?->valor_total) }}"
                   class="w-full border-black focus:border-black focus:ring-black">
            @error('valor_total')
                <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="forma_pagamento" class="mb-1 block text-xs uppercase tracking-widest">Forma de pagamento</label>
        <select name="forma_pagamento" id="forma_pagamento" x-model="forma"
                @change="if (forma !== 'credito') parcelado = false"
                class="w-full border-black focus:border-black focus:ring-black">
            <option value="">Selecione…</option>
            @foreach (\App\Models\Compra::formasPagamento() as $forma)
                <option value="{{ $forma }}" @selected($formaInicial === $forma)>{{ ucfirst($forma) }}</option>
            @endforeach
        </select>
        @error('forma_pagamento')
            <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
        @enderror
    </div>

    <div x-show="forma === 'credito'" x-cloak>
        <input type="hidden" name="parcelado" value="0">
        <label class="flex items-center gap-3 text-sm">
            <input type="checkbox" name="parcelado" value="1" x-model="parcelado"
                   class="border-black text-black focus:ring-black">
            <span class="uppercase tracking-widest text-xs">Compra parcelada</span>
        </label>
        @error('parcelado')
            <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
        @enderror
    </div>

    <div x-show="forma === 'credito' && parcelado" x-cloak>
        <label for="numero_parcelas" class="mb-1 block text-xs uppercase tracking-widest">Número de parcelas</label>
        <input type="number" min="2" name="numero_parcelas" id="numero_parcelas"
               value="{{ old('numero_parcelas', $compra?->numero_parcelas) }}"
               class="w-full border-black focus:border-black focus:ring-black">
        <p class="mt-1 text-xs text-neutral-500">O valor de cada parcela é calculado automaticamente.</p>
        @error('numero_parcelas')
            <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
        @enderror
    </div>
</div>
