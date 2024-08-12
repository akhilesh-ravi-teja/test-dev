<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantResource extends JsonResource
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
            'outlet_id' => $this->id,
            'restaurant_name' => $this->name,
            'user_id'=>$this->restautant_id,
            'logo'=>$this->logo,
            'address'=>$this->address,
            'updated_at' => $this->updated_at->format('d/m/Y'),
            'deleted_at' => $this->deleted_at->format('d/m/Y'),
        ];
    }
}
