<?php

namespace Tests\Unit;

use App\Models\Compra;
use App\Models\Estabelecimento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstabelecimentoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function estabelecimento_tem_nome(): void
    {
        $estabelecimento = Estabelecimento::factory()->create([
            'nome' => 'Supermercado Extra',
        ]);

        $this->assertEquals('Supermercado Extra', $estabelecimento->nome);
    }

    /** @test */
    public function estabelecimento_tem_endereco(): void
    {
        $estabelecimento = Estabelecimento::factory()->create([
            'endereco' => 'Rua das Flores, 123',
        ]);

        $this->assertEquals('Rua das Flores, 123', $estabelecimento->endereco);
    }

    /** @test */
    public function estabelecimento_tem_categoria(): void
    {
        $estabelecimento = Estabelecimento::factory()->create([
            'categoria' => 'Supermercado',
        ]);

        $this->assertEquals('Supermercado', $estabelecimento->categoria);
    }

    /** @test */
    public function estabelecimento_possui_categorias_predefinidas(): void
    {
        $categoriasValidas = Estabelecimento::categorias();

        $this->assertIsArray($categoriasValidas);
        $this->assertNotEmpty($categoriasValidas);
        $this->assertContains('Supermercado', $categoriasValidas);
        $this->assertContains('Farmácia', $categoriasValidas);
        $this->assertContains('Padaria', $categoriasValidas);
    }

    /** @test */
    public function estabelecimento_tem_muitas_compras(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $estabelecimento->compras()
        );
    }

    /** @test */
    public function estabelecimento_pode_ter_varias_compras(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        Compra::factory()->count(3)->create([
            'estabelecimento_id' => $estabelecimento->id,
        ]);

        $this->assertCount(3, $estabelecimento->compras);
    }

    /** @test */
    public function estabelecimento_sem_compras_retorna_colecao_vazia(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $this->assertCount(0, $estabelecimento->compras);
        $this->assertTrue($estabelecimento->compras->isEmpty());
    }
}
