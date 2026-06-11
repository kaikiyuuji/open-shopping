<?php

namespace App\Http\Requests;

use App\Models\Estabelecimento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EstabelecimentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome'      => ['required', 'string', 'min:2', 'max:255'],
            'endereco'  => ['required', 'string', 'max:255'],
            'categoria' => ['required', Rule::in(Estabelecimento::categorias())],
        ];
    }
}
