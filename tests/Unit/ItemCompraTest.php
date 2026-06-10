<?php

namespace Tests\Unit;

use App\Models\Compra;
use App\Models\ItemCompra;
use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemCompraTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function item_pertence_a_uma_compra(): void
    {
        $compra = Compra::factory()->create();
        $item = ItemCompra::factory()->create([
            'compra_id' => $compra->id,
        ]);

        $this->assertInstanceOf(Compra::class, $item->compra);
        $this->assertEquals($compra->id, $item->compra->id);
    }

    /** @test */
    public function item_pertence_a_um_produto(): void
    {
        $produto = Produto::factory()->create();
        $item = ItemCompra::factory()->create([
            'produto_id' => $produto->id,
        ]);

        $this->assertInstanceOf(Produto::class, $item->produto);
        $this->assertEquals($produto->id, $item->produto->id);
    }

    /** @test */
    public function item_tem_quantidade(): void
    {
        $item = ItemCompra::factory()->create([
            'quantidade' => 5,
        ]);

        $this->assertEquals(5, $item->quantidade);
    }

    /** @test */
    public function item_tem_preco_pago(): void
    {
        $item = ItemCompra::factory()->create([
            'preco_pago' => 9.99,
        ]);

        $this->assertEquals(9.99, $item->preco_pago);
    }

    /** @test */
    public function item_tem_relacionamento_belongs_to_compra(): void
    {
        $item = ItemCompra::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $item->compra()
        );
    }

    /** @test */
    public function item_tem_relacionamento_belongs_to_produto(): void
    {
        $item = ItemCompra::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $item->produto()
        );
    }
}
