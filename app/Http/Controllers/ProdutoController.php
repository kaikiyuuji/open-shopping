<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProdutoRequest;
use App\Models\Produto;

class ProdutoController extends Controller
{
    public function index()
    {
        $produtos = Produto::orderBy('nome')->get();

        return view('produtos.index', compact('produtos'));
    }

    public function create()
    {
        return view('produtos.create');
    }

    public function store(ProdutoRequest $request)
    {
        Produto::create($request->validated());

        return redirect()
            ->route('produtos.index')
            ->with('success', 'Produto cadastrado com sucesso.');
    }

    public function show(Produto $produto)
    {
        $historico = $produto->itens()
            ->with('compra.estabelecimento')
            ->get()
            ->sortByDesc(fn ($item) => $item->compra->data)
            ->values();

        return view('produtos.show', compact('produto', 'historico'));
    }

    public function edit(Produto $produto)
    {
        return view('produtos.edit', compact('produto'));
    }

    public function update(ProdutoRequest $request, Produto $produto)
    {
        $produto->update($request->validated());

        return redirect()
            ->route('produtos.index')
            ->with('success', 'Produto atualizado com sucesso.');
    }

    public function destroy(Produto $produto)
    {
        $produto->delete();

        return redirect()
            ->route('produtos.index')
            ->with('success', 'Produto excluído com sucesso.');
    }
}
