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
                                        <span class="title text-black fs-20 fw-semibold">128 Laporan</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                                        <div class="progress-bar bg-success position-absolute rounded bootom-0" style="width: 100%; height:5px;" aria-label="Progess-success" role="progressbar">
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
                                        <span class="title text-black fs-20 fw-semibold">24 Agenda</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                                        <div class="progress-bar rounded bg-secondary" style="width: 100%; height:5px;" aria-label="Progess-secondary" role="progressbar">
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
                                        <span class="title text-black fs-20 fw-semibold">97% Kehadiran</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                                        <div class="progress-bar rounded bg-danger" style="width: 100%; height:5px;" aria-label="Progess-danger"  role="progressbar">
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
                                        <span class="title text-black fs-20 fw-semibold">36 Pelamar</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                                        <div class="progress-bar rounded bg-warning" style="width: 100%; height:5px;" role="progressbar">
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
                                        <span class="title text-black fs-20 fw-semibold">245 Karyawan</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                                        <div class="progress-bar rounded bg-primary" style="width: 100%; height:5px;" role="progressbar">
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
                                        <span class="title text-black fs-20 fw-semibold">18 Artikel</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                                        <div class="progress-bar rounded bg-info" style="width: 100%; height:5px;" role="progressbar">
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

            <div class="row">
                <div class="col-xl-12 mb-3">
                    <h4 class="mb-3">Menu</h4>
                    <div class="dashboard-shortcut-menu">
                        <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card" aria-label="Absensi">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/bx-calendar-check.svg.svg') }}" alt="Icon Absensi" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Absensi</span>
                        </a>
                        <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card" aria-label="Agenda">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/bx-calendar.svg.svg') }}" alt="Icon Agenda" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Agenda</span>
                        </a>
                        <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card" aria-label="Blog">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/bxs-detail.svg.svg') }}" alt="Icon Blog" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Blog</span>
                        </a>
                        <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card" aria-label="Project">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/bx-task.svg.svg') }}" alt="Icon Project" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Project</span>
                        </a>
                        <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card" aria-label="Profile">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/bx-user-pin.svg.svg') }}" alt="Icon Profile" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Profile</span>
                        </a>
                        <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card" aria-label="Meeting">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/bxl-zoom.svg.svg') }}" alt="Icon Meeting" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Meeting</span>
                        </a>
                        <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card" aria-label="Finance">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/wallet-filled-money-tool 1.svg') }}" alt="Icon Finance" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Finance</span>
                        </a>
                        <a href="javascript:void(0)" class="dashboard-shortcut-item shortcut-card" aria-label="Lainnya">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/upload-rounded-symbol 1.svg') }}" alt="Icon Lainnya" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Lainnya</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-12 col-xxl-12 mt-3">
                <div class="row">
                
                    <!-- Start - Featured Diet Menu -->
                    <div class="col-xl-12">
                        <div class="card featuredMenu">
                            <div class="card-header border-0 d-flex justify-content-start align-items-center gap-3 flex-wrap">
                                <h4 class="card-title mb-0">Absensi Karyawan</h4>
                                <div id="attendanceDateFilterRoot">
                                    <input type="date" class="form-control form-control-sm" style="min-width: 190px;" aria-label="Filter tanggal absensi">
                                </div>
                            </div>
                            <div class="card-body loadmore-content height700 dz-scroll pt-0" id="FeaturedMenusContent">
                                <div class="d-flex">
                                    <img src="images/menus/1.avif" alt="menus" class="avatar avatar-lg rounded me-3">
                                    <div>
                                        <h5><a href="food-menu.html" class="text-black fs-16">Syarif Hidayatullah</a></h5>
                                        <span class="fs-14 text-primary"><i class="bx bx-time-five me-1"></i>Masuk 08:00 | <i class="bx bx-time-five ms-1 me-1"></i>Pulang --:--</span>
                                    </div>
                                </div>
                                <hr>
                                <div class="d-flex">
                                    <img src="images/menus/2.avif" alt="menus" class="avatar avatar-lg rounded me-3">
                                    <div>
                                        <h5><a href="food-menu.html" class="text-black fs-16">Dimas Prayoga</a></h5>
                                        <span class="fs-14 text-primary"><i class="bx bx-time-five me-1"></i>Masuk 08:00 | <i class="bx bx-time-five ms-1 me-1"></i>Pulang --:--</span>
                                    </div>
                                </div>
                                <hr>
                                <div class="d-flex">
                                    <img src="images/menus/3.avif" alt="menus" class="avatar avatar-lg rounded me-3">
                                    <div>
                                        <h5><a href="food-menu.html" class="text-black fs-16">Andre</a></h5>
                                        <span class="fs-14 text-primary"><i class="bx bx-time-five me-1"></i>Masuk 08:00 | <i class="bx bx-time-five ms-1 me-1"></i>Pulang --:--</span>
                                    </div>
                                </div>
                                <hr>
                            </div>
                            <div class="position-absolute top-100 start-50 translate-middle">
                                <a class="avatar avatar-sm dz-load-more bg-body rounded-circle fa fa-chevron-down text-primary shadow border-0" aria-label="Featured-icon" id="FeaturedMenus" rel="ajax/featured-menu-list.html">
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- End - Featured Diet Menu -->
                    
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
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/dayjs@1/dayjs.min.js"></script>
    <script src="https://unpkg.com/antd@6.2.3/dist/antd.min.js"></script>
    <script>
        (function () {
            const rootElement = document.getElementById('attendanceDateFilterRoot');

            if (!rootElement || !window.React || !window.ReactDOM || !window.antd || !window.dayjs) {
                return;
            }

            const DatePicker = window.antd.DatePicker;

            function AttendanceDateFilter() {
                return React.createElement(DatePicker, {
                    format: 'DD/MM/YYYY',
                    placeholder: 'Pilih tanggal',
                    allowClear: true,
                    style: { width: 190 },
                });
            }

            if (typeof window.ReactDOM.createRoot === 'function') {
                window.ReactDOM.createRoot(rootElement).render(React.createElement(AttendanceDateFilter));
                return;
            }

            window.ReactDOM.render(React.createElement(AttendanceDateFilter), rootElement);
        })();
    </script>
@endsection
