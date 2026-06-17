<ul>
    @foreach($subordinates as $subordinate)
        @if(is_array($subordinate) && !empty($subordinate) && isset($subordinate['name']))
        <li>
            <div>
                <img src="{{ $subordinate['profile_image'] ?? asset('default-profile.png') }}" alt="{{ $subordinate['name'] ?? 'No Name' }}">
                <strong>{{ $subordinate['name'] ?? 'No Name' }}</strong>
                <small>{{ $subordinate['role'] ?? 'No Role' }}</small>
                <small>{{ $subordinate['designation'] ?? 'No Designation' }}</small>
            </div>
            
            @if(!empty($subordinate['subordinates']) && is_array($subordinate['subordinates']))
                @include('employee.subtree', ['subordinates' => $subordinate['subordinates']])
            @endif
        </li>
        @endif
    @endforeach
</ul>
