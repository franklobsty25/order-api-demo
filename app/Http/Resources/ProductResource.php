<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\OrderDetailResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'unit_price' => $this->unit_price,
            'quantity' => $this->quantity,
            'order_details' => OrderDetailResource::collection(
                $this->whenLoaded('orderDetails')
            ),
        ];
    }
}
