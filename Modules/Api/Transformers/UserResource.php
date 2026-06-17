<?php

namespace Modules\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'profile_image' => $this->getProfileImage(),
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'designation' => new DesignationResource($this->whenLoaded('designation'))
        ];
    }
}
