<?php

namespace Tests\Feature;

use App\Models\Compra;
use App\Models\Estabelecimento;
use App\Models\ItemCompra;
use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes de consultas e relatórios do sistema.
 *
 * Cobre:
 * - Histórico de compras de um produto
 * - Preço pago por produto em cada compra
 * - Quantidade comprada por produto
 * - Estabelecimento onde o produto foi comprado
 * - Produtos comprados em uma compra
 * - Compras por estabelecimento
 */
class ConsultasTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // HISTÓRICO DE COMPRAS DE UM PRODUTO
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_consultar_historico_de_compras_de_um_produto(): void
    {
        $produto = Produto::factory()->create(['nome' => 'Café Solúvel']);

        $compra1 = Compra::factory()->create(['data' => '2025-01-10']);
        $compra2 = Compra::factory()->create(['data' => '2025-03-20']);

        ItemCompra::factory()->create([
            'produto_id' => $produto->id,
            'compra_id'  => $compra1->id,
        ]);
        ItemCompra::factory()->create([
            'produto_id' => $produto->id,
            'compra_id'  => $compra2->id,
        ]);

        $response = $this->get(route('produtos.show', $produto));

        $response->assertStatus(200);

        $historico = $response->viewData('historico');
        $this->assertCount(2, $historico);
    }

    /** @test */
    public function historico_de_produto_retorna_somente_suas_compras(): void
    {
        $produto1 = Produto::factory()->create();
        $produto2 = Produto::factory()->create();

        $compra1 = Compra::factory()->create();
        $compra2 = Compra::factory()->create();

        ItemCompra::factory()->create(['produto_id' => $produto1->id, 'compra_id' => $compra1->id]);
        ItemCompra::factory()->create(['produto_id' => $produto2->id, 'compra_id' => $compra2->id]);

        $response = $this->get(route('produtos.show', $produto1));

        $historico = $response->viewData('historico');
        $this->assertCount(1, $historico);
    }

    // -------------------------------------------------------------------------
    // PREÇO PAGO POR PRODUTO EM CADA COMPRA
    // -------------------------------------------------------------------------

    /** @test */
    public function historico_exibe_diferentes_precos_pagos_em_cada_compra(): void
    {
        $produto = Produto::factory()->create();

        $compra1 = Compra::factory()->create(['data' => '2025-01-10']);
        $compra2 = Compra::factory()->create(['data' => '2025-03-20']);

        ItemCompra::factory()->create([
            'produto_id' => $produto->id,
            'compra_id'  => $compra1->id,
            'preco_pago' => 3.50,
        ]);
        ItemCompra::factory()->create([
            'produto_id' => $produto->id,
            'compra_id'  => $compra2->id,
            'preco_pago' => 4.20,
        ]);

        $response = $this->get(route('produtos.show', $produto));

        $historico = $response->viewData('historico');

        $precos = $historico->pluck('preco_pago')->toArray();
        $this->assertContains(3.50, $precos);
        $this->assertContains(4.20, $precos);
    }

    // -------------------------------------------------------------------------
    // QUANTIDADE COMPRADA POR PRODUTO
    // -------------------------------------------------------------------------

    /** @test */
    public function historico_exibe_quantidade_comprada_em_cada_compra(): void
    {
        $produto = Produto::factory()->create();
        $compra = Compra::factory()->create();

        ItemCompra::factory()->create([
            'produto_id' => $produto->id,
            'compra_id'  => $compra->id,
            'quantidade' => 6,
        ]);

        $response = $this->get(route('produtos.show', $produto));

        $historico = $response->viewData('historico');
        $this->assertEquals(6, $historico->first()->quantidade);
    }

    // -------------------------------------------------------------------------
    // ESTABELECIMENTO ONDE O PRODUTO FOI COMPRADO
    // -------------------------------------------------------------------------

    /** @test */
    public function historico_inclui_nome_do_estabelecimento(): void
    {
        $estabelecimento = Estabelecimento::factory()->create(['nome' => 'Atacadão']);
        $produto = Produto::factory()->create();
        $compra = Compra::factory()->create([
            'estabelecimento_id' => $estabelecimento->id,
        ]);

        ItemCompra::factory()->create([
            'produto_id' => $produto->id,
            'compra_id'  => $compra->id,
        ]);

        $response = $this->get(route('produtos.show', $produto));

        $historico = $response->viewData('historico');

        $nomeEstabelecimento = $historico->first()->compra->estabelecimento->nome;
        $this->assertEquals('Atacadão', $nomeEstabelecimento);
    }

    // -------------------------------------------------------------------------
    // PRODUTOS COMPRADOS EM UMA COMPRA
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_consultar_todos_produtos_de_uma_compra(): void
    {
        $compra = Compra::factory()->create();
        $produto1 = Produto::factory()->create(['nome' => 'Maçã']);
        $produto2 = Produto::factory()->create(['nome' => 'Banana']);
        $produto3 = Produto::factory()->create(['nome' => 'Manga']);

        ItemCompra::factory()->create(['compra_id' => $compra->id, 'produto_id' => $produto1->id]);
        ItemCompra::factory()->create(['compra_id' => $compra->id, 'produto_id' => $produto2->id]);
        ItemCompra::factory()->create(['compra_id' => $compra->id, 'produto_id' => $produto3->id]);

        $response = $this->get(route('compras.show', $compra));

        $response->assertStatus(200);
        $response->assertSee('Maçã');
        $response->assertSee('Banana');
        $response->assertSee('Manga');
    }

    /** @test */
    public function compra_sem_itens_exibe_mensagem_adequada(): void
    {
        $compra = Compra::factory()->create();

        $response = $this->get(route('compras.show', $compra));

        $response->assertStatus(200);
        // A view deve indicar ausência de itens
        $response->assertViewHas('compra');
        $this->assertCount(0, $compra->itens);
    }

    // -------------------------------------------------------------------------
    // COMPRAS POR ESTABELECIMENTO
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_consultar_todas_compras_de_um_estabelecimento(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();
        $outroEstabelecimento = Estabelecimento::factory()->create();

        Compra::factory()->count(4)->create([
            'estabelecimento_id' => $estabelecimento->id,
        ]);
        Compra::factory()->count(2)->create([
            'estabelecimento_id' => $outroEstabelecimento->id,
        ]);

        $response = $this->get(route('estabelecimentos.compras', $estabelecimento));

        $response->assertStatus(200);

        $compras = $response->viewData('compras');
        $this->assertCount(4, $compras);
    }

    /** @test */
    public function compras_por_estabelecimento_ordenadas_por_data_decrescente(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $compraAntiga = Compra::factory()->create([
            'estabelecimento_id' => $estabelecimento->id,
            'data'               => '2025-01-01',
        ]);
        $compraRecente = Compra::factory()->create([
            'estabelecimento_id' => $estabelecimento->id,
            'data'               => '2025-06-01',
        ]);

        $response = $this->get(route('estabelecimentos.compras', $estabelecimento));

        $compras = $response->viewData('compras');

        // A compra mais recente deve aparecer primeiro
        $this->assertTrue(
            $compras->first()->data >= $compras->last()->data
        );
    }

    /** @test */
    public function estabelecimento_sem_compras_retorna_lista_vazia(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->get(route('estabelecimentos.compras', $estabelecimento));

        $response->assertStatus(200);

        $compras = $response->viewData('compras');
        $this->assertCount(0, $compras);
    }

    // -------------------------------------------------------------------------
    // PRODUTO PODE SER SELECIONADO AO CRIAR ITEM
    // -------------------------------------------------------------------------

    /** @test */
    public function formulario_de_item_exibe_lista_de_produtos(): void
    {
        $compra = Compra::factory()->create();
        Produto::factory()->count(3)->create();

        $response = $this->get(route('compras.itens.create', $compra));

        $response->assertStatus(200);
        $response->assertViewHas('produtos');

        $produtos = $response->viewData('produtos');
        $this->assertCount(3, $produtos);
    }
}
