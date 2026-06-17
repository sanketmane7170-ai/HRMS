<?php

namespace Modules\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Attendance\Entities\Attendance;

class TimelineResource extends JsonResource
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
            'date' => $this->date,
            'time' => $this->time,
            'type' => $this->type,
            'action' => $this->action,
        ];
    }
}
