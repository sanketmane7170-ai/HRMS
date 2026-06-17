<?php

namespace Modules\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Document\Entities\DocumentType;
use Modules\Document\Entities\DocumentRequest;

class ServiceRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $data = DocumentRequest::with(['type'])->where('id',$this->id)->first();
        return [
            'id' => $this->id,
            'reason' => $this->reason,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateString(),
            'document_type' => DocumentType::select('id','name')->where('id',$this->document_type_id)->first(),
            'document_download_url' => asset($this->file_path),
            'letter_addressed_to' => $this->letter_addressed_to
        ];
    }
}
