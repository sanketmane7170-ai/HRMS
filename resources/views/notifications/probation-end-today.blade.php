@include('notifications.partials.header',['title'=> getSetting('site_title')." | ". __trans('probation_end_today')])
<tr>
    <td>
        <p>Hi,</p>
    </td>
</tr>
<tr>
    <td>
        <p>{{$user->name}} has successfully completed their 6 month probation at {{getSetting('site_title')}} as of today, {{now()->format(config('project.date_format'))}}.</p>
    </td>
</tr>
<tr>
    <td>
        <p><a href="{{route('backend.users.show',$user)}}">Click here</a> to view their profile on WorkPilot. </p>
    </td>
</tr>
@include('notifications.partials.footer')
