<div>
    <label for="nome" class="mb-1 block text-xs uppercase tracking-widest">Nome</label>
    <input type="text" name="nome" id="nome" value="{{ old('nome', $produto?->nome) }}"
           class="w-full border-black focus:border-black focus:ring-black">
    @error('nome')
        <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="categoria" class="mb-1 block text-xs uppercase tracking-widest">Categoria</label>
    <input type="text" name="categoria" id="categoria" value="{{ old('categoria', $produto?->categoria) }}"
           class="w-full border-black focus:border-black focus:ring-black" placeholder="Ex.: Alimentos, Bebidas, Limpeza…">
    @error('categoria')
        <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
    @enderror
</div>
