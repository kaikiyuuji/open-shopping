<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtracaoOcr extends Model
{
    use HasFactory;

    public const STATUS_PROCESSANDO = 'processando';
    public const STATUS_CONCLUIDA = 'concluida';
    public const STATUS_FALHOU = 'falhou';
    public const STATUS_CONFIRMADA = 'confirmada';

    protected $table = 'extracoes_ocr';

    protected $fillable = [
        'compra_id',
        'imagem_path',
        'status',
        'itens',
        'erro',
    ];

    protected function casts(): array
    {
        return [
            'itens' => 'array',
        ];
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }
}
