<?php

namespace Modules\Document\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Document\Traits\DocumentParser;

class DocumentType extends Model
{
    use HasFactory, DocumentParser;

    protected $fillable = [
        'name', 'template','user_visible'
    ];

    protected static function newFactory()
    {
        return \Modules\Document\Database\factories\DocumentTypeFactory::new();
    }
}
