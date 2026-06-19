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
        .leave-history-timeline .timeline-status {
            left: -8px;
            min-width: 56px;
        }

        .leave-history-waiting-badge {
            padding: 0.35rem 0.6rem;
            line-height: 1;
            transform: translateX(-10px);
        }

        .leave-history-detail-trigger {
            cursor: pointer;
        }

        .leave-history-detail-trigger:hover .card-title,
        .leave-history-detail-trigger:focus .card-title {
            color: var(--bs-primary) !important;
        }

        .leave-history-detail-trigger:focus-visible {
            outline: 2px solid var(--bs-primary);
            outline-offset: 2px;
        }

        .leave-history-medical-note-preview {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 10px;
            background-color: #f2f2f2;
        }

        @media (max-width: 767.98px) {
            .leave-history-mobile-slider,
            .leave-balance-mobile-slider {
                display: flex;
                flex-wrap: nowrap;
                overflow-x: auto;
                gap: 12px;
                scroll-snap-type: x mandatory;
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            .leave-history-mobile-slider::-webkit-scrollbar,
            .leave-balance-mobile-slider::-webkit-scrollbar {
                display: none;
                width: 0;
                height: 0;
            }

            .leave-history-mobile-slide,
            .leave-balance-mobile-slide {
                flex: 0 0 100%;
                width: 100%;
                max-width: 100%;
                scroll-snap-align: start;
            }

            .leave-summary-card-header {
                gap: 1rem;
                padding: 1.25rem 1.25rem 0;
            }

            .leave-summary-card-header .card-action {
                width: 100%;
                margin-top: 0.85rem;
            }

            .leave-summary-tabs {
                display: flex;
                width: 100%;
                gap: 0;
            }

            .leave-summary-tabs .nav-item {
                flex: 1 1 50%;
                text-align: center;
            }

            .leave-summary-tabs .nav-link {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                padding: 0.75rem 0.5rem 0.9rem;
                white-space: nowrap;
            }

            .leave-summary-tabs .nav-link svg {
                width: 22px;
                height: 22px;
                flex-shrink: 0;
            }
        }
    </style>
@endsection

@section('navbarTitle', 'Attendances')

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Attendances',
    'current' => 'Leaves',
    'homeRoute' => 'dashboard',
])

@include('staff_attendance.layouts.profile-index')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Leaves</h5>
    </div>
</div>

@php
    $leaveSummaryYear = (int) ($leaveTracker['year'] ?? now('Asia/Jakarta')->year);
@endphp

@include('staff_attendance.leave-requests.partials.history-list-cards', [
    'leaveSummaryYear' => $leaveSummaryYear,
    'leaveEligibility' => $leaveEligibility ?? [],
    'leaveTracker' => $leaveTracker ?? [],
])

