<?php

namespace Modules\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class DeductionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $amount = ''; $status = 'No';
        if($this->deduction_type == 'percentage'){
            $amount = $this->amount.'% ('.$this->percentage_amount.')';
        } else {
            $amount = $this->amount;
        }
        if($this->is_fixed_for_current_month == 0){
            $status = 'No';
        } else {
            $status = 'Yes';
        }
        return [
            'id' => $this->id,
            'title' => $this->title,
            'deduction_type' => $this->deduction_type,
            'amount' => strval($amount),
            'is_fixed_for_current_month' => $status
        ];
    }
}
