<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDependentDocument extends Model
{
    use HasFactory;

    protected $fillable = ['user_dependent_id', 'document_name', 'document'];

    /**
     * Define the relationship with the UserDocument model.
     */
    public function userDocument()
    {
        return $this->belongsTo(UserDocument::class, 'user_documents_id');
    }
    public function dependentDocuments()
    {
        return $this->hasMany(UserDependentDocument::class, 'user_dependent_id');
    }
}
