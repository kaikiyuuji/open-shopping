<?php

namespace Tests\Feature;

use App\Jobs\ProcessarExtracaoOcr;
use App\Models\Compra;
use App\Models\ExtracaoOcr;
use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExtracaoOcrTest extends TestCase
{
    use RefreshDatabase;

    /**
     * PNG 1x1 real (sem depender da extensão GD).
     */
    private function imagemFake(): UploadedFile
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        );

        $caminho = tempnam(sys_get_temp_dir(), 'ocr');
        file_put_contents($caminho, $png);

        return new UploadedFile($caminho, 'cupom.png', 'image/png', null, true);
    }

    // -------------------------------------------------------------------------
    // UPLOAD — processamento não pode travar a requisição principal
    // -------------------------------------------------------------------------

    /** @test */
    public function upload_de_cupom_cria_extracao_e_despacha_job_para_fila(): void
    {
        Queue::fake();
        Storage::fake('local');
        $compra = Compra::factory()->create();

        $response = $this->post(route('compras.ocr.store', $compra), [
            'imagem' => $this->imagemFake(),
        ]);

        $response->assertRedirect(route('compras.show', $compra));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('extracoes_ocr', [
            'compra_id' => $compra->id,
            'status'    => ExtracaoOcr::STATUS_PROCESSANDO,
        ]);

        // O OCR roda em job na fila — a requisição nunca chama o serviço direto.
        Queue::assertPushed(ProcessarExtracaoOcr::class, 1);
    }

    /** @test */
    public function upload_exige_arquivo_de_imagem(): void
    {
        Queue::fake();
        $compra = Compra::factory()->create();

        $response = $this->post(route('compras.ocr.store', $compra), []);

        $response->assertSessionHasErrors('imagem');
        Queue::assertNothingPushed();
    }

    // -------------------------------------------------------------------------
    // JOB — integração com o microserviço Python
    // -------------------------------------------------------------------------

    /** @test */
    public function job_salva_itens_extraidos_e_marca_concluida(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ocr/cupom-teste.png', 'fake');

        Http::fake([
            '*/ocr' => Http::response([
                'texto' => 'ARROZ 1 UN x 25,99',
                'itens' => [
                    ['descricao' => 'ARROZ TIO JOAO 5KG', 'quantidade' => 1, 'preco' => 25.99],
                ],
            ]),
        ]);

        $extracao = ExtracaoOcr::factory()->create();

        (new ProcessarExtracaoOcr($extracao))->handle();

        $extracao->refresh();
        $this->assertEquals(ExtracaoOcr::STATUS_CONCLUIDA, $extracao->status);
        $this->assertCount(1, $extracao->itens);
        $this->assertEquals('ARROZ TIO JOAO 5KG', $extracao->itens[0]['descricao']);
        $this->assertEquals(25.99, $extracao->itens[0]['preco_pago']);
    }

    /** @test */
    public function job_marca_falhou_quando_servico_indisponivel(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ocr/cupom-teste.png', 'fake');

        Http::fake(['*/ocr' => Http::response('erro interno', 500)]);

        $extracao = ExtracaoOcr::factory()->create();

        (new ProcessarExtracaoOcr($extracao))->handle();

        $extracao->refresh();
        $this->assertEquals(ExtracaoOcr::STATUS_FALHOU, $extracao->status);
        $this->assertNotNull($extracao->erro);
    }

    // -------------------------------------------------------------------------
    // REVISÃO — usuário confere antes de salvar
    // -------------------------------------------------------------------------

    /** @test */
    public function tela_de_revisao_exibe_itens_extraidos(): void
    {
        $extracao = ExtracaoOcr::factory()->concluida()->create();

        $response = $this->get(route('compras.ocr.revisar', [
            'compra'   => $extracao->compra_id,
            'extracao' => $extracao->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('ocr.revisar');
        $response->assertSee('ARROZ TIO JOAO 5KG');
        $response->assertSee('FEIJAO CARIOCA 1KG');
    }

    /** @test */
    public function extracao_processando_nao_abre_revisao(): void
    {
        $extracao = ExtracaoOcr::factory()->create();

        $response = $this->get(route('compras.ocr.revisar', [
            'compra'   => $extracao->compra_id,
            'extracao' => $extracao->id,
        ]));

        $response->assertRedirect(route('compras.show', $extracao->compra_id));
    }

    /** @test */
    public function extracao_de_outra_compra_retorna_404(): void
    {
        $extracao = ExtracaoOcr::factory()->concluida()->create();
        $outraCompra = Compra::factory()->create();

        $response = $this->get(route('compras.ocr.revisar', [
            'compra'   => $outraCompra->id,
            'extracao' => $extracao->id,
        ]));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // CONFIRMAÇÃO — itens revisados viram produtos e itens reais
    // -------------------------------------------------------------------------

    /** @test */
    public function confirmar_cria_produtos_e_itens_da_compra(): void
    {
        $extracao = ExtracaoOcr::factory()->concluida()->create();

        $response = $this->post(route('compras.ocr.confirmar', [
            'compra'   => $extracao->compra_id,
            'extracao' => $extracao->id,
        ]), [
            'itens' => [
                [
                    'incluir'           => 1,
                    'produto_nome'      => 'Arroz Tio João 5kg',
                    'produto_categoria' => 'Alimentos',
                    'quantidade'        => 1,
                    'preco_pago'        => 25.99,
                ],
            ],
        ]);

        $response->assertRedirect(route('compras.show', $extracao->compra_id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('produtos', [
            'nome'      => 'Arroz Tio João 5kg',
            'categoria' => 'Alimentos',
        ]);
        $this->assertDatabaseHas('itens_compra', [
            'compra_id'  => $extracao->compra_id,
            'quantidade' => 1,
            'preco_pago' => 25.99,
        ]);
        $this->assertDatabaseHas('extracoes_ocr', [
            'id'     => $extracao->id,
            'status' => ExtracaoOcr::STATUS_CONFIRMADA,
        ]);
    }

    /** @test */
    public function confirmar_reutiliza_produto_existente_sem_duplicar(): void
    {
        $produto = Produto::factory()->create(['nome' => 'Leite Integral']);
        $extracao = ExtracaoOcr::factory()->concluida()->create();

        $this->post(route('compras.ocr.confirmar', [
            'compra'   => $extracao->compra_id,
            'extracao' => $extracao->id,
        ]), [
            'itens' => [
                [
                    'incluir'      => 1,
                    'produto_nome' => 'Leite Integral',
                    'quantidade'   => 2,
                    'preco_pago'   => 4.50,
                ],
            ],
        ]);

        $this->assertEquals(1, Produto::where('nome', 'Leite Integral')->count());
        $this->assertDatabaseHas('itens_compra', [
            'produto_id' => $produto->id,
            'quantidade' => 2,
        ]);
    }

    /** @test */
    public function itens_desmarcados_nao_sao_incluidos(): void
    {
        $extracao = ExtracaoOcr::factory()->concluida()->create();

        $this->post(route('compras.ocr.confirmar', [
            'compra'   => $extracao->compra_id,
            'extracao' => $extracao->id,
        ]), [
            'itens' => [
                [
                    'incluir'      => 0,
                    'produto_nome' => 'Produto Ignorado',
                    'quantidade'   => 1,
                    'preco_pago'   => 9.99,
                ],
            ],
        ]);

        $this->assertDatabaseMissing('produtos', ['nome' => 'Produto Ignorado']);
        $this->assertDatabaseCount('itens_compra', 0);
    }

    /** @test */
    public function pode_descartar_extracao(): void
    {
        $extracao = ExtracaoOcr::factory()->concluida()->create();

        $response = $this->delete(route('compras.ocr.destroy', [
            'compra'   => $extracao->compra_id,
            'extracao' => $extracao->id,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('extracoes_ocr', ['id' => $extracao->id]);
    }
}
