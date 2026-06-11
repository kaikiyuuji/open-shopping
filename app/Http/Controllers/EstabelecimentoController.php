<?php

namespace App\Http\Controllers;

use App\Http\Requests\EstabelecimentoRequest;
use App\Models\Estabelecimento;

class EstabelecimentoController extends Controller
{
    public function index()
    {
        $estabelecimentos = Estabelecimento::withCount('compras')->orderBy('nome')->get();

        return view('estabelecimentos.index', compact('estabelecimentos'));
    }

    public function create()
    {
        return view('estabelecimentos.create', [
            'categorias' => Estabelecimento::categorias(),
        ]);
    }

    public function store(EstabelecimentoRequest $request)
    {
        Estabelecimento::create($request->validated());

        return redirect()
            ->route('estabelecimentos.index')
            ->with('success', 'Estabelecimento cadastrado com sucesso.');
    }

    public function show(Estabelecimento $estabelecimento)
    {
        $estabelecimento->load(['compras' => fn ($query) => $query->orderByDesc('data')]);

        return view('estabelecimentos.show', compact('estabelecimento'));
    }

    public function edit(Estabelecimento $estabelecimento)
    {
        return view('estabelecimentos.edit', [
            'estabelecimento' => $estabelecimento,
            'categorias'      => Estabelecimento::categorias(),
        ]);
    }

    public function update(EstabelecimentoRequest $request, Estabelecimento $estabelecimento)
    {
        $estabelecimento->update($request->validated());

        return redirect()
            ->route('estabelecimentos.index')
            ->with('success', 'Estabelecimento atualizado com sucesso.');
    }

    public function destroy(Estabelecimento $estabelecimento)
    {
        $estabelecimento->delete();

        return redirect()
            ->route('estabelecimentos.index')
            ->with('success', 'Estabelecimento excluído com sucesso.');
    }

    public function compras(Estabelecimento $estabelecimento)
    {
        $compras = $estabelecimento->compras()->orderByDesc('data')->get();

        return view('estabelecimentos.compras', compact('estabelecimento', 'compras'));
    }
}
