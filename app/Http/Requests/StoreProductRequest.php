<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string'],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'url'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors(),
            ], 400)
        );
    }
}
