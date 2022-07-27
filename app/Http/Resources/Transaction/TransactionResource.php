<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
//        return parent::toArray($request);
        return [
            'id'    => $this->id,
            'amount'    => $this->amount,
            'status'    => $this->status,
            'created_at'    => (string)$this->created_at,
            'updated_at'    => (string)$this->updated_at,
        ];
    }
}
