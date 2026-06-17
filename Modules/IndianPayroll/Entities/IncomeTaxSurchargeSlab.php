<?php

namespace Modules\IndianPayroll\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\IndianPayroll\Entities\Traits\LogsStatutoryChanges;

class IncomeTaxSurchargeSlab extends Model
{
    use SoftDeletes, LogsStatutoryChanges;

    protected $table = 'ip_income_tax_surcharge_slabs';

    protected $fillable = ['financial_year', 'regime', 'income_from', 'income_to', 'surcharge_rate'];

    public static function forRegime(string $financialYear, string $regime)
    {
        return static::where('financial_year', $financialYear)
            ->where('regime', $regime)
            ->orderBy('income_from')
            ->get();
    }
}
