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

@include('admin_attendance.layout.navbar')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Business Trip</h5>
        <div class="d-flex align-items-center">
            <!-- <a href="javascript:void(0)" class="btn btn-primary btn-sm ms-2">+ New Project</a> -->
        </div>
    </div>
</div>
<!-- End - My Projects -->

<div class="col-xxl-3 col-xl-4 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="clearfix d-flex">
                    <div class="avatar avatar-sm rounded me-3 p-2">
                        <img src="assets/images/logo/figma.avif" alt="">
                    </div>
                    <div class="clearfix">
                        <h6 class="mb-0 fw-semibold"><a href="{{ route('admin-attendance.business-trip.detail') }}" class="stretched-link">#TRP-2605-0605</a></h6>
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
                <div class="mt-3">
                    <div class="d-flex justify-content-between">
                        <span>Complete</span>
                        <span>60%</span>
                    </div>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-purple" style="width:60%;" role="progressbar"></div>
                    </div>
                </div>
                <!-- 
                    Submit bussiness trip : 20%
                    
                -->
            </div>
            <div class="card-footer d-flex justify-content-between flex-wrap">
                <p class="mb-0 fw-medium"><span class="text-purple">Intercity (Luar Kota)</span></p>
                <span class="badge badge-sm badge-primary light">Pending</span>
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
@endsection
