<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\CustomerResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'amount' => $this->amount,
            'status' => $this->status,
            'order_details' => OrderDetailResource::collection(
                $this->whenLoaded('orderDetails')
            ),
            'customer' => new CustomerResource($this->customer),
        ];
    }
}
