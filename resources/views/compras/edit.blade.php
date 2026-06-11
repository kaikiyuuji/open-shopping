@extends('layouts.main')

@section('title', 'Editar Compra')

@section('content')
    <h1 class="mb-8 text-3xl font-light tracking-tight">Editar Compra</h1>

    <form action="{{ route('compras.update', $compra) }}" method="POST" class="max-w-xl space-y-6">
        @csrf
        @method('PUT')
        @include('compras._form')

        <div class="flex gap-4 pt-2">
            <button type="submit"
                    class="bg-black px-6 py-2 text-sm uppercase tracking-widest text-white transition hover:bg-neutral-700">
                Atualizar
            </button>
            <a href="{{ route('compras.index') }}"
               class="border border-black px-6 py-2 text-sm uppercase tracking-widest transition hover:bg-neutral-100">
                Cancelar
            </a>
        </div>
    </form>
@endsection
