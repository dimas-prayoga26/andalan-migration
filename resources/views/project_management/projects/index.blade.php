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
<style>
    .project-card-description {
        min-height: 58px;
    }

    .project-card-folder {
        width: 48px;
    }
</style>

@endsection

@section('content')

@include('layouts.breadcrumb', [
    'title' => 'Project Management',
    'current' => 'Projects',
    'homeRoute' => 'dashboard',
])

@include('project_management.layouts.profile-index')

@php
    $projectCards = collect($projectCards ?? []);
@endphp

<div class="tab-content" id="tabContentMyProfileBottom">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Projects</h5>
    </div>

    <div class="row">
        @forelse ($projectCards as $projectCard)
            <div class="col-xxl-3 col-xl-4 col-sm-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="clearfix d-flex">
                            <div class="avatar avatar-sm rounded me-3 p-2">
                                <img src="{{ asset('assets/images/files/folder.avif') }}" class="project-card-folder" alt="">
                            </div>
                            <div class="clearfix">
                                <h6 class="mb-0 fw-semibold">
                                    <a href="{{ $projectCard['detail_url'] }}" class="stretched-link">{{ $projectCard['name'] }}</a>
                                </h6>
                                <span class="small">{{ $projectCard['client_name'] }}</span>
                            </div>
                        </div>
                        <p class="my-3 project-card-description">{{ $projectCard['description'] }}</p>
                        <div class="clearfix">
                            <h6 class="mb-1 fw-medium">Team</h6>
                            <span class="fs-14">{{ $projectCard['team_count'] }} Staff</span>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between">
                                <span>Project Complete</span>
                                <span>{{ $projectCard['completion_rate'] }}%</span>
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-purple" style="width: {{ $projectCard['completion_rate'] }}%;" role="progressbar"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between flex-wrap">
                        <p class="mb-0 fw-medium">Due <span class="text-purple">: {{ $projectCard['due_label'] }}</span></p>
                        <span class="badge badge-sm badge-{{ $projectCard['status_class'] }} light">{{ $projectCard['status_label'] }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="mb-1">No active project found</h5>
                        <p class="mb-0 fs-14">Project cards appear after your employee is added as an active project member.</p>
                    </div>
                </div>
            </div>
        @endforelse
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
