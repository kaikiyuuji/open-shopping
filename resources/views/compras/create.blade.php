@extends('layouts.main')

@section('title', 'Nova Compra')

@section('content')
    <h1 class="mb-8 text-3xl font-light tracking-tight">Nova Compra</h1>

    <form action="{{ route('compras.store') }}" method="POST" class="max-w-xl space-y-6">
        @csrf
        @include('compras._form', ['compra' => null])

        <div class="flex gap-4 pt-2">
            <button type="submit"
                    class="bg-black px-6 py-2 text-sm uppercase tracking-widest text-white transition hover:bg-neutral-700">
                Salvar
            </button>
            <a href="{{ route('compras.index') }}"
               class="border border-black px-6 py-2 text-sm uppercase tracking-widest transition hover:bg-neutral-100">
                Cancelar
            </a>
        </div>
    </form>
@endsection
