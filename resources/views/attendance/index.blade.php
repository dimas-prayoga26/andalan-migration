@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
@endsection

@section('navbarTitle', 'Overview')

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Overview',
    'current' => 'Overview',
    'homeRoute' => 'dashboard',
])

@include('attendance.layouts.profile-index')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Overview</h5>
    </div>
</div>
@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script>
        $(function () {
            $('.attendance-tab-btn').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var targetUrl = $(this).data('href');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });
        });
    </script>
@endsection
