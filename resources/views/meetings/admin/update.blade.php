@extends('layouts.main')

@section('title', 'Update HR Meeting')

@section('navbarTitle', 'Setup Meeting')

@section('css')
@include('meetings.admin.partials.participant-picker-css')
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
</style>
@endsection

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
							<li class="breadcrumb-item active" aria-current="page">Update Meeting</li>
						</ol>
					</nav>
				</div>
				<!-- End - Page Title & Breadcrumb -->
				
				<div class="tab-content" id="tabContentMyProfileBottom">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header border-0 pb-0">
                                    <div>
                                        <h4 class="card-title">Schedule a Zoom Meeting</h4>
                                        <p class="fs-13 mb-0">
                                            Fill in the meeting details below to schedule and share the Zoom session.
                                        </p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('hr-meetings.update.save', $meeting) }}" class="card-body">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label for="meeting_title" class="form-label">Meeting Title</label>
                                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="meeting_title" name="title" value="{{ old('title', $meeting->title) }}" placeholder="Purpose" required>
                                                @error('title')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label for="meeting_type" class="form-label">Meeting Type</label>
                                                <select class="selectpicker form-select @error('type') is-invalid @enderror" id="meeting_type" name="type" required>
                                                    @foreach ($meetingTypeLabels as $value => $label)
                                                        <option value="{{ $value }}" @selected(old('type', $meeting->type) === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                @error('type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <div class="mb-3">
                                                @php
                                                    $meetingDateValue = old('meeting_date', optional($meeting->meeting_date)->format('Y-m-d'));
                                                    $meetingDateDisplay = '';

                                                    try {
                                                        $meetingDateDisplay = $meetingDateValue
                                                            ? \Carbon\Carbon::parse($meetingDateValue)->format('d/m/Y')
                                                            : '';
                                                    } catch (\Throwable $exception) {
                                                        $meetingDateDisplay = (string) $meetingDateValue;
                                                    }
                                                @endphp
                                                <label for="meeting_date" class="form-label">Date</label>
                                                <input type="hidden" id="meeting_date" name="meeting_date" value="{{ $meetingDateValue }}">
                                                <input type="text" class="form-control js-meeting-date-picker @error('meeting_date') is-invalid @enderror" id="meeting_date_display" value="{{ $meetingDateDisplay }}" data-hidden-target="#meeting_date" placeholder="dd/mm/yyyy" readonly required>
                                                @error('meeting_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <div class="mb-3">
                                                <label for="meeting_time" class="form-label">Time</label>
                                                <input type="time" class="form-control @error('meeting_time') is-invalid @enderror" id="meeting_time" name="meeting_time" value="{{ old('meeting_time', substr((string) $meeting->meeting_time, 0, 5)) }}" required>
                                                @error('meeting_time')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label for="meeting_link" class="form-label">Meeting Link</label>
                                                <input type="text" class="form-control @error('meeting_link') is-invalid @enderror" id="meeting_link" name="meeting_link" value="{{ old('meeting_link', $meeting->meeting_link) }}" placeholder="Link">
                                                @error('meeting_link')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                @php
                                                    $selectedParticipantGroupValue = old('participant_group', $selectedParticipantGroup ?? 'all_staff');
                                                    $selectedParticipants = old('participant_ids', $selectedParticipantIds ?? []);
                                                @endphp
                                                @include('meetings.admin.partials.participant-picker', [
                                                    'employeeOptions' => $employeeOptions,
                                                    'selectedParticipantGroupValue' => $selectedParticipantGroupValue,
                                                    'selectedParticipants' => $selectedParticipants,
                                                ])
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label for="exampleFormControlInput1" class="form-label">Meeting Status</label>
                                                <div class="form-group mt-1 mb-0">
                                                    <div class="form-check d-inline-block me-3">
                                                        <input class="form-check-input" type="radio" name="status" id="meeting_status_scheduled" value="scheduled" @checked(old('status', $meeting->status) === 'scheduled')>
                                                        <label class="form-check-label" for="meeting_status_scheduled">Scheduled</label>
                                                    </div>
                                                    <div class="form-check d-inline-block me-3">
                                                        <input class="form-check-input" type="radio" name="status" id="meeting_status_completed" value="completed" @checked(old('status', $meeting->status) === 'completed')>
                                                        <label class="form-check-label" for="meeting_status_completed">Completed</label>
                                                    </div>
                                                    <div class="form-check d-inline-block me-3">
                                                        <input class="form-check-input" type="radio" name="status" id="meeting_status_canceled" value="canceled" @checked(old('status', $meeting->status) === 'canceled')>
                                                        <label class="form-check-label" for="meeting_status_canceled">Canceled</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label for="attachment_link" class="form-label">Attachment Link</label>
                                                <input type="text" class="form-control @error('attachment_link') is-invalid @enderror" id="attachment_link" name="attachment_link" value="{{ old('attachment_link', $meeting->attachment_link) }}" placeholder="Presentation link">
                                                @error('attachment_link')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label for="meeting_notes" class="form-label">Notes</label>
                                                <input type="text" class="form-control @error('notes') is-invalid @enderror" id="meeting_notes" name="notes" value="{{ old('notes', $meeting->notes) }}" placeholder="Additional notes">
                                                @error('notes')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-3">
                                        <a class="btn light btn-danger me-2 mb-2 btn-lg" href="{{ route('hr-meetings.index') }}">Back</a>
                                        <button type="submit" class="btn light btn-warning mb-2 btn-lg">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-4">
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
                        <div class="col-12 col-md-4">
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
                                                    <i class="fa-solid fa-square text-secondary me-1"></i> Event Management (KMA)
                                                </div>
                                                <span>25</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-3">
                                                <div class="text-black">
                                                    <i class="fa-solid fa-square text-success me-1"></i> Property and Construction (TRAH)
                                                </div>
                                                <span>17</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-3">
                                                <div class="text-black">
                                                    <i class="fa-solid fa-square text-danger me-1"></i> IT and Social Media (TMS)
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
                    </div>
				</div>
                    
				<!-- End - Attendance -->
@endsection

@section('script')
    @include('meetings.admin.partials.participant-picker-script')
    <script>
        $(function () {
            $('.js-meeting-date-picker').each(function () {
                var input = $(this);
                var hiddenInput = $(input.data('hidden-target'));
                var initialDate = hiddenInput.val() ? moment(hiddenInput.val(), 'YYYY-MM-DD') : moment();

                input.daterangepicker({
                    singleDatePicker: true,
                    autoUpdateInput: false,
                    showDropdowns: true,
                    startDate: initialDate.isValid() ? initialDate : moment(),
                    locale: {
                        format: 'DD/MM/YYYY',
                        cancelLabel: 'Clear',
                    },
                });

                input.on('apply.daterangepicker', function (event, picker) {
                    input.val(picker.startDate.format('DD/MM/YYYY'));
                    hiddenInput.val(picker.startDate.format('YYYY-MM-DD'));
                    input.trigger('blur');
                });

                input.on('cancel.daterangepicker', function () {
                    input.val('');
                    hiddenInput.val('');
                });
            });
        });
    </script>
@endsection
