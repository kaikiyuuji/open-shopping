<?php

namespace Database\Factories;

use App\Models\Compra;
use App\Models\ItemCompra;
use App\Models\Produto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemCompra>
 */
class ItemCompraFactory extends Factory
{
    protected $model = ItemCompra::class;

    public function definition(): array
    {
        return [
            'compra_id'  => Compra::factory(),
            'produto_id' => Produto::factory(),
            'quantidade' => fake()->numberBetween(1, 10),
            'preco_pago' => fake()->randomFloat(2, 1, 100),
        ];
    }
}
