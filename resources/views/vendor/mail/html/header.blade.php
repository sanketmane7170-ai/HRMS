@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
    <img src="{{ getLogo() ?? asset('assets/backend/img/logo.png') }}" class="logo" alt="{{ config('app.name', 'WorkPilot') }} Logo" style="max-height: 50px; width: auto;">
</a>
</td>
</tr>
