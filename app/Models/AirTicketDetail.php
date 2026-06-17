<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirTicketDetail extends Model
{
    use HasFactory;

    // Table name (optional if table name matches plural of model)
    protected $table = 'air_ticket_details';

    // Fields that can be mass assigned
    protected $fillable = [
        'title',
        'qty',
        'percentage',
    ];

    // If your table uses timestamps (created_at, updated_at)
    public $timestamps = true;

    // Optional: cast percentage to float
    protected $casts = [
        'percentage' => 'float',
    ];
}
