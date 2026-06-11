<?php

namespace Database\Factories;

use App\Models\Estabelecimento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Estabelecimento>
 */
class EstabelecimentoFactory extends Factory
{
    protected $model = Estabelecimento::class;

    public function definition(): array
    {
        return [
            'nome'      => fake()->company(),
            'endereco'  => fake()->streetAddress(),
            'categoria' => fake()->randomElement(Estabelecimento::categorias()),
        ];
    }
}
