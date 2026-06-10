<?php

namespace Tests\Feature;

use App\Models\Compra;
use App\Models\Estabelecimento;
use App\Models\ItemCompra;
use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidacaoEstabelecimentoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function nome_do_estabelecimento_e_obrigatorio(): void
    {
        $response = $this->post(route('estabelecimentos.store'), [
            'nome'      => '',
            'endereco'  => 'Rua Teste, 1',
            'categoria' => 'Supermercado',
        ]);

        $response->assertSessionHasErrors('nome');
    }

    /** @test */
    public function endereco_do_estabelecimento_e_obrigatorio(): void
    {
        $response = $this->post(route('estabelecimentos.store'), [
            'nome'      => 'Loja X',
            'endereco'  => '',
            'categoria' => 'Supermercado',
        ]);

        $response->assertSessionHasErrors('endereco');
    }

    /** @test */
    public function categoria_do_estabelecimento_e_obrigatoria(): void
    {
        $response = $this->post(route('estabelecimentos.store'), [
            'nome'      => 'Loja X',
            'endereco'  => 'Rua Teste, 1',
            'categoria' => '',
        ]);

        $response->assertSessionHasErrors('categoria');
    }

    /** @test */
    public function categoria_do_estabelecimento_deve_ser_valida(): void
    {
        $response = $this->post(route('estabelecimentos.store'), [
            'nome'      => 'Loja X',
            'endereco'  => 'Rua Teste, 1',
            'categoria' => 'CategoriaInexistente',
        ]);

        $response->assertSessionHasErrors('categoria');
    }

    /** @test */
    public function nome_do_estabelecimento_deve_ter_pelo_menos_2_caracteres(): void
    {
        $response = $this->post(route('estabelecimentos.store'), [
            'nome'      => 'A',
            'endereco'  => 'Rua Teste, 1',
            'categoria' => 'Supermercado',
        ]);

        $response->assertSessionHasErrors('nome');
    }
}

class ValidacaoProdutoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function nome_do_produto_e_obrigatorio(): void
    {
        $response = $this->post(route('produtos.store'), [
            'nome'      => '',
            'categoria' => 'Alimentos',
        ]);

        $response->assertSessionHasErrors('nome');
    }

    /** @test */
    public function categoria_do_produto_e_obrigatoria(): void
    {
        $response = $this->post(route('produtos.store'), [
            'nome'      => 'Produto Teste',
            'categoria' => '',
        ]);

        $response->assertSessionHasErrors('categoria');
    }

    /** @test */
    public function nome_do_produto_deve_ser_unico(): void
    {
        Produto::factory()->create(['nome' => 'Arroz']);

        $response = $this->post(route('produtos.store'), [
            'nome'      => 'Arroz',
            'categoria' => 'Alimentos',
        ]);

        $response->assertSessionHasErrors('nome');
    }

    /** @test */
    public function nome_do_produto_pode_ser_editado_sem_conflito_com_si_mesmo(): void
    {
        $produto = Produto::factory()->create(['nome' => 'Feijão']);

        $response = $this->put(route('produtos.update', $produto), [
            'nome'      => 'Feijão',
            'categoria' => 'Alimentos',
        ]);

        $response->assertSessionMissingErrors('nome');
        $response->assertRedirect();
    }

    /** @test */
    public function nome_do_produto_deve_ter_pelo_menos_2_caracteres(): void
    {
        $response = $this->post(route('produtos.store'), [
            'nome'      => 'A',
            'categoria' => 'Alimentos',
        ]);

        $response->assertSessionHasErrors('nome');
    }
}

class ValidacaoCompraTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function estabelecimento_da_compra_e_obrigatorio(): void
    {
        $response = $this->post(route('compras.store'), [
            'estabelecimento_id' => '',
            'data'               => '2025-04-01',
            'valor_total'        => 100.00,
            'forma_pagamento'    => 'credito',
        ]);

        $response->assertSessionHasErrors('estabelecimento_id');
    }

    /** @test */
    public function estabelecimento_da_compra_deve_existir(): void
    {
        $response = $this->post(route('compras.store'), [
            'estabelecimento_id' => 9999,
            'data'               => '2025-04-01',
            'valor_total'        => 100.00,
            'forma_pagamento'    => 'credito',
        ]);

        $response->assertSessionHasErrors('estabelecimento_id');
    }

    /** @test */
    public function data_da_compra_e_obrigatoria(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->post(route('compras.store'), [
            'estabelecimento_id' => $estabelecimento->id,
            'data'               => '',
            'valor_total'        => 100.00,
            'forma_pagamento'    => 'credito',
        ]);

        $response->assertSessionHasErrors('data');
    }

    /** @test */
    public function data_da_compra_deve_ser_valida(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->post(route('compras.store'), [
            'estabelecimento_id' => $estabelecimento->id,
            'data'               => 'data-invalida',
            'valor_total'        => 100.00,
            'forma_pagamento'    => 'credito',
        ]);

        $response->assertSessionHasErrors('data');
    }

    /** @test */
    public function valor_total_da_compra_e_obrigatorio(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->post(route('compras.store'), [
            'estabelecimento_id' => $estabelecimento->id,
            'data'               => '2025-04-01',
            'valor_total'        => '',
            'forma_pagamento'    => 'credito',
        ]);

        $response->assertSessionHasErrors('valor_total');
    }

    /** @test */
    public function valor_total_da_compra_deve_ser_numerico(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->post(route('compras.store'), [
            'estabelecimento_id' => $estabelecimento->id,
            'data'               => '2025-04-01',
            'valor_total'        => 'abc',
            'forma_pagamento'    => 'credito',
        ]);

        $response->assertSessionHasErrors('valor_total');
    }

    /** @test */
    public function valor_total_da_compra_deve_ser_maior_que_zero(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->post(route('compras.store'), [
            'estabelecimento_id' => $estabelecimento->id,
            'data'               => '2025-04-01',
            'valor_total'        => 0,
            'forma_pagamento'    => 'credito',
        ]);

        $response->assertSessionHasErrors('valor_total');
    }

    /** @test */
    public function forma_de_pagamento_e_obrigatoria(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->post(route('compras.store'), [
            'estabelecimento_id' => $estabelecimento->id,
            'data'               => '2025-04-01',
            'valor_total'        => 100.00,
            'forma_pagamento'    => '',
        ]);

        $response->assertSessionHasErrors('forma_pagamento');
    }

    /** @test */
    public function forma_de_pagamento_deve_ser_valida(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->post(route('compras.store'), [
            'estabelecimento_id' => $estabelecimento->id,
            'data'               => '2025-04-01',
            'valor_total'        => 100.00,
            'forma_pagamento'    => 'boleto',
        ]);

        $response->assertSessionHasErrors('forma_pagamento');
    }

    /** @test */
    public function compra_parcelada_requer_numero_de_parcelas(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->post(route('compras.store'), [
            'estabelecimento_id' => $estabelecimento->id,
            'data'               => '2025-04-01',
            'valor_total'        => 300.00,
            'forma_pagamento'    => 'credito',
            'parcelado'          => true,
            'numero_parcelas'    => '',
        ]);

        $response->assertSessionHasErrors('numero_parcelas');
    }

    /** @test */
    public function compra_parcelada_requer_pelo_menos_2_parcelas(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->post(route('compras.store'), [
            'estabelecimento_id' => $estabelecimento->id,
            'data'               => '2025-04-01',
            'valor_total'        => 300.00,
            'forma_pagamento'    => 'credito',
            'parcelado'          => true,
            'numero_parcelas'    => 1,
        ]);

        $response->assertSessionHasErrors('numero_parcelas');
    }

    /** @test */
    public function compra_nao_parcelada_ignora_numero_de_parcelas(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->post(route('compras.store'), [
            'estabelecimento_id' => $estabelecimento->id,
            'data'               => '2025-04-01',
            'valor_total'        => 100.00,
            'forma_pagamento'    => 'debito',
            'parcelado'          => false,
        ]);

        $response->assertSessionMissingErrors('numero_parcelas');
        $response->assertRedirect();
    }

    /** @test */
    public function dinheiro_nao_pode_ser_parcelado(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->post(route('compras.store'), [
            'estabelecimento_id' => $estabelecimento->id,
            'data'               => '2025-04-01',
            'valor_total'        => 100.00,
            'forma_pagamento'    => 'dinheiro',
            'parcelado'          => true,
            'numero_parcelas'    => 3,
        ]);

        $response->assertSessionHasErrors(['parcelado']);
    }

    /** @test */
    public function debito_nao_pode_ser_parcelado(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();

        $response = $this->post(route('compras.store'), [
            'estabelecimento_id' => $estabelecimento->id,
            'data'               => '2025-04-01',
            'valor_total'        => 100.00,
            'forma_pagamento'    => 'debito',
            'parcelado'          => true,
            'numero_parcelas'    => 2,
        ]);

        $response->assertSessionHasErrors(['parcelado']);
    }
}

class ValidacaoItemCompraTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function produto_do_item_e_obrigatorio(): void
    {
        $compra = Compra::factory()->create();

        $response = $this->post(route('compras.itens.store', $compra), [
            'produto_id' => '',
            'quantidade' => 2,
            'preco_pago' => 5.00,
        ]);

        $response->assertSessionHasErrors('produto_id');
    }

    /** @test */
    public function produto_do_item_deve_existir(): void
    {
        $compra = Compra::factory()->create();

        $response = $this->post(route('compras.itens.store', $compra), [
            'produto_id' => 9999,
            'quantidade' => 2,
            'preco_pago' => 5.00,
        ]);

        $response->assertSessionHasErrors('produto_id');
    }

    /** @test */
    public function quantidade_do_item_e_obrigatoria(): void
    {
        $compra = Compra::factory()->create();
        $produto = Produto::factory()->create();

        $response = $this->post(route('compras.itens.store', $compra), [
            'produto_id' => $produto->id,
            'quantidade' => '',
            'preco_pago' => 5.00,
        ]);

        $response->assertSessionHasErrors('quantidade');
    }

    /** @test */
    public function quantidade_do_item_deve_ser_inteira(): void
    {
        $compra = Compra::factory()->create();
        $produto = Produto::factory()->create();

        $response = $this->post(route('compras.itens.store', $compra), [
            'produto_id' => $produto->id,
            'quantidade' => 1.5,
            'preco_pago' => 5.00,
        ]);

        $response->assertSessionHasErrors('quantidade');
    }

    /** @test */
    public function quantidade_do_item_deve_ser_maior_que_zero(): void
    {
        $compra = Compra::factory()->create();
        $produto = Produto::factory()->create();

        $response = $this->post(route('compras.itens.store', $compra), [
            'produto_id' => $produto->id,
            'quantidade' => 0,
            'preco_pago' => 5.00,
        ]);

        $response->assertSessionHasErrors('quantidade');
    }

    /** @test */
    public function preco_pago_do_item_e_obrigatorio(): void
    {
        $compra = Compra::factory()->create();
        $produto = Produto::factory()->create();

        $response = $this->post(route('compras.itens.store', $compra), [
            'produto_id' => $produto->id,
            'quantidade' => 2,
            'preco_pago' => '',
        ]);

        $response->assertSessionHasErrors('preco_pago');
    }

    /** @test */
    public function preco_pago_do_item_deve_ser_numerico(): void
    {
        $compra = Compra::factory()->create();
        $produto = Produto::factory()->create();

        $response = $this->post(route('compras.itens.store', $compra), [
            'produto_id' => $produto->id,
            'quantidade' => 2,
            'preco_pago' => 'abc',
        ]);

        $response->assertSessionHasErrors('preco_pago');
    }

    /** @test */
    public function preco_pago_do_item_deve_ser_maior_que_zero(): void
    {
        $compra = Compra::factory()->create();
        $produto = Produto::factory()->create();

        $response = $this->post(route('compras.itens.store', $compra), [
            'produto_id' => $produto->id,
            'quantidade' => 2,
            'preco_pago' => 0,
        ]);

        $response->assertSessionHasErrors('preco_pago');
    }
}
