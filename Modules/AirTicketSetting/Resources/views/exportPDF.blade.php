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
        <!-- Report Header -->
        <h2 class="title">Air-ticket Report</h2>
            <table class="table text-center table-hover" id="dataTable">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>{{__trans('user_name')}}</th>
                        <th>{{__trans('date')}}</th>
                        <th>{{__trans('allowance_amount')}}</th>
                        <th>{{__trans('quantity')}}</th>
                        <th>{{__trans('total_amount')}}</th>
                        <th>{{__trans('details')}}</th>
                        <th>{{__trans('status')}}</th>
                        <th>{{__trans('approval_date')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $value)
                    <tr>
                        <td>
                            {{ $loop->iteration }}
                        </td>
                        <td>
                            {{ isset($value->user) ? $value->user->name : 'N/A' }}
                        </td>
                        <td>
                            {{ \Carbon\Carbon::parse($value->date)->format('d-m-Y') }}
                        </td>
                        <td>
                            {{ $value->amount }}
                        </td>
                        <td>
                            {{ $value->quantity }}
                        </td>
                        <td>
                            {{ $value->total_amount }}
                        </td>
                        <td>
                            {{ $value->details }}
                        </td>
                        <td>
                            {{ $value->status }}
                        </td>
                        <td>
                            {{ $value->approve_date }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
    </body>
</html>