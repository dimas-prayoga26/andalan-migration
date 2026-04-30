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

@section('navbarTitle', 'Dashboard')

@section('content')
<!-- Start - Page Title & Breadcrumb -->
<div class="page-title">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li><h1>Projects</h1></li>
            <li class="breadcrumb-item">
                <a href="index.html">
                    <svg width="18" height="18" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.125 6.375L8.5 1.41667L14.875 6.375V14.1667C14.875 14.5424 14.7257 14.9027 14.4601 15.1684C14.1944 15.4341 13.8341 15.5833 13.4583 15.5833H3.54167C3.16594 15.5833 2.80561 15.4341 2.53993 15.1684C2.27426 14.9027 2.125 14.5424 2.125 14.1667V6.375Z" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.375 15.5833V8.5H10.625V15.5833" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Home
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Projects</li>
        </ol>
    </nav>
</div>
<!-- End - Page Title & Breadcrumb -->

<div class="tab-content d-flex flex-column" id="tabContentMyProfileBottom" style="min-height: calc(100vh - 310px);">
    <div class="row">

    <div class="accordion accordion-gap" id="accordionExampleIcon">
        <div class="accordion-item">
            <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOneIcon" aria-expanded="true" aria-controls="collapseOneIcon">
                <i class="fa-solid fa-circle-info me-2 fs-5"></i> Accordion Item #1
            </button>
            </h2>
            <div id="collapseOneIcon" class="accordion-collapse collapse show" data-bs-parent="#accordionExampleIcon">
            <div class="accordion-body">
                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. 
            </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwoIcon" aria-expanded="false" aria-controls="collapseTwoIcon">
                <i class="fa-solid fa-circle-info me-2 fs-5"></i> Accordion Item #2
            </button>
            </h2>
            <div id="collapseTwoIcon" class="accordion-collapse collapse" data-bs-parent="#accordionExampleIcon">
            <div class="accordion-body">
                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. 
            </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThreeIcon" aria-expanded="false" aria-controls="collapseThreeIcon">
                <i class="fa-solid fa-circle-info me-2 fs-5"></i> Accordion Item #3
            </button>
            </h2>
            <div id="collapseThreeIcon" class="accordion-collapse collapse" data-bs-parent="#accordionExampleIcon">
            <div class="accordion-body">
                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. 
            </div>
            </div>
        </div>
        </div>

    </div>
</div>

<div class="d-flex justify-content-center mt-auto pt-3 mb-2">
    <nav aria-label="Page navigation example">
        <ul class="pagination mb-0">
            <li class="page-item">
                <a class="page-link" href="javascript:void(0);" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <li class="page-item"><a class="page-link" href="javascript:void(0);">1</a></li>
            <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
            <li class="page-item"><a class="page-link" href="javascript:void(0);">3</a></li>
            <li class="page-item">
                <a class="page-link" href="javascript:void(0);" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
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

