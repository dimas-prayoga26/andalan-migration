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
    'current' => 'Business Trip - Reimbursement',
    'homeRoute' => 'dashboard',
])

@include('attendance.layouts.profile-index')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Business Trip - Reimbursement</h5>
        <div class="d-flex align-items-center">

        </div>
    </div>
</div>
<!-- End - My Projects -->

<div class="card">
    <div class="card-header border-0 pb-0">
        <div>
            <h4 class="card-title">Reimbursement Form</h4>
            <p class="fs-13 mb-0">Please itemize your trip expenses below and attach clear photos or PDFs of all receipts. Claims must be submitted within 7 days of returning from your trip.</p>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-2">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Date</label>
                    <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="Date">
                </div>
            </div>
            <div class="col-12 col-md-2">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Amount</label>
                    <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="Amount">
                </div>
            </div>
            <div class="col-12 col-md-2">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Category</label>
                    <select class="selectpicker form-select" required>
                        <option value="AL">Accommodation</option>
                        <option value="WY">Transportation</option>
                        <option value="WY">Meals & Entertaintment</option>
                        <option value="WY">Local Transport</option>
                        <option value="WY">Others</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-2">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Notes</label>
                    <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="Reimbursement Notes">
                </div>
            </div>
            <div class="col-12 col-md-2">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Receipt</label>
                    <input type="file" class="form-control" id="exampleFormControlInput1" placeholder="Amount">
                </div>
            </div>
            <div class="col-12 col-md-2">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Action</label>
                    <div class="d-flex align-items-center">                                                        
                        <button type="button" class="btn btn-success light me-2" data-bs-dismiss="modal">Add</button>
                        <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Remove</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-3">
            <a class="btn light btn-danger me-2 mb-2 btn-lg" href="{{ isset($businessTrip) ? route('attendance.business-trips.show', $businessTrip) : route('attendance.business-trips') }}">Back</a>
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
