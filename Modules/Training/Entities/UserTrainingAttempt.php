<?php
namespace Modules\Training\Entities;

use Illuminate\Database\Eloquent\Model;

class UserTrainingAttempt extends Model
{
    protected $fillable = [
        'user_id', 'training_id', 'question_id', 'selected_option', 'is_correct'
    ];
}