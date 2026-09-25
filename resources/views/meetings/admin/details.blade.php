@extends('layouts.main')

@section('title', 'HR Meeting Details')

@section('navbarTitle', 'Meeting Details')

@section('css')
<style>
    .meeting-summary-donut {
        width: 170px;
        height: 170px;
        border-radius: 50%;
        background: conic-gradient(
            #2444c3 0deg 75deg,
            #9b2cf3 75deg 150deg,
            #22c55e 150deg 201deg,
            #f43f86 201deg 261deg,
            #ffb000 261deg 360deg
        );
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 170px;
    }

    .meeting-summary-donut::after {
        content: "";
        position: absolute;
        inset: 10px;
        border-radius: 50%;
        background: #fff;
    }

    .meeting-summary-donut-content {
        position: relative;
        z-index: 1;
        text-align: center;
        line-height: 1.1;
    }

    .meeting-summary-donut-content strong {
        display: block;
        color: #071739;
        font-size: 28px;
        font-weight: 700;
    }

    .meeting-zoom-logo {
        width: 28px;
        height: 28px;
        object-fit: contain;
    }

    .meeting-staff-img {
        width: 30px;
        height: 30px;
        object-fit: cover;
        background: #f3f4f6;
    }
</style>
@endsection

@section('content')
<!-- Start - Page Title & Breadcrumb -->
				<div class="page-title">
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb">
							<li><h1>Meeting</h1></li>
							<li class="breadcrumb-item">
								<a href="{{ route('dashboard') }}">
									<svg width="18" height="18" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M2.125 6.375L8.5 1.41667L14.875 6.375V14.1667C14.875 14.5424 14.7257 14.9027 14.4601 15.1684C14.1944 15.4341 13.8341 15.5833 13.4583 15.5833H3.54167C3.16594 15.5833 2.80561 15.4341 2.53993 15.1684C2.27426 14.9027 2.125 14.5424 2.125 14.1667V6.375Z" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
										<path d="M6.375 15.5833V8.5H10.625V15.5833" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									Home
								</a>
							</li>
							<li class="breadcrumb-item" aria-current="page"><a href="{{ route('hr-meetings.index') }}">Meeting</a></li>
							<li class="breadcrumb-item active" aria-current="page">Meeting Details</li>
						</ol>
					</nav>
				</div>
				<!-- End - Page Title & Breadcrumb -->
				
				<div class="tab-content" id="tabContentMyProfileBottom">

                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="card">
                                <div class="card-header pb-0 border-0">
                                    <div class="clearfix d-flex">
                                        <div class="avatar avatar-sm rounded me-3 p-2">
                                            <img src="{{ asset('assets/images/logo/large/zoom.png') }}" class="meeting-zoom-logo" alt="Zoom">
                                        </div>
                                        <div class="clearfix">
                                            <h4 class="mb-0 fw-semibold"><a href="report-project-details.html" class="stretched-link">Weekly Meeting</a></h4>
                                            <span class="small">Evaluasi Mingguan</span>	
                                        </div>	
                                    </div>
                                </div>
                                <div class="card-body px-3">
                                    <div class="row ps-3 mb-3">
                                        <div class="col-md-6 col-12">
                                            <span class="d-block">Date</span>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <span class="d-block fw-semibold">Friday, 04 September 2025</span>
                                        </div>
                                    </div>
                                    <div class="row ps-3 mb-3">
                                        <div class="col-md-6 col-12">
                                            <span class="d-block">Time</span>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <span class="d-block fw-semibold">10:00 WIB</span>
                                        </div>
                                    </div>
                                    <div class="row ps-3 mb-3">
                                        <div class="col-md-6 col-12">
                                            <span class="d-block">Meeting Status</span>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <span class="d-block text-success fw-semibold">Completed</span>
                                        </div>
                                    </div>
                                    <div class="row ps-3 mb-3">
                                        <div class="col-md-6 col-12">
                                            <span class="d-block">Attachments Link</span>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <a href="https://www.canva.com" target="_blank" rel="noopener noreferrer"><span class="d-block text-primary fw-semibold">canva.com</span></a>
                                            
                                        </div>
                                    </div>
                                    <div class="row ps-3 mb-3">
                                        <div class="col-md-6 col-12">
                                            <span class="d-block">Total Task</span>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <span class="d-block fw-semibold">18 Tasks</span>
                                        </div>
                                    </div>
                                    <div class="row ps-3 mb-3">
                                        <div class="col-md-6 col-12">
                                            <span class="d-block">Joined</span>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <span class="d-block fw-semibold">12 Staff</span>
                                        </div>
                                    </div>
                                    <div class="clearfix mt-3 ms-3">
                                        <h6 class="mb-1 fw-semibold">Staff</h6>
                                        <div class="avatar-list avatar-list-stacked">
                                            <img src="{{ asset('files/employees/ceo.png') }}" class="avatar avatar-xs rounded-circle border-2 border-white meeting-staff-img" alt="Staff">
                                            <img src="{{ asset('files/employees/gamer.png') }}" class="avatar avatar-xs rounded-circle border-2 border-white meeting-staff-img" alt="Staff">
                                            <img src="{{ asset('files/employees/girl.png') }}" class="avatar avatar-xs rounded-circle border-2 border-white meeting-staff-img" alt="Staff">
                                            <img src="{{ asset('files/employees/man.png') }}" class="avatar avatar-xs rounded-circle border-2 border-white meeting-staff-img" alt="Staff">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="card">
                                <div class="card-header pb-0 border-0">
                                    <div class="clearfix">
                                        <h4 class="card-title mb-0">Tasks Summary</h4>
                                        <small class="d-block">24 Overdue Tasks</small>
                                    </div>
                                </div>
                                <div class="card-body pb-0">
                                    <div class="row align-items-center">
                                        <div class="col-sm-6 mb-3">
                                            <div id="chartTasksSummary" class="d-flex justify-content-center">
                                                <div class="meeting-summary-donut" aria-label="Tasks summary chart">
                                                    <div class="meeting-summary-donut-content">
                                                        <strong>120</strong>
                                                        <span>Total</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 mb-3">
                                            <div class="d-flex justify-content-between mb-3">
                                                <div class="text-black">
                                                    <i class="fa-solid fa-square text-primary me-1"></i> Administration
                                                </div>
                                                <span>25</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-3">
                                                <div class="text-black">
                                                    <i class="fa-solid fa-square text-secondary me-1"></i> Event (KMA)
                                                </div>
                                                <span>25</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-3">
                                                <div class="text-black">
                                                    <i class="fa-solid fa-square text-success me-1"></i> Property (TRAH)
                                                </div>
                                                <span>17</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-3">
                                                <div class="text-black">
                                                    <i class="fa-solid fa-square text-danger me-1"></i> IT and Socmed (TMS)
                                                </div>
                                                <span>20</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <div class="text-black">
                                                    <i class="fa-solid fa-square text-warning me-1"></i> Others
                                                </div>
                                                <span>38</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <a href="" target="_blank" class="btn light btn-primary mt-3 m-3 mb-2 btn-lg">Generate MoM PDF</a>
							    <div class="mb-3"></div>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="card">
                                <div class="card-header pb-0 border-0">
                                    <div class="clearfix d-flex">
                                        
                                        <div class="clearfix">
                                            <h4 class="mb-0 fw-semibold"><a href="report-project-details.html" class="stretched-link">Log Attendance</a></h4>
                                            <span class="small">Record of attendance</span>	
                                        </div>	
                                    </div>
                                </div>
                                <div class="card-body px-3 dz-scroll height380">
                                    <div class="table-responsive">
                                            <table class="table table-sm table-sm-responsive table-bottom-borderless mb-0">
                                                <thead class="text-nowrap">
                                                    <tr>
                                                        <th class="mw-10">No</th>
                                                        <th class="mw-150">User</th>
                                                        <th class="mw-150">Time</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>1.</td>
                                                        <td>Williams</td>
                                                        <td>09:01 WIB</td>
                                                    </tr>
                                                    <tr>
                                                        <td>2</td>
                                                        <td>Paul</td>
                                                        <td>09:0 WIB</td>
                                                    </tr>
                                                    <tr>
                                                        <td>3.</td>
                                                        <td>Sarah</td>
                                                        <td>09:00 WIB</td>
                                                    </tr>
                                                    <tr>
                                                        <td>4.</td>
                                                        <td>Marcus</td>
                                                        <td>09:00 WIB</td>
                                                    </tr>
                                                    <tr>
                                                        <td>5.</td>
                                                        <td>Maria</td>
                                                        <td>09:00 WIB</td>
                                                    </tr>
                                                    <tr>
                                                        <td>6.</td>
                                                        <td>Robert</td>
                                                        <td>09:00 WIB</td>
                                                    </tr>
                                                    <tr>
                                                        <td>7.</td>
                                                        <td>Juan</td>
                                                        <td>09:10 WIB</td>
                                                    </tr>
                                                    <tr>
                                                        <td>8.</td>
                                                        <td>Robert</td>
                                                        <td>09:10 WIB</td>
                                                    </tr>
                                                    <tr>
                                                        <td>9.</td>
                                                        <td>Bob</td>
                                                        <td>09:10 WIB</td>
                                                    </tr>
                                                    <tr>
                                                        <td>10.</td>
                                                        <td>Alex</td>
                                                        <td>09:10 WIB</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="card">
                                <div class="card-header pb-0 border-0">
                                    <div class="clearfix">
                                        <h4 class="card-title mb-0">Administration</h4>
                                        <small class="d-block">Administrative operational support</small>
                                    </div>
                                </div>
                                <div class="card-body dz-scroll height380">
                                    <div class="d-flex justify-content-between mb-3">
                                        <div class="clearfix">
                                            <span class="text-gray fw-semibold">5 / 8 Completed <span class="text-success">(62%)</span></span>
                                        </div>
                                        <div class="clearfix">
                                            <a class="text-success fw-semibold" data-bs-toggle="modal" data-bs-target="#create">+ Add Task</a>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <a data-bs-toggle="modal" data-bs-target="#details"><h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6></a>
                                            <span class="small">Due in 1 day (10 jun) <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#details">View More</a>
                                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#update">Update</a>
                                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#delete">Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Design Stage</h6>
                                            <span class="small">18 May 2026 (2 days overdue) by <span class="text-primary">Rexy</span></span>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <button type="button" class="btn btn-sm btn-light btn-square" disabled><i class="bi bi-grid"></i></button>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-success me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Develop halaman utama website promotion</h6>
                                            <span class="small">19 May 2026 (Status: Completed) by <span class="text-primary">Syafiq</span></span>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <button type="button" class="btn btn-sm btn-light btn-square"><i class="bi bi-grid"></i></button>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="card">
                                <div class="card-header pb-0 border-0">
                                    <div class="clearfix">
                                        <h4 class="card-title mb-0">Event Management (KMA)</h4>
                                        <small class="d-block">Event Planning & Operations</small>
                                    </div>
                                </div>
                                <div class="card-body dz-scroll height380">
                                    <div class="d-flex justify-content-between mb-3">
                                        <div class="clearfix">
                                            <span class="text-gray fw-semibold">5 / 8 Completed <span class="text-success">(62%)</span></span>
                                        </div>
                                        <div class="clearfix">
                                            <a class="text-success fw-semibold" data-bs-toggle="modal" data-bs-target="#create">+ Add Task</a>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <a data-bs-toggle="modal" data-bs-target="#details"><h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6></a>
                                            <span class="small">Due in 1 day (10 jun) <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#details">View More</a>
                                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#update">Update</a>
                                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#delete">Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Design Stage</h6>
                                            <span class="small">18 May 2026 (2 days overdue) by <span class="text-primary">Rexy</span></span>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <button type="button" class="btn btn-sm btn-light btn-square" disabled><i class="bi bi-grid"></i></button>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-success me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Develop halaman utama website promotion</h6>
                                            <span class="small">19 May 2026 (Status: Completed) by <span class="text-primary">Syafiq</span></span>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <button type="button" class="btn btn-sm btn-light btn-square"><i class="bi bi-grid"></i></button>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="card">
                                <div class="card-header pb-0 border-0">
                                    <div class="clearfix">
                                        <h4 class="card-title mb-0">Property and Construction (TRAH)</h4>
                                        <small class="d-block">Property & Construction Operations</small>
                                    </div>
                                </div>
                                <div class="card-body dz-scroll height380">
                                    <div class="d-flex justify-content-between mb-3">
                                        <div class="clearfix">
                                            <span class="text-gray fw-semibold">5 / 8 Completed <span class="text-success">(62%)</span></span>
                                        </div>
                                        <div class="clearfix">
                                            <a class="text-success fw-semibold" data-bs-toggle="modal" data-bs-target="#create">+ Add Task</a>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <a data-bs-toggle="modal" data-bs-target="#details"><h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6></a>
                                            <span class="small">Due in 1 day (10 jun) <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#details">View More</a>
                                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#update">Update</a>
                                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#delete">Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Design Stage</h6>
                                            <span class="small">18 May 2026 (2 days overdue) by <span class="text-primary">Rexy</span></span>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <button type="button" class="btn btn-sm btn-light btn-square" disabled><i class="bi bi-grid"></i></button>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-success me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Develop halaman utama website promotion</h6>
                                            <span class="small">19 May 2026 (Status: Completed) by <span class="text-primary">Syafiq</span></span>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <button type="button" class="btn btn-sm btn-light btn-square"><i class="bi bi-grid"></i></button>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="card">
                                <div class="card-header pb-0 border-0">
                                    <div class="clearfix">
                                        <h4 class="card-title mb-0">IT and Social Media (TMS)</h4>
                                        <small class="d-block">Digital publication and IT operational support</small>
                                    </div>
                                </div>
                                <div class="card-body dz-scroll height380">
                                    <div class="d-flex justify-content-between mb-3">
                                        <div class="clearfix">
                                            <span class="text-gray fw-semibold">5 / 8 Completed <span class="text-success">(62%)</span></span>
                                        </div>
                                        <div class="clearfix">
                                            <a class="text-success fw-semibold" data-bs-toggle="modal" data-bs-target="#create">+ Add Task</a>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <a data-bs-toggle="modal" data-bs-target="#details"><h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6></a>
                                            <span class="small">Due in 1 day (10 jun) <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#details">View More</a>
                                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#update">Update</a>
                                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#delete">Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Design Stage</h6>
                                            <span class="small">18 May 2026 (2 days overdue) by <span class="text-primary">Rexy</span></span>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <button type="button" class="btn btn-sm btn-light btn-square" disabled><i class="bi bi-grid"></i></button>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-success me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Develop halaman utama website promotion</h6>
                                            <span class="small">19 May 2026 (Status: Completed) by <span class="text-primary">Syafiq</span></span>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <button type="button" class="btn btn-sm btn-light btn-square"><i class="bi bi-grid"></i></button>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="card">
                                <div class="card-header pb-0 border-0">
                                    <div class="clearfix">
                                        <h4 class="card-title mb-0">Others</h4>
                                        <small class="d-block">General & Miscellaneous Support</small>
                                    </div>
                                </div>
                                <div class="card-body dz-scroll height380">
                                    <div class="d-flex justify-content-between mb-3">
                                        <div class="clearfix">
                                            <span class="text-gray fw-semibold">5 / 8 Completed <span class="text-success">(62%)</span></span>
                                        </div>
                                        <div class="clearfix">
                                            <a class="text-success fw-semibold" data-bs-toggle="modal" data-bs-target="#create">+ Add Task</a>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <a data-bs-toggle="modal" data-bs-target="#details"><h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6></a>
                                            <span class="small">Due in 1 day (10 jun) <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#details">View More</a>
                                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#update">Update</a>
                                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#delete">Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Design Stage</h6>
                                            <span class="small">18 May 2026 (2 days overdue) by <span class="text-primary">Rexy</span></span>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <button type="button" class="btn btn-sm btn-light btn-square" disabled><i class="bi bi-grid"></i></button>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-success me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Develop halaman utama website promotion</h6>
                                            <span class="small">19 May 2026 (Status: Completed) by <span class="text-primary">Syafiq</span></span>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <button type="button" class="btn btn-sm btn-light btn-square"><i class="bi bi-grid"></i></button>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="timeline-vr-badge bg-light me-2"></div>
                                        <div class="clearfix ms-2">
                                            <h6 class="fs-13 mb-0 fw-semibold">Sistem registrasi peserta</h6>
                                            <span class="small">20 May 2026 (Due in 1 day) by <span class="text-primary">Syafiq</span></span><br>
                                        </div>
                                        <div class="clearfix ms-auto">
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-grid"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" >Edit</a>
                                                    <a class="dropdown-item" >Delete</a>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
				</div>
                    
				<!-- End - Attendance -->
@endsection
