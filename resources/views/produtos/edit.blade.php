@extends('layouts.main')

@section('title', 'Editar Produto')

@section('content')
    <h1 class="mb-8 text-3xl font-light tracking-tight">Editar Produto</h1>

    <form action="{{ route('produtos.update', $produto) }}" method="POST" class="max-w-xl space-y-6">
        @csrf
        @method('PUT')
        @include('produtos._form')

        <div class="flex gap-4 pt-2">
            <button type="submit"
                    class="bg-black px-6 py-2 text-sm uppercase tracking-widest text-white transition hover:bg-neutral-700">
                Atualizar
            </button>
            <a href="{{ route('produtos.index') }}"
               class="border border-black px-6 py-2 text-sm uppercase tracking-widest transition hover:bg-neutral-100">
                Cancelar
            </a>
        </div>
    </form>
@endsection
