<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'description' => $this->description,
            'images' => $this->images,
            'created_at' => $this->created_at->format('d-m-y H:m:s'),
            'updated_at' => $this->updated_at->format('d-m-y H:m:s'),
        ];
    }
}