<div class="row">
    <div class="col-md-6 col-sm-12">
        <div class="card">
            <div class="card-header d-sm-flex d-block pb-0 border-0 leave-summary-card-header">
                <div>
                    <h4 class="card-title">Leave Summary</h4>
                    <p class="fs-13 mb-0">Monitor your time-off and your eligibility status</p>
                </div>
                <div class="card-action stat-card-tabs">
                    <ul class="nav nav-underline leave-summary-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link text-black fs-16 fw-medium active" data-bs-toggle="tab" href="#Eligibility" role="tab">
                                <svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0)">
                                    <path d="M0.988957 17.074C0.328275 17.2006 -0.104585 17.8385 0.0219823 18.4992C0.133362 19.0814 0.644694 19.4864 1.21678 19.4864C1.29272 19.4864 1.37119 19.4788 1.44713 19.4636L6.4592 18.5017C6.74524 18.446 7.00091 18.2916 7.18316 18.0638L9.33481 15.3502L8.61593 14.9832C8.08435 14.7148 7.71475 14.2288 7.58818 13.639L5.55804 16.1982L0.988957 17.074Z" fill="#A639FA"/>
                                    <path d="M18.84 6.493C20.3135 6.493 21.508 5.29848 21.508 3.82496C21.508 2.35144 20.3135 1.15692 18.84 1.15692C17.3665 1.15692 16.1719 2.35144 16.1719 3.82496C16.1719 5.29848 17.3665 6.493 18.84 6.493Z" fill="#A639FA"/>
                                    <path d="M13.0179 3.15671C12.7369 2.86813 12.4762 2.75422 12.1902 2.75422C12.0864 2.75422 11.9826 2.76941 11.8712 2.79472L7.29203 3.88067C6.6592 4.03002 6.26937 4.66539 6.41872 5.29569C6.54782 5.8374 7.02877 6.20192 7.56289 6.20192C7.65404 6.20192 7.74514 6.19179 7.8363 6.16901L11.7371 5.24507C11.9902 5.52605 13.2584 6.90057 13.4888 7.14358C11.8763 8.86996 10.2639 10.5938 8.65137 12.3202C8.62605 12.3481 8.60329 12.3759 8.58049 12.4037C8.10966 13.0036 8.25397 13.9453 8.96275 14.3022L13.9064 16.826L11.3397 20.985C10.9878 21.5571 11.165 22.3063 11.7371 22.6607C11.9371 22.7848 12.1573 22.843 12.375 22.843C12.7825 22.843 13.1824 22.638 13.4128 22.2658L16.6732 16.9829C16.8529 16.6918 16.901 16.34 16.8074 16.0134C16.7137 15.6843 16.4884 15.411 16.1821 15.2565L12.8331 13.5529L16.3543 9.7863L19.0122 12.0392C19.2324 12.2265 19.5032 12.3176 19.7716 12.3176C20.0601 12.3176 20.3487 12.2113 20.574 12.0038L23.6243 9.16106C24.1002 8.71808 24.128 7.97386 23.685 7.49797C23.4521 7.24989 23.1383 7.12333 22.8244 7.12333C22.5383 7.12333 22.2497 7.22711 22.0245 7.43721L19.7412 9.56101C19.7386 9.56354 14.0178 4.1819 13.0179 3.15671Z" fill="#A639FA"/>
                                    </g>
                                    <defs>
                                    <clipPath id="clip0">
                                    <rect width="24" height="24" fill="white"/>
                                    </clipPath>
                                    </defs>
                                </svg>
                                Eligibility
                                <span class="bg-secondary"></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-black fs-16 fw-medium" data-bs-toggle="tab" href="#Tracker" role="tab">
                                <svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10.8586 5.22599L5.87121 10.5543C5.50758 11.0846 5.64394 11.8068 6.17172 12.1679L11.1945 15.6098V18.9558C11.1945 19.5921 11.6995 20.125 12.3359 20.1376C12.9874 20.1477 13.5177 19.625 13.5177 18.976V15.0013C13.5177 14.6174 13.3283 14.2588 13.0126 14.0442L9.79041 11.8346L12.5025 8.95836L13.8914 12.1225C14.0758 12.5442 14.4949 12.817 14.9546 12.817H19.1844C19.8207 12.817 20.3536 12.3119 20.3662 11.6755C20.3763 11.024 19.8536 10.4937 19.2046 10.4937H15.7172C15.2576 9.44824 14.7677 8.41288 14.3409 7.35228C14.1237 6.81693 14.0025 6.5846 13.6036 6.21592C13.5227 6.14016 12.9596 5.62501 12.4571 5.16541C11.995 4.74619 11.2828 4.77397 10.8586 5.22599Z" fill="#FF3282"/>
                                    <path d="M15.6162 5.80681C17.0861 5.80681 18.2778 4.61517 18.2778 3.1452C18.2778 1.67523 17.0861 0.483582 15.6162 0.483582C14.1462 0.483582 12.9545 1.67523 12.9545 3.1452C12.9545 4.61517 14.1462 5.80681 15.6162 5.80681Z" fill="#FF3282"/>
                                    <path d="M4.89899 23.5164C7.60463 23.5164 9.79798 21.3231 9.79798 18.6174C9.79798 15.9118 7.60463 13.7184 4.89899 13.7184C2.19335 13.7184 0 15.9118 0 18.6174C0 21.3231 2.19335 23.5164 4.89899 23.5164Z" fill="#FF3282"/>
                                    <path d="M19.101 23.5164C21.8066 23.5164 24 21.3231 24 18.6174C24 15.9118 21.8066 13.7184 19.101 13.7184C16.3954 13.7184 14.202 15.9118 14.202 18.6174C14.202 21.3231 16.3954 23.5164 19.101 23.5164Z" fill="#FF3282"/>
                                </svg>
                                Tracker
                                <span class="bg-danger"></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body pb-0">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="Eligibility" role="tabpanel">
                        <div>
                            <h4 class="card-title">Leave Balance & Eligibility</h4>
                            <p class="fs-13 mb-0">Please ensure you have met the 1-year service requirement and your request does not exceed the maximum monthly limit.</p>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Full Name</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveEligibility['full_name'] ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Supervisor</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveEligibility['supervisor_name'] ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Join Date</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">{{ $leaveEligibility['join_date_label'] ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Current Tenure</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">{{ $leaveEligibility['tenure_label'] ?? '-' }}</span> <br>
                                @if (($leaveEligibility['is_eligible'] ?? false) === true)
                                    <span class="text-success">Eligible</span>
                                @else
                                    <span class="text-danger">Not Eligible</span>
                                @endif
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Available Balance</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveEligibility['available_balance_label'] ?? '0 Days' }}</span> <br>
                                @if (($leaveEligibility['is_eligible'] ?? false) === true)
                                    <span class="text-success">Eligible</span>
                                @else
                                    <span class="text-danger">Not Eligible</span>
                                @endif
                                <br>
                                <span class="text-gray">{{ $leaveEligibility['available_balance_note'] ?? 'No balance available yet.' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Next Accrual</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveEligibility['next_accrual_label'] ?? '+0 Day' }}</span> <br>
                                <span class="text-gray">{{ $leaveEligibility['next_accrual_note'] ?? 'No automatic accrual configured.' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Joint Holiday</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveEligibility['joint_holiday_label'] ?? '0 / 0 Days' }}</span> <br>
                                <ul class="list-unstyled mb-0 text-gray">
                                    @forelse (($leaveEligibility['joint_holiday_items'] ?? []) as $jointHolidayItem)
                                        <li>{{ $jointHolidayItem }}</li>
                                    @empty
                                        <li>No joint holiday scheduled.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade mb-3" id="Tracker" role="tabpanel">
                        <div>
                            <h4 class="card-title">My Time Off Tracker</h4>
                            <p class="fs-13 mb-0">Review your {{ $leaveTracker['year'] ?? now('Asia/Jakarta')->year }} usage and check the status of your recent requests.</p>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Annual Leave Taken</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveTracker['annual_leave_taken_label'] ?? '0 Days' }}</span> <br>
                                <span class="text-gray">{{ $leaveTracker['annual_leave_taken_breakdown'] ?? 'No leave taken yet.' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Annual Leave Taken This Month ({{ $leaveTracker['month_label'] ?? now('Asia/Jakarta')->format('F') }})</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveTracker['annual_leave_taken_month_label'] ?? '0 Days' }}</span> <br>
                                <span class="text-gray">{{ $leaveTracker['annual_leave_taken_month_breakdown'] ?? 'No leave taken this month.' }}</span> <br>
                                <span class="text-gray">{{ $leaveTracker['annual_leave_monthly_limit_label'] ?? 'Maximum limit is 2 days per month' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Sick Leave Taken</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveTracker['sick_leave_taken_label'] ?? '0 Days' }}</span> <br>
                                <span class="text-gray">{{ $leaveTracker['sick_leave_taken_breakdown'] ?? 'No leave taken yet.' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Sick Leave Taken This Month ({{ $leaveTracker['month_label'] ?? now('Asia/Jakarta')->format('F') }})</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveTracker['sick_leave_taken_month_label'] ?? '0 Days' }}</span> <br>
                                <span class="text-gray">{{ $leaveTracker['sick_leave_taken_month_breakdown'] ?? 'No leave taken this month.' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Special Leave Taken</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveTracker['special_leave_taken_label'] ?? '0 Days' }}</span> <br>
                                <span class="text-gray">{{ $leaveTracker['special_leave_taken_breakdown'] ?? 'No leave taken yet.' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Special Leave Taken This Month ({{ $leaveTracker['month_label'] ?? now('Asia/Jakarta')->format('F') }})</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveTracker['special_leave_taken_month_label'] ?? '0 Days' }}</span> <br>
                                <span class="text-gray">{{ $leaveTracker['special_leave_taken_month_breakdown'] ?? 'No leave taken this month.' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Unpaid Leave Taken</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveTracker['unpaid_leave_taken_label'] ?? '0 Days' }}</span> <br>
                                <span class="text-gray">{{ $leaveTracker['unpaid_leave_taken_breakdown'] ?? 'No leave taken yet.' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Unpaid Leave Taken This Month ({{ $leaveTracker['month_label'] ?? now('Asia/Jakarta')->format('F') }})</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveTracker['unpaid_leave_taken_month_label'] ?? '0 Days' }}</span> <br>
                                <span class="text-gray">{{ $leaveTracker['unpaid_leave_taken_month_breakdown'] ?? 'No leave taken this month.' }}</span>
                            </div>
                        </div>
                        <hr>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Active / Pending Requests</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">{{ $leaveTracker['pending_requests_label'] ?? '0 Requests' }}</span> <br>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Total Approved Leaves</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">{{ $leaveTracker['approved_requests_label'] ?? '0 Requests' }}</span> <br>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Total Rejected Leaves</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">{{ $leaveTracker['rejected_requests_label'] ?? '0 Requests' }}</span> <br>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-6 col-xl-6 col-sm-6">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <div>
                    <h4 class="card-title">Confirm Leave Request</h4>
                    <p class="fs-13 mb-0">Ensure your leave dates are accurate and required attachments are uploaded. This will be sent to HR and your manager for approval.</p>
                </div>
            </div>
            <div class="card-body">
                <form id="leaveRequestForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="start_date" id="leaveStartDateInput">
                    <input type="hidden" name="end_date" id="leaveEndDateInput">
                    <input type="hidden" name="attachment_path" id="leaveAttachmentPathInput">
                    <div id="leaveRequestAlert" class="alert d-none mb-3" role="alert"></div>

                    <div class="mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="leaveDateRangeInput" placeholder="Add Date" readonly>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-12 mb-3" id="leaveTypeWrapper">
                            <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                            <select id="leaveTypeSelect" name="permission_type_id" class="selectpicker form-select" required data-live-search="true" title="Choose Leave Type">
                                @foreach (($leaveTypes ?? collect()) as $leaveType)
                                    @php
                                        $normalizedLeaveTypeName = is_string($leaveType->name) ? strtolower(trim($leaveType->name)) : '';
                                        $leaveTypeOptionLabel = $leaveType->name;
                                        if ($normalizedLeaveTypeName === 'cuti tahunan' || $normalizedLeaveTypeName === 'annual leave') {
                                            $leaveTypeOptionLabel .= ' (Sisa cuti '.($leaveEligibility['available_balance_label'] ?? '0 Days').')';
                                        }
                                    @endphp
                                    <option value="{{ $leaveType->id }}">{{ $leaveTypeOptionLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6 mb-3 d-none" id="specialLeaveTypeWrapper">
                            <label class="form-label">Special Leave Type <span class="text-danger">*</span></label>
                            <select id="specialLeaveSubTypeSelect" name="special_leave_sub_type_id" class="selectpicker form-select" data-live-search="true" title="Choose Special Leave Type">
                                @foreach (($specialLeaveSubTypes ?? collect()) as $specialLeaveSubType)
                                    <option value="{{ $specialLeaveSubType->id }}">{{ $specialLeaveSubType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="mb-3">
                                <label class="form-label">Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="reason" rows="2" placeholder="Misal: Istri melahirkan" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="mb-3">
                                <label class="form-label">Handover Notes (optional)</label>
                                <textarea class="form-control" name="handover_notes" rows="2" placeholder="Misal: Deployment akan di-backup oleh Budi"></textarea>
                            </div>
                        </div>
                    </div>

                    <div id="sickAttachmentWrapper" class="d-none">
                        <div class="d-flex flex-column align-items-start gap-2">
                            <label class="form-label mb-0">Attachment (max 1 MB) <span class="text-danger">*</span></label>
                            <div class="avatar avatar-xl avatar-preview">
                                <img class="imagePreview w-100 h-100" id="leaveAttachmentPreview" src="{{ asset('assets/images/avatar/placeholder.avif') }}" alt="Attachment Preview">
                                <input type="file" class="imageUpload d-none" id="leaveAttachmentFileInput" name="attachment_file" accept=".png,.jpg,.jpeg,.pdf">
                                <a class="avatar avatar-xs position-absolute bottom-0 end-0 bg-white shadow-sm upload-trigger" id="leaveAttachmentUploadTrigger">
                                    <i class="fa-solid fa-upload"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn light btn-success m-3 mb-2 btn-lg w-100" id="leaveRequestSubmitButton">Request Time Off</button>
                </form>
            </div>
            <div class="mb-3"></div>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Leave List</h5>
            <div class="d-flex align-items-center">
                <a class="btn rounded btn-primary mt-xxl-0 mt-xl-3 mt-lg-0 mt-3" data-bs-toggle="modal" data-bs-target="#filter">
                    <svg class="me-2" width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.31615 6H14.4744C14.4744 6.53043 14.6882 7.03914 15.0686 7.41421C15.4491 7.78929 15.9651 8 16.5032 8H18.532C19.07 8 19.5861 7.78929 19.9665 7.41421C20.347 7.03914 20.5607 6.53043 20.5607 6H21.5751C21.8442 6 22.1022 5.89464 22.2924 5.70711C22.4827 5.51957 22.5895 5.26522 22.5895 5C22.5895 4.73478 22.4827 4.48043 22.2924 4.29289C22.1022 4.10536 21.8442 4 21.5751 4H20.5607C20.5607 3.46957 20.347 2.96086 19.9665 2.58579C19.5861 2.21071 19.07 2 18.532 2H16.5032C15.9651 2 15.4491 2.21071 15.0686 2.58579C14.6882 2.96086 14.4744 3.46957 14.4744 4H3.31615C3.04711 4 2.7891 4.10536 2.59887 4.29289C2.40863 4.48043 2.30176 4.73478 2.30176 5C2.30176 5.26522 2.40863 5.51957 2.59887 5.70711C2.7891 5.89464 3.04711 6 3.31615 6ZM16.5032 4H18.532V5V6H16.5032V4ZM21.5751 11H12.4456C12.4456 10.4696 12.2319 9.96086 11.8514 9.58579C11.471 9.21071 10.9549 9 10.4169 9H8.38809C7.85002 9 7.334 9.21071 6.95353 9.58579C6.57306 9.96086 6.35931 10.4696 6.35931 11H3.31615C3.04711 11 2.7891 11.1054 2.59887 11.2929C2.40863 11.4804 2.30176 11.7348 2.30176 12C2.30176 12.2652 2.40863 12.5196 2.59887 12.7071C2.7891 12.8946 3.04711 13 3.31615 13H6.35931C6.35931 13.5304 6.57306 14.0391 6.95353 14.4142C7.334 14.7893 7.85002 15 8.38809 15H10.4169C10.9549 15 11.471 14.7893 11.8514 14.4142C12.2319 14.0391 12.4456 13.5304 12.4456 13H21.5751C21.8442 13 22.1022 12.8946 22.2924 12.7071C22.4827 12.5196 22.5895 12.2652 22.5895 12C22.5895 11.7348 22.4827 11.4804 22.2924 11.2929C22.1022 11.1054 21.8442 11 21.5751 11ZM8.38809 13V11H10.4169V12V13H8.38809ZM21.5751 18H18.532C18.532 17.4696 18.3182 16.9609 17.9378 16.5858C17.5573 16.2107 17.0413 16 16.5032 16H14.4744C13.9364 16 13.4203 16.2107 13.0399 16.5858C12.6594 16.9609 12.4456 17.4696 12.4456 18H3.31615C3.04711 18 2.7891 18.1054 2.59887 18.2929C2.40863 18.4804 2.30176 18.7348 2.30176 19C2.30176 19.2652 2.40863 19.5196 2.59887 19.7071C2.7891 19.8946 3.04711 20 3.31615 20H12.4456C12.4456 20.5304 12.6594 21.0391 13.0399 21.4142C13.4203 21.7893 13.9364 22 14.4744 22H16.5032C17.0413 22 17.5573 21.7893 17.9378 21.4142C18.3182 21.0391 18.532 20.5304 18.532 20H21.5751C21.8442 20 22.1022 19.8946 22.2924 19.7071C22.4827 19.5196 22.5895 19.2652 22.5895 19C22.5895 18.7348 22.4827 18.4804 22.2924 18.2929C22.1022 18.1054 21.8442 18 21.5751 18ZM14.4744 20V18H16.5032V19V20H14.4744Z" fill="#fff"></path>
                    </svg>
                    Filter
                </a>
            </div>
        </div>
    </div>
    <div class="col-12" id="leaveHistoryCardsContainer">
        <div class="row leave-history-mobile-slider" id="leaveHistoryCardsSlider">
            @forelse (($leaveHistoryCards ?? collect()) as $leaveHistoryCard)
                <div class="col-xxl-3 col-xl-4 col-sm-6 leave-history-mobile-slide">
                    <div class="card leave-history-detail-trigger" role="button" tabindex="0" data-leave-request-id="{{ $leaveHistoryCard['id'] ?? '' }}" data-leave-type-id="{{ $leaveHistoryCard['leave_type_id'] ?? '' }}" data-start-date="{{ $leaveHistoryCard['start_date_value'] ?? '' }}" data-end-date="{{ $leaveHistoryCard['end_date_value'] ?? '' }}" data-handover-notes="{{ $leaveHistoryCard['handover_notes'] ?? '' }}" data-detail-title="{{ $leaveHistoryCard['title'] ?? 'Leave Request' }}" data-detail-modal-title="{{ $leaveHistoryCard['modal_title'] ?? ($leaveHistoryCard['title'] ?? 'Leave Request') }}" data-detail-leave-type="{{ $leaveHistoryCard['detail_leave_type'] ?? ($leaveHistoryCard['title'] ?? 'Leave Request') }}" data-detail-period="{{ $leaveHistoryCard['period_label'] ?? '-' }}" data-detail-reason="{{ $leaveHistoryCard['reason'] ?? '-' }}" data-detail-due="{{ $leaveHistoryCard['due_date_label'] ?? '-' }}" data-detail-status="{{ $leaveHistoryCard['status_label'] ?? 'Pending' }}" data-detail-status-class="{{ $leaveHistoryCard['status_badge_class'] ?? 'badge-primary light' }}" data-detail-status-text-class="{{ $leaveHistoryCard['status_text_class'] ?? 'text-primary' }}" data-detail-status-date="{{ $leaveHistoryCard['status_date_label'] ?? '' }}" data-detail-is-sick="{{ ! empty($leaveHistoryCard['is_sick_leave']) ? 'true' : 'false' }}" data-detail-attachment-url="{{ $leaveHistoryCard['attachment_url'] ?? '' }}" data-detail-timeline='@json($leaveHistoryCard['timeline'] ?? [])'>
                        <div class="card-body">
                            <div class="clearfix d-flex">
                                <div class="avatar avatar-sm rounded me-3 p-2">
                                    <img src="{{ asset('assets/'.($leaveHistoryCard['icon_file'] ?? 'annual_leave.svg')) }}" alt="Leave Type">
                                </div>
                                <div class="clearfix">
                                    <h6 class="card-title mb-0 fw-semibold">{{ $leaveHistoryCard['title'] ?? 'Leave Request' }}</h6>
                                    <span class="small">{{ $leaveHistoryCard['period_label'] ?? '-' }}</span>
                                </div>
                                <div class="clearfix ms-auto leave-history-card-actions">
                                    <div class="dropdown">
                                        <a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-grid"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            @if (! empty($leaveHistoryCard['can_view']))
                                                <a href="#" class="dropdown-item leave-history-action-view">View</a>
                                            @endif
                                            @if (! empty($leaveHistoryCard['can_update']))
                                                <a href="#" class="dropdown-item leave-history-action-update">Update</a>
                                            @endif
                                            @if (! empty($leaveHistoryCard['can_delete']))
                                                <a href="#" class="dropdown-item leave-history-action-delete">Delete</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="my-3">{{ $leaveHistoryCard['reason'] ?? '-' }}</p>
                            <div class="widget-timeline1 leave-history-timeline">
                                <ul class="timeline">
                                    @foreach (($leaveHistoryCard['timeline'] ?? []) as $timelineItem)
                                        <li>
                                            <span class="timeline-status">
                                                @if (($timelineItem['date_label'] ?? '') === 'Waiting')
                                                    <span class="badge badge-sm badge-secondary light leave-history-waiting-badge">Waiting</span>
                                                @else
                                                    {{ $timelineItem['date_label'] ?? '' }}
                                                @endif
                                            </span>
                                            <div class="timeline-badge {{ $timelineItem['badge_class'] ?? 'border-dark' }}"></div>
                                            <div class="timeline-panel">
                                                <span>{{ $timelineItem['title'] ?? '-' }}</span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between flex-wrap">
                            <p class="mb-0 fw-medium">Due <span class="text-purple">: {{ $leaveHistoryCard['due_date_label'] ?? '-' }}</span></p>
                            <span class="badge badge-sm {{ $leaveHistoryCard['status_badge_class'] ?? 'badge-primary light' }}">
                                {{ $leaveHistoryCard['status_label'] ?? 'Pending' }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <span class="text-gray">Belum ada history leave request.</span>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="modal fade" id="leaveHistoryDetailModal" tabindex="-1" aria-labelledby="leaveHistoryDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="leaveHistoryDetailModalLabel">Leave Request Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6 class="fw-semibold text-gray mb-1" id="leaveHistoryDetailIntroTitle">Out of Office mode: ON</h6>
                    <p class="mb-0 text-gray" id="leaveHistoryDetailIntroText">Whether you're traveling the globe or just relaxing on the couch, enjoy your well-deserved break. Disconnect, recharge, and have fun!</p>
                </div>
                <div class="row py-2">
                    <div class="col-4">
                        <span>Leave Type</span>
                    </div>
                    <div class="col-8">
                        <span class="text-gray fw-semibold" id="leaveHistoryDetailType">-</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-4">
                        <span>Reason</span>
                    </div>
                    <div class="col-8">
                        <span class="text-gray" id="leaveHistoryDetailReason">-</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-4">
                        <span>Leave Duration</span>
                    </div>
                    <div class="col-8">
                        <span class="text-gray fw-semibold" id="leaveHistoryDetailPeriod">-</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-4">
                        <span>Status</span>
                    </div>
                    <div class="col-8">
                        <span class="fw-semibold" id="leaveHistoryDetailStatusText">Pending</span>
                        <span class="text-gray" id="leaveHistoryDetailStatusDate"></span>
                    </div>
                </div>
                <div class="row py-2 d-none" id="leaveHistoryDetailMedicalNotesRow">
                    <div class="col-4">
                        <span>Medical Notes</span>
                    </div>
                    <div class="col-8">
                        <a href="{{ asset('assets/not_available_images.png') }}" target="_blank" rel="noopener" id="leaveHistoryDetailMedicalNotesLink">
                            <img src="{{ asset('assets/not_available_images.png') }}" alt="Medical Notes" class="leave-history-medical-note-preview" id="leaveHistoryDetailMedicalNotesImage">
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="filter" tabindex="-1" aria-labelledby="filterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Filter Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="leaveHistoryFilterForm">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="mb-3">
                                <label class="form-label">Filter by Status</label>
                                <select class="form-control selectpicker" id="leaveHistoryStatusFilter" name="status">
                                    <option value="all" selected="">Select All</option>
                                    <option value="approved">Approved</option>
                                    <option value="pending">Pending Review</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="canceled">Canceled</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Filter by Leave Type</label>
                                <select class="form-control selectpicker" id="leaveHistoryTypeFilter" name="leave_type">
                                    <option value="all" selected="">Select All</option>
                                    <option value="annual_leave">Annual Leave</option>
                                    <option value="sick_leave">Sick Leave</option>
                                    <option value="special_leave">Special Leave</option>
                                    <option value="unpaid_leave">Unpaid Leave</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Filter by Timeframe</label>
                                <select class="form-control selectpicker" id="leaveHistoryTimeframeFilter" name="timeframe">
                                    <option value="all">Select All</option>
                                    <option value="this_month">This Month</option>
                                    <option value="last_month">Last Month</option>
                                    <option value="year_to_date" selected="">Year-to-Date</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="leaveHistoryFilterForm" id="leaveHistoryFilterApplyButton">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Box Start -->
<div class="modal fade" id="annualLeave" tabindex="-1" aria-labelledby="annualLeaveLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="annualLeaveLabel">Annual Leave Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <h5 class="text-muted mb-0 fw-bold">Out of Office mode: ON</h5>
                            <p class="form-label text-muted mb-3">Enjoy your well-deserved break. Disconnect, recharge, and have fun.</p>
                            <p class="form-label text-muted mb-3 d-none">
                                Whether you’re traveling the globe or just relaxing on the couch, enjoy your well-deserved break. Disconnect, recharge, and have fun!
                            </p>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Leave Type</span>
                                </div>
                                <div class="col-8">
                                    <span class="text-gray fw-semibold">Annual Leave</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Yearly Taken</span>
                                </div>
                                <div class="col-8">
                                    <span class="text-gray fw-semibold">{{ $leaveTracker['annual_leave_taken_label'] ?? '0 Days' }}</span><br>
                                    <span class="text-gray">{{ $leaveTracker['annual_leave_taken_breakdown'] ?? 'No leave taken yet.' }}</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>This Month</span>
                                </div>
                                <div class="col-8">
                                    <span class="text-gray fw-semibold">{{ $leaveTracker['annual_leave_taken_month_label'] ?? '0 Days' }}</span><br>
                                    <span class="text-gray">{{ $leaveTracker['annual_leave_taken_month_breakdown'] ?? 'No leave taken this month.' }}</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Monthly Limit</span>
                                </div>
                                <div class="col-8">
                                    <span class="text-gray">{{ $leaveTracker['annual_leave_monthly_limit_label'] ?? 'Maximum limit is 2 days per month' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal-Box-End -->
<!-- Modal Box Start -->
<div class="modal fade" id="update" tabindex="-1" aria-labelledby="updateLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="updateLabel">Update Leave Request</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="leaveRequestUpdateForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="leaveUpdateRequestIdInput">
                    <input type="hidden" name="start_date" id="leaveUpdateStartDateInput">
                    <input type="hidden" name="end_date" id="leaveUpdateEndDateInput">
                    <div class="alert d-none" id="leaveUpdateAlert" role="alert"></div>
                    <div class="row">
                        <div class="col-12 col-md-12 mb-3" id="leaveUpdateTypeWrapper">
                            <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                            <select class="selectpicker form-select" id="leaveUpdateTypeSelect" name="permission_type_id" required>
                                <option value="">Choose Leave Type</option>
                                @foreach (($leaveTypes ?? collect()) as $leaveType)
                                    <option value="{{ $leaveType->id }}">
                                        {{ $leaveType->name }}
                                        @if (($leaveType->code ?? '') === 'ANNUAL')
                                            ({{ $leaveTracker['annual_leave_remaining_label'] ?? '0 Days' }} left)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6 mb-3 d-none" id="leaveUpdateSpecialLeaveTypeWrapper">
                            <label class="form-label">Special Leave Type <span class="text-danger">*</span></label>
                            <select class="selectpicker form-select" id="leaveUpdateSpecialLeaveSubTypeSelect" name="special_leave_sub_type_id">
                                <option value="">Choose Special Leave First</option>
                                @foreach (($specialLeaveSubTypes ?? collect()) as $specialLeaveSubType)
                                    <option value="{{ $specialLeaveSubType->id }}">{{ $specialLeaveSubType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="leaveUpdateDateRangeInput" placeholder="Add Date" readonly required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="leaveUpdateReasonInput" name="reason" rows="3" placeholder="Misal: Istri melahirkan" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Handover Notes</label>
                        <textarea class="form-control" id="leaveUpdateHandoverNotesInput" name="handover_notes" rows="3" placeholder="Misal: Deployment akan di-backup oleh Budi"></textarea>
                    </div>
                    <div class="d-none" id="leaveUpdateSickAttachmentWrapper">
                        <div class="d-flex flex-column align-items-start gap-2">
                            <label class="form-label mb-0">Attachment (max 1 MB)</label>
                            <div class="avatar avatar-xl avatar-preview">
                                <img class="imagePreview w-100 h-100" id="leaveUpdateAttachmentPreview" src="{{ asset('assets/images/avatar/placeholder.avif') }}" alt="Attachment Preview">
                                <input type="file" class="imageUpload d-none" id="leaveUpdateAttachmentFileInput" name="attachment_file" accept=".png,.jpg,.jpeg,.pdf">
                                <a class="avatar avatar-xs position-absolute bottom-0 end-0 bg-white shadow-sm upload-trigger" id="leaveUpdateAttachmentUploadTrigger">
                                    <i class="fa-solid fa-upload"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-warning light" form="leaveRequestUpdateForm" id="leaveUpdateSubmitButton">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Box Start -->
<div class="modal fade" id="delete" tabindex="-1" aria-labelledby="deleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteLabel">Delete Leave Request</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="leaveRequestDeleteForm">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" id="leaveDeleteRequestIdInput">
                    <div class="alert d-none" id="leaveDeleteAlert" role="alert"></div>
                    <div class="row">
                        <div class="col-xl-12">
                            <h5 class="text-muted fw-bold text-center">Change of Plans?</h5>
                            <p class="form-label text-muted mb-3" id="leaveDeleteDescription">
                                Do you want to cancel this pending time off? We will simply remove the request from the approval list so you can submit a new one later if needed.
                            </p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark light" data-bs-dismiss="modal">Nevermind</button>
                <button type="submit" class="btn btn-danger light" form="leaveRequestDeleteForm" id="leaveDeleteSubmitButton">Yes, Cancel It</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal-Box-End -->
@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

            function escapeHtml(value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function sanitizeBadgeClass(value) {
                return String(value || 'badge-primary light').replace(/[^a-zA-Z0-9_ -]/g, '');
            }

            function renderLeaveHistoryTimeline(timelineItems) {
                if (!Array.isArray(timelineItems) || timelineItems.length === 0) {
                    return '<li><span class="timeline-status">-</span><div class="timeline-badge border-secondary"></div><div class="timeline-panel"><span>No timeline recorded.</span></div></li>';
                }

                return timelineItems.map(function (timelineItem) {
                    var dateLabel = timelineItem.date_label || '';
                    var dateHtml = dateLabel === 'Waiting'
                        ? '<span class="badge badge-sm badge-secondary light leave-history-waiting-badge">Waiting</span>'
                        : escapeHtml(dateLabel);

                    return '<li>'
                        + '<span class="timeline-status">' + dateHtml + '</span>'
                        + '<div class="timeline-badge ' + sanitizeBadgeClass(timelineItem.badge_class || 'border-dark') + '"></div>'
                        + '<div class="timeline-panel"><span>' + escapeHtml(timelineItem.title || '-') + '</span></div>'
                        + '</li>';
                }).join('');
            }

            function leaveHistoryCardHtml(card) {
                card = card || {};

                var title = card.title || 'Leave Request';
                var leaveRequestId = card.id || '';
                var leaveTypeId = card.leave_type_id || '';
                var startDateValue = card.start_date_value || '';
                var endDateValue = card.end_date_value || '';
                var handoverNotes = card.handover_notes || '';
                var canView = card.can_view === true;
                var canUpdate = card.can_update === true;
                var canDelete = card.can_delete === true;
                var modalTitle = card.modal_title || title;
                var detailLeaveType = card.detail_leave_type || title;
                var iconFile = card.icon_file || 'annual_leave.svg';
                var periodLabel = card.period_label || '-';
                var reason = card.reason || '-';
                var dueDateLabel = card.due_date_label || '-';
                var statusLabel = card.status_label || 'Pending';
                var statusClass = sanitizeBadgeClass(card.status_badge_class || 'badge-primary light');
                var statusTextClass = sanitizeBadgeClass(card.status_text_class || 'text-primary');
                var statusDateLabel = card.status_date_label || '';
                var isSickLeave = card.is_sick_leave ? 'true' : 'false';
                var attachmentUrl = card.attachment_url || '';
                var timelineItems = Array.isArray(card.timeline) ? card.timeline : [];
                var timelineAttribute = escapeHtml(JSON.stringify(timelineItems));

                return '<div class="col-xxl-3 col-xl-4 col-sm-6 leave-history-mobile-slide">'
                    + '<div class="card leave-history-detail-trigger" role="button" tabindex="0"'
                    + ' data-leave-request-id="' + escapeHtml(leaveRequestId) + '"'
                    + ' data-leave-type-id="' + escapeHtml(leaveTypeId) + '"'
                    + ' data-start-date="' + escapeHtml(startDateValue) + '"'
                    + ' data-end-date="' + escapeHtml(endDateValue) + '"'
                    + ' data-handover-notes="' + escapeHtml(handoverNotes) + '"'
                    + ' data-detail-title="' + escapeHtml(title) + '"'
                    + ' data-detail-modal-title="' + escapeHtml(modalTitle) + '"'
                    + ' data-detail-leave-type="' + escapeHtml(detailLeaveType) + '"'
                    + ' data-detail-period="' + escapeHtml(periodLabel) + '"'
                    + ' data-detail-reason="' + escapeHtml(reason) + '"'
                    + ' data-detail-due="' + escapeHtml(dueDateLabel) + '"'
                    + ' data-detail-status="' + escapeHtml(statusLabel) + '"'
                    + ' data-detail-status-class="' + statusClass + '"'
                    + ' data-detail-status-text-class="' + statusTextClass + '"'
                    + ' data-detail-status-date="' + escapeHtml(statusDateLabel) + '"'
                    + ' data-detail-is-sick="' + isSickLeave + '"'
                    + ' data-detail-attachment-url="' + escapeHtml(attachmentUrl) + '"'
                    + ' data-detail-timeline="' + timelineAttribute + '">'
                    + '<div class="card-body">'
                    + '<div class="clearfix d-flex">'
                    + '<div class="avatar avatar-sm rounded me-3 p-2">'
                    + '<img src="' + escapeHtml(leaveTypeIconBaseUrl + '/' + iconFile) + '" alt="Leave Type">'
                    + '</div>'
                    + '<div class="clearfix">'
                    + '<h6 class="card-title mb-0 fw-semibold">' + escapeHtml(title) + '</h6>'
                    + '<span class="small">' + escapeHtml(periodLabel) + '</span>'
                    + '</div>'
                    + '<div class="clearfix ms-auto leave-history-card-actions">'
                    + '<div class="dropdown">'
                    + '<a href="javascript:void(0)" type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false">'
                    + '<i class="bi bi-grid"></i>'
                    + '</a>'
                    + '<div class="dropdown-menu dropdown-menu-end">'
                    + (canView ? '<a href="#" class="dropdown-item leave-history-action-view">View</a>' : '')
                    + (canUpdate ? '<a href="#" class="dropdown-item leave-history-action-update">Update</a>' : '')
                    + (canDelete ? '<a href="#" class="dropdown-item leave-history-action-delete">Delete</a>' : '')
                    + '</div>'
                    + '</div>'
                    + '</div>'
                    + '</div>'
                    + '<p class="my-3">' + escapeHtml(reason) + '</p>'
                    + '<div class="widget-timeline1 leave-history-timeline"><ul class="timeline">'
                    + renderLeaveHistoryTimeline(timelineItems)
                    + '</ul></div>'
                    + '</div>'
                    + '<div class="card-footer d-flex justify-content-between flex-wrap">'
                    + '<p class="mb-0 fw-medium">Due <span class="text-purple">: ' + escapeHtml(dueDateLabel) + '</span></p>'
                    + '<span class="badge badge-sm ' + statusClass + '">' + escapeHtml(statusLabel) + '</span>'
                    + '</div>'
                    + '</div>'
                    + '</div>';
            }

            function renderLeaveHistoryCards(cards) {
                var cardHtml = Array.isArray(cards) && cards.length > 0
                    ? cards.map(leaveHistoryCardHtml).join('')
                    : '<div class="col-12"><div class="card"><div class="card-body text-center py-5"><span class="text-gray">Belum ada history leave request.</span></div></div></div>';

                return '<div class="row leave-history-mobile-slider" id="leaveHistoryCardsSlider">' + cardHtml + '</div>';
            }

            function fillLeaveHistoryDetailModal($card) {
                var isSickLeave = String($card.data('detail-is-sick')) === 'true';
                var statusTextClass = sanitizeBadgeClass($card.data('detail-status-text-class') || 'text-primary');
                var statusDateLabel = $card.data('detail-status-date') || '';
                var attachmentUrl = $card.data('detail-attachment-url') || '';

                $('#leaveHistoryDetailModalLabel').text($card.data('detail-modal-title') || 'Leave Request Details');
                $('#leaveHistoryDetailIntroTitle').text(isSickLeave ? 'Your health comes first' : 'Out of Office mode: ON');
                $('#leaveHistoryDetailIntroText').text(isSickLeave
                    ? "Take all the time you need to rest, hydrate, and recover. We've got the fort down here, so just focus on feeling better!"
                    : "Whether you're traveling the globe or just relaxing on the couch, enjoy your well-deserved break. Disconnect, recharge, and have fun!");
                $('#leaveHistoryDetailType').text($card.data('detail-leave-type') || $card.data('detail-title') || '-');
                $('#leaveHistoryDetailPeriod').text($card.data('detail-period') || '-');
                $('#leaveHistoryDetailReason').text($card.data('detail-reason') || '-');
                $('#leaveHistoryDetailStatusText')
                    .removeClass()
                    .addClass('fw-semibold ' + statusTextClass)
                    .text($card.data('detail-status') || 'Pending');
                $('#leaveHistoryDetailStatusDate').text(statusDateLabel ? ' on ' + statusDateLabel : '');
                $('#leaveHistoryDetailMedicalNotesRow').toggleClass('d-none', !isSickLeave);
                $('#leaveHistoryDetailMedicalNotesImage').attr('src', attachmentUrl || medicalNotesUnavailableUrl);
                $('#leaveHistoryDetailMedicalNotesLink').attr('href', attachmentUrl || medicalNotesUnavailableUrl);
            }

            function showLeaveHistoryDetailModal($card) {
                fillLeaveHistoryDetailModal($card);

                var detailModalElement = document.getElementById('leaveHistoryDetailModal');
                if (window.bootstrap && detailModalElement) {
                    var detailModal = bootstrap.Modal.getInstance(detailModalElement) || new bootstrap.Modal(detailModalElement);
                    detailModal.show();
                    return;
                }

                $('#leaveHistoryDetailModal').modal('show');
            }

            $(document).on('click', '.leave-history-detail-trigger', function (event) {
                if ($(event.target).closest('.leave-history-card-actions').length) {
                    return;
                }

                showLeaveHistoryDetailModal($(this));
            });

            $(document).on('click', '.leave-history-action-view', function (event) {
                event.preventDefault();
                event.stopPropagation();

                showLeaveHistoryDetailModal($(this).closest('.leave-history-detail-trigger'));
            });

            $(document).on('click', '.leave-history-action-update', function (event) {
                event.preventDefault();
                event.stopPropagation();

                showLeaveUpdateModal($(this).closest('.leave-history-detail-trigger'));
            });

            $(document).on('click', '.leave-history-action-delete', function (event) {
                event.preventDefault();
                event.stopPropagation();

                showLeaveDeleteModal($(this).closest('.leave-history-detail-trigger'));
            });

            $(document).on('keydown', '.leave-history-detail-trigger', function (event) {
                if (event.key !== 'Enter' && event.key !== ' ') {
                    return;
                }

                if ($(event.target).closest('.leave-history-card-actions').length) {
                    return;
                }

                event.preventDefault();
                showLeaveHistoryDetailModal($(this));
            });

            var specialLeaveTypeId = @json($specialLeaveTypeId ?? null);
            var sickLeaveTypeId = @json($sickLeaveTypeId ?? null);
            var leaveStoreUrl = @json(route('attendance.leave-requests.store'));
            var leaveUpdateUrlTemplate = @json(route('attendance.leave-requests.update', ['leaveRequest' => '__LEAVE_REQUEST_ID__']));
            var leaveDeleteUrlTemplate = @json(route('attendance.leave-requests.destroy', ['leaveRequest' => '__LEAVE_REQUEST_ID__']));
            var leaveHistoryCardsUrl = @json(route('attendance.leave-requests.cards'));
            var attachmentPreviewPlaceholderUrl = @json(asset('assets/images/avatar/placeholder.avif'));
            var medicalNotesUnavailableUrl = @json(asset('assets/not_available_images.png'));
            var leaveTypeIconBaseUrl = @json(asset('assets'));

            var $leaveForm = $('#leaveRequestForm');
            var $leaveHistoryFilterForm = $('#leaveHistoryFilterForm');
            var $leaveHistoryStatusFilter = $('#leaveHistoryStatusFilter');
            var $leaveHistoryTypeFilter = $('#leaveHistoryTypeFilter');
            var $leaveHistoryTimeframeFilter = $('#leaveHistoryTimeframeFilter');
            var $leaveHistoryCardsContainer = $('#leaveHistoryCardsContainer');
            var $leaveHistoryFilterApplyButton = $('#leaveHistoryFilterApplyButton');
            var $leaveDateRangeInput = $('#leaveDateRangeInput');
            var $leaveStartDateInput = $('#leaveStartDateInput');
            var $leaveEndDateInput = $('#leaveEndDateInput');
            var $leaveTypeWrapper = $('#leaveTypeWrapper');
            var $leaveTypeSelect = $('#leaveTypeSelect');
            var $specialLeaveTypeWrapper = $('#specialLeaveTypeWrapper');
            var $specialLeaveSubTypeSelect = $('#specialLeaveSubTypeSelect');
            var $sickAttachmentWrapper = $('#sickAttachmentWrapper');
            var $attachmentFileInput = $('#leaveAttachmentFileInput');
            var $attachmentUploadTrigger = $('#leaveAttachmentUploadTrigger');
            var $attachmentPreview = $('#leaveAttachmentPreview');
            var $submitButton = $('#leaveRequestSubmitButton');
            var $alertBox = $('#leaveRequestAlert');
            var $leaveUpdateForm = $('#leaveRequestUpdateForm');
            var $leaveUpdateRequestIdInput = $('#leaveUpdateRequestIdInput');
            var $leaveUpdateDateRangeInput = $('#leaveUpdateDateRangeInput');
            var $leaveUpdateStartDateInput = $('#leaveUpdateStartDateInput');
            var $leaveUpdateEndDateInput = $('#leaveUpdateEndDateInput');
            var $leaveUpdateTypeWrapper = $('#leaveUpdateTypeWrapper');
            var $leaveUpdateTypeSelect = $('#leaveUpdateTypeSelect');
            var $leaveUpdateSpecialLeaveTypeWrapper = $('#leaveUpdateSpecialLeaveTypeWrapper');
            var $leaveUpdateSpecialLeaveSubTypeSelect = $('#leaveUpdateSpecialLeaveSubTypeSelect');
            var $leaveUpdateReasonInput = $('#leaveUpdateReasonInput');
            var $leaveUpdateHandoverNotesInput = $('#leaveUpdateHandoverNotesInput');
            var $leaveUpdateSickAttachmentWrapper = $('#leaveUpdateSickAttachmentWrapper');
            var $leaveUpdateAttachmentFileInput = $('#leaveUpdateAttachmentFileInput');
            var $leaveUpdateAttachmentUploadTrigger = $('#leaveUpdateAttachmentUploadTrigger');
            var $leaveUpdateAttachmentPreview = $('#leaveUpdateAttachmentPreview');
            var $leaveUpdateSubmitButton = $('#leaveUpdateSubmitButton');
            var $leaveUpdateAlert = $('#leaveUpdateAlert');
            var $leaveDeleteForm = $('#leaveRequestDeleteForm');
            var $leaveDeleteRequestIdInput = $('#leaveDeleteRequestIdInput');
            var $leaveDeleteDescription = $('#leaveDeleteDescription');
            var $leaveDeleteSubmitButton = $('#leaveDeleteSubmitButton');
            var $leaveDeleteAlert = $('#leaveDeleteAlert');

            function showAlert(type, message) {
                var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                $alertBox.removeClass('d-none alert-success alert-danger').addClass(alertClass).text(message);
            }

            function clearAlert() {
                $alertBox.addClass('d-none').removeClass('alert-success alert-danger').text('');
            }

            function clearAttachmentPreview() {
                $attachmentPreview.attr('src', attachmentPreviewPlaceholderUrl);
            }

            function showUpdateAlert(type, message) {
                var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                $leaveUpdateAlert.removeClass('d-none alert-success alert-danger').addClass(alertClass).text(message);
            }

            function clearUpdateAlert() {
                $leaveUpdateAlert.addClass('d-none').removeClass('alert-success alert-danger').text('');
            }

            function clearUpdateAttachmentPreview() {
                $leaveUpdateAttachmentPreview.attr('src', attachmentPreviewPlaceholderUrl);
            }

            function buildLeaveUpdateUrl(leaveRequestId) {
                return leaveUpdateUrlTemplate.replace('__LEAVE_REQUEST_ID__', encodeURIComponent(leaveRequestId || ''));
            }

            function showDeleteAlert(type, message) {
                var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                $leaveDeleteAlert.removeClass('d-none alert-success alert-danger').addClass(alertClass).text(message);
            }

            function showSwalAlert(iconType, titleText, messageText) {
                if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
                    Swal.fire({
                        icon: iconType,
                        title: titleText,
                        text: messageText,
                        timer: iconType === 'success' ? 1800 : undefined,
                        showConfirmButton: iconType !== 'success'
                    });
                    return;
                }

                if (iconType === 'success') {
                    showDeleteAlert('success', messageText);
                    return;
                }

                showDeleteAlert('error', messageText);
            }

            function clearDeleteAlert() {
                $leaveDeleteAlert.addClass('d-none').removeClass('alert-success alert-danger').text('');
            }

            function buildLeaveDeleteUrl(leaveRequestId) {
                return leaveDeleteUrlTemplate.replace('__LEAVE_REQUEST_ID__', encodeURIComponent(leaveRequestId || ''));
            }

            function formatDateRangeDisplay(startDateValue, endDateValue) {
                if (!startDateValue || !endDateValue || typeof moment === 'undefined') {
                    return '';
                }

                var startDate = moment(startDateValue, 'YYYY-MM-DD');
                var endDate = moment(endDateValue, 'YYYY-MM-DD');
                if (!startDate.isValid() || !endDate.isValid()) {
                    return '';
                }

                return startDate.format('DD/MM/YYYY') + ' - ' + endDate.format('DD/MM/YYYY');
            }

            function getLeaveHistoryFilters() {
                return {
                    status: $leaveHistoryStatusFilter.val() || 'all',
                    leave_type: $leaveHistoryTypeFilter.val() || 'all',
                    timeframe: $leaveHistoryTimeframeFilter.val() || 'all'
                };
            }

            function refreshLeaveHistoryCards() {
                if (!$leaveHistoryCardsContainer.length) {
                    return $.Deferred().resolve().promise();
                }

                return $.ajax({
                    url: leaveHistoryCardsUrl,
                    method: 'GET',
                    data: getLeaveHistoryFilters(),
                    success: function (response) {
                        if (response && Array.isArray(response.cards)) {
                            $leaveHistoryCardsContainer.html(renderLeaveHistoryCards(response.cards));
                        }
                    }
                });
            }

            function hideLeaveHistoryFilterModal() {
                var filterModalElement = document.getElementById('filter');
                if (window.bootstrap && filterModalElement) {
                    var filterModal = bootstrap.Modal.getInstance(filterModalElement) || new bootstrap.Modal(filterModalElement);
                    filterModal.hide();
                    return;
                }

                $('#filter').modal('hide');
            }

            function toggleConditionalFields() {
                var selectedLeaveTypeId = $leaveTypeSelect.val();
                var isSpecialLeave = specialLeaveTypeId && selectedLeaveTypeId === String(specialLeaveTypeId);
                var isSickLeave = sickLeaveTypeId && selectedLeaveTypeId === String(sickLeaveTypeId);

                if (isSpecialLeave) {
                    $leaveTypeWrapper.removeClass('col-md-12').addClass('col-md-6');
                    $specialLeaveTypeWrapper.removeClass('d-none');
                    $specialLeaveSubTypeSelect.prop('required', true);
                } else {
                    $leaveTypeWrapper.removeClass('col-md-6').addClass('col-md-12');
                    $specialLeaveTypeWrapper.addClass('d-none');
                    $specialLeaveSubTypeSelect.prop('required', false);
                    $specialLeaveSubTypeSelect.selectpicker('val', '');
                    $specialLeaveSubTypeSelect.selectpicker('refresh');
                }

                if (isSickLeave) {
                    $sickAttachmentWrapper.removeClass('d-none');
                    $attachmentFileInput.prop('required', true);
                } else {
                    $sickAttachmentWrapper.addClass('d-none');
                    $attachmentFileInput.prop('required', false);
                    $attachmentFileInput.val('');
                    clearAttachmentPreview();
                }
            }

            function toggleUpdateConditionalFields() {
                var selectedLeaveTypeId = $leaveUpdateTypeSelect.val();
                var isSpecialLeave = specialLeaveTypeId && selectedLeaveTypeId === String(specialLeaveTypeId);
                var isSickLeave = sickLeaveTypeId && selectedLeaveTypeId === String(sickLeaveTypeId);

                if (isSpecialLeave) {
                    $leaveUpdateTypeWrapper.removeClass('col-md-12').addClass('col-md-6');
                    $leaveUpdateSpecialLeaveTypeWrapper.removeClass('d-none');
                    $leaveUpdateSpecialLeaveSubTypeSelect.prop('required', true);
                } else {
                    $leaveUpdateTypeWrapper.removeClass('col-md-6').addClass('col-md-12');
                    $leaveUpdateSpecialLeaveTypeWrapper.addClass('d-none');
                    $leaveUpdateSpecialLeaveSubTypeSelect.prop('required', false);
                    $leaveUpdateSpecialLeaveSubTypeSelect.selectpicker('val', '');
                    $leaveUpdateSpecialLeaveSubTypeSelect.selectpicker('refresh');
                }

                if (isSickLeave) {
                    $leaveUpdateSickAttachmentWrapper.removeClass('d-none');
                    $leaveUpdateAttachmentFileInput.prop('required', !($leaveUpdateForm.data('existingAttachmentUrl') || ''));
                } else {
                    $leaveUpdateSickAttachmentWrapper.addClass('d-none');
                    $leaveUpdateAttachmentFileInput.prop('required', false);
                    $leaveUpdateAttachmentFileInput.val('');
                    clearUpdateAttachmentPreview();
                }
            }

            function fillLeaveUpdateModal($card) {
                var leaveRequestId = $card.data('leave-request-id') || '';
                var leaveTypeId = $card.data('leave-type-id') || '';
                var startDateValue = $card.data('start-date') || '';
                var endDateValue = $card.data('end-date') || '';
                var attachmentUrl = $card.data('detail-attachment-url') || '';

                clearUpdateAlert();
                $leaveUpdateRequestIdInput.val(leaveRequestId);
                $leaveUpdateStartDateInput.val(startDateValue);
                $leaveUpdateEndDateInput.val(endDateValue);
                $leaveUpdateDateRangeInput.val(formatDateRangeDisplay(startDateValue, endDateValue));
                if ($leaveUpdateDateRangeInput.data('daterangepicker') && typeof moment !== 'undefined') {
                    $leaveUpdateDateRangeInput.data('daterangepicker').setStartDate(moment(startDateValue, 'YYYY-MM-DD'));
                    $leaveUpdateDateRangeInput.data('daterangepicker').setEndDate(moment(endDateValue, 'YYYY-MM-DD'));
                }
                $leaveUpdateReasonInput.val($card.data('detail-reason') || '');
                $leaveUpdateHandoverNotesInput.val($card.data('handover-notes') || '');
                $leaveUpdateAttachmentFileInput.val('');
                $leaveUpdateAttachmentPreview.attr('src', attachmentUrl || attachmentPreviewPlaceholderUrl);
                $leaveUpdateForm.data('existingAttachmentUrl', attachmentUrl);

                $leaveUpdateTypeSelect.selectpicker('val', leaveTypeId ? String(leaveTypeId) : '');
                $leaveUpdateSpecialLeaveSubTypeSelect.selectpicker('val', '');
                $leaveUpdateTypeSelect.selectpicker('refresh');
                $leaveUpdateSpecialLeaveSubTypeSelect.selectpicker('refresh');
                toggleUpdateConditionalFields();
            }

            function showLeaveUpdateModal($card) {
                fillLeaveUpdateModal($card);

                var updateModalElement = document.getElementById('update');
                if (window.bootstrap && updateModalElement) {
                    var updateModal = bootstrap.Modal.getInstance(updateModalElement) || new bootstrap.Modal(updateModalElement);
                    updateModal.show();
                    return;
                }

                $('#update').modal('show');
            }

            function hideLeaveUpdateModal() {
                var updateModalElement = document.getElementById('update');
                if (window.bootstrap && updateModalElement) {
                    var updateModal = bootstrap.Modal.getInstance(updateModalElement) || new bootstrap.Modal(updateModalElement);
                    updateModal.hide();
                    return;
                }

                $('#update').modal('hide');
            }

            function fillLeaveDeleteModal($card) {
                var leaveRequestId = $card.data('leave-request-id') || '';
                var leaveType = $card.data('detail-title') || 'Leave Request';
                var period = $card.data('detail-period') || '-';

                clearDeleteAlert();
                $leaveDeleteRequestIdInput.val(leaveRequestId);
                $leaveDeleteDescription.text(
                    'Do you want to cancel ' + leaveType + ' for ' + period + '? We will remove this request from your leave list.'
                );
            }

            function showLeaveDeleteModal($card) {
                fillLeaveDeleteModal($card);

                var deleteModalElement = document.getElementById('delete');
                if (window.bootstrap && deleteModalElement) {
                    var deleteModal = bootstrap.Modal.getInstance(deleteModalElement) || new bootstrap.Modal(deleteModalElement);
                    deleteModal.show();
                    return;
                }

                $('#delete').modal('show');
            }

            function hideLeaveDeleteModal() {
                var deleteModalElement = document.getElementById('delete');
                if (window.bootstrap && deleteModalElement) {
                    var deleteModal = bootstrap.Modal.getInstance(deleteModalElement) || new bootstrap.Modal(deleteModalElement);
                    deleteModal.hide();
                    return;
                }

                $('#delete').modal('hide');
            }

            function initLeaveDateRangePicker() {
                if (!$.fn.daterangepicker || !$leaveDateRangeInput.length) {
                    return;
                }

                $leaveDateRangeInput.daterangepicker({
                    autoApply: true,
                    autoUpdateInput: false,
                    locale: {
                        format: 'DD/MM/YYYY',
                        cancelLabel: 'Clear'
                    }
                });

                $leaveDateRangeInput.on('apply.daterangepicker', function (event, picker) {
                    $(this).val(
                        picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY')
                    );
                    $leaveStartDateInput.val(picker.startDate.format('YYYY-MM-DD'));
                    $leaveEndDateInput.val(picker.endDate.format('YYYY-MM-DD'));
                });

                $leaveDateRangeInput.on('cancel.daterangepicker', function () {
                    $(this).val('');
                    $leaveStartDateInput.val('');
                    $leaveEndDateInput.val('');
                });
            }

            function initAttachmentPreview() {
                $attachmentUploadTrigger.on('click', function (event) {
                    event.preventDefault();
                    $attachmentFileInput.trigger('click');
                });

                $attachmentFileInput.on('change', function () {
                    var selectedFile = this.files && this.files[0] ? this.files[0] : null;
                    if (!selectedFile) {
                        clearAttachmentPreview();
                        return;
                    }

                    if (selectedFile.type && selectedFile.type.indexOf('image/') === 0) {
                        var reader = new FileReader();
                        reader.onload = function (loadEvent) {
                            $attachmentPreview.attr('src', loadEvent.target.result);
                        };
                        reader.readAsDataURL(selectedFile);
                        return;
                    }

                    clearAttachmentPreview();
                });
            }

            function initLeaveUpdateDateRangePicker() {
                if (!$.fn.daterangepicker || !$leaveUpdateDateRangeInput.length) {
                    return;
                }

                $leaveUpdateDateRangeInput.daterangepicker({
                    autoApply: true,
                    autoUpdateInput: false,
                    locale: {
                        format: 'DD/MM/YYYY',
                        cancelLabel: 'Clear'
                    }
                });

                $leaveUpdateDateRangeInput.on('apply.daterangepicker', function (event, picker) {
                    $(this).val(
                        picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY')
                    );
                    $leaveUpdateStartDateInput.val(picker.startDate.format('YYYY-MM-DD'));
                    $leaveUpdateEndDateInput.val(picker.endDate.format('YYYY-MM-DD'));
                });

                $leaveUpdateDateRangeInput.on('cancel.daterangepicker', function () {
                    $(this).val('');
                    $leaveUpdateStartDateInput.val('');
                    $leaveUpdateEndDateInput.val('');
                });
            }

            function initLeaveUpdateAttachmentPreview() {
                $leaveUpdateAttachmentUploadTrigger.on('click', function (event) {
                    event.preventDefault();
                    $leaveUpdateAttachmentFileInput.trigger('click');
                });

                $leaveUpdateAttachmentFileInput.on('change', function () {
                    var selectedFile = this.files && this.files[0] ? this.files[0] : null;
                    if (!selectedFile) {
                        clearUpdateAttachmentPreview();
                        return;
                    }

                    if (selectedFile.type && selectedFile.type.indexOf('image/') === 0) {
                        var reader = new FileReader();
                        reader.onload = function (loadEvent) {
                            $leaveUpdateAttachmentPreview.attr('src', loadEvent.target.result);
                        };
                        reader.readAsDataURL(selectedFile);
                        return;
                    }

                    clearUpdateAttachmentPreview();
                });
            }

            function resetLeaveForm() {
                $leaveForm.trigger('reset');
                $leaveStartDateInput.val('');
                $leaveEndDateInput.val('');
                $leaveDateRangeInput.val('');
                $specialLeaveSubTypeSelect.selectpicker('val', '');
                $leaveTypeSelect.selectpicker('val', '');
                $specialLeaveSubTypeSelect.selectpicker('refresh');
                $leaveTypeSelect.selectpicker('refresh');
                clearAttachmentPreview();
                toggleConditionalFields();
            }

            function initLeaveRequestSubmit() {
                $leaveForm.on('submit', function (event) {
                    event.preventDefault();
                    clearAlert();

                    if (!$leaveStartDateInput.val() || !$leaveEndDateInput.val()) {
                        showAlert('error', 'Pilih rentang tanggal izin terlebih dahulu.');
                        return;
                    }

                    var formData = new FormData($leaveForm[0]);
                    $submitButton.prop('disabled', true);

                    $.ajax({
                        url: leaveStoreUrl,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            var successMessage = response && response.message ? response.message : 'Pengajuan izin berhasil disimpan.';
                            showAlert('success', successMessage);
                            resetLeaveForm();
                            refreshLeaveHistoryCards();
                        },
                        error: function (xhr) {
                            var errorMessage = 'Gagal menyimpan pengajuan izin.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                                var firstFieldErrors = Object.values(xhr.responseJSON.errors)[0];
                                if (firstFieldErrors && firstFieldErrors.length > 0) {
                                    errorMessage = firstFieldErrors[0];
                                }
                            }
                            showAlert('error', errorMessage);
                        },
                        complete: function () {
                            $submitButton.prop('disabled', false);
                        }
                    });
                });
            }

            function initLeaveRequestUpdateSubmit() {
                $leaveUpdateForm.on('submit', function (event) {
                    event.preventDefault();
                    clearUpdateAlert();

                    var leaveRequestId = $leaveUpdateRequestIdInput.val();
                    if (!leaveRequestId) {
                        showUpdateAlert('error', 'Data leave request tidak ditemukan.');
                        return;
                    }

                    if (!$leaveUpdateStartDateInput.val() || !$leaveUpdateEndDateInput.val()) {
                        showUpdateAlert('error', 'Pilih rentang tanggal izin terlebih dahulu.');
                        return;
                    }

                    var formData = new FormData($leaveUpdateForm[0]);
                    $leaveUpdateSubmitButton.prop('disabled', true);

                    $.ajax({
                        url: buildLeaveUpdateUrl(leaveRequestId),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            var successMessage = response && response.message ? response.message : 'Data izin berhasil diperbarui.';
                            showUpdateAlert('success', successMessage);
                            refreshLeaveHistoryCards();
                            hideLeaveUpdateModal();
                        },
                        error: function (xhr) {
                            var errorMessage = 'Gagal memperbarui data izin.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                                var firstFieldErrors = Object.values(xhr.responseJSON.errors)[0];
                                if (firstFieldErrors && firstFieldErrors.length > 0) {
                                    errorMessage = firstFieldErrors[0];
                                }
                            }
                            showUpdateAlert('error', errorMessage);
                        },
                        complete: function () {
                            $leaveUpdateSubmitButton.prop('disabled', false);
                        }
                    });
                });
            }

            function initLeaveRequestDeleteSubmit() {
                $leaveDeleteForm.on('submit', function (event) {
                    event.preventDefault();
                    clearDeleteAlert();

                    var leaveRequestId = $leaveDeleteRequestIdInput.val();
                    if (!leaveRequestId) {
                        showDeleteAlert('error', 'Data leave request tidak ditemukan.');
                        return;
                    }

                    var formData = new FormData($leaveDeleteForm[0]);
                    $leaveDeleteSubmitButton.prop('disabled', true);

                    $.ajax({
                        url: buildLeaveDeleteUrl(leaveRequestId),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            var successMessage = response && response.message ? response.message : 'Data izin berhasil dihapus.';
                            refreshLeaveHistoryCards();
                            hideLeaveDeleteModal();
                            showSwalAlert('success', 'Berhasil', successMessage);
                        },
                        error: function (xhr) {
                            var errorMessage = 'Gagal menghapus data izin.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                                var firstFieldErrors = Object.values(xhr.responseJSON.errors)[0];
                                if (firstFieldErrors && firstFieldErrors.length > 0) {
                                    errorMessage = firstFieldErrors[0];
                                }
                            }
                            showDeleteAlert('error', errorMessage);
                        },
                        complete: function () {
                            $leaveDeleteSubmitButton.prop('disabled', false);
                        }
                    });
                });
            }

            function initLeaveHistoryFilter() {
                $leaveHistoryFilterForm.on('submit', function (event) {
                    event.preventDefault();
                    $leaveHistoryFilterApplyButton.prop('disabled', true);

                    refreshLeaveHistoryCards()
                        .done(function () {
                            hideLeaveHistoryFilterModal();
                        })
                        .always(function () {
                            $leaveHistoryFilterApplyButton.prop('disabled', false);
                        });
                });
            }

            initLeaveDateRangePicker();
            initAttachmentPreview();
            initLeaveUpdateDateRangePicker();
            initLeaveUpdateAttachmentPreview();
            initLeaveRequestSubmit();
            initLeaveRequestUpdateSubmit();
            initLeaveRequestDeleteSubmit();
            initLeaveHistoryFilter();

            $leaveTypeSelect.on('changed.bs.select change', function () {
                toggleConditionalFields();
            });

            $leaveUpdateTypeSelect.on('changed.bs.select change', function () {
                toggleUpdateConditionalFields();
            });

            toggleConditionalFields();
            toggleUpdateConditionalFields();
        });
    </script>
@endsection
