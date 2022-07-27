<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Transaction\TransactionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
//        return parent::toArray($request);
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'created_at' => (string)$this->created_at,
            'updated_at' => (string)$this->updated_at,
            'transactions' => TransactionResource::collection($this->whenLoaded('latest_transactions'))
        ];
    }
}
