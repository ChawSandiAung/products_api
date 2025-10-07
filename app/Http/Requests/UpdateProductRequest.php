<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('id');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'base_price' => ['sometimes', 'numeric', 'min:0'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],

            'variants' => ['sometimes', 'array'],
            'variants.*.id' => ['sometimes', 'integer', 'exists:variants,id'],
            'variants.*.carat' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'variants.*.metal_type' => ['sometimes', Rule::in(['gold', 'white_gold', 'platinum'])],
            'variants.*.price' => ['sometimes', 'numeric', 'min:0'],
            'variants.*.stock' => ['sometimes', 'integer', 'min:0'],
            // We'll inject per-item unique rule for sku in withValidator()
            'variants_to_delete' => ['sometimes', 'array'],
            'variants_to_delete.*' => ['integer', 'exists:variants,id'],
        ];
    }

    public function withValidator($validator)
    {
        $variants = $this->input('variants', []);

        if (!is_array($variants)) {
            return;
        }

        foreach ($variants as $index => $variant) {
            // If the row includes an id, ignore that id for unique check; else a normal unique check
            $ignoreId = $variant['id'] ?? null;

            $validator->sometimes(
                "variants.$index.sku",
                [
                    'string',
                    'max:255',
                    Rule::unique('variants', 'sku')->ignore($ignoreId),
                ],
                fn () => array_key_exists('sku', $variant)
            );
        }
    }
}