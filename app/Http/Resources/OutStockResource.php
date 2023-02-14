<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OutStockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);

        return [
            'id' => $this->id,
            'name' => $this->stocks ? $this->stocks->items->name : "null",
            'acceptor' => $this->acceptor,
            'stock_id' => $this->stock_id,
            // 'stock_id' => $this->stocks ? $this->stocks->items->id : "null",
            'sender' => $this->sender,
            'quantity' => (int)$this->quantity < 0 ? 0 : $this->quantity,
            'created_at' => $this->created_at,
        ];
    }
}