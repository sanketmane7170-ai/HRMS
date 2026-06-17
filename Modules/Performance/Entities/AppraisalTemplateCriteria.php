<?php
namespace Modules\Performance\Entities;

use Illuminate\Database\Eloquent\Model;

class AppraisalTemplateCriteria extends Model
{
    protected $table = 'appraisal_template_criteria'; // <-- Add this

    protected $fillable = [
        'template_id',
        'criteria_name',
        'description',
        'weight',
        'max_score',
        'comments',
    ];

    public function template()
    {
        return $this->belongsTo(AppraisalTemplate::class, 'template_id');
    }
}
