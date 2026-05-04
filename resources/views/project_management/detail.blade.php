@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <link rel="stylesheet" href="https://unpkg.com/antd@6.2.3/dist/antd.css">
@endsection

@section('navbarTitle', 'Reports')

@section('content')
<!-- Start - Page Title & Breadcrumb -->
<div class="page-title">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li><h1>Reports</h1></li>
            <li class="breadcrumb-item">
                <a href="index.html">
                    <svg width="18" height="18" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.125 6.375L8.5 1.41667L14.875 6.375V14.1667C14.875 14.5424 14.7257 14.9027 14.4601 15.1684C14.1944 15.4341 13.8341 15.5833 13.4583 15.5833H3.54167C3.16594 15.5833 2.80561 15.4341 2.53993 15.1684C2.27426 14.9027 2.125 14.5424 2.125 14.1667V6.375Z" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.375 15.5833V8.5H10.625V15.5833" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Home
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">reports</li>
        </ol>
    </nav>
</div>
<!-- End - Page Title & Breadcrumb -->

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <a href="javascript:void(0);" class="btn btn-outline-secondary btn-sm">Back</a>
    <div class="d-flex align-items-center gap-2">
        <a href="javascript:void(0);" class="btn btn-outline-success btn-sm">Generate Default Task</a>
        <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm">Create Task</a>
    </div>
</div>

<div class="tab-content d-flex flex-column" id="tabContentMyProfileBottom" style="min-height: calc(100vh - 310px);">
    <div class="row">

    <div class="accordion accordion-gap" id="accordionExampleIcon">
        <div class="accordion-item">
            <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOneIcon" aria-expanded="true" aria-controls="collapseOneIcon">
                <i class="fa-solid fa-user-gear me-2 fs-5"></i> Administration
            </button>
            </h2>
            <div id="collapseOneIcon" class="accordion-collapse collapse show" data-bs-parent="#accordionExampleIcon">
            <div class="accordion-body">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <h6 class="mb-0 text-danger">Google Drive Link</h6>
                    <button type="button" class="btn btn-link btn-sm text-muted p-0" aria-label="More options">
                        <i class="fa-solid fa-ellipsis"></i>
                    </button>
                </div>
                <hr class="my-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-8 col-lg-6">
                        <input type="text" class="form-control" placeholder="Create" aria-label="Administration input">
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-success px-4">Create</button>
                    </div>
                </div>
            </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwoIcon" aria-expanded="false" aria-controls="collapseTwoIcon">
                <i class="fa-solid fa-pen-ruler me-2 fs-5"></i> Design
            </button>
            </h2>
            <div id="collapseTwoIcon" class="accordion-collapse collapse" data-bs-parent="#accordionExampleIcon">
            <div class="accordion-body">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <h6 class="mb-0 text-danger">Design Link</h6>
                    <button type="button" class="btn btn-link btn-sm text-muted p-0" aria-label="More options">
                        <i class="fa-solid fa-ellipsis"></i>
                    </button>
                </div>
                <hr class="my-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-8 col-lg-6">
                        <input type="text" class="form-control" placeholder="Create" aria-label="Design input">
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-success px-4">Create</button>
                    </div>
                </div>
            </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThreeIcon" aria-expanded="false" aria-controls="collapseThreeIcon">
                <i class="fa-solid fa-file-lines me-2 fs-5"></i> Documentation
            </button>
            </h2>
            <div id="collapseThreeIcon" class="accordion-collapse collapse" data-bs-parent="#accordionExampleIcon">
            <div class="accordion-body">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <h6 class="mb-0 text-danger">Document Link</h6>
                    <button type="button" class="btn btn-link btn-sm text-muted p-0" aria-label="More options">
                        <i class="fa-solid fa-ellipsis"></i>
                    </button>
                </div>
                <hr class="my-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-8 col-lg-6">
                        <input type="text" class="form-control" placeholder="Create" aria-label="Documentation input">
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-success px-4">Create</button>
                    </div>
                </div>
            </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFourIcon" aria-expanded="false" aria-controls="collapseFourIcon">
                <i class="fa-solid fa-video me-2 fs-5"></i> Video Content
            </button>
            </h2>
            <div id="collapseFourIcon" class="accordion-collapse collapse" data-bs-parent="#accordionExampleIcon">
            <div class="accordion-body">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <h6 class="mb-0 text-danger">Video Link</h6>
                    <button type="button" class="btn btn-link btn-sm text-muted p-0" aria-label="More options">
                        <i class="fa-solid fa-ellipsis"></i>
                    </button>
                </div>
                <hr class="my-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-8 col-lg-6">
                        <input type="text" class="form-control" placeholder="Create" aria-label="Video content input">
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-success px-4">Create</button>
                    </div>
                </div>
            </div>
            </div>
        </div>
        </div>

    </div>
</div>

<!-- End - Content Body -->

@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
@endsection
