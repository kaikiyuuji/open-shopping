<?php

namespace Database\Factories;

use App\Models\Compra;
use App\Models\Estabelecimento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compra>
 */
class CompraFactory extends Factory
{
    protected $model = Compra::class;

    public function definition(): array
    {
        return [
            'estabelecimento_id' => Estabelecimento::factory(),
            'data'               => fake()->date(),
            'valor_total'        => fake()->randomFloat(2, 10, 500),
            'forma_pagamento'    => fake()->randomElement(Compra::formasPagamento()),
            'parcelado'          => false,
            'numero_parcelas'    => null,
        ];
    }
}
