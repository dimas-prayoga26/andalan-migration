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

    .meeting-staff-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        font-size: 11px;
        line-height: 1;
    }

    .meeting-staff-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .meeting-staff-initials {
        width: 100%;
        height: 100%;
        align-items: center;
        justify-content: center;
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

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif
				
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
                                            <h4 class="mb-0 fw-semibold"><a href="{{ $meeting->meeting_link ?: '#' }}" class="stretched-link" @if($meeting->meeting_link) target="_blank" rel="noopener noreferrer" @endif>{{ $meeting->title }}</a></h4>
                                            <span class="small">{{ $meetingTypeLabels[$meeting->type] ?? $meeting->type }}</span>	
                                        </div>	
                                    </div>
                                </div>
                                <div class="card-body px-3">
                                    <div class="row ps-3 mb-3">
                                        <div class="col-md-6 col-12">
                                            <span class="d-block">Date</span>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <span class="d-block fw-semibold">{{ optional($meeting->meeting_date)->format('l, d F Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="row ps-3 mb-3">
                                        <div class="col-md-6 col-12">
                                            <span class="d-block">Time</span>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <span class="d-block fw-semibold">{{ substr((string) $meeting->meeting_time, 0, 5) }} WIB</span>
                                        </div>
                                    </div>
                                    <div class="row ps-3 mb-3">
                                        <div class="col-md-6 col-12">
                                            <span class="d-block">Meeting Status</span>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <span class="d-block fw-semibold">{{ ucfirst($meeting->status) }}</span>
                                        </div>
                                    </div>
                                    <div class="row ps-3 mb-3">
                                        <div class="col-md-6 col-12">
                                            <span class="d-block">Attachment Link</span>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            @if ($meeting->attachment_link)
                                                <a href="{{ $meeting->attachment_link }}" target="_blank" rel="noopener noreferrer"><span class="d-block text-primary fw-semibold">{{ parse_url($meeting->attachment_link, PHP_URL_HOST) ?: $meeting->attachment_link }}</span></a>
                                            @else
                                                <span class="d-block fw-semibold">-</span>
                                            @endif
                                            
                                        </div>
                                    </div>
                                    <div class="row ps-3 mb-3">
                                        <div class="col-md-6 col-12">
                                            <span class="d-block">Total Tasks</span>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <span class="d-block fw-semibold">{{ (int) $meeting->tasks_count }} Tasks</span>
                                        </div>
                                    </div>
                                    <div class="row ps-3 mb-3">
                                        <div class="col-md-6 col-12">
                                            <span class="d-block">Joined</span>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <span class="d-block fw-semibold">{{ (int) $meeting->joined_count }} Staff</span>
                                        </div>
                                    </div>
                                    <div class="clearfix mt-3 ms-3">
                                        <h6 class="mb-1 fw-semibold">Staff</h6>
                                        <div class="avatar-list avatar-list-stacked">
                                            @php
                                                $meetingStaffAvatars = collect($meetingStaffAvatars ?? []);
                                            @endphp
                                            @forelse ($meetingStaffAvatars->take(6) as $staffAvatar)
                                                <span class="avatar avatar-xs rounded-circle border-2 border-white meeting-staff-img meeting-staff-avatar bg-primary-subtle text-primary fw-semibold" title="{{ $staffAvatar['name'] }}">
                                                    <img src="{{ $staffAvatar['avatar_url'] }}" alt="{{ $staffAvatar['name'] }}" loading="lazy" decoding="async" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                                                    <span class="meeting-staff-initials" style="display: none;">{{ $staffAvatar['initials'] }}</span>
                                                </span>
                                            @empty
                                                <span class="text-muted small">No staff invited.</span>
                                            @endforelse
                                            @if ($meetingStaffAvatars->count() > 6)
                                                <span class="avatar avatar-xs rounded-circle border-2 border-white meeting-staff-img meeting-staff-avatar bg-light text-primary fw-semibold" title="{{ $meetingStaffAvatars->count() - 6 }} more staff">
                                                    +{{ $meetingStaffAvatars->count() - 6 }}
                                                </span>
                                            @endif
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
                                        <small class="d-block">{{ (int) $meetingTaskSummary['overdue'] }} Overdue Tasks</small>
                                    </div>
                                </div>
                                <div class="card-body pb-0">
                                    <div class="row align-items-center">
                                        <div class="col-sm-6 mb-3">
                                            <div id="chartTasksSummary" class="d-flex justify-content-center">
                                                <div class="meeting-summary-donut" style="background: conic-gradient({{ $meetingTaskSummary['gradient'] }});" aria-label="Tasks summary chart">
                                                    <div class="meeting-summary-donut-content">
                                                        <strong>{{ (int) $meetingTaskSummary['total'] }}</strong>
                                                        <span>Total</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 mb-3">
                                            @foreach ($meetingTaskSummary['segments'] as $segment)
                                                <div class="d-flex justify-content-between mb-3">
                                                    <div class="text-black">
                                                        <i class="fa-solid fa-square me-1" style="color: {{ $segment['color'] }}"></i> {{ $segment['label'] }}
                                                    </div>
                                                    <span>{{ (int) $segment['count'] }}</span>
                                                </div>
                                            @endforeach
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
                                            <h4 class="mb-0 fw-semibold">Meeting Attendance</h4>
                                            <span class="small">Staff joined in this meeting</span>	
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
                                                    @forelse ($meetingAttendanceRows as $attendanceRow)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}.</td>
                                                            <td>{{ $attendanceRow['name'] }}</td>
                                                            <td>{{ $attendanceRow['time'] }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-muted">No attendance data available.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @include('meetings.admin.partials.task-cards', ['showTaskSummary' => false])
                    
				<!-- End - Attendance -->
@endsection

@section('script')
    @include('meetings.admin.partials.task-create-modal-script')
@endsection
