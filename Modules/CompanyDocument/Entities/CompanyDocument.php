<?php
namespace Modules\CompanyDocument\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'legal_trade_name',
        'license_number',
        'license_expiry',
        'added_date',
        'mol_code',
        'document',
        'employer_reference',
        'short_name',
        'routing_number',
        'column_index',
        'logo',
        'small_logo',
        'sign',
        'header',
        'footer',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'company_document_id');
    }

}
