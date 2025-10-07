<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],

            'variants' => ['sometimes', 'array'],
            'variants.*.carat' => ['nullable', 'numeric', 'min:0'],
            'variants.*.metal_type' => ['required_with:variants', Rule::in(['gold', 'white_gold', 'platinum'])],
            'variants.*.price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.stock' => ['required_with:variants', 'integer', 'min:0'],
            'variants.*.sku' => ['required_with:variants', 'string', 'max:255', 'unique:variants,sku'],
        ];
    }
}