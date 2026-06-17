<?php

namespace Modules\Recruitment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OfferLetterTemplate extends Model
{
    use HasFactory;

    protected $table = 'recruitment_offer_letter_templates';

    protected $fillable = [
        'name',
        'content',
        'created_by'
    ];

    /**
     * Get the user who created the template.
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
