@extends('layouts.main')

@section('title', 'Produtos')

@section('content')
    <div class="mb-8 flex items-end justify-between">
        <h1 class="text-3xl font-light tracking-tight">Produtos</h1>
        <a href="{{ route('produtos.create') }}"
           class="bg-black px-5 py-2 text-sm uppercase tracking-widest text-white transition hover:bg-neutral-700">
            Novo
        </a>
    </div>

    @if ($produtos->isEmpty())
        <p class="border border-dashed border-neutral-300 px-6 py-12 text-center text-sm text-neutral-500">
            Nenhum produto cadastrado.
        </p>
    @else
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="border-b-2 border-black text-left uppercase tracking-widest">
                    <th class="py-3 pr-4 font-medium">Nome</th>
                    <th class="py-3 pr-4 font-medium">Categoria</th>
                    <th class="py-3 text-right font-medium">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($produtos as $produto)
                    <tr class="border-b border-neutral-200 transition hover:bg-neutral-50">
                        <td class="py-3 pr-4">
                            <a href="{{ route('produtos.show', $produto) }}" class="underline-offset-4 hover:underline">
                                {{ $produto->nome }}
                            </a>
                        </td>
                        <td class="py-3 pr-4 text-neutral-600">{{ $produto->categoria }}</td>
                        <td class="py-3 text-right">
                            <div class="flex justify-end gap-3 text-xs uppercase tracking-widest">
                                <a href="{{ route('produtos.show', $produto) }}" class="underline-offset-4 hover:underline">Histórico</a>
                                <a href="{{ route('produtos.edit', $produto) }}" class="underline-offset-4 hover:underline">Editar</a>
                                <form action="{{ route('produtos.destroy', $produto) }}" method="POST"
                                      onsubmit="return confirm('Excluir este produto?');">
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
