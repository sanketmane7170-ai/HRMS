<?php

namespace Modules\Leave\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HalfDayLeave implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ((request()->start_date != request()->end_date) && request()->is_half_day == '1') {
            $fail(__trans('when_half_day_is_selected_then_start _and_ end_date_must_be_same'));
        }
    }
}
