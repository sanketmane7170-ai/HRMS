<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; text-align: center; padding: 5px; }
        th { background-color: #f2f2f2; }
        .header { text-align: left; font-weight: bold; }
        .title { text-align: center; font-weight: bold; font-size: 16px; }
    </style>
</head>
<body>
    @php
        $monthDays = now()->month($month)->daysInMonth;
    @endphp
    
    <table class="table table-hover">
        <thead>
            <tr>
                @foreach (\Modules\Attendance\Enums\AttendanceStatus::cases() as $case)
                <td>
                    @php
                        $imagePath = public_path('modules/attendance/images/' . $case->value . '.svg');
                        $mimeType = mime_content_type($imagePath);
                        $imageData = base64_encode(file_get_contents($imagePath));
                        $src = 'data:' . $mimeType . ';base64,' . $imageData;
                    @endphp
                    @if($case->value =='sickleave')
                        <img src="{{ $src }}" style="width: 20px !important;height: 20px !important;position: relative !important; top: 03px !important;" /> {{ __trans('Sick-Leave') }}
                    @else
                        <img src="{{ $src }}" style="width: 16px;height: 16px;" /> {{ __trans($case->value) }}
                    @endif
                </td>
                @endforeach
                <td>
                    @php
                        $pimagePath = public_path('modules/attendance/images/present_full.svg');
                        $mimeType = mime_content_type($imagePath);
                        $pimageData = base64_encode(file_get_contents($pimagePath));
                        $psrc = 'data:' . $mimeType . ';base64,' . $pimageData;
                    @endphp
                    <img src="{{ $psrc }}" style="width: 16px;height: 16px;" /> {{ __trans('Present (with checkout)') }}
                </td>
                <td>
                    @php
                        $pimagePath = public_path('modules/attendance/images/weekendWork.svg');
                        $mimeType = mime_content_type($imagePath);
                        $pimageData = base64_encode(file_get_contents($pimagePath));
                        $psrc = 'data:' . $mimeType . ';base64,' . $pimageData;
                    @endphp
                    <img src="{{ $psrc }}" style="width: 16px;height: 16px;" /> {{ __trans('Cancel Off') }}
                </td>
                <td>
                    @php
                        $pimagePath = public_path('modules/attendance/images/vacation.png');
                        $mimeType = mime_content_type($imagePath);
                        $pimageData = base64_encode(file_get_contents($pimagePath));
                        $vsrc = 'data:' . $mimeType . ';base64,' . $pimageData;
                    @endphp
                    <img src="{{ $vsrc }}" style="width: 16px;height: 16px;" /> {{ __trans('Vacation') }}
                </td>
                <td>
                    @php
                        $pimagePath = public_path('modules/attendance/images/PH.jpeg');
                        $mimeType = mime_content_type($imagePath);
                        $pimageData = base64_encode(file_get_contents($pimagePath));
                        $phsrc = 'data:' . $mimeType . ';base64,' . $pimageData;
                    @endphp
                    <img src="{{ $phsrc }}" style="width: 16px;height: 16px;" /> {{ __trans('PH') }}
                </td>
                <td>
                    @php
                        $pimagePath = public_path('modules/attendance/images/unpaid.jpeg');
                        $mimeType = mime_content_type($imagePath);
                        $pimageData = base64_encode(file_get_contents($pimagePath));
                        $unsrc = 'data:' . $mimeType . ';base64,' . $pimageData;
                    @endphp
                    <img src="{{ $unsrc }}" style="width: 16px;height: 16px;" /> {{ __trans('Unpaid') }}
                </td>
                <td>
                    @php
                        $viimagePath = public_path('modules/attendance/images/visit_in.jpeg');
                        $mimeType = mime_content_type($viimagePath);
                        $vimageData = base64_encode(file_get_contents($viimagePath));
                        $visitsrc = 'data:' . $mimeType . ';base64,' . $vimageData;
                    @endphp
                    <img src="{{ $visitsrc }}" style="width:16px;" /> {{ __trans('Visit') }}
                </td>
            </tr>
        </thead>
    </table>
       
    <table class="table table-hover">
        <thead>
            <tr>
                <td>{{ __trans('employee') }}</td>
                @for ($i = 1; $i <= $monthDays; $i++) 
                    <td><?= $i ?></td>
                @endfor
                <td class="text-end">@if(getSetting('payroll_calculation') == 'hourly'){{ __trans('total_hrs') }} @else {{ __trans('total') }}@endif</td>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
            @include('attendance::attendance.partials.user-attendance-row-pdf')
            @endforeach
        </tbody>
    </table>
    <br>
</body>
</html>