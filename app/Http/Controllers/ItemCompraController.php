<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemCompraRequest;
use App\Models\Compra;
use App\Models\ItemCompra;
use App\Models\Produto;

class ItemCompraController extends Controller
{
    public function create(Compra $compra)
    {
        $produtos = Produto::orderBy('nome')->get();

        return view('itens.create', compact('compra', 'produtos'));
    }

    public function store(ItemCompraRequest $request, Compra $compra)
    {
        $compra->itens()->create([
            'produto_id' => $this->resolverProduto($request),
            'quantidade' => $request->validated('quantidade'),
            'preco_pago' => $request->validated('preco_pago'),
        ]);

        return redirect()
            ->route('compras.show', $compra)
            ->with('success', 'Item adicionado com sucesso.');
    }

    public function edit(Compra $compra, ItemCompra $item)
    {
        $produtos = Produto::orderBy('nome')->get();

        return view('itens.edit', compact('compra', 'item', 'produtos'));
    }

    public function update(ItemCompraRequest $request, Compra $compra, ItemCompra $item)
    {
        $item->update([
            'produto_id' => $this->resolverProduto($request),
            'quantidade' => $request->validated('quantidade'),
            'preco_pago' => $request->validated('preco_pago'),
        ]);

        return redirect()
            ->route('compras.show', $compra)
            ->with('success', 'Item atualizado com sucesso.');
    }

    public function destroy(Compra $compra, ItemCompra $item)
    {
        $item->delete();

        return redirect()
            ->route('compras.show', $compra)
            ->with('success', 'Item excluído com sucesso.');
    }

    /**
     * Usa o produto selecionado ou cria um novo durante o lançamento do item.
     */
    private function resolverProduto(ItemCompraRequest $request): int
    {
        if ($request->boolean('produto_novo')) {
            $produto = Produto::firstOrCreate(
                ['nome' => $request->validated('produto_nome')],
                ['categoria' => $request->validated('produto_categoria')],
            );

            return $produto->id;
        }

        return (int) $request->validated('produto_id');
    }
}
