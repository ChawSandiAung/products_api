<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'carat' => $this->carat,
            'metal_type' => $this->metal_type,
            'price' => (float) $this->price,
            'stock' => (int) $this->stock,
            'sku' => $this->sku,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}