<?php

namespace Modules\IndianPayroll\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\IndianPayroll\Entities\Traits\LogsStatutoryChanges;

class IncomeTaxSlab extends Model
{
    use SoftDeletes, LogsStatutoryChanges;

    protected $table = 'ip_income_tax_slabs';

    public const REGIME_OLD = 'old';
    public const REGIME_NEW = 'new';

    protected $fillable = ['financial_year', 'regime', 'slab_from', 'slab_to', 'rate'];

    public static function forRegime(string $financialYear, string $regime)
    {
        return static::where('financial_year', $financialYear)
            ->where('regime', $regime)
            ->orderBy('slab_from')
            ->get();
    }
}
