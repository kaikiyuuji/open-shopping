<?php

namespace Database\Factories;

use App\Models\Produto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Produto>
 */
class ProdutoFactory extends Factory
{
    protected $model = Produto::class;

    public function definition(): array
    {
        return [
            'nome'      => fake()->unique()->words(3, true),
            'categoria' => fake()->randomElement(['Alimentos', 'Bebidas', 'Limpeza', 'Higiene']),
        ];
    }
}
