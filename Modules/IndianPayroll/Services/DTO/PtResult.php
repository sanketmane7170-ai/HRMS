<?php

namespace Modules\IndianPayroll\Services\DTO;

final class PtResult
{
    public function __construct(
        public readonly bool $applicable,
        public readonly float $amount,
    ) {
    }
}
