<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompraRequest;
use App\Models\Compra;
use App\Models\Estabelecimento;

class CompraController extends Controller
{
    public function index()
    {
        $compras = Compra::with('estabelecimento')->orderByDesc('data')->get();

        return view('compras.index', compact('compras'));
    }

    public function create()
    {
        $estabelecimentos = Estabelecimento::orderBy('nome')->get();

        return view('compras.create', compact('estabelecimentos'));
    }

    public function store(CompraRequest $request)
    {
        Compra::create($this->dadosCompra($request));

        return redirect()
            ->route('compras.index')
            ->with('success', 'Compra cadastrada com sucesso.');
    }

    public function show(Compra $compra)
    {
        $compra->load(['estabelecimento', 'itens.produto', 'extracoes']);

        return view('compras.show', compact('compra'));
    }

    public function edit(Compra $compra)
    {
        $estabelecimentos = Estabelecimento::orderBy('nome')->get();

        return view('compras.edit', compact('compra', 'estabelecimentos'));
    }

    public function update(CompraRequest $request, Compra $compra)
    {
        $compra->update($this->dadosCompra($request));

        return redirect()
            ->route('compras.index')
            ->with('success', 'Compra atualizada com sucesso.');
    }

    public function destroy(Compra $compra)
    {
        $compra->delete();

        return redirect()
            ->route('compras.index')
            ->with('success', 'Compra excluída com sucesso.');
    }

    /**
     * Normaliza os dados validados: compras não parceladas nunca guardam parcelas.
     */
    private function dadosCompra(CompraRequest $request): array
    {
        $dados = $request->validated();
        $dados['parcelado'] = $request->boolean('parcelado');

        if (! $dados['parcelado']) {
            $dados['numero_parcelas'] = null;
        }

        return $dados;
    }
}
