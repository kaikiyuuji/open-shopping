<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estabelecimento extends Model
{
    use HasFactory;

    protected $table = 'estabelecimentos';

    protected $fillable = [
        'nome',
        'endereco',
        'categoria',
    ];

    /**
     * Categorias pré-definidas para estabelecimentos.
     *
     * @return array<int, string>
     */
    public static function categorias(): array
    {
        return [
            'Supermercado',
            'Farmácia',
            'Padaria',
            'Restaurante',
            'Loja',
            'Outros',
        ];
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class);
    }
}
