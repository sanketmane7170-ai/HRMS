<?php
namespace Modules\Training\Entities;

use Illuminate\Database\Eloquent\Model;

class TrainingAnswer extends Model
{
    protected $fillable = ['training_question_id', 'option_label', 'option_text', 'is_correct'];

    public function question()
    {
        return $this->belongsTo(TrainingQuestion::class, 'training_question_id');
    }
}
