<?php

namespace Modules\Api\Transformers\Leave;

use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
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
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'type' => new TypeListResource($this->whenLoaded('type')),
            'reason' => $this->reason,
            'remark' => $this->remark,
            'is_half_day' => $this->is_half_day,
            'leave_type_id' => $this->leave_type_id,
            'status' => $this->status
        ];
    }
}
