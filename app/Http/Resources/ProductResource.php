<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        $base = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'base_price' => (float) $this->base_price,
            'slug' => $this->slug,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];

        // If relation loaded
        if ($this->relationLoaded('variants')) {
            $base['variants'] = VariantResource::collection($this->variants);
            $base['variants_count'] = $this->variants->count();
        } else {
            $base['variants_count'] = $this->variants_count ?? null;
        }

        return $base;
    }
}