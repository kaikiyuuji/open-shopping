<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'produto_novo'      => ['sometimes', 'boolean'],
            'produto_id'        => [Rule::requiredIf(! $this->boolean('produto_novo')), 'nullable', 'exists:produtos,id'],
            'produto_nome'      => ['required_if:produto_novo,true', 'nullable', 'string', 'min:2', 'max:255'],
            'produto_categoria' => ['required_if:produto_novo,true', 'nullable', 'string', 'max:255'],
            'quantidade'        => ['required', 'integer', 'gt:0'],
            'preco_pago'        => ['required', 'numeric', 'gt:0'],
        ];
    }
}
