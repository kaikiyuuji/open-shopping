<?php

namespace Tests\Unit;

use App\Models\Compra;
use App\Models\ItemCompra;
use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdutoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function produto_tem_nome(): void
    {
        $produto = Produto::factory()->create([
            'nome' => 'Arroz Integral',
        ]);

        $this->assertEquals('Arroz Integral', $produto->nome);
    }

    /** @test */
    public function produto_tem_categoria(): void
    {
        $produto = Produto::factory()->create([
            'categoria' => 'Alimentos',
        ]);

        $this->assertEquals('Alimentos', $produto->categoria);
    }

    /** @test */
    public function produto_tem_nome_unico(): void
    {
        Produto::factory()->create(['nome' => 'Leite Integral']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Produto::factory()->create(['nome' => 'Leite Integral']);
    }

    /** @test */
    public function produto_pode_aparecer_em_varias_compras(): void
    {
        $produto = Produto::factory()->create();
        $compras = Compra::factory()->count(3)->create();

        foreach ($compras as $compra) {
            ItemCompra::factory()->create([
                'produto_id' => $produto->id,
                'compra_id'  => $compra->id,
            ]);
        }

        $this->assertCount(3, $produto->itens);
    }

    /** @test */
    public function produto_tem_muitos_itens(): void
    {
        $produto = Produto::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $produto->itens()
        );
    }

    /** @test */
    public function produto_e_vinculado_a_compras_por_itens(): void
    {
        $produto = Produto::factory()->create();
        $compra = Compra::factory()->create();

        ItemCompra::factory()->create([
            'produto_id' => $produto->id,
            'compra_id'  => $compra->id,
        ]);

        // Produto se relaciona com compras via itens (hasManyThrough ou via itens)
        $comprasViaProduto = $produto->itens->pluck('compra_id');
        $this->assertContains($compra->id, $comprasViaProduto);
    }

    /** @test */
    public function produto_sem_compras_retorna_itens_vazios(): void
    {
        $produto = Produto::factory()->create();

        $this->assertCount(0, $produto->itens);
        $this->assertTrue($produto->itens->isEmpty());
    }
}
