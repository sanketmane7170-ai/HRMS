<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.partials.header-files')

</head>

<body class="error-page">
    @yield('content')
    @include('layouts.partials.footer-files')
    @include('layouts.partials.alert')

</body>

</html>
