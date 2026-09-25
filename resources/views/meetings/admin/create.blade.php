@extends('layouts.main')

@section('title', 'Create HR Meeting')

@section('navbarTitle', 'Setup Meeting')

@section('content')
<!-- Start - Page Title & Breadcrumb -->
				<div class="page-title">
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb">
							<li><h1>Setup Meeting</h1></li>
							<li class="breadcrumb-item">
								<a href="{{ route('dashboard') }}">
									<svg width="18" height="18" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M2.125 6.375L8.5 1.41667L14.875 6.375V14.1667C14.875 14.5424 14.7257 14.9027 14.4601 15.1684C14.1944 15.4341 13.8341 15.5833 13.4583 15.5833H3.54167C3.16594 15.5833 2.80561 15.4341 2.53993 15.1684C2.27426 14.9027 2.125 14.5424 2.125 14.1667V6.375Z" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
										<path d="M6.375 15.5833V8.5H10.625V15.5833" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									Home
								</a>
							</li>
							<li class="breadcrumb-item" aria-current="page">Setup Meeting</li>
							<li class="breadcrumb-item active" aria-current="page">Create Meeting</li>
						</ol>
					</nav>
				</div>
				<!-- End - Page Title & Breadcrumb -->
				
				<div class="tab-content" id="tabContentMyProfileBottom">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header border-0 pb-0">
                                    <div>
                                        <h4 class="card-title">Schedule a Zoom Meeting</h4>
                                        <p class="fs-13 mb-0">
                                            Please provide the requested information below so we can arrange the digital logistics and secure a hosted link for your upcoming virtual meeting.
                                        </p>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label for="exampleFormControlInput1" class="form-label">Meeting Title</label>
                                                <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="Purpose">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label for="exampleFormControlInput1" class="form-label">Meeting Type</label>
                                                <select class="selectpicker form-select" required>
                                                    <option value="AL">Weekly Meeting</option>
                                                    <option value="WY">BOD Meeting</option>
                                                    <option value="WY">Evaluation</option>
                                                    <option value="WY">Division Meeting</option>
                                                    <option value="WY">Other Meeting</option>
                                                </select>
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
                                                <label for="exampleFormControlInput1" class="form-label">Times</label>
                                                <input type="time" class="form-control" id="exampleFormControlInput1" placeholder="Times">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label for="exampleFormControlInput1" class="form-label">Meeting Link</label>
                                                <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="Link">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label for="exampleFormControlInput1" class="form-label">Invited Participants</label>
                                                <select id="multi-value-select" multiple="multiple">
                                                    <optgroup label="Group">
                                                        <option selected="selected">All Staff</option>
                                                        <option>BOD</option>
                                                    </optgroup>
                                                    <optgroup label="Group">
                                                        <option>Rexy Aldinny</option>
                                                        <option>Muhammad Syafiq</option>
                                                        <option>Adam</option>
                                                        <option>Adam</option>
                                                        <option>Adam</option>
                                                        <option>Adam</option>
                                                    </optgroup>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label for="exampleFormControlInput1" class="form-label">Meeting Status</label>
                                                <div class="form-group mt-1 mb-0">
                                                    <div class="form-check d-inline-block me-3">
                                                        <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1" checked>
                                                        <label class="form-check-label" for="flexRadioDefault1">Scheduled</label>
                                                    </div>
                                                    <div class="form-check d-inline-block me-3">
                                                        <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault3">
                                                        <label class="form-check-label" for="flexRadioDefault3">Completed</label>
                                                    </div>
                                                    <div class="form-check d-inline-block me-3">
                                                        <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2">
                                                        <label class="form-check-label" for="flexRadioDefault2">Canceled</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label for="exampleFormControlInput1" class="form-label">Attachments Link</label>
                                                <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="Link for Presentation">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label for="exampleFormControlInput1" class="form-label">Notes</label>
                                                <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="Additional notes">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-3">
                                        <a class="btn light btn-danger me-2 mb-2 btn-lg" href="{{ route('hr-meetings.index') }}">Back</a>
                                        <a class="btn light btn-success mb-2 btn-lg" data-bs-toggle="modal" data-bs-target="#reimbursement">Submit</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

				</div>
                    
				<!-- End - Attendance -->
@endsection
