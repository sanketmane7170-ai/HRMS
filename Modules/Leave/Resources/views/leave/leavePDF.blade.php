<!DOCTYPE html>
<html>
<head>
    <style>
        .card {
            padding: 15px;
            background: #fff;
            -webkit-box-shadow: 0 0 13px 0 rgba(82, 63, 105, 0.05);
            box-shadow: 0 0 13px 0 rgba(82, 63, 105, 0.05);
            margin-bottom: 17px;
            border-radius: 10px;
            display: inline-block;
            width: 100%;
            border: 1px solid #E2E2E2;
        }
        .card1 {
            padding: 15px;
            background: #fff;
            -webkit-box-shadow: 0 0 13px 0 rgba(82, 63, 105, 0.05);
            box-shadow: 0 0 13px 0 rgba(82, 63, 105, 0.05);
            margin-bottom: 17px;
            border-radius: 10px;
            display: inline-block;
            
            border: 1px solid #E2E2E2;
        }
        body .row1>* {
            padding-right: calc(var(--bs-gutter-x) / 3);
            padding-left: calc(var(--bs-gutter-x) / 3);
        }
        .top-stat-box .card-body {
            border-radius: 10px;
            background: linear-gradient(140deg, #FF6270 0%, #E7AB7C 100%);
        }
        .card-body {
            position: relative;
            padding: 15px;
        }
        .dash-widget-header {
            align-items: center;
            display: flex;
        }
        .dash-count {
            font-size: 18px;
            margin-left: 15px;
            padding-top: 5px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .row1 {
            --bs-gutter-y: 0;
            display: flex;
            flex-wrap: wrap;
            margin-top: calc(var(--bs-gutter-y)* -1);
            margin-right: calc(var(--bs-gutter-x) / -2);
            margin-left: calc(var(--bs-gutter-x) / -2);
        }
        
    </style>
</head>
    <body>
        <!-- Report Header -->
        <h2 class="title">{{$leave->user->name}}  {!! $leave->status->getHtml()!!} Leave Details</h2>

        <div class="page-wrapper">
            <div class="content container-fluid">
                <div class="row">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table">
                                        @foreach($leaveTypes->chunk(2) as $chunk)
                                            <tr>
                                                @foreach($chunk as $type)
                                                    @php
                                                        $balance = Modules\Leave\Entities\LeaveBalance::where([
                                                            'user_id' => $leave->user_id,
                                                            'year' => date('Y'),
                                                            'leave_type_id' => $type->id
                                                        ])->first();
                                                        $available = $balance ? $balance->available : 0;
                                                    @endphp
                                                    <td>
                                                        <p>{{$type->name}}:</p> 
                                                        <strong>{{$available}}</strong>
                                                    </td>
                                                @endforeach
                                                @if($chunk->count() < 2)
                                                    <td></td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table">
                                        <tr>
                                            <td> <strong>{{__trans('start_date')}}</strong> </td>
                                            <td> {{formatDate($leave->start_date)}}</td>
                                        </tr>
                                        <tr>
                                            <td> <strong>{{__trans('end_date')}}</strong> </td>
                                            <td> {{formatDate($leave->end_date)}}</td>
                                        </tr>
                                        <tr>
                                            <td> <strong>{{__trans('leave_type')}}</strong> </td>
                                            <td> {{$leave->type->name}}</td>
                                        </tr>
                                        <tr>
                                            <td> <strong>{{__trans('is_half_day')}}</strong> </td>
                                            <td> {{$leave->is_half_day ?'Yes' :'No'}}</td>
                                        </tr>
                                        <tr>
                                            <td> <strong>{{__trans('status')}}</strong> </td>
                                            <td> {!! $leave->status->getHtml()!!}</td>
                                        </tr>
                                        <tr>
                                            <td> <strong>{{__trans('total_leave_days')}}</strong> </td>
                                            <td> {{$leave->total_leave_days}}</td>
                                        </tr>
                                        <tr>
                                            <td> <strong>{{__trans('created_at')}}</strong> </td>
                                            <td> {!! formatDate($leave->created_at)!!}</td>
                                        </tr>
                                        <tr>
                                            <td> <strong>{{__trans('created_by')}}</strong> </td>
                                            <td> {{$leave->user->name}} ({{$leave->user->employee_id}})</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-12 mt-2 p-4">
                                    <label for="reason"> <strong>{{__trans('reason')}}</strong></label>
                                    <p>
                                        {{$leave->reason}}
                                    </p>
                                    @if($leave->file_path)
                                    <a href="{{asset($leave->file_path)}}" target="_blank">{{__trans('view_document')}}</a>
                                    @endif
                                </div>
        
                                @if ($leave->remark)
                                <div class="col-md-12 mt-2 p-4">
                                    <label for="reason"> <strong>{{__trans('remark')}}</strong></label>
                                    <p>
                                        {{$leave->remark}}
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </body>
</html>
