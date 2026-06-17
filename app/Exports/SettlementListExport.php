<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\UserSettlement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SettlementListExport implements FromQuery, WithHeadings, ShouldAutoSize, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        $query = \App\Models\UserSettlement::query();
        return $query;
    }

    public function map($settlement): array
    {
        $data = [
            \App\Models\User::where('id',$settlement->user_id)->value('name'),
            $settlement->hire_date,
            $settlement->departure_date,
            \App\Models\DepartureReason::where('id',$settlement->departure_reason_id)->value('name'),
            $settlement->contract_type,
            $settlement->total_service_duration,
            $settlement->settlement_amount,
        ];

        return $data;
    }

    public function headings(): array
    {
        $headers = [
            __trans('name'),
            __trans('hire_date'),
            __trans('departure_date'),
            __trans('total_service_duration'),
            __trans('departure_reason'),
            __trans('contract_type'),
            __trans('settlement_amount'),
        ];

        return $headers;
    }
}
