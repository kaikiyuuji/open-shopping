@extends('layouts.main')

@section('title', 'Estabelecimentos')

@section('content')
    <div class="mb-8 flex items-end justify-between">
        <h1 class="text-3xl font-light tracking-tight">Estabelecimentos</h1>
        <a href="{{ route('estabelecimentos.create') }}"
           class="bg-black px-5 py-2 text-sm uppercase tracking-widest text-white transition hover:bg-neutral-700">
            Novo
        </a>
    </div>

    @if ($estabelecimentos->isEmpty())
        <p class="border border-dashed border-neutral-300 px-6 py-12 text-center text-sm text-neutral-500">
            Nenhum estabelecimento cadastrado.
        </p>
    @else
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="border-b-2 border-black text-left uppercase tracking-widest">
                    <th class="py-3 pr-4 font-medium">Nome</th>
                    <th class="py-3 pr-4 font-medium">Endereço</th>
                    <th class="py-3 pr-4 font-medium">Categoria</th>
                    <th class="py-3 pr-4 font-medium">Compras</th>
                    <th class="py-3 text-right font-medium">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($estabelecimentos as $estabelecimento)
                    <tr class="border-b border-neutral-200 transition hover:bg-neutral-50">
                        <td class="py-3 pr-4">
                            <a href="{{ route('estabelecimentos.show', $estabelecimento) }}" class="underline-offset-4 hover:underline">
                                {{ $estabelecimento->nome }}
                            </a>
                        </td>
                        <td class="py-3 pr-4 text-neutral-600">{{ $estabelecimento->endereco }}</td>
                        <td class="py-3 pr-4">{{ $estabelecimento->categoria }}</td>
                        <td class="py-3 pr-4">{{ $estabelecimento->compras_count }}</td>
                        <td class="py-3 text-right">
                            <div class="flex justify-end gap-3 text-xs uppercase tracking-widest">
                                <a href="{{ route('estabelecimentos.compras', $estabelecimento) }}" class="underline-offset-4 hover:underline">Compras</a>
                                <a href="{{ route('estabelecimentos.edit', $estabelecimento) }}" class="underline-offset-4 hover:underline">Editar</a>
                                <form action="{{ route('estabelecimentos.destroy', $estabelecimento) }}" method="POST"
                                      onsubmit="return confirm('Excluir este estabelecimento?');">
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
