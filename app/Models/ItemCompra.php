<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemCompra extends Model
{
    use HasFactory;

    protected $table = 'itens_compra';

    protected $fillable = [
        'compra_id',
        'produto_id',
        'quantidade',
        'preco_pago',
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
            'preco_pago' => 'float',
        ];
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}
