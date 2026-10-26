<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type?->value,
            'listing_type' => $this->listing_type?->value,
            'status' => $this->status?->value,
            'price' => [
                'amount' => (float) $this->price,
                'currency' => $this->currency,
            ],
            'is_featured' => $this->is_featured,
            'governorate' => $this->whenLoaded('governorate', function () {
                return [
                    'id' => $this->governorate->id,
                    'name' => $this->governorate->name,
                ];
            }),
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(fn ($image) => [
                    'id' => $image->id,
                    'path' => $image->path,
                    'sort_order' => $image->sort_order,
                ]);
            }),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
