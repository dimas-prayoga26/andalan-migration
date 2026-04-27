@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
@endsection

@section('navbarTitle', 'Dashboard')

@section('content')

    <div class="row">

        <div class="col-xl-12 col-xxl-12">
            <div class="row">

                <div class="dashboard-menu-carousel owl-carousel">
                    <!-- Start - Laporan Pekerjaan -->
                    <div class="dashboard-activity-item">
                        <div class="card overflow-hidden avtivity-card">
                            <div class="card-body">
                                <div class="d-flex gap-md-4 gap-3 align-items-center">
                                    <span class="avatar avatar-lg avatar-success rounded-circle border-0">
                                        <i class="bx bx-notepad fs-1 text-success"></i>
                                    </span>
                                    <div>
                                        <p class="fs-12 mb-2">Laporan Pekerjaan</p>
                                        <span class="title text-black fs-24 fw-semibold">128 Laporan</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                                        <div class="progress-bar bg-success position-absolute rounded bootom-0" style="width: 76%; height:5px;" aria-label="Progess-success" role="progressbar">
                                            <span class="sr-only">76% Complete</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="effect bg-success"></div>
                        </div>
                    </div>
                    <!-- End - Laporan Pekerjaan -->

                    <!-- Start - Agenda Kegiatan -->
                    <div class="dashboard-activity-item">
                        <div class="card overflow-hidden avtivity-card">
                            <div class="card-body">
                                <div class="d-flex gap-md-4 gap-3 align-items-center">
                                    <span class="avatar avatar-lg avatar-secondary rounded-circle border-0">
                                        <i class="bx bx-calendar-alt fs-1 text-secondary"></i>
                                    </span>
                                    <div>
                                        <p class="fs-12 mb-2">Agenda Kegiatan</p>
                                        <span class="title text-black fs-24 fw-semibold">24 Agenda</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                                        <div class="progress-bar rounded bg-secondary" style="width: 58%; height:5px;" aria-label="Progess-secondary" role="progressbar">
                                            <span class="sr-only">58% Complete</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="effect bg-secondary"></div>
                        </div>
                    </div>
                    <!-- End - Agenda Kegiatan -->

                    <!-- Start - Data Absensi -->
                    <div class="dashboard-activity-item">
                        <div class="card overflow-hidden avtivity-card">
                            <div class="card-body">
                                <div class="d-flex gap-md-4 gap-3 align-items-center">
                                    <span class="avatar avatar-lg avatar-danger rounded-circle border-0">
                                        <i class="bx bx-fingerprint fs-1 text-danger"></i>
                                    </span>
                                    <div>
                                        <p class="fs-12 mb-2">Data Absensi</p>
                                        <span class="title text-black fs-24 fw-semibold">97% Kehadiran</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                                        <div class="progress-bar rounded bg-danger" style="width: 97%; height:5px;" aria-label="Progess-danger"  role="progressbar">
                                            <span class="sr-only">97% Complete</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="effect bg-danger"></div>
                        </div>
                    </div>
                    <!-- End - Data Absensi -->

                    <!-- Start - Data Pelamar -->
                    <div class="dashboard-activity-item">
                        <div class="card overflow-hidden avtivity-card">
                            <div class="card-body">
                                <div class="d-flex gap-md-4 gap-3 align-items-center">
                                    <span class="avatar avatar-lg avatar-warning rounded-circle border-0">
                                        <i class="bx bx-id-card fs-1 text-warning"></i>
                                    </span>
                                    <div>
                                        <p class="fs-12 mb-2">Data Pelamar</p>
                                        <span class="title text-black fs-24 fw-semibold">36 Pelamar</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                                        <div class="progress-bar rounded bg-warning" style="width: 64%; height:5px;" role="progressbar">
                                            <span class="sr-only">64% Complete</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="effect bg-warning"></div>
                        </div>
                    </div>
                    <!-- End - Data Pelamar -->

                    <!-- Start - Data Karyawan -->
                    <div class="dashboard-activity-item">
                        <div class="card overflow-hidden avtivity-card">
                            <div class="card-body">
                                <div class="d-flex gap-md-4 gap-3 align-items-center">
                                    <span class="avatar avatar-lg avatar-primary rounded-circle border-0">
                                        <i class="bx bx-group fs-1 text-primary"></i>
                                    </span>
                                    <div>
                                        <p class="fs-12 mb-2">Data Karyawan</p>
                                        <span class="title text-black fs-24 fw-semibold">245 Karyawan</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                                        <div class="progress-bar rounded bg-primary" style="width: 70%; height:5px;" role="progressbar">
                                            <span class="sr-only">70% Complete</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="effect bg-primary"></div>
                        </div>
                    </div>
                    <!-- End - Data Karyawan -->

                    <!-- Start - Blog Management -->
                    <div class="dashboard-activity-item">
                        <div class="card overflow-hidden avtivity-card">
                            <div class="card-body">
                                <div class="d-flex gap-md-4 gap-3 align-items-center">
                                    <span class="avatar avatar-lg avatar-info rounded-circle border-0">
                                        <i class="bx bx-news fs-1 text-info"></i>
                                    </span>
                                    <div>
                                        <p class="fs-12 mb-2">Blog Management</p>
                                        <span class="title text-black fs-24 fw-semibold">18 Artikel</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                                        <div class="progress-bar rounded bg-info" style="width: 54%; height:5px;" role="progressbar">
                                            <span class="sr-only">54% Complete</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="effect bg-info"></div>
                        </div>
                    </div>
                    <!-- End - Blog Management -->
                </div>

            </div>

            <div class="dashboard-shortcut-menu">
                <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card">
                    <span class="dashboard-shortcut-hero">
                        <i class="bx bx-notepad dashboard-shortcut-icon" aria-hidden="true"></i>
                        <span class="dashboard-shortcut-subtitle">Halaman</span>
                    </span>
                    <span class="dashboard-shortcut-label">Laporan Pekerjaan</span>
                </a>
                <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card">
                    <span class="dashboard-shortcut-hero">
                        <i class="bx bx-calendar-alt dashboard-shortcut-icon" aria-hidden="true"></i>
                        <span class="dashboard-shortcut-subtitle">Halaman</span>
                    </span>
                    <span class="dashboard-shortcut-label">Agenda Kegiatan</span>
                </a>
                <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card">
                    <span class="dashboard-shortcut-hero">
                        <i class="bx bx-fingerprint dashboard-shortcut-icon" aria-hidden="true"></i>
                        <span class="dashboard-shortcut-subtitle">Halaman</span>
                    </span>
                    <span class="dashboard-shortcut-label">Data Absensi</span>
                </a>
                <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card">
                    <span class="dashboard-shortcut-hero">
                        <i class="bx bx-id-card dashboard-shortcut-icon" aria-hidden="true"></i>
                        <span class="dashboard-shortcut-subtitle">Halaman</span>
                    </span>
                    <span class="dashboard-shortcut-label">Data Pelamar</span>
                </a>
                <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card">
                    <span class="dashboard-shortcut-hero">
                        <i class="bx bx-group dashboard-shortcut-icon" aria-hidden="true"></i>
                        <span class="dashboard-shortcut-subtitle">Halaman</span>
                    </span>
                    <span class="dashboard-shortcut-label">Data Karyawan</span>
                </a>
                <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card">
                    <span class="dashboard-shortcut-hero">
                        <i class="bx bx-news dashboard-shortcut-icon" aria-hidden="true"></i>
                        <span class="dashboard-shortcut-subtitle">Halaman</span>
                    </span>
                    <span class="dashboard-shortcut-label">Blog Management</span>
                </a>
                <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card">
                    <span class="dashboard-shortcut-hero">
                        <i class="bx bx-folder dashboard-shortcut-icon" aria-hidden="true"></i>
                        <span class="dashboard-shortcut-subtitle">Halaman</span>
                    </span>
                    <span class="dashboard-shortcut-label">Project List</span>
                </a>
                <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card">
                    <span class="dashboard-shortcut-hero">
                        <i class="bx bx-wallet dashboard-shortcut-icon" aria-hidden="true"></i>
                        <span class="dashboard-shortcut-subtitle">Halaman</span>
                    </span>
                    <span class="dashboard-shortcut-label">Finance Management</span>
                </a>
                <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card">
                    <span class="dashboard-shortcut-hero">
                        <i class="bx bx-file dashboard-shortcut-icon" aria-hidden="true"></i>
                        <span class="dashboard-shortcut-subtitle">Halaman</span>
                    </span>
                    <span class="dashboard-shortcut-label">Administration Management</span>
                </a>
                <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card">
                    <span class="dashboard-shortcut-hero">
                        <i class="bx bx-slider-alt dashboard-shortcut-icon" aria-hidden="true"></i>
                        <span class="dashboard-shortcut-subtitle">Halaman</span>
                    </span>
                    <span class="dashboard-shortcut-label">Options</span>
                </a>
                <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card">
                    <span class="dashboard-shortcut-hero">
                        <i class="bx bx-user-circle dashboard-shortcut-icon" aria-hidden="true"></i>
                        <span class="dashboard-shortcut-subtitle">Halaman</span>
                    </span>
                    <span class="dashboard-shortcut-label">My Profile</span>
                </a>
                <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card">
                    <span class="dashboard-shortcut-hero">
                        <i class="bx bx-video dashboard-shortcut-icon" aria-hidden="true"></i>
                        <span class="dashboard-shortcut-subtitle">Halaman</span>
                    </span>
                    <span class="dashboard-shortcut-label">Zoom Meeting</span>
                </a>

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
