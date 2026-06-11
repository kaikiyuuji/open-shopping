<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmarExtracaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'itens'                    => ['required', 'array', 'min:1'],
            'itens.*.incluir'          => ['sometimes', 'boolean'],
            'itens.*.produto_nome'     => ['required_if:itens.*.incluir,1', 'nullable', 'string', 'min:2', 'max:255'],
            'itens.*.produto_categoria' => ['nullable', 'string', 'max:255'],
            'itens.*.quantidade'       => ['required_if:itens.*.incluir,1', 'nullable', 'integer', 'gt:0'],
            'itens.*.preco_pago'       => ['required_if:itens.*.incluir,1', 'nullable', 'numeric', 'gt:0'],
        ];
    }
}
