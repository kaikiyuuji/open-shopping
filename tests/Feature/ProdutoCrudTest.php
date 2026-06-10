<?php

namespace Tests\Feature;

use App\Models\Compra;
use App\Models\Estabelecimento;
use App\Models\ItemCompra;
use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdutoCrudTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // LISTAGEM
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_listar_produtos(): void
    {
        Produto::factory()->count(3)->create();

        $response = $this->get(route('produtos.index'));

        $response->assertStatus(200);
        $response->assertViewIs('produtos.index');
        $response->assertViewHas('produtos');
    }

    /** @test */
    public function listagem_de_produtos_exibe_nomes(): void
    {
        Produto::factory()->create(['nome' => 'Azeite Extra Virgem']);

        $response = $this->get(route('produtos.index'));

        $response->assertSee('Azeite Extra Virgem');
    }

    // -------------------------------------------------------------------------
    // CRIAÇÃO
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_acessar_formulario_de_criacao_de_produto(): void
    {
        $response = $this->get(route('produtos.create'));

        $response->assertStatus(200);
        $response->assertViewIs('produtos.create');
    }

    /** @test */
    public function pode_cadastrar_novo_produto(): void
    {
        $response = $this->post(route('produtos.store'), [
            'nome'      => 'Feijão Carioca',
            'categoria' => 'Alimentos',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('produtos', [
            'nome'      => 'Feijão Carioca',
            'categoria' => 'Alimentos',
        ]);
    }

    /** @test */
    public function cadastro_de_produto_redireciona_com_sucesso(): void
    {
        $response = $this->post(route('produtos.store'), [
            'nome'      => 'Macarrão',
            'categoria' => 'Alimentos',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    // -------------------------------------------------------------------------
    // VISUALIZAÇÃO / HISTÓRICO
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_visualizar_produto(): void
    {
        $produto = Produto::factory()->create();

        $response = $this->get(route('produtos.show', $produto));

        $response->assertStatus(200);
        $response->assertViewIs('produtos.show');
        $response->assertViewHas('produto');
    }

    /** @test */
    public function visualizacao_exibe_historico_de_compras_do_produto(): void
    {
        $produto = Produto::factory()->create(['nome' => 'Leite']);
        $compra = Compra::factory()->create();
        ItemCompra::factory()->create([
            'produto_id' => $produto->id,
            'compra_id'  => $compra->id,
            'preco_pago' => 4.50,
            'quantidade' => 2,
        ]);

        $response = $this->get(route('produtos.show', $produto));

        $response->assertStatus(200);
        $response->assertViewHas('historico');
    }

    /** @test */
    public function historico_exibe_preco_pago_em_cada_compra(): void
    {
        $produto = Produto::factory()->create();
        $compra = Compra::factory()->create();
        ItemCompra::factory()->create([
            'produto_id' => $produto->id,
            'compra_id'  => $compra->id,
            'preco_pago' => 7.99,
        ]);

        $response = $this->get(route('produtos.show', $produto));

        $response->assertSee('7,99');
    }

    /** @test */
    public function historico_exibe_quantidade_comprada_por_compra(): void
    {
        $produto = Produto::factory()->create();
        $compra = Compra::factory()->create();
        ItemCompra::factory()->create([
            'produto_id' => $produto->id,
            'compra_id'  => $compra->id,
            'quantidade' => 4,
        ]);

        $response = $this->get(route('produtos.show', $produto));

        $response->assertSee('4');
    }

    /** @test */
    public function historico_exibe_estabelecimento_onde_produto_foi_comprado(): void
    {
        $estabelecimento = Estabelecimento::factory()->create(['nome' => 'Mercado X']);
        $produto = Produto::factory()->create();
        $compra = Compra::factory()->create([
            'estabelecimento_id' => $estabelecimento->id,
        ]);
        ItemCompra::factory()->create([
            'produto_id' => $produto->id,
            'compra_id'  => $compra->id,
        ]);

        $response = $this->get(route('produtos.show', $produto));

        $response->assertSee('Mercado X');
    }

    // -------------------------------------------------------------------------
    // EDIÇÃO
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_acessar_formulario_de_edicao_de_produto(): void
    {
        $produto = Produto::factory()->create();

        $response = $this->get(route('produtos.edit', $produto));

        $response->assertStatus(200);
        $response->assertViewIs('produtos.edit');
        $response->assertViewHas('produto');
    }

    /** @test */
    public function pode_editar_produto(): void
    {
        $produto = Produto::factory()->create([
            'nome'      => 'Nome Original',
            'categoria' => 'Alimentos',
        ]);

        $response = $this->put(route('produtos.update', $produto), [
            'nome'      => 'Nome Editado',
            'categoria' => 'Bebidas',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('produtos', [
            'id'        => $produto->id,
            'nome'      => 'Nome Editado',
            'categoria' => 'Bebidas',
        ]);
    }

    /** @test */
    public function edicao_de_produto_redireciona_com_sucesso(): void
    {
        $produto = Produto::factory()->create();

        $response = $this->put(route('produtos.update', $produto), [
            'nome'      => 'Nome Atualizado',
            'categoria' => 'Higiene',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    // -------------------------------------------------------------------------
    // EXCLUSÃO
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_excluir_produto(): void
    {
        $produto = Produto::factory()->create();

        $response = $this->delete(route('produtos.destroy', $produto));

        $response->assertRedirect(route('produtos.index'));
        $this->assertDatabaseMissing('produtos', [
            'id' => $produto->id,
        ]);
    }

    /** @test */
    public function exclusao_de_produto_redireciona_com_sucesso(): void
    {
        $produto = Produto::factory()->create();

        $response = $this->delete(route('produtos.destroy', $produto));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function produto_inexistente_retorna_404(): void
    {
        $response = $this->get(route('produtos.show', ['produto' => 9999]));

        $response->assertStatus(404);
    }
}
