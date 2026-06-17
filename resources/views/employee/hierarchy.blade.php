@extends('layouts.backend')
@section('content')

<!-- Include Tree CSS -->
<link rel="stylesheet" href="{{ asset('assets/backend/css/tree.css') }}">
<div class="page-wrapper" style="overflow-x: auto; overflow-y: hidden;">
    <div class="content container-fluid">
        <div class="tree">
            <ul>
                <li>
                    <div>
                        <img src="{{ $tree['profile_image'] ?? asset('default-profile.png') }}" alt="{{ $tree['name'] ?? 'No Name' }}">
                        <strong>{{ $tree['name'] ?? 'No Name' }}</strong>
                        <small>{{ $tree['role'] ?? 'No Role' }}</small>
                        <small>{{ $tree['designation'] ?? 'No Designation' }}</small>
                    </div>

                    @if(!empty($tree['subordinates']) && is_array($tree['subordinates']))
                    @include('employee.subtree', ['subordinates' => $tree['subordinates']])
                    @endif
                </li>
            </ul>


        </div>
    </div>
</div>

@endsection
