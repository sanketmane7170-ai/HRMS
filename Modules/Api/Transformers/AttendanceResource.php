<?php

namespace Modules\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Attendance\Entities\Attendance;

class AttendanceResource extends JsonResource
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
            'status' => $this->status,
            'clock_in' => $this->clock_in,
            'clock_out' => $this->clock_out,
            'break_in' => $this->break_in,
            'break_out' => $this->break_out
            //'user_id' => $this->user_id
        ];
    }
}
