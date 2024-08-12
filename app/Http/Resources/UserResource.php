<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'restaurant_id'=>$this->restautant_id,
            'role'=>$this->role,
            'email'=>$this->email,
            'country_code'=>$this->country_code,
            'device_details'=>$this->device_details,
            'status'=>$this->status,
            'token'=>$this->token,
            'created_by'=>$this->created_by,
            'created_at' => $this->created_at->format('d/m/Y'),
            'updated_at' => $this->updated_at->format('d/m/Y'),
            'deleted_at'=>$this->deleted_at,
        ];
    }
}
