@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
@endsection

@section('navbarTitle', 'Attendances')

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Attendances',
    'current' => 'Business Trip',
    'homeRoute' => 'dashboard',
])

@include('attendance.layouts.profile-index')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Business Trip</h5>
        <div class="d-flex align-items-center">
            <a data-bs-toggle="modal" data-bs-target="#create" class="btn btn-success light btn-sm ms-2">+ Business Trip</a>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="clearfix d-flex">
                    <div class="avatar avatar-sm rounded me-3 p-2">
                        <img src="{{ asset('assets/images/logo/figma.avif') }}" alt="">
                    </div>
                    <div class="clearfix">
                        <h6 class="mb-0 fw-semibold">#TRP-2026-054</h6>
                        <span class="small">Surabaya, Jawa Timur</span>
                    </div>
                </div>
                <p class="my-3">Presentasi dan survei lokasi serta pengukuran cafe & finalisasi kontrak dengan klien.</p>
                <div class="row py-1">
                    <div class="col-12">
                        <span>Date :</span>
                    </div>
                    <div class="col-12">
                        <span>10 Jun 2026 - 12 Jun 2026 (3 Days)</span> <br>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between flex-wrap">
                <p class="mb-0 fw-medium">Due <span class="text-purple">: 12 Jun 2026</span></p>
                <span class="badge badge-sm badge-primary light">Pending</span>
            </div>
        </div>
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
