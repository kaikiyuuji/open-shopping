<?php

namespace App\Http\Requests;

use App\Models\Compra;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estabelecimento_id' => ['required', 'exists:estabelecimentos,id'],
            'data'               => ['required', 'date'],
            'valor_total'        => ['required', 'numeric', 'gt:0'],
            'forma_pagamento'    => ['required', Rule::in(Compra::formasPagamento())],
            'parcelado'          => ['sometimes', 'boolean'],
            'numero_parcelas'    => [
                Rule::requiredIf($this->boolean('parcelado')),
                'nullable',
                'integer',
                'min:2',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->boolean('parcelado') && $this->input('forma_pagamento') !== 'credito') {
                $validator->errors()->add(
                    'parcelado',
                    'Apenas compras no crédito podem ser parceladas.'
                );
            }
        });
    }
}
