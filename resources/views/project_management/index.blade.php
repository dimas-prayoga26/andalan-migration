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

        <!-- Start - My Projects -->
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h5 class="mb-0">My Projects</h5>
                    <input type="text" class="form-control form-control" placeholder="Search project..." aria-label="Search project" style="width: auto;">
                    <button type="button" class="btn btn-primary">Button</button>
                </div>
                <div class="d-flex align-items-center">
                    <a href="javascript:void(0)" class="btn btn-primary btn-sm ms-2">+ New Project</a>
                </div>
            </div>
        </div>
        <!-- End - My Projects -->


        <div class="col-xxl-4 col-xl-4 col-sm-6 mb-3">
            <div class="card h-120 overflow-hidden position-relative">
                <div class="row g-0">
                    <div class="col-md-4">
                    <img src="https://picsum.photos/seed/figma-design/640/480" class="img-fluid rounded-start w-100 h-100 object-fit-cover" alt="...">
                    </div>
                    <div class="col-md-8">
                    <div class="card-body">
                        <h5 class="card-title">Silaturahmi Ramadhan 1445 H</h5>
                        <a href="{{ url('/project-management/detail') }}" class="stretched-link" aria-label="Buka detail Silaturahmi Ramadhan 1445 H"></a>
                        <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                        <p class="card-text"><small class="text-body-secondary">25 Mar 2024 - 25 Mar 2024</small></p>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-xl-4 col-sm-6 mb-3">
            <div class="card h-120 overflow-hidden position-relative">
                <div class="row g-0">
                    <div class="col-md-4">
                    <img src="https://picsum.photos/seed/figma-design/640/480" class="img-fluid rounded-start w-100 h-100 object-fit-cover" alt="...">
                    </div>
                    <div class="col-md-8">
                    <div class="card-body">
                        <h5 class="card-title">Mudik Ceria 2024</h5>
                        <a href="{{ url('/project-management/detail') }}" class="stretched-link" aria-label="Buka detail Mudik Ceria 2024"></a>
                        <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                        <p class="card-text"><small class="text-body-secondary">25 Mar 2024 - 25 Mar 2024</small></p>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-xl-4 col-sm-6 mb-3">
            <div class="card h-120 overflow-hidden position-relative">
                <div class="row g-0">
                    <div class="col-md-4">
                    <img src="https://picsum.photos/seed/figma-design/640/480" class="img-fluid rounded-start w-100 h-100 object-fit-cover" alt="...">
                    </div>
                    <div class="col-md-8">
                    <div class="card-body">
                        <h5 class="card-title">Rakor Pendampingan Desa</h5>
                        <a href="{{ url('/project-management/detail') }}" class="stretched-link" aria-label="Buka detail Rakor Pendampingan Desa"></a>
                        <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                        <p class="card-text"><small class="text-body-secondary">25 Mar 2024 - 25 Mar 2024</small></p>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-xl-4 col-sm-6 mb-3">
            <div class="card h-120 overflow-hidden position-relative">
                <div class="row g-0">
                    <div class="col-md-4">
                    <img src="https://picsum.photos/seed/figma-design/640/480" class="img-fluid rounded-start w-100 h-100 object-fit-cover" alt="...">
                    </div>
                    <div class="col-md-8">
                    <div class="card-body">
                        <h5 class="card-title">Silaturahmi Ramadhan 1445 H</h5>
                        <a href="{{ url('/project-management/detail') }}" class="stretched-link" aria-label="Buka detail Silaturahmi Ramadhan 1445 H"></a>
                        <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                        <p class="card-text"><small class="text-body-secondary">25 Mar 2024 - 25 Mar 2024</small></p>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-xl-4 col-sm-6 mb-3">
            <div class="card h-120 overflow-hidden position-relative">
                <div class="row g-0">
                    <div class="col-md-4">
                    <img src="https://picsum.photos/seed/figma-design/640/480" class="img-fluid rounded-start w-100 h-100 object-fit-cover" alt="...">
                    </div>
                    <div class="col-md-8">
                    <div class="card-body">
                        <h5 class="card-title">Mudik Ceria 2024</h5>
                        <a href="{{ url('/project-management/detail') }}" class="stretched-link" aria-label="Buka detail Mudik Ceria 2024"></a>
                        <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                        <p class="card-text"><small class="text-body-secondary">25 Mar 2024 - 25 Mar 2024</small></p>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-xl-4 col-sm-6 mb-3">
            <div class="card h-120 overflow-hidden position-relative">
                <div class="row g-0">
                    <div class="col-md-4">
                    <img src="https://picsum.photos/seed/figma-design/640/480" class="img-fluid rounded-start w-100 h-100 object-fit-cover" alt="...">
                    </div>
                    <div class="col-md-8">
                    <div class="card-body">
                        <h5 class="card-title">Rakor Pendampingan Desa</h5>
                        <a href="{{ url('/project-management/detail') }}" class="stretched-link" aria-label="Buka detail Rakor Pendampingan Desa"></a>
                        <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                        <p class="card-text"><small class="text-body-secondary">25 Mar 2024 - 25 Mar 2024</small></p>
                    </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- <div class="col-xxl-4 col-xl-4 col-sm-6 mb-3">
            <div class="card h-120 overflow-hidden position-relative">
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-4">
                            <img src="https://picsum.photos/seed/figma-design/640/480" alt="Figma Design" class="d-block w-100 h-100 object-fit-cover">
                        </div>
                        <div class="col-8">
                            <div class="card-body">
                                <h6 class="mb-0 fw-semibold"><a href="profile/projects-details.html" class="stretched-link">Figma Design</a></h6>
                                <p class="my-3">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between flex-wrap">
                    <p class="mb-0 fw-medium">Due <span class="text-purple">: 2023-06-02</span></p>
                    <span class="badge badge-sm badge-primary light">In Progress</span>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-xl-4 col-sm-6 mb-3">
            <div class="card h-120 overflow-hidden position-relative">
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-4">
                            <img src="https://picsum.photos/seed/github-repository/640/480" alt="Github Repository" class="img-fluid w-100 h-100 object-fit-cover">
                        </div>
                        <div class="col-8">
                            <div class="card-body">
                                <h6 class="mb-0 fw-semibold"><a href="profile/projects-details.html" class="stretched-link">Github Repository</a></h6>
                                <p class="my-3">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between flex-wrap">
                    <p class="mb-0 fw-medium">Due <span class="text-danger">: 2023-06-02</span></p>
                    <span class="badge badge-sm badge-danger light">Pending</span>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-xl-4 col-sm-6 mb-3">
            <div class="card h-120 overflow-hidden position-relative">
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-4">
                            <img src="https://picsum.photos/seed/github-repository/640/480" alt="Github Repository" class="img-fluid w-100 h-100 object-fit-cover">
                        </div>
                        <div class="col-8">
                            <div class="card-body">
                                <h6 class="mb-0 fw-semibold"><a href="profile/projects-details.html" class="stretched-link">Github Repository</a></h6>
                                <p class="my-3">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between flex-wrap">
                    <p class="mb-0 fw-medium">Due <span class="text-danger">: 2023-06-02</span></p>
                    <span class="badge badge-sm badge-danger light">Pending</span>
                </div>
            </div>
        </div> --}}

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

