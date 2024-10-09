<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'fullname' => $this->firstname . ' ' . $this->lastname,
            'phonenumber' => $this->phonenumber,
            'email' => $this->email,
            'address' => $this->address,
        ];
    }
}
