@extends('layouts.main')

@section('title', 'Novo Estabelecimento')

@section('content')
    <h1 class="mb-8 text-3xl font-light tracking-tight">Novo Estabelecimento</h1>

    <form action="{{ route('estabelecimentos.store') }}" method="POST" class="max-w-xl space-y-6">
        @csrf
        @include('estabelecimentos._form', ['estabelecimento' => null])

        <div class="flex gap-4 pt-2">
            <button type="submit"
                    class="bg-black px-6 py-2 text-sm uppercase tracking-widest text-white transition hover:bg-neutral-700">
                Salvar
            </button>
            <a href="{{ route('estabelecimentos.index') }}"
               class="border border-black px-6 py-2 text-sm uppercase tracking-widest transition hover:bg-neutral-100">
                Cancelar
            </a>
        </div>
    </form>
@endsection
