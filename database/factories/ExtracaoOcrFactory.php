<?php

namespace Database\Factories;

use App\Models\Compra;
use App\Models\ExtracaoOcr;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExtracaoOcr>
 */
class ExtracaoOcrFactory extends Factory
{
    protected $model = ExtracaoOcr::class;

    public function definition(): array
    {
        return [
            'compra_id'   => Compra::factory(),
            'imagem_path' => 'ocr/cupom-teste.png',
            'status'      => ExtracaoOcr::STATUS_PROCESSANDO,
            'itens'       => null,
            'erro'        => null,
        ];
    }

    public function concluida(array $itens = []): static
    {
        return $this->state([
            'status' => ExtracaoOcr::STATUS_CONCLUIDA,
            'itens'  => $itens ?: [
                ['descricao' => 'ARROZ TIO JOAO 5KG', 'quantidade' => 1, 'preco_pago' => 25.99],
                ['descricao' => 'FEIJAO CARIOCA 1KG', 'quantidade' => 2, 'preco_pago' => 8.50],
            ],
        ]);
    }
}
