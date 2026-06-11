<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmarExtracaoRequest;
use App\Http\Requests\ExtracaoOcrRequest;
use App\Jobs\ProcessarExtracaoOcr;
use App\Models\Compra;
use App\Models\ExtracaoOcr;
use App\Models\Produto;

class ExtracaoOcrController extends Controller
{
    public function store(ExtracaoOcrRequest $request, Compra $compra)
    {
        $path = $request->file('imagem')->store('ocr', 'local');

        $extracao = $compra->extracoes()->create([
            'imagem_path' => $path,
            'status'      => ExtracaoOcr::STATUS_PROCESSANDO,
        ]);

        ProcessarExtracaoOcr::dispatch($extracao);

        return redirect()
            ->route('compras.show', $compra)
            ->with('success', 'Cupom enviado. A extração roda em segundo plano — atualize a página em instantes.');
    }

    public function revisar(Compra $compra, ExtracaoOcr $extracao)
    {
        abort_unless($extracao->compra_id === $compra->id, 404);

        if ($extracao->status !== ExtracaoOcr::STATUS_CONCLUIDA) {
            return redirect()
                ->route('compras.show', $compra)
                ->with('success', 'Esta extração ainda não está pronta para revisão.');
        }

        return view('ocr.revisar', [
            'compra'   => $compra,
            'extracao' => $extracao,
            'produtos' => Produto::orderBy('nome')->get(),
        ]);
    }

    public function confirmar(ConfirmarExtracaoRequest $request, Compra $compra, ExtracaoOcr $extracao)
    {
        abort_unless($extracao->compra_id === $compra->id, 404);

        $criados = 0;

        foreach ($request->validated('itens') as $item) {
            if (empty($item['incluir'])) {
                continue;
            }

            $produto = Produto::firstOrCreate(
                ['nome' => $item['produto_nome']],
                ['categoria' => ($item['produto_categoria'] ?? null) ?: 'Outros'],
            );

            $compra->itens()->create([
                'produto_id' => $produto->id,
                'quantidade' => $item['quantidade'],
                'preco_pago' => $item['preco_pago'],
            ]);

            $criados++;
        }

        $extracao->update(['status' => ExtracaoOcr::STATUS_CONFIRMADA]);

        return redirect()
            ->route('compras.show', $compra)
            ->with('success', $criados > 0
                ? "{$criados} item(ns) adicionados à compra a partir do cupom."
                : 'Extração revisada — nenhum item incluído.');
    }

    public function destroy(Compra $compra, ExtracaoOcr $extracao)
    {
        abort_unless($extracao->compra_id === $compra->id, 404);

        $extracao->delete();

        return redirect()
            ->route('compras.show', $compra)
            ->with('success', 'Extração descartada.');
    }
}
