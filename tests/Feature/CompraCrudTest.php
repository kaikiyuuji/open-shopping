<?php

namespace Tests\Feature;

use App\Models\Compra;
use App\Models\Estabelecimento;
use App\Models\ItemCompra;
use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompraCrudTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // LISTAGEM
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_listar_compras(): void
    {
        Compra::factory()->count(3)->create();

        $response = $this->get(route('compras.index'));

        $response->assertStatus(200);
        $response->assertViewIs('compras.index');
        $response->assertViewHas('compras');
    }

    /** @test */
    public function listagem_de_compras_exibe_informacoes_basicas(): void
    {
        $estabelecimento = Estabelecimento::factory()->create(['nome' => 'Mercado Teste']);
        $compra = Compra::factory()->create([
            'estabelecimento_id' => $estabelecimento->id,
            'valor_total'        => 200.00,
        ]);

        $response = $this->get(route('compras.index'));

        $response->assertSee('Mercado Teste');
    }

    // -------------------------------------------------------------------------
    // CRIAÇÃO
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_acessar_formulario_de_criacao_de_compra(): void
    {
        $response = $this->get(route('compras.create'));

        $response->assertStatus(200);
        $response->assertViewIs('compras.create');
    }

    /** @test */
    public function formulario_de_compra_exibe_lista_de_estabelecimentos(): void
    {
        Estabelecimento::factory()->count(2)->create();

        $response = $this->get(route('compras.create'));

        $response->assertStatus(200);
        $response->assertViewHas('estabelecimentos');
    }

    /** @test */
    public function pode_cadastrar_compra_a_vista_no_debito(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->post(route('compras.store'), [
            'estabelecimento_id' => $estabelecimento->id,
            'data'               => '2025-04-10',
            'valor_total'        => 99.90,
            'forma_pagamento'    => 'debito',
            'parcelado'          => false,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('compras', [
            'estabelecimento_id' => $estabelecimento->id,
            'forma_pagamento'    => 'debito',
            'parcelado'          => false,
        ]);
    }

    /** @test */
    public function pode_cadastrar_compra_parcelada(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->post(route('compras.store'), [
            'estabelecimento_id' => $estabelecimento->id,
            'data'               => '2025-04-10',
            'valor_total'        => 600.00,
            'forma_pagamento'    => 'credito',
            'parcelado'          => true,
            'numero_parcelas'    => 6,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('compras', [
            'estabelecimento_id' => $estabelecimento->id,
            'parcelado'          => true,
            'numero_parcelas'    => 6,
        ]);
    }

    /** @test */
    public function pode_cadastrar_compra_em_dinheiro(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->post(route('compras.store'), [
            'estabelecimento_id' => $estabelecimento->id,
            'data'               => '2025-04-15',
            'valor_total'        => 45.50,
            'forma_pagamento'    => 'dinheiro',
            'parcelado'          => false,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('compras', [
            'forma_pagamento' => 'dinheiro',
            'parcelado'       => false,
        ]);
    }

    /** @test */
    public function cadastro_de_compra_redireciona_com_sucesso(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->post(route('compras.store'), [
            'estabelecimento_id' => $estabelecimento->id,
            'data'               => '2025-04-10',
            'valor_total'        => 150.00,
            'forma_pagamento'    => 'credito',
            'parcelado'          => false,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    // -------------------------------------------------------------------------
    // VISUALIZAÇÃO
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_visualizar_uma_compra(): void
    {
        $compra = Compra::factory()->create();

        $response = $this->get(route('compras.show', $compra));

        $response->assertStatus(200);
        $response->assertViewIs('compras.show');
        $response->assertViewHas('compra');
    }

    /** @test */
    public function visualizacao_de_compra_exibe_itens(): void
    {
        $compra = Compra::factory()->create();
        $produto = Produto::factory()->create(['nome' => 'Produto Visível']);
        ItemCompra::factory()->create([
            'compra_id'  => $compra->id,
            'produto_id' => $produto->id,
        ]);

        $response = $this->get(route('compras.show', $compra));

        $response->assertSee('Produto Visível');
    }

    // -------------------------------------------------------------------------
    // EDIÇÃO
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_acessar_formulario_de_edicao_de_compra(): void
    {
        $compra = Compra::factory()->create();

        $response = $this->get(route('compras.edit', $compra));

        $response->assertStatus(200);
        $response->assertViewIs('compras.edit');
        $response->assertViewHas('compra');
    }

    /** @test */
    public function pode_editar_compra(): void
    {
        $compra = Compra::factory()->create([
            'valor_total' => 100.00,
        ]);

        $response = $this->put(route('compras.update', $compra), [
            'estabelecimento_id' => $compra->estabelecimento_id,
            'data'               => $compra->data->format('Y-m-d'),
            'valor_total'        => 200.00,
            'forma_pagamento'    => $compra->forma_pagamento,
            'parcelado'          => false,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('compras', [
            'id'          => $compra->id,
            'valor_total' => 200.00,
        ]);
    }

    /** @test */
    public function edicao_de_compra_redireciona_com_sucesso(): void
    {
        $compra = Compra::factory()->create();

        $response = $this->put(route('compras.update', $compra), [
            'estabelecimento_id' => $compra->estabelecimento_id,
            'data'               => $compra->data->format('Y-m-d'),
            'valor_total'        => 99.00,
            'forma_pagamento'    => 'debito',
            'parcelado'          => false,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    // -------------------------------------------------------------------------
    // EXCLUSÃO
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_excluir_compra(): void
    {
        $compra = Compra::factory()->create();

        $response = $this->delete(route('compras.destroy', $compra));

        $response->assertRedirect(route('compras.index'));
        $this->assertDatabaseMissing('compras', [
            'id' => $compra->id,
        ]);
    }

    /** @test */
    public function exclusao_de_compra_redireciona_com_sucesso(): void
    {
        $compra = Compra::factory()->create();

        $response = $this->delete(route('compras.destroy', $compra));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function compra_inexistente_retorna_404(): void
    {
        $response = $this->get(route('compras.show', ['compra' => 9999]));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // CONSULTA POR ESTABELECIMENTO
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_consultar_compras_por_estabelecimento(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();
        Compra::factory()->count(3)->create([
            'estabelecimento_id' => $estabelecimento->id,
        ]);
        Compra::factory()->count(2)->create(); // compras de outro estabelecimento

        $response = $this->get(route('estabelecimentos.compras', $estabelecimento));

        $response->assertStatus(200);
        $response->assertViewHas('compras');

        $comprasNaView = $response->viewData('compras');
        $this->assertCount(3, $comprasNaView);
    }
}
