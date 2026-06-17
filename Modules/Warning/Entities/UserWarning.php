<?php

namespace Modules\Warning\Entities;

use App\Models\User;
use App\Traits\Query;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Warning\Enums\WarningType;

class UserWarning extends Model
{
    use HasFactory, Query;

    protected $fillable = [
        'user_id', 'date', 'detail', 'type', 'acknowledgement', 'document', 'ack_datetime', 'ack_document'
    ];

    protected $casts = [
        'type' => WarningType::class
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFileName(): string
    {
        return str()->slug($this->type->name) . "_" . $this->id . "_" . str()->slug($this->user->name) . ".pdf";
    }
}
