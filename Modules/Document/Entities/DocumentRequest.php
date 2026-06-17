<?php

namespace Modules\Document\Entities;

use App\Models\User;
use App\Traits\Query;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\File;
use Modules\Document\Enums\DocumentRequestStatus;
use Modules\Document\Traits\DocumentParser;
use Mpdf\Mpdf;

class DocumentRequest extends Model
{
    use HasFactory, Query, DocumentParser;

    public static function boot()
    {
        static::creating(function ($documentRequest) {
            if (php_sapi_name() != 'cli') {
                $documentRequest->user_id = auth()->id();
            }
        });

        parent::boot();
    }

    protected $fillable = [
        'reason', 'remark', 'user_id', 'document_type_id', 'status','letter_addressed_to','amount'
    ];

    protected $casts = [
        'status' => DocumentRequestStatus::class
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }


    public function getFileName(): string
    {
        return str()->slug($this->type->name) . "_" . $this->id . "_" . str()->slug($this->user->name) . ".pdf";
    }

    /**
     * Generate Html of the requests document and save the file location
     */
    public function generateDocumentPdf($html)
    {
        $mpdf = new Mpdf(['tempDir' => public_path('uploads/mpdf/temp')]);
        $mpdf->WriteHTML($html);
        //call watermark content aand image
        $mpdf->SetWatermarkText(getSetting('site_title'));
        $mpdf->showWatermarkText = true;
        $mpdf->watermarkTextAlpha = 0.1;
        $filename = $this->getFileName();
        $location = "uploads/users/$this->user_id/document-request";
        $storagePath = public_path($location);
        if (!File::isDirectory($storagePath)) {
            File::makeDirectory($storagePath, 0777, true, true);
        }
        $mpdf->Output("$storagePath/$filename");
        $this->file_path = "$location/$filename";
        $this->status = DocumentRequestStatus::Completed;
        $this->save();
    }
}
