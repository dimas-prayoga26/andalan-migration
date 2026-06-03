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
    'current' => 'Overtime',
    'homeRoute' => 'dashboard',
])

@include('attendance.layouts.profile-index')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Business Trip - Create</h5>
        <div class="d-flex align-items-center">

        </div>
    </div>
</div>
<!-- End - My Projects -->

<div class="card">
    <div class="card-header border-0 pb-0">
        <div>
            <h4 class="card-title">Business Trip Request</h4>
            <p class="fs-13 mb-0">
                Please fill out the details below to request approval and arrange logistics for your upcoming trip.
            </p>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-12">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Purpose</label>
                    <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="Purpose">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Dates</label>
                    <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="Dates">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Business Trip Type</label>
                    <select class="selectpicker form-select" required>
                        <option value="AL">Local (Dalam Kota)</option>
                        <option value="WY">Intercity (Luar Kota)</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Province</label>
                    <select class="selectpicker form-select" required>
                        <option value="AL">DKI Jakarta</option>
                        <option value="WY">Jawa Tenggah</option>
                        <option value="WY">Jawa Timur</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">City / Regency</label>
                    <select class="selectpicker form-select" required>
                        <option value="AL">Jakarta Pusat</option>
                        <option value="AL">Jakarta Barat</option>
                        <option value="AL">Jakarta Timur</option>
                        <option value="AL">Jakarta Utara</option>
                        <option value="AL">Jakarta Selatan</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Transportation</label>
                    <select class="selectpicker form-select" required>
                        <option value="AL">Self-Managed</option>
                        <option value="AL">Booked by GA</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Accommodation</label>
                    <select class="selectpicker form-select" required>
                        <option value="AL">Self-Managed</option>
                        <option value="WY">Booked by GA</option>
                        <option value="AL">Not Needed</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-12">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Transportation Mode</label>
                    <div class="form-group mt-1 mb-0">
                        <div class="form-check d-inline-block me-3">
                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1">
                            <label class="form-check-label" for="flexRadioDefault1">Flight</label>
                        </div>
                        <div class="form-check d-inline-block me-3">
                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault3">
                            <label class="form-check-label" for="flexRadioDefault3">Bus</label>
                        </div>
                        <div class="form-check d-inline-block me-3">
                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2">
                            <label class="form-check-label" for="flexRadioDefault2">Train</label>
                        </div>
                        <div class="form-check d-inline-block me-3">
                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault4">
                            <label class="form-check-label" for="flexRadioDefault4">Car</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Departure Date</label>
                    <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="Dates">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Departure Time</label>
                    <select class="selectpicker form-select" required>
                        <option value="AL">Morning (06:00 - 11:59)</option>
                        <option value="WY">Afternoon (12:00 - 17:59)</option>
                        <option value="WY">Evening (18:00 - 23:59)</option>
                        <option value="WY">Early Morning (00:00 - 05:59)</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Date Check In</label>
                    <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="Dates">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Date Check Out</label>
                    <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="Dates">
                </div>
            </div>
            
        </div>
        <div class="d-flex justify-content-end mt-3">
            <a class="btn light btn-danger me-2 mb-2 btn-lg" href="attendance-business-trip.html">Back</a>
            <a class="btn light btn-success mb-2 btn-lg" data-bs-toggle="modal" data-bs-target="#reimbursement">Submit</a>
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
