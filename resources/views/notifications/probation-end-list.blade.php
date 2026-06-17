@include('notifications.partials.header')
<tr>
    <td>
        <p>Hi</p>
    </td>
</tr>
<tr>
    <td>
        <p>Here is the list of employees that will complete their probation within the next 40 days.</p>
    </td>
</tr>
<tr>
    <td>
        <table width="100%" border="1" cellspacing="0" cellpadding="10" bgcolor="#ffffff">
            <thead>
                <tr>
                    <th style="text-align: left;">{{__trans('employee_id')}}</th>
                    <th style="text-align: left;">{{__trans('employee_name')}}</th>
                    <th style="text-align: left;">{{__trans('department')}}</th>
                    <th style="text-align: left;">{{__trans('reports_to')}}</th>
                    <th style="text-align: left;">{{__trans('probation_end_date')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                <tr>
                    <td>{{$record['employee_id']}}</td>
                    <td>{{$record['name']}}</td>
                    <td>{{$record['department']}}</td>
                    <td>{{$record['manager']}}</td>
                    <td>{{$record['probation_end_date']}}</td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </td>
</tr>

@include('notifications.partials.footer')
