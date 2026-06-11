<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExtracaoOcrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'imagem' => ['required', 'image', 'max:10240'],
        ];
    }
}
