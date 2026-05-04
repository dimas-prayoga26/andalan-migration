@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <link rel="stylesheet" href="https://unpkg.com/antd@6.2.3/dist/antd.css">
    <!-- Start - All Required Plugins -->

@endsection

@section('navbarTitle', 'Applicants')

@section('content')
<!-- Start - Page Title & Breadcrumb -->
    <div class="row">
        <!-- Start - Error Section -->
        <div class="clearfix">
            <div class="container">
                <div class="row justify-content-center h-100 align-items-center">
                    <div class="col-xl-6 error-page">
                        <div class="error-inner text-center">
                            <div class="dz-error" data-text="503">503</div>
                            <h2 class="error-head"><i class="fa fa-thumbs-down text-danger"></i> Service Unavailable</h2>
                            <p>Sorry, we are under maintenance!</p>
                            <div>
                                <a class="btn btn-primary fs-16" href="./index.html">Back to Home</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End - Error Section -->
    </div>

@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>

@endsection
