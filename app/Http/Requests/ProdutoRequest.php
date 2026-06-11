<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProdutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('produtos', 'nome')->ignore($this->route('produto')),
            ],
            'categoria' => ['required', 'string', 'max:255'],
        ];
    }
}
