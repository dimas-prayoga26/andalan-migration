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
        <h5 class="mb-0">Business Trip - Cash Advance</h5>
        <div class="d-flex align-items-center">

        </div>
    </div>
</div>
<!-- End - My Projects -->

<div class="card">
    <div class="card-header border-0 pb-0">
        <div>
            <h4 class="card-title">Request Cash Advance</h4>
            <p class="fs-13 mb-0">Please submit your advance request at least 3 days before the funds are needed. Any unspent funds and valid receipts must be reported within 7 days after the trip concludes.</p>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-2">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Date</label>
                    <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="Date Needed">
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
                    <label for="exampleFormControlInput1" class="form-label">Breakdown</label>
                    <select class="selectpicker form-select" required>
                        <option value="AL">Accommodation</option>
                        <option value="WY">Transportation</option>
                        <option value="WY">Meals & Entertaintment</option>
                        <option value="WY">Local Transport</option>
                        <option value="WY">Others</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Notes</label>
                    <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="Cash advance for local transportation (airport taxis), daily meals, and client entertainment">
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
            <div class="col-12 col-md-2">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Amount Realized</label>
                    <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="Amount Realized">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Attachments</label>
                    <input type="file" class="form-control" id="exampleFormControlInput1" placeholder="Cash advance for local transportation (airport taxis), daily meals, and client entertainment">
                </div>
            </div>
            <hr>
        </div>
        <div class="row">
            <h6 class="card-title">Approved By Finance</h6>
            <div class="col-12 col-md-2">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Date</label>
                    <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="Date Needed" disabled>
                </div>
            </div>
            <div class="col-12 col-md-2">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Amount</label>
                    <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="Amount" disabled>
                </div>
            </div>
            <div class="col-12 col-md-2">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Amount Approved</label>
                    <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="Amount Approved" disabled>
                </div>
            </div>
            <div class="col-12 col-md-2">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Breakdown</label>
                    <select class="selectpicker form-select" required disabled>
                        <option value="AL">Accommodation</option>
                        <option value="WY">Transportation</option>
                        <option value="WY">Meals & Entertaintment</option>
                        <option value="WY">Local Transport</option>
                        <option value="WY">Others</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Notes</label>
                    <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="Cash advance for local transportation (airport taxis), daily meals, and client entertainment" disabled>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-3">
            <a class="btn light btn-danger me-2 mb-2 btn-lg" href="attendance-business-trip-details.html">Back</a>
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
