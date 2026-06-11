<?php

namespace App\Jobs;

use App\Models\ExtracaoOcr;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessarExtracaoOcr implements ShouldQueue
{
    use Queueable;

    public int $timeout = 180;

    public function __construct(
        public ExtracaoOcr $extracao,
    ) {}

    public function handle(): void
    {
        try {
            $resposta = Http::timeout(120)
                ->attach(
                    'arquivo',
                    Storage::disk('local')->get($this->extracao->imagem_path),
                    basename($this->extracao->imagem_path),
                )
                ->post(rtrim(config('services.ocr.url'), '/').'/ocr')
                ->throw()
                ->json();

            $this->extracao->update([
                'status' => ExtracaoOcr::STATUS_CONCLUIDA,
                'itens'  => collect($resposta['itens'] ?? [])->map(fn (array $item) => [
                    'descricao'  => $item['descricao'] ?? '',
                    'quantidade' => (int) ($item['quantidade'] ?? 1),
                    'preco_pago' => (float) ($item['preco'] ?? 0),
                ])->values()->all(),
                'erro'   => null,
            ]);
        } catch (Throwable $e) {
            $this->extracao->update([
                'status' => ExtracaoOcr::STATUS_FALHOU,
                'erro'   => $e->getMessage(),
            ]);
        }
    }

    public function failed(Throwable $e): void
    {
        $this->extracao->update([
            'status' => ExtracaoOcr::STATUS_FALHOU,
            'erro'   => $e->getMessage(),
        ]);
    }
}
