<?php

namespace Tests\Feature;

use App\Models\Compra;
use App\Models\ItemCompra;
use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemCompraCrudTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // CRIAÇÃO
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_cadastrar_item_de_compra(): void
    {
        $compra = Compra::factory()->create();
        $produto = Produto::factory()->create();

        $response = $this->post(route('compras.itens.store', $compra), [
            'produto_id' => $produto->id,
            'quantidade' => 2,
            'preco_pago' => 5.99,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('itens_compra', [
            'compra_id'  => $compra->id,
            'produto_id' => $produto->id,
            'quantidade' => 2,
            'preco_pago' => 5.99,
        ]);
    }

    /** @test */
    public function cadastro_de_item_redireciona_com_sucesso(): void
    {
        $compra = Compra::factory()->create();
        $produto = Produto::factory()->create();

        $response = $this->post(route('compras.itens.store', $compra), [
            'produto_id' => $produto->id,
            'quantidade' => 1,
            'preco_pago' => 12.50,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function pode_criar_produto_durante_cadastro_de_item(): void
    {
        $compra = Compra::factory()->create();

        // Simula criação inline de produto durante cadastro de item
        $response = $this->post(route('compras.itens.store', $compra), [
            'produto_novo'     => true,
            'produto_nome'     => 'Produto Novo Inline',
            'produto_categoria'=> 'Limpeza',
            'quantidade'       => 3,
            'preco_pago'       => 7.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('produtos', [
            'nome'      => 'Produto Novo Inline',
            'categoria' => 'Limpeza',
        ]);
        $this->assertDatabaseCount('itens_compra', 1);
    }

    // -------------------------------------------------------------------------
    // EDIÇÃO
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_acessar_formulario_de_edicao_de_item(): void
    {
        $item = ItemCompra::factory()->create();

        $response = $this->get(route('compras.itens.edit', [
            'compra' => $item->compra_id,
            'item'   => $item->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('itens.edit');
        $response->assertViewHas('item');
    }

    /** @test */
    public function pode_editar_item_de_compra(): void
    {
        $item = ItemCompra::factory()->create([
            'quantidade' => 1,
            'preco_pago' => 10.00,
        ]);

        $response = $this->put(route('compras.itens.update', [
            'compra' => $item->compra_id,
            'item'   => $item->id,
        ]), [
            'produto_id' => $item->produto_id,
            'quantidade' => 3,
            'preco_pago' => 10.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('itens_compra', [
            'id'         => $item->id,
            'quantidade' => 3,
        ]);
    }

    /** @test */
    public function edicao_de_item_redireciona_com_sucesso(): void
    {
        $item = ItemCompra::factory()->create();

        $response = $this->put(route('compras.itens.update', [
            'compra' => $item->compra_id,
            'item'   => $item->id,
        ]), [
            'produto_id' => $item->produto_id,
            'quantidade' => 2,
            'preco_pago' => 15.00,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    // -------------------------------------------------------------------------
    // EXCLUSÃO
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_excluir_item_de_compra(): void
    {
        $item = ItemCompra::factory()->create();

        $response = $this->delete(route('compras.itens.destroy', [
            'compra' => $item->compra_id,
            'item'   => $item->id,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('itens_compra', [
            'id' => $item->id,
        ]);
    }

    /** @test */
    public function exclusao_de_item_redireciona_com_sucesso(): void
    {
        $item = ItemCompra::factory()->create();

        $response = $this->delete(route('compras.itens.destroy', [
            'compra' => $item->compra_id,
            'item'   => $item->id,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    // -------------------------------------------------------------------------
    // CONSULTAS SOBRE ITENS
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_consultar_produtos_comprados_em_uma_compra(): void
    {
        $compra = Compra::factory()->create();
        $produto1 = Produto::factory()->create(['nome' => 'Produto A']);
        $produto2 = Produto::factory()->create(['nome' => 'Produto B']);

        ItemCompra::factory()->create(['compra_id' => $compra->id, 'produto_id' => $produto1->id]);
        ItemCompra::factory()->create(['compra_id' => $compra->id, 'produto_id' => $produto2->id]);

        $response = $this->get(route('compras.show', $compra));

        $response->assertStatus(200);
        $response->assertSee('Produto A');
        $response->assertSee('Produto B');
    }
}
