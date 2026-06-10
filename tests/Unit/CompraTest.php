<?php

namespace Tests\Unit;

use App\Models\Compra;
use App\Models\Estabelecimento;
use App\Models\ItemCompra;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompraTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function compra_pertence_a_um_estabelecimento(): void
    {
        $estabelecimento = Estabelecimento::factory()->create();
        $compra = Compra::factory()->create([
            'estabelecimento_id' => $estabelecimento->id,
        ]);

        $this->assertInstanceOf(Estabelecimento::class, $compra->estabelecimento);
        $this->assertEquals($estabelecimento->id, $compra->estabelecimento->id);
    }

    /** @test */
    public function compra_herda_categoria_do_estabelecimento(): void
    {
        $estabelecimento = Estabelecimento::factory()->create([
            'categoria' => 'Supermercado',
        ]);

        $compra = Compra::factory()->create([
            'estabelecimento_id' => $estabelecimento->id,
        ]);

        $this->assertEquals('Supermercado', $compra->categoria);
    }

    /** @test */
    public function compra_tem_data(): void
    {
        $compra = Compra::factory()->create([
            'data' => '2025-03-15',
        ]);

        $this->assertNotNull($compra->data);
    }

    /** @test */
    public function compra_tem_valor_total(): void
    {
        $compra = Compra::factory()->create([
            'valor_total' => 150.75,
        ]);

        $this->assertEquals(150.75, $compra->valor_total);
    }

    /** @test */
    public function compra_tem_forma_de_pagamento(): void
    {
        $compra = Compra::factory()->create([
            'forma_pagamento' => 'credito',
        ]);

        $this->assertEquals('credito', $compra->forma_pagamento);
    }

    /** @test */
    public function compra_pode_ser_a_vista(): void
    {
        $compra = Compra::factory()->create([
            'forma_pagamento' => 'debito',
            'parcelado'       => false,
        ]);

        $this->assertFalse((bool) $compra->parcelado);
    }

    /** @test */
    public function compra_pode_ser_parcelada(): void
    {
        $compra = Compra::factory()->create([
            'forma_pagamento'   => 'credito',
            'parcelado'         => true,
            'numero_parcelas'   => 3,
            'valor_total'       => 300.00,
        ]);

        $this->assertTrue((bool) $compra->parcelado);
        $this->assertEquals(3, $compra->numero_parcelas);
    }

    /** @test */
    public function compra_pode_ser_paga_em_dinheiro(): void
    {
        $compra = Compra::factory()->create([
            'forma_pagamento' => 'dinheiro',
            'parcelado'       => false,
        ]);

        $this->assertEquals('dinheiro', $compra->forma_pagamento);
        $this->assertFalse((bool) $compra->parcelado);
    }

    /** @test */
    public function compra_parcelada_calcula_automaticamente_valor_da_parcela(): void
    {
        $compra = Compra::factory()->create([
            'forma_pagamento' => 'credito',
            'parcelado'       => true,
            'numero_parcelas' => 4,
            'valor_total'     => 400.00,
        ]);

        $this->assertEquals(100.00, $compra->valor_parcela);
    }

    /** @test */
    public function compra_a_vista_nao_tem_parcelamento(): void
    {
        $compra = Compra::factory()->create([
            'forma_pagamento' => 'debito',
            'parcelado'       => false,
            'numero_parcelas' => null,
        ]);

        $this->assertFalse((bool) $compra->parcelado);
        $this->assertNull($compra->numero_parcelas);
    }

    /** @test */
    public function compra_em_dinheiro_nao_tem_parcelamento(): void
    {
        $compra = Compra::factory()->create([
            'forma_pagamento' => 'dinheiro',
            'parcelado'       => false,
            'numero_parcelas' => null,
        ]);

        $this->assertFalse((bool) $compra->parcelado);
        $this->assertNull($compra->numero_parcelas);
    }

    /** @test */
    public function compra_tem_muitos_itens(): void
    {
        $compra = Compra::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $compra->itens()
        );
    }

    /** @test */
    public function compra_pode_ter_varios_itens(): void
    {
        $compra = Compra::factory()->create();

        ItemCompra::factory()->count(5)->create([
            'compra_id' => $compra->id,
        ]);

        $this->assertCount(5, $compra->itens);
    }

    /** @test */
    public function compra_sem_itens_retorna_colecao_vazia(): void
    {
        $compra = Compra::factory()->create();

        $this->assertCount(0, $compra->itens);
    }

    /** @test */
    public function formas_de_pagamento_validas_sao_definidas(): void
    {
        $formas = Compra::formasPagamento();

        $this->assertIsArray($formas);
        $this->assertContains('credito', $formas);
        $this->assertContains('debito', $formas);
        $this->assertContains('dinheiro', $formas);
    }
}
