<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GovernorateStatisticsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'counts' => $this->resource['counts'],
        ];
    }
}
