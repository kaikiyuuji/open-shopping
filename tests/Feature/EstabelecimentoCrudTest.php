<?php

namespace Tests\Feature;

use App\Models\Compra;
use App\Models\Estabelecimento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstabelecimentoCrudTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // LISTAGEM
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_listar_estabelecimentos(): void
    {
        Estabelecimento::factory()->count(3)->create();

        $response = $this->get(route('estabelecimentos.index'));

        $response->assertStatus(200);
        $response->assertViewIs('estabelecimentos.index');
        $response->assertViewHas('estabelecimentos');
    }

    /** @test */
    public function listagem_de_estabelecimentos_exibe_nomes(): void
    {
        $estabelecimento = Estabelecimento::factory()->create([
            'nome' => 'Mercado Bom Preço',
        ]);

        $response = $this->get(route('estabelecimentos.index'));

        $response->assertSee('Mercado Bom Preço');
    }

    // -------------------------------------------------------------------------
    // CRIAÇÃO
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_acessar_formulario_de_criacao_de_estabelecimento(): void
    {
        $response = $this->get(route('estabelecimentos.create'));

        $response->assertStatus(200);
        $response->assertViewIs('estabelecimentos.create');
    }

    /** @test */
    public function pode_cadastrar_novo_estabelecimento(): void
    {
        $dados = [
            'nome'      => 'Padaria Central',
            'endereco'  => 'Av. Brasil, 456',
            'categoria' => 'Padaria',
        ];

        $response = $this->post(route('estabelecimentos.store'), $dados);

        $response->assertRedirect(route('estabelecimentos.index'));
        $this->assertDatabaseHas('estabelecimentos', [
            'nome'      => 'Padaria Central',
            'endereco'  => 'Av. Brasil, 456',
            'categoria' => 'Padaria',
        ]);
    }

    /** @test */
    public function cadastro_de_estabelecimento_redireciona_com_sucesso(): void
    {
        $response = $this->post(route('estabelecimentos.store'), [
            'nome'      => 'Farmácia Popular',
            'endereco'  => 'Rua da Saúde, 10',
            'categoria' => 'Farmácia',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    // -------------------------------------------------------------------------
    // VISUALIZAÇÃO
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_visualizar_um_estabelecimento(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->get(route('estabelecimentos.show', $estabelecimento));

        $response->assertStatus(200);
        $response->assertViewIs('estabelecimentos.show');
        $response->assertViewHas('estabelecimento');
    }

    // -------------------------------------------------------------------------
    // EDIÇÃO
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_acessar_formulario_de_edicao_de_estabelecimento(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->get(route('estabelecimentos.edit', $estabelecimento));

        $response->assertStatus(200);
        $response->assertViewIs('estabelecimentos.edit');
        $response->assertViewHas('estabelecimento');
    }

    /** @test */
    public function pode_editar_estabelecimento(): void
    {
        $estabelecimento = Estabelecimento::factory()->create([
            'nome' => 'Nome Antigo',
        ]);

        $response = $this->put(route('estabelecimentos.update', $estabelecimento), [
            'nome'      => 'Nome Novo',
            'endereco'  => $estabelecimento->endereco,
            'categoria' => $estabelecimento->categoria,
        ]);

        $response->assertRedirect(route('estabelecimentos.index'));
        $this->assertDatabaseHas('estabelecimentos', [
            'id'   => $estabelecimento->id,
            'nome' => 'Nome Novo',
        ]);
    }

    /** @test */
    public function edicao_de_estabelecimento_redireciona_com_sucesso(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->put(route('estabelecimentos.update', $estabelecimento), [
            'nome'      => 'Nome Atualizado',
            'endereco'  => 'Novo Endereço, 99',
            'categoria' => 'Supermercado',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    // -------------------------------------------------------------------------
    // EXCLUSÃO
    // -------------------------------------------------------------------------

    /** @test */
    public function pode_excluir_estabelecimento(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->delete(route('estabelecimentos.destroy', $estabelecimento));

        $response->assertRedirect(route('estabelecimentos.index'));
        $this->assertDatabaseMissing('estabelecimentos', [
            'id' => $estabelecimento->id,
        ]);
    }

    /** @test */
    public function exclusao_de_estabelecimento_redireciona_com_sucesso(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->delete(route('estabelecimentos.destroy', $estabelecimento));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function estabelecimento_inexistente_retorna_404(): void
    {
        $response = $this->get(route('estabelecimentos.show', ['estabelecimento' => 9999]));

        $response->assertStatus(404);
    }
}
