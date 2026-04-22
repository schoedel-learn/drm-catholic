<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-topbar="light">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') | Catholic.Work - Diocesan CRM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Diocesan Relationship Management Platform" name="description" />
    <meta content="Catholic.Work" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('images/cw-icon-purple.svg')}}">
    @include('velzon.layouts.head-css')
</head>

<body>
    @yield('content')

    @include('velzon.layouts.vendor-scripts')
    @yield('script')
</body>

</html>