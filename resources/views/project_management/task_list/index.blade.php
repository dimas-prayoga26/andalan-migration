@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')

@php
    $dashboardCssPath = public_path('assets/css/dashboard.css');
    $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    $attendanceCssPath = public_path('assets/css/attendance.css');
    $attendanceCssVersion = file_exists($attendanceCssPath) ? filemtime($attendanceCssPath) : time();
@endphp
<link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
<link rel="stylesheet" href="{{ asset('assets/css/attendance.css') }}?v={{ $attendanceCssVersion }}">
<link rel="stylesheet" href="https://unpkg.com/antd@6.2.3/dist/antd.css">

@endsection

@section('content')

@include('layouts.breadcrumb', [
    'title' => 'Project Management',
    'current' => 'Overview',
    'homeRoute' => 'dashboard',
])

@include('project_management.layouts.profile-index')

<div class="tab-content" id="tabContentMyProfileBottom">

    <!-- Start - Documents -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Task List</h5>
        <div class="d-flex align-items-center">
            <ul class="nav nav-pills nav-pills-square-sm gap-2" id="myTabView" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="project-list-tab" data-bs-toggle="tab" data-bs-target="#project-list-pane" type="button" role="tab" aria-controls="project-list-pane" aria-selected="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-list">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="project-grid-tab" data-bs-toggle="tab" data-bs-target="#project-grid-pane" type="button" role="tab" aria-controls="project-grid-pane" aria-selected="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <!-- End - Documents -->

    <!-- Start - Billing Statement -->
    <div class="tab-content" id="myTabContentView">
        <div class="tab-pane fade show active" id="project-list-pane" role="tabpanel" aria-labelledby="project-list-tab" tabindex="0">
            <div class="row">

                <!-- Start - Plan List -->
                <div class="col-xl-8 col-xxl-9">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card plan-list">
                                <div class="card-header align-items-center d-flex flex-wrap d-block pb-0 border-0">
                                    <div class="">
                                        <h4 class="card-title">Task List</h4>
                                        <p class="fs-13 mb-0">Turn your daily goals into accomplishments.</p>
                                    </div>
                                    <div class="card-tabs mt-md-0 mt-3">
                                        <ul class="nav nav-pills nav-pills-card gap-1" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#All" role="tab" aria-selected="false">All</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link active" data-bs-toggle="tab" href="#Unifinshed" role="tab" aria-selected="true">Ongoing</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#Finished" role="tab">Done</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a class="btn rounded btn-primary mt-xxl-0 mt-xl-3 mt-lg-0 mt-3" data-bs-toggle="modal" data-bs-target="#filter">
                                        <svg class="me-2" width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M3.31615 6H14.4744C14.4744 6.53043 14.6882 7.03914 15.0686 7.41421C15.4491 7.78929 15.9651 8 16.5032 8H18.532C19.07 8 19.5861 7.78929 19.9665 7.41421C20.347 7.03914 20.5607 6.53043 20.5607 6H21.5751C21.8442 6 22.1022 5.89464 22.2924 5.70711C22.4827 5.51957 22.5895 5.26522 22.5895 5C22.5895 4.73478 22.4827 4.48043 22.2924 4.29289C22.1022 4.10536 21.8442 4 21.5751 4H20.5607C20.5607 3.46957 20.347 2.96086 19.9665 2.58579C19.5861 2.21071 19.07 2 18.532 2H16.5032C15.9651 2 15.4491 2.21071 15.0686 2.58579C14.6882 2.96086 14.4744 3.46957 14.4744 4H3.31615C3.04711 4 2.7891 4.10536 2.59887 4.29289C2.40863 4.48043 2.30176 4.73478 2.30176 5C2.30176 5.26522 2.40863 5.51957 2.59887 5.70711C2.7891 5.89464 3.04711 6 3.31615 6ZM16.5032 4H18.532V5V6H16.5032V4ZM21.5751 11H12.4456C12.4456 10.4696 12.2319 9.96086 11.8514 9.58579C11.471 9.21071 10.9549 9 10.4169 9H8.38809C7.85002 9 7.334 9.21071 6.95353 9.58579C6.57306 9.96086 6.35931 10.4696 6.35931 11H3.31615C3.04711 11 2.7891 11.1054 2.59887 11.2929C2.40863 11.4804 2.30176 11.7348 2.30176 12C2.30176 12.2652 2.40863 12.5196 2.59887 12.7071C2.7891 12.8946 3.04711 13 3.31615 13H6.35931C6.35931 13.5304 6.57306 14.0391 6.95353 14.4142C7.334 14.7893 7.85002 15 8.38809 15H10.4169C10.9549 15 11.471 14.7893 11.8514 14.4142C12.2319 14.0391 12.4456 13.5304 12.4456 13H21.5751C21.8442 13 22.1022 12.8946 22.2924 12.7071C22.4827 12.5196 22.5895 12.2652 22.5895 12C22.5895 11.7348 22.4827 11.4804 22.2924 11.2929C22.1022 11.1054 21.8442 11 21.5751 11ZM8.38809 13V11H10.4169V12V13H8.38809ZM21.5751 18H18.532C18.532 17.4696 18.3182 16.9609 17.9378 16.5858C17.5573 16.2107 17.0413 16 16.5032 16H14.4744C13.9364 16 13.4203 16.2107 13.0399 16.5858C12.6594 16.9609 12.4456 17.4696 12.4456 18H3.31615C3.04711 18 2.7891 18.1054 2.59887 18.2929C2.40863 18.4804 2.30176 18.7348 2.30176 19C2.30176 19.2652 2.40863 19.5196 2.59887 19.7071C2.7891 19.8946 3.04711 20 3.31615 20H12.4456C12.4456 20.5304 12.6594 21.0391 13.0399 21.4142C13.4203 21.7893 13.9364 22 14.4744 22H16.5032C17.0413 22 17.5573 21.7893 17.9378 21.4142C18.3182 21.0391 18.532 20.5304 18.532 20H21.5751C21.8442 20 22.1022 19.8946 22.2924 19.7071C22.4827 19.5196 22.5895 19.2652 22.5895 19C22.5895 18.7348 22.4827 18.4804 22.2924 18.2929C22.1022 18.1054 21.8442 18 21.5751 18ZM14.4744 20V18H16.5032V19V20H14.4744Z" fill="#fff"></path>
                                        </svg>May 2026
                                    </a>
                                </div>
                                
                                <div class="card-body tab-content pt-2">
                                    <div class="alert alert-warning outline-dashed border-2 py-3 px-3 mt-3 mb-3 text-dark d-flex align-items-center">
                                        <div class="clearfix">
                                            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M15 30C18.9782 30 22.7936 28.4196 25.6066 25.6066C28.4196 22.7936 30 18.9782 30 15C30 11.0218 28.4196 7.20644 25.6066 4.3934C22.7936 1.58035 18.9782 0 15 0C11.0218 0 7.20644 1.58035 4.3934 4.3934C1.58035 7.20644 0 11.0218 0 15C0 18.9782 1.58035 22.7936 4.3934 25.6066C7.20644 28.4196 11.0218 30 15 30ZM12.6562 19.6875H14.0625V15.9375H12.6562C11.877 15.9375 11.25 15.3105 11.25 14.5312C11.25 13.752 11.877 13.125 12.6562 13.125H15.4688C16.248 13.125 16.875 13.752 16.875 14.5312V19.6875H17.3438C18.123 19.6875 18.75 20.3145 18.75 21.0938C18.75 21.873 18.123 22.5 17.3438 22.5H12.6562C11.877 22.5 11.25 21.873 11.25 21.0938C11.25 20.3145 11.877 19.6875 12.6562 19.6875ZM15 7.5C15.4973 7.5 15.9742 7.69754 16.3258 8.04918C16.6775 8.40081 16.875 8.87772 16.875 9.375C16.875 9.87228 16.6775 10.3492 16.3258 10.7008C15.9742 11.0525 15.4973 11.25 15 11.25C14.5027 11.25 14.0258 11.0525 13.6742 10.7008C13.3225 10.3492 13.125 9.87228 13.125 9.375C13.125 8.87772 13.3225 8.40081 13.6742 8.04918C14.0258 7.69754 14.5027 7.5 15 7.5Z" fill="#FF8A11"></path>
                                            </svg>
                                        </div>
                                        <div class="mx-3">
                                            <h6 class="mb-0 fw-semibold">Pending Tasks Reminder!</h6>
                                            <p class="mb-0">You have a few active tasks remaining from April. Let's wrap them up to keep your records up to date.</p>
                                        </div>
                                    </div>
                                    <div class="alert alert-success outline-dashed border-2 py-3 px-3 mt-3 mb-3 text-dark d-flex align-items-center">
                                        <div class="clearfix">
                                            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M15 30C18.9782 30 22.7936 28.4196 25.6066 25.6066C28.4196 22.7936 30 18.9782 30 15C30 11.0218 28.4196 7.20644 25.6066 4.3934C22.7936 1.58035 18.9782 0 15 0C11.0218 0 7.20644 1.58035 4.3934 4.3934C1.58035 7.20644 0 11.0218 0 15C0 18.9782 1.58035 22.7936 4.3934 25.6066C7.20644 28.4196 11.0218 30 15 30ZM12.6562 19.6875H14.0625V15.9375H12.6562C11.877 15.9375 11.25 15.3105 11.25 14.5312C11.25 13.752 11.877 13.125 12.6562 13.125H15.4688C16.248 13.125 16.875 13.752 16.875 14.5312V19.6875H17.3438C18.123 19.6875 18.75 20.3145 18.75 21.0938C18.75 21.873 18.123 22.5 17.3438 22.5H12.6562C11.877 22.5 11.25 21.873 11.25 21.0938C11.25 20.3145 11.877 19.6875 12.6562 19.6875ZM15 7.5C15.4973 7.5 15.9742 7.69754 16.3258 8.04918C16.6775 8.40081 16.875 8.87772 16.875 9.375C16.875 9.87228 16.6775 10.3492 16.3258 10.7008C15.9742 11.0525 15.4973 11.25 15 11.25C14.5027 11.25 14.0258 11.0525 13.6742 10.7008C13.3225 10.3492 13.125 9.87228 13.125 9.375C13.125 8.87772 13.3225 8.40081 13.6742 8.04918C14.0258 7.69754 14.5027 7.5 15 7.5Z" fill="#2BC155"></path>
                                            </svg>
                                        </div>
                                        <div class="mx-3">
                                            <h6 class="mb-0 fw-semibold">Ready for a Great Day? 👋</h6>
                                            <p class="mb-0">What’s on your agenda today? Let's add your tasks to keep your workflow organized. <a class="text-success" type="button" data-bs-toggle="modal" data-bs-target="#create">Add Task</a></p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mb-3">
                                        <a type="button" data-bs-toggle="modal" data-bs-target="#create" class="btn btn-success light">Add Task</a>
                                    </div>

                                    <!-- Start - All Tab -->
                                    <div class="tab-pane fade" id="All">

                                        <!-- Start - Workout 1 -->
                                        <div class="d-flex border-bottom flex-wrap py-3 align-items-center px-3 list-row">
                                            <div class="col-xl-8 col-xxl-5 col-lg-6 col-sm-8 d-flex gap-3 align-items-center">
                                                <div class="avatar avatar-lg bg-primary-subtle d-grid border-0 rounded">
                                                    <div class="d-grid">
                                                        <p class="fs-24 text-primary mb-0">4</p>
                                                        <span class="fs-14 text-primary lh-1">Mon</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="fs-20 "><a href="workout-statistic.html" class="text-black">Routine Cardio Burn Workout</a></h4>
                                                    <span class="text-danger fs-16 fw-semibold">UNFINISHED</span>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-xxl-2 col-lg-2 col-sm-4 col-12 d-flex align-items-center gap-2 justify-content-xl-end justify-content-lg-center justify-content-md-end justify-content-center px-3 mt-md-0 mt-3">
                                                <span class="text-warning fs-16">Daily Activity</span>
                                            </div>
                                            <div class="col-xl-12 col-xxl-5 col-lg-4 col-12 d-flex gap-3 align-items-center justify-content-xxl-end justify-content-xl-between justify-content-lg-end justify-content-between mt-xxl-0 mt-xl-3 mt-lg-0 mt-3">
                                                <a class="btn play-btn btn-lg btn-light text-primary fs-14"><i class="las la-caret-right text-primary fs-24"></i>Mark as Done</a>
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="btn btn-lg light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <svg width="6" height="26" viewBox="0 0 6 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M6 3C6 4.65685 4.65685 6 3 6C1.34315 6 0 4.65685 0 3C0 1.34315 1.34315 0 3 0C4.65685 0 6 1.34315 6 3Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 13C6 14.6569 4.65685 16 3 16C1.34315 16 0 14.6569 0 13C0 11.3431 1.34315 10 3 10C4.65685 10 6 11.3431 6 13Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 23C6 24.6569 4.65685 26 3 26C1.34315 26 0 24.6569 0 23C0 21.3431 1.34315 20 3 20C4.65685 20 6 21.3431 6 23Z" fill="var(--bs-body-color)"></path>
                                                        </svg>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" >Edit</a>
                                                        <a class="dropdown-item" >Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End - Workout 1 -->

                                        <!-- Start - Workout 2 -->
                                        <div class="d-flex border-bottom flex-wrap py-3 align-items-center px-3 list-row">
                                            <div class="col-xl-8 col-xxl-5 col-lg-6 col-sm-8 d-flex gap-3 align-items-center">
                                                <div class="avatar avatar-lg bg-primary-subtle d-grid border-0 rounded">
                                                    <div class="d-grid">
                                                        <p class="fs-24 text-primary mb-0">5</p>
                                                        <span class="fs-14 text-primary lh-1">Tue</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="fs-20 "><a href="workout-statistic.html" class="text-black">Total Body Yoga Workout</a></h4>
                                                    <span class="text-secondary fs-16 fw-semibold">On Progress</span>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-xxl-2 col-lg-2 col-sm-4 col-12 d-flex align-items-center gap-2 justify-content-xl-end justify-content-lg-center justify-content-md-end justify-content-center px-3 mt-md-0 mt-3">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="url(#clip1)">
                                                    <path d="M12 5.9999C13.6568 5.9999 14.9999 4.65677 14.9999 2.99995C14.9999 1.34312 13.6568 1.61033e-07 12 1.41496e-07C10.3431 1.21959e-07 9.00001 1.34312 9.00001 2.99995C9.00001 4.65677 10.3431 5.9999 12 5.9999Z" fill="#70349D"/>
                                                    <path d="M17.8307 21.8297L14.1363 23.2153L15.9735 23.9042C16.7642 24.1978 17.6173 23.791 17.9048 23.0261C18.0579 22.618 18.0126 22.1905 17.8307 21.8297Z" fill="#70349D"/>
                                                    <path d="M5.02699 16.5949C4.25285 16.3078 3.38711 16.6974 3.09565 17.473C2.80488 18.2486 3.19821 19.1128 3.97375 19.4043L5.59202 20.0111L9.86434 18.4088L5.02699 16.5949Z" fill="#70349D"/>
                                                    <path d="M20.9047 17.473C20.6132 16.6974 19.7475 16.3078 18.9734 16.5949L6.97366 21.0948C6.19803 21.3863 5.80475 22.2505 6.09551 23.0262C6.38299 23.7908 7.23593 24.198 8.02685 23.9043L20.0266 19.4044C20.8023 19.1129 21.1956 18.2487 20.9047 17.473Z" fill="#70349D"/>
                                                    <path d="M22.5 11.9998L18.9273 11.9998L16.3419 6.82899C16.0732 6.29213 15.5267 5.98627 14.9634 5.99991L12 5.9999L9.03685 5.99991C8.47364 5.98627 7.92779 6.29217 7.65849 6.82899L5.0731 11.9998L1.50044 11.9998C0.672112 11.9998 0.000488132 12.6714 0.000488122 13.4997C0.000488112 14.328 0.672112 14.9997 1.50044 14.9997L6.00034 14.9997C6.56869 14.9997 7.08797 14.6789 7.34208 14.1706L9.00024 10.8543L9.00024 16.483L12.0001 17.6079L15.0001 16.4827L15.0001 10.8543L16.6583 14.1706C16.9124 14.6789 17.4317 14.9997 18 14.9997L22.4999 14.9997C23.3283 14.9997 23.9999 14.328 23.9999 13.4997C23.9999 12.6714 23.3283 11.9998 22.5 11.9998Z" fill="#70349D"/>
                                                    </g>
                                                    <defs>
                                                    <clipPath id="clip1">
                                                    <rect width="24" height="24" fill="#70349D"/>
                                                    </clipPath>
                                                    </defs>
                                                </svg>
                                                <span class="text-secondary fs-16">Yoga</span>
                                            </div>
                                            <div class="col-xl-12 col-xxl-5 col-lg-4 col-12 d-flex gap-3 align-items-center justify-content-xxl-end justify-content-xl-between justify-content-lg-end justify-content-between mt-xxl-0 mt-xl-3 mt-lg-0 mt-3">
                                                <a class="btn play-btn btn-lg btn-light text-primary fs-14"><i class="las la-caret-right text-primary fs-24"></i>Start Workout</a>
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="btn btn-lg light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <svg width="6" height="26" viewBox="0 0 6 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M6 3C6 4.65685 4.65685 6 3 6C1.34315 6 0 4.65685 0 3C0 1.34315 1.34315 0 3 0C4.65685 0 6 1.34315 6 3Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 13C6 14.6569 4.65685 16 3 16C1.34315 16 0 14.6569 0 13C0 11.3431 1.34315 10 3 10C4.65685 10 6 11.3431 6 13Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 23C6 24.6569 4.65685 26 3 26C1.34315 26 0 24.6569 0 23C0 21.3431 1.34315 20 3 20C4.65685 20 6 21.3431 6 23Z" fill="var(--bs-body-color)"></path>
                                                        </svg>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" >Edit</a>
                                                        <a class="dropdown-item" >Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End - Workout 2 -->

                                        <!-- Start - Workout 3 -->
                                        <div class="d-flex border-bottom flex-wrap py-3 align-items-center px-3 list-row">
                                            <div class="col-xl-8 col-xxl-5 col-lg-6 col-sm-8 d-flex gap-3 align-items-center">
                                                <div class="avatar avatar-lg bg-primary-subtle d-grid border-0 rounded">
                                                    <div class="d-grid">
                                                        <p class="fs-24 text-primary mb-0">6</p>
                                                        <span class="fs-14 text-primary lh-1">Wed</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="fs-20 "><a href="workout-statistic.html" class="text-black">Routine Cardio Burn Workout</a></h4>
                                                    <span class="text-danger fs-16 fw-semibold">UNFINISHED</span>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-xxl-2 col-lg-2 col-sm-4 col-12 d-flex align-items-center gap-2 justify-content-xl-end justify-content-lg-center justify-content-md-end justify-content-center px-3 mt-md-0 mt-3">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="url(#clip2)">
                                                    <path d="M10.8586 5.22599L5.87121 10.5543C5.50758 11.0846 5.64394 11.8068 6.17172 12.1679L11.1945 15.6098L11.1945 18.9558C11.1945 19.5921 11.6995 20.125 12.3359 20.1376C12.9874 20.1477 13.5177 19.625 13.5177 18.976L13.5177 15.0013C13.5177 14.6174 13.3283 14.2588 13.0126 14.0442L9.79041 11.8346L12.5025 8.95837L13.8914 12.1225C14.0758 12.5442 14.4949 12.817 14.9546 12.817L19.1844 12.817C19.8207 12.817 20.3536 12.3119 20.3662 11.6755C20.3763 11.024 19.8536 10.4937 19.2046 10.4937L15.7172 10.4937C15.2576 9.44824 14.7677 8.41289 14.3409 7.35228C14.1237 6.81693 14.0025 6.5846 13.6036 6.21592C13.5227 6.14016 12.9596 5.62501 12.4571 5.16541C11.995 4.74619 11.2828 4.77397 10.8586 5.22599Z" fill="#1EA7C5"/>
                                                    <path d="M15.6162 5.80681C17.0861 5.80681 18.2778 4.61517 18.2778 3.1452C18.2778 1.67523 17.0861 0.483582 15.6162 0.483582C14.1462 0.483582 12.9545 1.67523 12.9545 3.1452C12.9545 4.61517 14.1462 5.80681 15.6162 5.80681Z" fill="#1EA7C5"/>
                                                    <path d="M4.89899 23.5164C7.60463 23.5164 9.79798 21.3231 9.79798 18.6174C9.79798 15.9118 7.60463 13.7184 4.89899 13.7184C2.19335 13.7184 -1.81927e-07 15.9118 -2.13831e-07 18.6174C-2.45735e-07 21.3231 2.19335 23.5164 4.89899 23.5164Z" fill="#1EA7C5"/>
                                                    <path d="M19.101 23.5164C21.8066 23.5164 24 21.3231 24 18.6174C24 15.9118 21.8066 13.7184 19.101 13.7184C16.3954 13.7184 14.202 15.9118 14.202 18.6174C14.202 21.3231 16.3954 23.5164 19.101 23.5164Z" fill="#1EA7C5"/>
                                                    </g>
                                                    <defs>
                                                    <clipPath id="clip2">
                                                    <rect width="24" height="24" fill="white"/>
                                                    </clipPath>
                                                    </defs>
                                                </svg>
                                                <span class="text-info fs-16">Cycling</span>
                                            </div>
                                            <div class="col-xl-12 col-xxl-5 col-lg-4 col-12 d-flex gap-3 align-items-center justify-content-xxl-end justify-content-xl-between justify-content-lg-end justify-content-between mt-xxl-0 mt-xl-3 mt-lg-0 mt-3">
                                                <a class="btn play-btn btn-lg btn-light text-primary fs-14"><i class="las la-caret-right text-primary fs-24"></i>Start Workout</a>
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="btn btn-lg light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <svg width="6" height="26" viewBox="0 0 6 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M6 3C6 4.65685 4.65685 6 3 6C1.34315 6 0 4.65685 0 3C0 1.34315 1.34315 0 3 0C4.65685 0 6 1.34315 6 3Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 13C6 14.6569 4.65685 16 3 16C1.34315 16 0 14.6569 0 13C0 11.3431 1.34315 10 3 10C4.65685 10 6 11.3431 6 13Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 23C6 24.6569 4.65685 26 3 26C1.34315 26 0 24.6569 0 23C0 21.3431 1.34315 20 3 20C4.65685 20 6 21.3431 6 23Z" fill="var(--bs-body-color)"></path>
                                                        </svg>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" >Edit</a>
                                                        <a class="dropdown-item" >Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End - Workout 3 -->

                                        <!-- Start - Workout 4 -->
                                        <div class="d-flex border-bottom flex-wrap py-3 align-items-center px-3 list-row">
                                            <div class="col-xl-7 col-xxl-5 col-lg-6 col-md-7 d-flex gap-3 align-items-center">
                                                <div class="avatar avatar-lg bg-light d-grid border-0 rounded">
                                                    <div class="d-grid">
                                                        <p class="fs-24 text-black mb-0">28</p>
                                                        <span class="fs-14 text-black lh-1">Fri</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="fs-20 "><a href="workout-statistic.html" class="text-black">Weekly Routine Cycling</a></h4>
                                                    <span class="text-primary fs-16 fw-semibold">Finished</span>
                                                    <span class="ps-3 pe-3 fs-16">34Km</span>
                                                    <span class="fs-16">00:23:45”</span>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-xxl-2 col-lg-2 col-md-3 col-6 d-flex align-items-center gap-2 justify-content-xl-end justify-content-lg-center justify-content-md-end justify-content-start mt-md-0 mt-3">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="url(#clip3)">
                                                    <path d="M10.8586 5.22599L5.87121 10.5543C5.50758 11.0846 5.64394 11.8068 6.17172 12.1679L11.1945 15.6098L11.1945 18.9558C11.1945 19.5921 11.6995 20.125 12.3359 20.1376C12.9874 20.1477 13.5177 19.625 13.5177 18.976L13.5177 15.0013C13.5177 14.6174 13.3283 14.2588 13.0126 14.0442L9.79041 11.8346L12.5025 8.95837L13.8914 12.1225C14.0758 12.5442 14.4949 12.817 14.9546 12.817L19.1844 12.817C19.8207 12.817 20.3536 12.3119 20.3662 11.6755C20.3763 11.024 19.8536 10.4937 19.2046 10.4937L15.7172 10.4937C15.2576 9.44824 14.7677 8.41289 14.3409 7.35228C14.1237 6.81693 14.0025 6.5846 13.6036 6.21592C13.5227 6.14016 12.9596 5.62501 12.4571 5.16541C11.995 4.74619 11.2828 4.77397 10.8586 5.22599Z" fill="#1EA7C5"/>
                                                    <path d="M15.6162 5.80681C17.0861 5.80681 18.2778 4.61517 18.2778 3.1452C18.2778 1.67523 17.0861 0.483582 15.6162 0.483582C14.1462 0.483582 12.9545 1.67523 12.9545 3.1452C12.9545 4.61517 14.1462 5.80681 15.6162 5.80681Z" fill="#1EA7C5"/>
                                                    <path d="M4.89899 23.5164C7.60463 23.5164 9.79798 21.3231 9.79798 18.6174C9.79798 15.9118 7.60463 13.7184 4.89899 13.7184C2.19335 13.7184 -1.81927e-07 15.9118 -2.13831e-07 18.6174C-2.45735e-07 21.3231 2.19335 23.5164 4.89899 23.5164Z" fill="#1EA7C5"/>
                                                    <path d="M19.101 23.5164C21.8066 23.5164 24 21.3231 24 18.6174C24 15.9118 21.8066 13.7184 19.101 13.7184C16.3954 13.7184 14.202 15.9118 14.202 18.6174C14.202 21.3231 16.3954 23.5164 19.101 23.5164Z" fill="#1EA7C5"/>
                                                    </g>
                                                    <defs>
                                                    <clipPath id="clip3">
                                                    <rect width="24" height="24" fill="white"/>
                                                    </clipPath>
                                                    </defs>
                                                </svg>
                                                <span class="text-info fs-16">Cycling</span>
                                            </div>
                                            <div class="col-xl-2 col-xxl-5 col-lg-4 col-md-2 col-6 d-flex align-items-center justify-content-end mt-md-0 mt-3">
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="btn btn-lg light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <svg width="6" height="26" viewBox="0 0 6 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M6 3C6 4.65685 4.65685 6 3 6C1.34315 6 0 4.65685 0 3C0 1.34315 1.34315 0 3 0C4.65685 0 6 1.34315 6 3Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 13C6 14.6569 4.65685 16 3 16C1.34315 16 0 14.6569 0 13C0 11.3431 1.34315 10 3 10C4.65685 10 6 11.3431 6 13Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 23C6 24.6569 4.65685 26 3 26C1.34315 26 0 24.6569 0 23C0 21.3431 1.34315 20 3 20C4.65685 20 6 21.3431 6 23Z" fill="var(--bs-body-color)"></path>
                                                        </svg>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" >Edit</a>
                                                        <a class="dropdown-item" >Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End - Workout 4 -->

                                        <!-- Start - Workout 5 -->
                                        <div class="d-flex border-bottom flex-wrap py-3 align-items-center px-3 list-row">
                                            <div class="col-xl-7 col-xxl-5 col-lg-6 col-md-7 d-flex gap-3 align-items-center">
                                                <div class="avatar avatar-lg bg-light d-grid border-0 rounded">
                                                    <div class="d-grid">
                                                        <p class="fs-24 text-black mb-0">21</p>
                                                        <span class="fs-14 text-black lh-1">Thu</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="fs-20 "><a href="workout-statistic.html" class="text-black">2020 Runner Event Workout</a></h4>
                                                    <span class="text-primary fs-16 fw-semibold">Finished</span>
                                                    <span class="ps-3 pe-3 fs-16">34Km</span>
                                                    <span class="fs-16">00:23:45”</span>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-xxl-2 col-lg-2 col-md-3 col-6 d-flex align-items-center gap-2 justify-content-xl-end justify-content-lg-center justify-content-md-end justify-content-start mt-md-0 mt-3">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="url(#clip4)">
                                                    <path d="M0.988957 17.0741C0.328275 17.2007 -0.104585 17.8386 0.0219821 18.4993C0.133361 19.0815 0.644693 19.4865 1.21678 19.4865C1.29272 19.4865 1.37119 19.4789 1.44713 19.4637L6.4592 18.5018C6.74524 18.4461 7.0009 18.2917 7.18316 18.0639L9.33481 15.3503L8.61593 14.9832C8.08435 14.7149 7.71474 14.2289 7.58818 13.6391L5.55804 16.1983L0.988957 17.0741Z" fill="#FF9432"/>
                                                    <path d="M18.84 6.49306C20.3135 6.49306 21.508 5.29854 21.508 3.82502C21.508 2.3515 20.3135 1.15698 18.84 1.15698C17.3665 1.15698 16.1719 2.3515 16.1719 3.82502C16.1719 5.29854 17.3665 6.49306 18.84 6.49306Z" fill="#FF9432"/>
                                                    <path d="M13.0179 3.15677C12.7369 2.8682 12.4762 2.75428 12.1902 2.75428C12.0864 2.75428 11.9826 2.76947 11.8712 2.79479L7.29203 3.88073C6.6592 4.03008 6.26937 4.66545 6.41872 5.29576C6.54782 5.83746 7.02877 6.20198 7.56289 6.20198C7.65404 6.20198 7.74514 6.19185 7.8363 6.16907L11.7371 5.24513C11.9902 5.52611 13.2584 6.90063 13.4888 7.14364C11.8763 8.87002 10.2639 10.5939 8.65137 12.3202C8.62605 12.3481 8.60329 12.3759 8.58049 12.4038C8.10966 13.0037 8.25397 13.9454 8.96275 14.3023L13.9064 16.826L11.3397 20.985C10.9878 21.5571 11.165 22.3064 11.7371 22.6608C11.9371 22.7848 12.1573 22.843 12.375 22.843C12.7825 22.843 13.1824 22.638 13.4128 22.2659L16.6732 16.983C16.8529 16.6919 16.901 16.34 16.8074 16.0135C16.7137 15.6844 16.4884 15.411 16.1821 15.2566L12.8331 13.553L16.3543 9.78636L19.0122 12.0393C19.2324 12.2266 19.5032 12.3177 19.7716 12.3177C20.0601 12.3177 20.3487 12.2114 20.574 12.0038L23.6243 9.16112C24.1002 8.71814 24.128 7.97392 23.685 7.49803C23.4521 7.24996 23.1383 7.12339 22.8244 7.12339C22.5383 7.12339 22.2497 7.22717 22.0245 7.43728L19.7412 9.56107C19.7386 9.56361 14.0178 4.18196 13.0179 3.15677Z" fill="#FF9432"/>
                                                    </g>
                                                    <defs>
                                                    <clipPath id="clip4">
                                                    <rect width="24" height="24" fill="white"/>
                                                    </clipPath>
                                                    </defs>
                                                </svg>
                                                <span class="text-warning fs-16">Running</span>
                                            </div>
                                            <div class="col-xl-2 col-xxl-5 col-lg-4 col-md-2 col-6 d-flex align-items-center justify-content-end mt-md-0 mt-3">
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="btn btn-lg light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <svg width="6" height="26" viewBox="0 0 6 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M6 3C6 4.65685 4.65685 6 3 6C1.34315 6 0 4.65685 0 3C0 1.34315 1.34315 0 3 0C4.65685 0 6 1.34315 6 3Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 13C6 14.6569 4.65685 16 3 16C1.34315 16 0 14.6569 0 13C0 11.3431 1.34315 10 3 10C4.65685 10 6 11.3431 6 13Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 23C6 24.6569 4.65685 26 3 26C1.34315 26 0 24.6569 0 23C0 21.3431 1.34315 20 3 20C4.65685 20 6 21.3431 6 23Z" fill="var(--bs-body-color)"></path>
                                                        </svg>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" >Edit</a>
                                                        <a class="dropdown-item" >Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End - Workout 5 -->

                                        <!-- Start - Workout 6 -->
                                        <div class="d-flex border-bottom flex-wrap py-3 align-items-center px-3 list-row">
                                            <div class="col-xl-7 col-xxl-5 col-lg-6 col-md-7 d-flex gap-3 align-items-center">
                                                <div class="avatar avatar-lg bg-light d-grid border-0 rounded">
                                                    <div class="d-grid">
                                                        <p class="fs-24 text-black mb-0">18</p>
                                                        <span class="fs-14 text-black lh-1">Sat</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="fs-20 "><a href="workout-statistic.html" class="text-black">Daily Running Workout</a></h4>
                                                    <span class="text-primary fs-16 fw-semibold">Finished</span>
                                                    <span class="ps-3 pe-3 fs-16">34Km</span>
                                                    <span class="fs-16">00:23:45”</span>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-xxl-2 col-lg-2 col-md-3 col-6 d-flex align-items-center gap-2 justify-content-xl-end justify-content-lg-center justify-content-md-end justify-content-start mt-md-0 mt-3">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="url(#clip5)">
                                                    <path d="M0.988957 17.0741C0.328275 17.2007 -0.104585 17.8386 0.0219821 18.4993C0.133361 19.0815 0.644693 19.4865 1.21678 19.4865C1.29272 19.4865 1.37119 19.4789 1.44713 19.4637L6.4592 18.5018C6.74524 18.4461 7.0009 18.2917 7.18316 18.0639L9.33481 15.3503L8.61593 14.9832C8.08435 14.7149 7.71474 14.2289 7.58818 13.6391L5.55804 16.1983L0.988957 17.0741Z" fill="#FF9432"/>
                                                    <path d="M18.84 6.49306C20.3135 6.49306 21.508 5.29854 21.508 3.82502C21.508 2.3515 20.3135 1.15698 18.84 1.15698C17.3665 1.15698 16.1719 2.3515 16.1719 3.82502C16.1719 5.29854 17.3665 6.49306 18.84 6.49306Z" fill="#FF9432"/>
                                                    <path d="M13.0179 3.15677C12.7369 2.8682 12.4762 2.75428 12.1902 2.75428C12.0864 2.75428 11.9826 2.76947 11.8712 2.79479L7.29203 3.88073C6.6592 4.03008 6.26937 4.66545 6.41872 5.29576C6.54782 5.83746 7.02877 6.20198 7.56289 6.20198C7.65404 6.20198 7.74514 6.19185 7.8363 6.16907L11.7371 5.24513C11.9902 5.52611 13.2584 6.90063 13.4888 7.14364C11.8763 8.87002 10.2639 10.5939 8.65137 12.3202C8.62605 12.3481 8.60329 12.3759 8.58049 12.4038C8.10966 13.0037 8.25397 13.9454 8.96275 14.3023L13.9064 16.826L11.3397 20.985C10.9878 21.5571 11.165 22.3064 11.7371 22.6608C11.9371 22.7848 12.1573 22.843 12.375 22.843C12.7825 22.843 13.1824 22.638 13.4128 22.2659L16.6732 16.983C16.8529 16.6919 16.901 16.34 16.8074 16.0135C16.7137 15.6844 16.4884 15.411 16.1821 15.2566L12.8331 13.553L16.3543 9.78636L19.0122 12.0393C19.2324 12.2266 19.5032 12.3177 19.7716 12.3177C20.0601 12.3177 20.3487 12.2114 20.574 12.0038L23.6243 9.16112C24.1002 8.71814 24.128 7.97392 23.685 7.49803C23.4521 7.24996 23.1383 7.12339 22.8244 7.12339C22.5383 7.12339 22.2497 7.22717 22.0245 7.43728L19.7412 9.56107C19.7386 9.56361 14.0178 4.18196 13.0179 3.15677Z" fill="#FF9432"/>
                                                    </g>
                                                    <defs>
                                                    <clipPath id="clip5">
                                                    <rect width="24" height="24" fill="white"/>
                                                    </clipPath>
                                                    </defs>
                                                </svg>
                                                <span class="text-warning fs-16">Running</span>
                                            </div>
                                            <div class="col-xl-2 col-xxl-5 col-lg-4 col-md-2 col-6 d-flex align-items-center justify-content-end mt-md-0 mt-3">
                                                <div class="dropdown more-dropdown mb-3">
                                                    <a href="javascript:void(0)" class="btn btn-lg light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <svg width="6" height="26" viewBox="0 0 6 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M6 3C6 4.65685 4.65685 6 3 6C1.34315 6 0 4.65685 0 3C0 1.34315 1.34315 0 3 0C4.65685 0 6 1.34315 6 3Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 13C6 14.6569 4.65685 16 3 16C1.34315 16 0 14.6569 0 13C0 11.3431 1.34315 10 3 10C4.65685 10 6 11.3431 6 13Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 23C6 24.6569 4.65685 26 3 26C1.34315 26 0 24.6569 0 23C0 21.3431 1.34315 20 3 20C4.65685 20 6 21.3431 6 23Z" fill="var(--bs-body-color)"></path>
                                                        </svg>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" >Edit</a>
                                                        <a class="dropdown-item" >Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End - Workout 6 -->

                                        <!-- Start - Workout 7 -->
                                        <div class="d-flex border-bottom flex-wrap py-3 align-items-center px-3 list-row">
                                            <div class="col-xl-7 col-xxl-5 col-lg-6 col-md-7 d-flex gap-3 align-items-center">
                                                <div class="avatar avatar-lg bg-light d-grid border-0 rounded">
                                                    <div class="d-grid">
                                                        <p class="fs-24 text-black mb-0">21</p>
                                                        <span class="fs-14 text-black lh-1">Tue</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="fs-20 "><a href="workout-statistic.html" class="text-black">2020 Runner Event Workout</a></h4>
                                                    <span class="text-primary fs-16 fw-semibold">Finished</span>
                                                    <span class="ps-3 pe-3 fs-16">34Km</span>
                                                    <span class="fs-16">00:23:45”</span>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-xxl-2 col-lg-2 col-md-3 col-6 d-flex align-items-center gap-2 justify-content-xl-end justify-content-lg-center justify-content-md-end justify-content-start mt-md-0 mt-3">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="url(#clip3)">
                                                    <path d="M10.8586 5.22599L5.87121 10.5543C5.50758 11.0846 5.64394 11.8068 6.17172 12.1679L11.1945 15.6098L11.1945 18.9558C11.1945 19.5921 11.6995 20.125 12.3359 20.1376C12.9874 20.1477 13.5177 19.625 13.5177 18.976L13.5177 15.0013C13.5177 14.6174 13.3283 14.2588 13.0126 14.0442L9.79041 11.8346L12.5025 8.95837L13.8914 12.1225C14.0758 12.5442 14.4949 12.817 14.9546 12.817L19.1844 12.817C19.8207 12.817 20.3536 12.3119 20.3662 11.6755C20.3763 11.024 19.8536 10.4937 19.2046 10.4937L15.7172 10.4937C15.2576 9.44824 14.7677 8.41289 14.3409 7.35228C14.1237 6.81693 14.0025 6.5846 13.6036 6.21592C13.5227 6.14016 12.9596 5.62501 12.4571 5.16541C11.995 4.74619 11.2828 4.77397 10.8586 5.22599Z" fill="#1EA7C5"/>
                                                    <path d="M15.6162 5.80681C17.0861 5.80681 18.2778 4.61517 18.2778 3.1452C18.2778 1.67523 17.0861 0.483582 15.6162 0.483582C14.1462 0.483582 12.9545 1.67523 12.9545 3.1452C12.9545 4.61517 14.1462 5.80681 15.6162 5.80681Z" fill="#1EA7C5"/>
                                                    <path d="M4.89899 23.5164C7.60463 23.5164 9.79798 21.3231 9.79798 18.6174C9.79798 15.9118 7.60463 13.7184 4.89899 13.7184C2.19335 13.7184 -1.81927e-07 15.9118 -2.13831e-07 18.6174C-2.45735e-07 21.3231 2.19335 23.5164 4.89899 23.5164Z" fill="#1EA7C5"/>
                                                    <path d="M19.101 23.5164C21.8066 23.5164 24 21.3231 24 18.6174C24 15.9118 21.8066 13.7184 19.101 13.7184C16.3954 13.7184 14.202 15.9118 14.202 18.6174C14.202 21.3231 16.3954 23.5164 19.101 23.5164Z" fill="#1EA7C5"/>
                                                    </g>
                                                    <defs>
                                                    <clipPath id="clip3">
                                                    <rect width="24" height="24" fill="white"/>
                                                    </clipPath>
                                                    </defs>
                                                </svg>
                                                <span class="text-info fs-16">Cycling</span>
                                            </div>
                                            <div class="col-xl-2 col-xxl-5 col-lg-4 col-md-2 col-6 d-flex align-items-center justify-content-end mt-md-0 mt-3">
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="btn btn-lg light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <svg width="6" height="26" viewBox="0 0 6 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M6 3C6 4.65685 4.65685 6 3 6C1.34315 6 0 4.65685 0 3C0 1.34315 1.34315 0 3 0C4.65685 0 6 1.34315 6 3Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 13C6 14.6569 4.65685 16 3 16C1.34315 16 0 14.6569 0 13C0 11.3431 1.34315 10 3 10C4.65685 10 6 11.3431 6 13Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 23C6 24.6569 4.65685 26 3 26C1.34315 26 0 24.6569 0 23C0 21.3431 1.34315 20 3 20C4.65685 20 6 21.3431 6 23Z" fill="var(--bs-body-color)"></path>
                                                        </svg>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" >Edit</a>
                                                        <a class="dropdown-item" >Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End - Workout 7 -->

                                        <!-- Start - Workout 8 -->
                                        <div class="d-flex flex-wrap py-3 align-items-center px-3 list-row">
                                            <div class="col-xl-7 col-xxl-5 col-lg-6 col-md-7 d-flex gap-3 align-items-center">
                                                <div class="avatar avatar-lg bg-light d-grid border-0 rounded">
                                                    <div class="d-grid">
                                                        <p class="fs-24 text-black mb-0">18</p>
                                                        <span class="fs-14 text-black lh-1">Sat</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="fs-20 "><a href="workout-statistic.html" class="text-black">Daily Running Workout</a></h4>
                                                    <span class="text-primary fs-16 fw-semibold">Finished</span>
                                                    <span class="ps-3 pe-3 fs-16">34Km</span>
                                                    <span class="fs-16">00:23:45”</span>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-xxl-2 col-lg-2 col-md-3 col-6 d-flex align-items-center gap-2 justify-content-xl-end justify-content-lg-center justify-content-md-end justify-content-start mt-md-0 mt-3">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="url(#clip4)">
                                                    <path d="M0.988957 17.0741C0.328275 17.2007 -0.104585 17.8386 0.0219821 18.4993C0.133361 19.0815 0.644693 19.4865 1.21678 19.4865C1.29272 19.4865 1.37119 19.4789 1.44713 19.4637L6.4592 18.5018C6.74524 18.4461 7.0009 18.2917 7.18316 18.0639L9.33481 15.3503L8.61593 14.9832C8.08435 14.7149 7.71474 14.2289 7.58818 13.6391L5.55804 16.1983L0.988957 17.0741Z" fill="#FF9432"/>
                                                    <path d="M18.84 6.49306C20.3135 6.49306 21.508 5.29854 21.508 3.82502C21.508 2.3515 20.3135 1.15698 18.84 1.15698C17.3665 1.15698 16.1719 2.3515 16.1719 3.82502C16.1719 5.29854 17.3665 6.49306 18.84 6.49306Z" fill="#FF9432"/>
                                                    <path d="M13.0179 3.15677C12.7369 2.8682 12.4762 2.75428 12.1902 2.75428C12.0864 2.75428 11.9826 2.76947 11.8712 2.79479L7.29203 3.88073C6.6592 4.03008 6.26937 4.66545 6.41872 5.29576C6.54782 5.83746 7.02877 6.20198 7.56289 6.20198C7.65404 6.20198 7.74514 6.19185 7.8363 6.16907L11.7371 5.24513C11.9902 5.52611 13.2584 6.90063 13.4888 7.14364C11.8763 8.87002 10.2639 10.5939 8.65137 12.3202C8.62605 12.3481 8.60329 12.3759 8.58049 12.4038C8.10966 13.0037 8.25397 13.9454 8.96275 14.3023L13.9064 16.826L11.3397 20.985C10.9878 21.5571 11.165 22.3064 11.7371 22.6608C11.9371 22.7848 12.1573 22.843 12.375 22.843C12.7825 22.843 13.1824 22.638 13.4128 22.2659L16.6732 16.983C16.8529 16.6919 16.901 16.34 16.8074 16.0135C16.7137 15.6844 16.4884 15.411 16.1821 15.2566L12.8331 13.553L16.3543 9.78636L19.0122 12.0393C19.2324 12.2266 19.5032 12.3177 19.7716 12.3177C20.0601 12.3177 20.3487 12.2114 20.574 12.0038L23.6243 9.16112C24.1002 8.71814 24.128 7.97392 23.685 7.49803C23.4521 7.24996 23.1383 7.12339 22.8244 7.12339C22.5383 7.12339 22.2497 7.22717 22.0245 7.43728L19.7412 9.56107C19.7386 9.56361 14.0178 4.18196 13.0179 3.15677Z" fill="#FF9432"/>
                                                    </g>
                                                    <defs>
                                                    <clipPath id="clip4">
                                                    <rect width="24" height="24" fill="white"/>
                                                    </clipPath>
                                                    </defs>
                                                </svg>
                                                <span class="text-warning fs-16">Running</span>
                                            </div>
                                            <div class="col-xl-2 col-xxl-5 col-lg-4 col-md-2 col-6 d-flex align-items-center justify-content-end mt-md-0 mt-3">
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="btn btn-lg light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <svg width="6" height="26" viewBox="0 0 6 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M6 3C6 4.65685 4.65685 6 3 6C1.34315 6 0 4.65685 0 3C0 1.34315 1.34315 0 3 0C4.65685 0 6 1.34315 6 3Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 13C6 14.6569 4.65685 16 3 16C1.34315 16 0 14.6569 0 13C0 11.3431 1.34315 10 3 10C4.65685 10 6 11.3431 6 13Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 23C6 24.6569 4.65685 26 3 26C1.34315 26 0 24.6569 0 23C0 21.3431 1.34315 20 3 20C4.65685 20 6 21.3431 6 23Z" fill="var(--bs-body-color)"></path>
                                                        </svg>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" >Edit</a>
                                                        <a class="dropdown-item" >Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End - Workout 8 -->

                                    </div>
                                    <!-- End - All Tab -->

                                    <!-- Start - Unfinished Tab -->
                                    <div class="tab-pane active show fade" id="Unifinshed">
                                        
                                        <!-- Start - Workout 1 -->
                                        <div class="d-flex border-bottom flex-wrap py-3 align-items-center px-3 list-row">
                                            <div class="col-xl-8 col-xxl-8 col-lg-8 col-sm-8 d-flex gap-3 align-items-center">
                                                <div class="avatar avatar-lg bg-primary-subtle d-grid border-0 rounded">
                                                    <div class="d-grid">
                                                        <p class="fs-18 text-primary mb-0">18 - 20</p>
                                                        <span class="fs-12 text-primary lh-1 mt-1">1 day left</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="fs-16"><a type="button" data-bs-toggle="modal" data-bs-target="#details" class="text-black">Mengerjakan Website Utama dan Sub Halaman Company Profile Rajawali Inti Coal Halmahera</a></h4>
                                                    <span class="fs-14 me-2">
                                                        <span class="text-danger fw-semibold">UNFINISHED</span> 
                                                        <span class="ps-3 pe-3 fs-14">asigned by <span class="fw-semibold text-primary">@rexyaldinny</span></span>
                                                        <span class="text-danger fw-semibold">Priority: High</span> 
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-xxl-4 col-lg-4 col-12 d-flex gap-3 align-items-center justify-content-xxl-end justify-content-xl-between justify-content-lg-end justify-content-between mt-xxl-0 mt-xl-3 mt-lg-0 mt-3">
                                                <a type="button" data-bs-toggle="modal" data-bs-target="#complete" class="btn play-btn btn-lg btn-light text-primary fs-14"><i class="las la-caret-right text-primary fs-24"></i>Mark as Done</a>
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="btn btn-lg light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <svg width="6" height="26" viewBox="0 0 6 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M6 3C6 4.65685 4.65685 6 3 6C1.34315 6 0 4.65685 0 3C0 1.34315 1.34315 0 3 0C4.65685 0 6 1.34315 6 3Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 13C6 14.6569 4.65685 16 3 16C1.34315 16 0 14.6569 0 13C0 11.3431 1.34315 10 3 10C4.65685 10 6 11.3431 6 13Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 23C6 24.6569 4.65685 26 3 26C1.34315 26 0 24.6569 0 23C0 21.3431 1.34315 20 3 20C4.65685 20 6 21.3431 6 23Z" fill="var(--bs-body-color)"></path>
                                                        </svg>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#details">View More</a>
                                                        <a class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#update">Update Task</a>
                                                        <a class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#delete">Delete Task</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End - Workout 1 -->

                                        <!-- Start - Workout 1 -->
                                        <div class="d-flex border-bottom flex-wrap py-3 align-items-center px-3 list-row">
                                            <div class="col-xl-8 col-xxl-8 col-lg-8 col-sm-8 d-flex gap-3 align-items-center">
                                                <div class="avatar avatar-lg bg-light d-grid border-0 rounded">
                                                    <div class="d-grid">
                                                        <p class="fs-24 text-black mb-0">18</p>
                                                        <span class="fs-14 text-black lh-1">Mon</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="fs-16">
                                                        <a type="button" data-bs-toggle="modal" data-bs-target="#details" class="text-black">
                                                            Mengerjakan Website Registrasi FPV 2026
                                                        </a>
                                                    </h4>
                                                    <span class="fs-14 me-2">
                                                        <span class="text-success fw-semibold">Finished</span> 
                                                        <span class="ps-3 pe-3 fs-14">Festival Pelatihan Vokasi 2026</span>
                                                        <span class="text-success fw-semibold">Priority: Low</span> 
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-xxl-4 col-lg-4 col-12 d-flex gap-3 align-items-center justify-content-xxl-end justify-content-xl-between justify-content-lg-end justify-content-between mt-xxl-0 mt-xl-3 mt-lg-0 mt-3">
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="btn btn-lg light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <svg width="6" height="26" viewBox="0 0 6 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M6 3C6 4.65685 4.65685 6 3 6C1.34315 6 0 4.65685 0 3C0 1.34315 1.34315 0 3 0C4.65685 0 6 1.34315 6 3Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 13C6 14.6569 4.65685 16 3 16C1.34315 16 0 14.6569 0 13C0 11.3431 1.34315 10 3 10C4.65685 10 6 11.3431 6 13Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 23C6 24.6569 4.65685 26 3 26C1.34315 26 0 24.6569 0 23C0 21.3431 1.34315 20 3 20C4.65685 20 6 21.3431 6 23Z" fill="var(--bs-body-color)"></path>
                                                        </svg>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#details">View More</a>
                                                        <a class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#update">Update Task</a>
                                                        <a class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#delete">Delete Task</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End - Workout 1 -->

                                    </div>
                                    <!-- End - Unfinished Tab -->

                                    <!-- Start - Finished Tab -->
                                    <div class="tab-pane fade" id="Finished">
                                        
                                        <!-- Start - Workout 1 -->
                                        <div class="d-flex border-bottom flex-wrap py-3 align-items-center px-3 list-row">
                                            <div class="col-xl-8 col-xxl-5 col-lg-6 col-sm-8 d-flex gap-3 align-items-center">
                                                <div class="avatar avatar-lg bg-primary-subtle d-grid border-0 rounded">
                                                    <div class="d-grid">
                                                        <p class="fs-24 text-primary mb-0">4</p>
                                                        <span class="fs-14 text-primary lh-1">Mon</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="fs-20 "><a href="workout-statistic.html" class="text-black">Routine Cardio Burn Workout</a></h4>
                                                    <span class="text-danger fs-16 fw-semibold">UNFINISHED</span>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-xxl-2 col-lg-2 col-sm-4 col-12 d-flex align-items-center gap-2 justify-content-xl-end justify-content-lg-center justify-content-md-end justify-content-center px-3 mt-md-0 mt-3">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="url(#clip0)">
                                                        <path d="M0.988957 17.0741C0.328275 17.2007 -0.104585 17.8386 0.0219821 18.4993C0.133361 19.0815 0.644693 19.4865 1.21678 19.4865C1.29272 19.4865 1.37119 19.4789 1.44713 19.4637L6.4592 18.5018C6.74524 18.4461 7.0009 18.2917 7.18316 18.0639L9.33481 15.3503L8.61593 14.9832C8.08435 14.7149 7.71474 14.2289 7.58818 13.6391L5.55804 16.1983L0.988957 17.0741Z" fill="#FF9432"/>
                                                        <path d="M18.84 6.49306C20.3135 6.49306 21.508 5.29854 21.508 3.82502C21.508 2.3515 20.3135 1.15698 18.84 1.15698C17.3665 1.15698 16.1719 2.3515 16.1719 3.82502C16.1719 5.29854 17.3665 6.49306 18.84 6.49306Z" fill="#FF9432"/>
                                                        <path d="M13.0179 3.15677C12.7369 2.8682 12.4762 2.75428 12.1902 2.75428C12.0864 2.75428 11.9826 2.76947 11.8712 2.79479L7.29203 3.88073C6.6592 4.03008 6.26937 4.66545 6.41872 5.29576C6.54782 5.83746 7.02877 6.20198 7.56289 6.20198C7.65404 6.20198 7.74514 6.19185 7.8363 6.16907L11.7371 5.24513C11.9902 5.52611 13.2584 6.90063 13.4888 7.14364C11.8763 8.87002 10.2639 10.5939 8.65137 12.3202C8.62605 12.3481 8.60329 12.3759 8.58049 12.4038C8.10966 13.0037 8.25397 13.9454 8.96275 14.3023L13.9064 16.826L11.3397 20.985C10.9878 21.5571 11.165 22.3064 11.7371 22.6608C11.9371 22.7848 12.1573 22.843 12.375 22.843C12.7825 22.843 13.1824 22.638 13.4128 22.2659L16.6732 16.983C16.8529 16.6919 16.901 16.34 16.8074 16.0135C16.7137 15.6844 16.4884 15.411 16.1821 15.2566L12.8331 13.553L16.3543 9.78636L19.0122 12.0393C19.2324 12.2266 19.5032 12.3177 19.7716 12.3177C20.0601 12.3177 20.3487 12.2114 20.574 12.0038L23.6243 9.16112C24.1002 8.71814 24.128 7.97392 23.685 7.49803C23.4521 7.24996 23.1383 7.12339 22.8244 7.12339C22.5383 7.12339 22.2497 7.22717 22.0245 7.43728L19.7412 9.56107C19.7386 9.56361 14.0178 4.18196 13.0179 3.15677Z" fill="#FF9432"/>
                                                    </g>
                                                    <defs>
                                                        <clipPath id="clip0">
                                                            <rect width="24" height="24" fill="white"/>
                                                        </clipPath>
                                                    </defs>
                                                </svg>
                                                <span class="text-warning fs-16">Running</span>
                                            </div>
                                            <div class="col-xl-12 col-xxl-5 col-lg-4 col-12 d-flex gap-3 align-items-center justify-content-xxl-end justify-content-xl-between justify-content-lg-end justify-content-between mt-xxl-0 mt-xl-3 mt-lg-0 mt-3">
                                                <a class="btn play-btn btn-lg btn-light text-primary fs-14"><i class="las la-caret-right text-primary fs-24"></i>Start Workout</a>
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="btn btn-lg light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <svg width="6" height="26" viewBox="0 0 6 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M6 3C6 4.65685 4.65685 6 3 6C1.34315 6 0 4.65685 0 3C0 1.34315 1.34315 0 3 0C4.65685 0 6 1.34315 6 3Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 13C6 14.6569 4.65685 16 3 16C1.34315 16 0 14.6569 0 13C0 11.3431 1.34315 10 3 10C4.65685 10 6 11.3431 6 13Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 23C6 24.6569 4.65685 26 3 26C1.34315 26 0 24.6569 0 23C0 21.3431 1.34315 20 3 20C4.65685 20 6 21.3431 6 23Z" fill="var(--bs-body-color)"></path>
                                                        </svg>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" >Edit</a>
                                                        <a class="dropdown-item" >Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End - Workout 1 -->

                                        <!-- Start - Workout 2 -->
                                        <div class="d-flex border-bottom flex-wrap py-3 align-items-center px-3 list-row">
                                            <div class="col-xl-7 col-xxl-5 col-lg-6 col-md-7 d-flex gap-3 align-items-center">
                                                <div class="avatar avatar-lg bg-light d-grid border-0 rounded">
                                                    <div class="d-grid">
                                                        <p class="fs-24 text-black mb-0">28</p>
                                                        <span class="fs-14 text-black lh-1">Fri</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="fs-20 "><a href="workout-statistic.html" class="text-black">Weekly Routine Cycling</a></h4>
                                                    <span class="text-primary fs-16 fw-semibold">Finished</span>
                                                    <span class="ps-3 pe-3 fs-16">34Km</span>
                                                    <span class="fs-16">00:23:45”</span>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-xxl-2 col-lg-2 col-md-3 col-6 d-flex align-items-center gap-2 justify-content-xl-end justify-content-lg-center justify-content-md-end justify-content-start mt-md-0 mt-3">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="url(#clip3)">
                                                    <path d="M10.8586 5.22599L5.87121 10.5543C5.50758 11.0846 5.64394 11.8068 6.17172 12.1679L11.1945 15.6098L11.1945 18.9558C11.1945 19.5921 11.6995 20.125 12.3359 20.1376C12.9874 20.1477 13.5177 19.625 13.5177 18.976L13.5177 15.0013C13.5177 14.6174 13.3283 14.2588 13.0126 14.0442L9.79041 11.8346L12.5025 8.95837L13.8914 12.1225C14.0758 12.5442 14.4949 12.817 14.9546 12.817L19.1844 12.817C19.8207 12.817 20.3536 12.3119 20.3662 11.6755C20.3763 11.024 19.8536 10.4937 19.2046 10.4937L15.7172 10.4937C15.2576 9.44824 14.7677 8.41289 14.3409 7.35228C14.1237 6.81693 14.0025 6.5846 13.6036 6.21592C13.5227 6.14016 12.9596 5.62501 12.4571 5.16541C11.995 4.74619 11.2828 4.77397 10.8586 5.22599Z" fill="#1EA7C5"/>
                                                    <path d="M15.6162 5.80681C17.0861 5.80681 18.2778 4.61517 18.2778 3.1452C18.2778 1.67523 17.0861 0.483582 15.6162 0.483582C14.1462 0.483582 12.9545 1.67523 12.9545 3.1452C12.9545 4.61517 14.1462 5.80681 15.6162 5.80681Z" fill="#1EA7C5"/>
                                                    <path d="M4.89899 23.5164C7.60463 23.5164 9.79798 21.3231 9.79798 18.6174C9.79798 15.9118 7.60463 13.7184 4.89899 13.7184C2.19335 13.7184 -1.81927e-07 15.9118 -2.13831e-07 18.6174C-2.45735e-07 21.3231 2.19335 23.5164 4.89899 23.5164Z" fill="#1EA7C5"/>
                                                    <path d="M19.101 23.5164C21.8066 23.5164 24 21.3231 24 18.6174C24 15.9118 21.8066 13.7184 19.101 13.7184C16.3954 13.7184 14.202 15.9118 14.202 18.6174C14.202 21.3231 16.3954 23.5164 19.101 23.5164Z" fill="#1EA7C5"/>
                                                    </g>
                                                    <defs>
                                                    <clipPath id="clip3">
                                                    <rect width="24" height="24" fill="white"/>
                                                    </clipPath>
                                                    </defs>
                                                </svg>
                                                <span class="text-info fs-16">Cycling</span>
                                            </div>
                                            <div class="col-xl-2 col-xxl-5 col-lg-4 col-md-2 col-6 d-flex align-items-center justify-content-end mt-md-0 mt-3">
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="btn btn-lg light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <svg width="6" height="26" viewBox="0 0 6 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M6 3C6 4.65685 4.65685 6 3 6C1.34315 6 0 4.65685 0 3C0 1.34315 1.34315 0 3 0C4.65685 0 6 1.34315 6 3Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 13C6 14.6569 4.65685 16 3 16C1.34315 16 0 14.6569 0 13C0 11.3431 1.34315 10 3 10C4.65685 10 6 11.3431 6 13Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 23C6 24.6569 4.65685 26 3 26C1.34315 26 0 24.6569 0 23C0 21.3431 1.34315 20 3 20C4.65685 20 6 21.3431 6 23Z" fill="var(--bs-body-color)"></path>
                                                        </svg>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" >Edit</a>
                                                        <a class="dropdown-item" >Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End - Workout 2 -->

                                        <!-- Start - Workout 3 -->
                                        <div class="d-flex  flex-wrap py-3 align-items-center px-3 list-row">
                                            <div class="col-xl-7 col-xxl-5 col-lg-6 col-md-7 d-flex gap-3 align-items-center">
                                                <div class="avatar avatar-lg bg-light d-grid border-0 rounded">
                                                    <div class="d-grid">
                                                        <p class="fs-24 text-black mb-0">21</p>
                                                        <span class="fs-14 text-black lh-1">Thu</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="fs-20 "><a href="workout-statistic.html" class="text-black">2020 Runner Event Workout</a></h4>
                                                    <span class="text-primary fs-16 fw-semibold">Finished</span>
                                                    <span class="ps-3 pe-3 fs-16">34Km</span>
                                                    <span class="fs-16">00:23:45”</span>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-xxl-2 col-lg-2 col-md-3 col-6 d-flex align-items-center gap-2 justify-content-xl-end justify-content-lg-center justify-content-md-end justify-content-start mt-md-0 mt-3">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="url(#clip4)">
                                                    <path d="M0.988957 17.0741C0.328275 17.2007 -0.104585 17.8386 0.0219821 18.4993C0.133361 19.0815 0.644693 19.4865 1.21678 19.4865C1.29272 19.4865 1.37119 19.4789 1.44713 19.4637L6.4592 18.5018C6.74524 18.4461 7.0009 18.2917 7.18316 18.0639L9.33481 15.3503L8.61593 14.9832C8.08435 14.7149 7.71474 14.2289 7.58818 13.6391L5.55804 16.1983L0.988957 17.0741Z" fill="#FF9432"/>
                                                    <path d="M18.84 6.49306C20.3135 6.49306 21.508 5.29854 21.508 3.82502C21.508 2.3515 20.3135 1.15698 18.84 1.15698C17.3665 1.15698 16.1719 2.3515 16.1719 3.82502C16.1719 5.29854 17.3665 6.49306 18.84 6.49306Z" fill="#FF9432"/>
                                                    <path d="M13.0179 3.15677C12.7369 2.8682 12.4762 2.75428 12.1902 2.75428C12.0864 2.75428 11.9826 2.76947 11.8712 2.79479L7.29203 3.88073C6.6592 4.03008 6.26937 4.66545 6.41872 5.29576C6.54782 5.83746 7.02877 6.20198 7.56289 6.20198C7.65404 6.20198 7.74514 6.19185 7.8363 6.16907L11.7371 5.24513C11.9902 5.52611 13.2584 6.90063 13.4888 7.14364C11.8763 8.87002 10.2639 10.5939 8.65137 12.3202C8.62605 12.3481 8.60329 12.3759 8.58049 12.4038C8.10966 13.0037 8.25397 13.9454 8.96275 14.3023L13.9064 16.826L11.3397 20.985C10.9878 21.5571 11.165 22.3064 11.7371 22.6608C11.9371 22.7848 12.1573 22.843 12.375 22.843C12.7825 22.843 13.1824 22.638 13.4128 22.2659L16.6732 16.983C16.8529 16.6919 16.901 16.34 16.8074 16.0135C16.7137 15.6844 16.4884 15.411 16.1821 15.2566L12.8331 13.553L16.3543 9.78636L19.0122 12.0393C19.2324 12.2266 19.5032 12.3177 19.7716 12.3177C20.0601 12.3177 20.3487 12.2114 20.574 12.0038L23.6243 9.16112C24.1002 8.71814 24.128 7.97392 23.685 7.49803C23.4521 7.24996 23.1383 7.12339 22.8244 7.12339C22.5383 7.12339 22.2497 7.22717 22.0245 7.43728L19.7412 9.56107C19.7386 9.56361 14.0178 4.18196 13.0179 3.15677Z" fill="#FF9432"/>
                                                    </g>
                                                    <defs>
                                                    <clipPath id="clip4">
                                                    <rect width="24" height="24" fill="white"/>
                                                    </clipPath>
                                                    </defs>
                                                </svg>
                                                <span class="text-warning fs-16">Running</span>
                                            </div>
                                            <div class="col-xl-2 col-xxl-5 col-lg-4 col-md-2 col-6 d-flex align-items-center justify-content-end mt-md-0 mt-3">
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="btn btn-lg light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <svg width="6" height="26" viewBox="0 0 6 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M6 3C6 4.65685 4.65685 6 3 6C1.34315 6 0 4.65685 0 3C0 1.34315 1.34315 0 3 0C4.65685 0 6 1.34315 6 3Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 13C6 14.6569 4.65685 16 3 16C1.34315 16 0 14.6569 0 13C0 11.3431 1.34315 10 3 10C4.65685 10 6 11.3431 6 13Z" fill="var(--bs-body-color)"></path>
                                                            <path d="M6 23C6 24.6569 4.65685 26 3 26C1.34315 26 0 24.6569 0 23C0 21.3431 1.34315 20 3 20C4.65685 20 6 21.3431 6 23Z" fill="var(--bs-body-color)"></path>
                                                        </svg>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" >Edit</a>
                                                        <a class="dropdown-item" >Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End - Workout 3 -->

                                    </div>
                                    <!-- End - Finished Tab -->

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End - Plan List -->
                
                <!-- Start - Next week plan -->
                <div class="col-xl-4 col-xxl-3">	
                    <div class="row">	
                        <div class="col-xl-12">
                            <div class="card flex-xl-column flex-md-row flex-column">
                                <div class="card-body widget-events">
                                    <input type='text' class="form-control d-none" id='datetimepicker1'>
                                </div>
                                <div class="card-body">
                                    <h4 class="text-black mb-4">This week plan</h4>
                                    <div class="d-flex mb-4 align-items-center">
                                        <span class="avatar avatar-primary border-0 rounded me-3">18</span>
                                        <div>
                                            <h6 class="fs-16"><a href="workout-statistic.html" class="text-black">Meta Ads Rumah ...</a></h6>
                                            <span class="fs-14">20 May (Due in 1 day)</span>
                                        </div>
                                    </div>
                                    <div class="d-flex mb-4 align-items-center">
                                        <span class="avatar avatar-primary border-0 rounded me-3">20</span>
                                        <div>
                                            <h6 class="fs-16"><a href="workout-statistic.html" class="text-black">Sistem Registrasi ...</a></h6>
                                            <span class="fs-14">20 May (Due in 1 day)</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-primary border-0 rounded me-3">21</span>
                                        <div>
                                            <h6 class="fs-16"><a href="workout-statistic.html" class="text-black">Mengerjakan ...</a></h6>
                                            <span class="fs-14">20 May (Due in 1 day)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End - Next week plan -->
                
            </div>
        </div>
        <div class="tab-pane fade" id="project-grid-pane" role="tabpanel" aria-labelledby="project-grid-tab" tabindex="0">
            <div class="row">
                <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                    <div class="card">
                        <div class="card-body text-center p-4 d-flex flex-column justify-content-between">
                            <div class="m-auto pb-3">
                                <img src="assets/images/files/folder.avif" alt="">
                            </div>
                            <div class="clearfix">
                                <h5 class="mb-0">CRUD Invoices</h5>
                                <span class="fs-14">7 files</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                    <div class="card">
                        <div class="card-body text-center p-4 d-flex flex-column justify-content-between">
                            <div class="m-auto pb-3">
                                <img src="assets/images/files/folder.avif" alt="">
                            </div>
                            <div class="clearfix">
                                <h5 class="mb-0">Tower Hill Docs</h5>
                                <span class="fs-14">3 files</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                    <div class="card">
                        <div class="card-body text-center p-4 d-flex flex-column justify-content-between">
                            <div class="m-auto pb-3">
                                <img src="assets/images/files/folder.avif" alt="">
                            </div>
                            <div class="clearfix">
                                <h5 class="mb-0">Mivy App</h5>
                                <span class="fs-14">25 files</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                    <div class="card">
                        <div class="card-body text-center p-4 d-flex flex-column justify-content-between">
                            <div class="m-auto pb-3">
                                <img src="assets/images/files/folder.avif" alt="">
                            </div>
                            <div class="clearfix">
                                <h5 class="mb-0">Leaf CRM API Co..</h5>
                                <span class="fs-14">2 days ago</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                    <div class="card">
                        <div class="card-body text-center p-4 d-flex flex-column justify-content-between">
                            <div class="m-auto pb-3">
                                <img src="assets/images/files/csv.avif" alt="">
                            </div>
                            <div class="clearfix">
                                <h5 class="mb-0">Tower Hill bilboa..</h5>
                                <span class="fs-14">2 days ago</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                    <div class="card">
                        <div class="card-body text-center p-4 d-flex flex-column justify-content-between">
                            <div class="m-auto pb-3">
                                <img src="assets/images/files/css.avif" alt="">
                            </div>
                            <div class="clearfix">
                                <h5 class="mb-0">Orders backup</h5>
                                <span class="fs-14">2 days ago</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                    <div class="card">
                        <div class="card-body text-center p-4 d-flex flex-column justify-content-between">
                            <div class="m-auto pb-3">
                                <img src="assets/images/files/pdf.avif" alt="">
                            </div>
                            <div class="clearfix">
                                <h5 class="mb-0">Avionica.</h5>
                                <span class="fs-14">2 days ago</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                    <div class="card">
                        <div class="card-body text-center p-4 d-flex flex-column justify-content-between">
                            <div class="m-auto pb-3">
                                <img src="assets/images/files/html.avif" alt="">
                            </div>
                            <div class="clearfix">
                                <h5 class="mb-0">9 Degree CRUD Req..</h5>
                                <span class="fs-14">2 days ago</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                    <div class="card">
                        <div class="card-body text-center p-4 d-flex flex-column justify-content-between">
                            <div class="m-auto pb-3">
                                <img src="assets/images/files/ppt.avif" alt="">
                            </div>
                            <div class="clearfix">
                                <h5 class="mb-0">User CRUD Styles</h5>
                                <span class="fs-14">2 days ago</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                    <div class="card">
                        <div class="card-body text-center p-4 d-flex flex-column justify-content-between">
                            <div class="m-auto pb-3">
                                <img src="assets/images/files/mp3.avif" alt="">
                            </div>
                            <div class="clearfix">
                                <h5 class="mb-0">Craft Logo</h5>
                                <span class="fs-14">2 days ago</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End - Billing Statement -->
</div>


<!-- Start - Modal Create New Task -->
<div class="modal fade" id="create">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form class="comment-form">
                    <div class="row"> 
                        <div class="col-12 col-lg-12">
                            <div class="mb-3">
                                <label class="form-label">Task Name <span class="required text-danger">*</span></label>
                                <input type="text" class="form-control" name="Author" placeholder="Contoh: Rekap absensi bulanan">
                            </div>
                        </div>
                        <div class="col-12 col-lg-12">
                            <div class="mb-3">
                                <label class="form-label">Due Date <span class="required text-danger">*</span></label>
                                <input type="text" class="form-control" name="Email" value="20 May 2026">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success">Save changes</button>
            </div>
        </div>
    </div>
</div>
<!-- End - Modal Create New Task -->

<!-- Start - Modal Update Task -->
<div class="modal fade" id="update">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Complete a Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form class="comment-form">
                    <div class="row"> 
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Task Name <span class="required text-danger">*</span></label>
                                <input type="text" class="form-control" name="Author" placeholder="Contoh: Rekap absensi bulanan" value="Mengerjakan Website Company Profile Rajawali Inti Coal Halmahera">
                            </div>
                        </div> 
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Task Description <span class="required text-danger">*</span></label>
                                <textarea class="form-control" rows="3" placeholder="Tambahkan detail atau konteks pekerjaan"></textarea>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Date <span class="required text-danger">*</span></label>
                                <input type="text" class="form-control" name="Email" value="20 May 2026">
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Due Date <span class="required text-danger">*</span></label>
                                <input type="text" class="form-control" name="Email" value="20 May 2026">
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Workload <span class="required text-danger">*</span></label>
                                <select class="form-control default-select">
                                    <option class="text-success fw-semibold">Light</option>
                                    <option class="text-warning fw-semibold" selected>Moderate</option>
                                    <option class="text-danger fw-semibold">Heavy</option>
                                </select>									
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Priority <span class="required text-danger">*</span></label>
                                <select class="form-control default-select">
                                    <option class="text-success fw-semibold">Low</option>
                                    <option class="text-warning fw-semibold" selected>Medium</option>
                                    <option class="text-danger fw-semibold">High</option>
                                </select>									
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Attachment</label>
                                <input type="text" class="form-control" name="Author" placeholder="Contoh: Link Google Drive, Figma, atau Docs">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Blockers</label>
                                <input type="text" class="form-control" name="Author" placeholder="Contoh: Menunggu persetujuan (approval) dokumen dari manajer">
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Task Category <span class="required text-danger">*</span></label>
                                <select class="form-control default-select">
                                    <option selected>Daily Task Report</option>
                                    <option>Project Report</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Project Name <span class="required text-danger">*</span></label>
                                <select class="form-control default-select" disabled>
                                    <option>Pilih Nama Project</option>
                                    <option>Option 2</option>
                                    <option>Option 3</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Task Status <span class="required text-danger">*</span></label>
                                <select class="form-control default-select">
                                    <option>To Do</option>
                                    <option>On Progress</option>
                                    <option>Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Start - Modal Details New Task -->
<div class="modal fade" id="details">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row py-2">
                    <div class="col-4">
                        <span>Task Name</span>
                    </div>
                    <div class="col-8">
                        <span class="text-gray fw-semibold">Mengerjakan Website Company Profile Rajawali Inti Coal Halmahera</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-4">
                        <span>Task Description</span>
                    </div>
                    <div class="col-8">
                        <span class="text-gray fw-normal">Rekap laporan cuti bulan Mei 2026 dan serahkan ke divisi keuangan untuk selanjutnya dibuat journal bulanan</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-4">
                        <span>Date - Due Date</span>
                    </div>
                    <div class="col-8">
                        <span class="text-gray fw-semibold">18 Mei 2026 - 19 Mei 2026 (2 days)</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-4">
                        <span>Attachment</span>
                    </div>
                    <div class="col-8">
                        <span class="text-primary fw-semibold"><a href="http://" class="text-primary" target="_blank" rel="noopener noreferrer">Drive Link</a></span> / 
                        <span class="text-gray fw-semibold">No attachment</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-4">
                        <span>Blockers</span>
                    </div>
                    <div class="col-8">
                        <span class="text-gray fw-normal">Menunggu approval dari PIC dan Manager</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-4">
                        <span>Task Category</span>
                    </div>
                    <div class="col-8">
                        <span class="text-gray fw-semibold">Project Report</span> <br>
                        <span class="text-gray fw-normal">Muktamar PKB IV Bali 2024</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-4">
                        <span>Assigned by</span>
                    </div>
                    <div class="col-8">
                        <span class="text-gray fw-semibold"><a href="" class="text-primary">Rexy Aldinny</a></span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-4">
                        <span>Task Status</span>
                    </div>
                    <div class="col-8">
                        <span class="text-danger fw-semibold">On Progress</span>
                    </div>
                </div>
                <div class="alert alert-danger outline-dashed border-2 py-3 px-3 mt-3 mb-0 text-dark d-flex align-items-center">
                    <div class="clearfix">
                        <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 30C18.9782 30 22.7936 28.4196 25.6066 25.6066C28.4196 22.7936 30 18.9782 30 15C30 11.0218 28.4196 7.20644 25.6066 4.3934C22.7936 1.58035 18.9782 0 15 0C11.0218 0 7.20644 1.58035 4.3934 4.3934C1.58035 7.20644 0 11.0218 0 15C0 18.9782 1.58035 22.7936 4.3934 25.6066C7.20644 28.4196 11.0218 30 15 30ZM12.6562 19.6875H14.0625V15.9375H12.6562C11.877 15.9375 11.25 15.3105 11.25 14.5312C11.25 13.752 11.877 13.125 12.6562 13.125H15.4688C16.248 13.125 16.875 13.752 16.875 14.5312V19.6875H17.3438C18.123 19.6875 18.75 20.3145 18.75 21.0938C18.75 21.873 18.123 22.5 17.3438 22.5H12.6562C11.877 22.5 11.25 21.873 11.25 21.0938C11.25 20.3145 11.877 19.6875 12.6562 19.6875ZM15 7.5C15.4973 7.5 15.9742 7.69754 16.3258 8.04918C16.6775 8.40081 16.875 8.87772 16.875 9.375C16.875 9.87228 16.6775 10.3492 16.3258 10.7008C15.9742 11.0525 15.4973 11.25 15 11.25C14.5027 11.25 14.0258 11.0525 13.6742 10.7008C13.3225 10.3492 13.125 9.87228 13.125 9.375C13.125 8.87772 13.3225 8.40081 13.6742 8.04918C14.0258 7.69754 14.5027 7.5 15 7.5Z" fill="#F94687"></path>
                        </svg>
                    </div>
                    <div class="mx-3">
                        <h6 class="mb-0 fw-semibold">Pending Tasks Reminder!</h6>
                        <p class="mb-0">You currently have active tasks that are not yet marked as complete.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- End - Modal Details New Task -->
<!-- End - Modal Update Task -->
<!-- Start - Modal Delete -->
<div class="modal fade" id="delete">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteLabel">Delete Task</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <h5 class="text-muted fw-bold text-center">Withdraw Task Submission?</h5>
                            <p class="form-label text-muted mb-3">
                                Do you want to cancel this pending task? We will remove it from the reviewer's queue so you can submit a revised one later if necessary.
                            </p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark light" data-bs-dismiss="modal">Nevermind</button>
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Yes, Delete It</button>
            </div>
        </div>
    </div>
</div>
<!-- End - Modal Delete -->
@endsection

@section('script')

    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endsection

