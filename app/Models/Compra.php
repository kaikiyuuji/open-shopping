<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    use HasFactory;

    protected $table = 'compras';

    protected $fillable = [
        'estabelecimento_id',
        'data',
        'valor_total',
        'forma_pagamento',
        'parcelado',
        'numero_parcelas',
    ];

    protected function casts(): array
    {
        return [
            'data'        => 'date',
            'valor_total' => 'float',
            'parcelado'   => 'boolean',
        ];
    }

    /**
     * Formas de pagamento válidas.
     *
     * @return array<int, string>
     */
    public static function formasPagamento(): array
    {
        return ['credito', 'debito', 'dinheiro'];
    }

    public function estabelecimento(): BelongsTo
    {
        return $this->belongsTo(Estabelecimento::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ItemCompra::class);
    }

    public function extracoes(): HasMany
    {
        return $this->hasMany(ExtracaoOcr::class);
    }

    /**
     * Categoria herdada do estabelecimento.
     */
    public function getCategoriaAttribute(): ?string
    {
        return $this->estabelecimento?->categoria;
    }

    /**
     * Valor da parcela calculado automaticamente para compras parceladas.
     */
    public function getValorParcelaAttribute(): ?float
    {
        if (! $this->parcelado || ! $this->numero_parcelas) {
            return null;
        }

        return round($this->valor_total / $this->numero_parcelas, 2);
    }
}
