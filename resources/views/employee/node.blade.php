<div>
    <img src="{{ $node['profile_image'] }}" alt="{{ $node['name'] }}">
    <strong>{{ $node['name'] }}</strong><br>
    <small>{{ $node['role'] }}</small><br>
    <small>{{ $node['designation'] }}</small>
</div>

@if(!empty($node['subordinates']))
    <ul>
        @foreach($node['subordinates'] as $child)
            <li>
                @include('employee.node', ['node' => $child])
            </li>
        @endforeach
    </ul>
@endif
