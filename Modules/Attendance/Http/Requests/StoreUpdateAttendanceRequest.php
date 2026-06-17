<?php

namespace Modules\Attendance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Attendance\Enums\AttendanceStatus;

class StoreUpdateAttendanceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'clock_in' => (request()->status == AttendanceStatus::Present->value) ? ['required', 'date_format:H:i'] : [],
            // 'clock_out' => (request()->status == AttendanceStatus::Present->value) ? ['required', 'date_format:H:i', 'after:clock_in'] : [],
            'clock_in' => (request()->status == AttendanceStatus::Present->value) ? ['required', 'date_format:H:i'] : [],
            'clock_out' => (request()->status == AttendanceStatus::Present->value) ? ['required', 'date_format:H:i'] : [],
            'remark' => 'required',
            'clockout_date' => 'required'
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'remark.required' => __trans('the_remark_field_is_required_for_updating_attendance_record'),
            'clock_out.after' => __trans('the_clock_out_time_must_be_after_clock_in_time')
        ];
    }
}
