@extends('layouts.backend')
@section('content')

<!-- Include Custom Admin Hierarchy CSS -->
<link rel="stylesheet" href="{{ asset('assets/backend/css/admin-hierarchy.css') }}">
<div class="page-wrapper">
    <div class="content container-fluid">
<div class="page-content">
    <div class="admin-hierarchy">
        @foreach($tree as $role)
            <div class="role-container">
                <h2>{{ strtoupper($role['name']) }}</h2>
                <div class="scroll-container">
                    <div class="user-container">
                        @foreach($role['users'] as $user)
                            <div class="user-card">
                                <img src="{{ $user['profile_image'] }}" alt="{{ $user['name'] }}" 
                                     onerror="this.onerror=null;this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png';">
                                <strong>{{ $user['name'] }}</strong>
                                <small>{{ $role['name'] }}</small>
                                <small>{{ $user['designation'] }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="role-separator"></div>
        @endforeach
    </div>
</div>
</div>
</div>

@endsection
