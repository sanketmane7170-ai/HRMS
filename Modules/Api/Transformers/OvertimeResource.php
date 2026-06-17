<?php

namespace Modules\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class OvertimeResource extends JsonResource
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
            'overtime_type' => $this->overtime_type,
            'rate_per_hour' => strval($this->rate_per_hour),
            'hours' => strval($this->hours),
            'calculated_amount' => strval($this->calculated_amount),
        ];
    }
}
