<div>
    <label for="nome" class="mb-1 block text-xs uppercase tracking-widest">Nome</label>
    <input type="text" name="nome" id="nome" value="{{ old('nome', $estabelecimento?->nome) }}"
           class="w-full border-black focus:border-black focus:ring-black">
    @error('nome')
        <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="endereco" class="mb-1 block text-xs uppercase tracking-widest">Endereço</label>
    <input type="text" name="endereco" id="endereco" value="{{ old('endereco', $estabelecimento?->endereco) }}"
           class="w-full border-black focus:border-black focus:ring-black">
    @error('endereco')
        <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="categoria" class="mb-1 block text-xs uppercase tracking-widest">Categoria</label>
    <select name="categoria" id="categoria" class="w-full border-black focus:border-black focus:ring-black">
        <option value="">Selecione…</option>
        @foreach ($categorias as $categoria)
            <option value="{{ $categoria }}" @selected(old('categoria', $estabelecimento?->categoria) === $categoria)>
                {{ $categoria }}
            </option>
        @endforeach
    </select>
    @error('categoria')
        <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
    @enderror
</div>
