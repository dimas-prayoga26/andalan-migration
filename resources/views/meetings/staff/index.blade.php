@extends('layouts.main')

@section('title', 'Zoom Meeting')

@section('navbarTitle', 'Zoom Meeting')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Scheduled</h5>
        </div>
    </div>

    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <div class="card overflow-hidden">
            <div class="card-body p-xxl-4">
                <div class="d-flex justify-content-between mb-4 align-items-center gap-2">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm p-2">
                            <img src="{{ asset('assets/images/logo/large/zoom.png') }}" width="35" alt="">
                        </div>
                        <span class="ms-3">Weekly Meeting</span>
                    </div>
                    <button type="button" class="btn btn-square btn-primary light btn-sm">
                        <i class="bi bi-grid"></i>
                    </button>
                </div>
                <h2 class="fw-semibold pt-1">14 Sep, 09:00 WIB</h2>
            </div>
            <a href="#" target="_blank" class="btn light btn-primary mt-0 m-3 mb-2 btn-lg">Join Now</a>
            <div class="mb-3"></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Completed</h5>
        </div>
    </div>

    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <div class="card overflow-hidden">
            <div class="card-body p-xxl-4">
                <div class="d-flex justify-content-between mb-4 align-items-center gap-2">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm p-2">
                            <img src="{{ asset('assets/images/logo/large/zoom.png') }}" width="35" alt="">
                        </div>
                        <span class="ms-3">Weekly Meeting</span>
                    </div>
                    <button type="button" class="btn btn-square btn-success light btn-sm">
                        <i class="bi bi-grid"></i>
                    </button>
                </div>
                <h2 class="fw-semibold pt-1 mb-3">11 Sep, 10:00 WIB</h2>
                <p class="mb-0">
                    <i class="bi bi-people me-1"></i>
                    10 Staff Joined
                </p>
                <p class="mb-0">
                    <i class="bi bi-check2-circle me-1"></i>
                    18 Number of Task
                </p>
            </div>
            <a href="{{ route('zoom-meeting.details') }}" class="btn light btn-success mt-0 m-3 mb-2 btn-lg">Meeting Details</a>
            <div class="mb-3"></div>
        </div>
    </div>
</div>
@endsection
