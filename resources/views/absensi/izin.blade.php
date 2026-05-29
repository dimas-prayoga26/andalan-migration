@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
        $absensiCssPath = public_path('assets/css/absensi.css');
        $absensiCssVersion = file_exists($absensiCssPath) ? filemtime($absensiCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <link rel="stylesheet" href="{{ asset('assets/css/absensi.css') }}?v={{ $absensiCssVersion }}">
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

        @media (max-width: 767.98px) {
            .leave-history-mobile-slider {
                display: flex;
                flex-wrap: nowrap;
                overflow-x: auto;
                gap: 12px;
                scroll-snap-type: x mandatory;
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            .leave-history-mobile-slider::-webkit-scrollbar {
                display: none;
                width: 0;
                height: 0;
            }

            .leave-history-mobile-slide {
                flex: 0 0 100%;
                width: 100%;
                max-width: 100%;
                scroll-snap-align: start;
            }
        }
    </style>
@endsection

@section('navbarTitle', 'Attendances')

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Attendances',
    'current' => 'Leaves & Sick',
    'homeRoute' => 'dashboard',
])

@include('absensi.layouts_absensi.profileIndex')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Leaves & Sick</h5>
    </div>
</div>

<div class="row">
    <div class="col-xxl-6 col-xl-6 col-sm-6">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <div>
                    <h4 class="card-title">Leave Balance & Eligibility</h4>
                    <p class="fs-13 mb-0">Please ensure you have met the 1-year service requirement and your request does not exceed the maximum monthly limit.</p>
                </div>
            </div>
            <div class="card-body">
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
                        <span class="text-gray">{{ $leaveEligibility['available_balance_note'] ?? '-' }}</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Leave Used in {{ $leaveEligibility['leave_used_year'] ?? now('Asia/Jakarta')->year }}</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray fw-semibold">{{ $leaveEligibility['leave_used_label'] ?? '0 Days' }}</span> <br>
                        <span class="text-gray">{{ $leaveEligibility['leave_used_breakdown'] ?? '-' }}</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Leave Taken This Month ({{ $leaveEligibility['leave_taken_month_label'] ?? now('Asia/Jakarta')->format('F') }})</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray fw-semibold">{{ $leaveEligibility['leave_taken_month_value_label'] ?? '0 Days' }}</span> <br>
                        <span class="text-gray">{{ $leaveEligibility['monthly_limit_label'] ?? 'Maximum limit is 0 days per month' }}</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Next Accrual</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray fw-semibold">{{ $leaveEligibility['next_accrual_label'] ?? '+0 Day' }}</span> <br>
                        <span class="text-gray">{{ $leaveEligibility['next_accrual_note'] ?? '-' }}</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Active / Pending Requests</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray">{{ $leaveEligibility['pending_requests_label'] ?? '0 Requests' }}</span> <br>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Total Approved Leaves</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray">{{ $leaveEligibility['approved_requests_label'] ?? '0 Requests' }}</span> <br>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Total Rejected Leaves</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray">{{ $leaveEligibility['rejected_requests_label'] ?? '0 Requests' }}</span> <br>
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
                        <div class="col-12 col-md-6 mb-3">
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
                        <label class="form-label">Attachment (max 1 MB) <span class="text-danger">*</span></label>
                        <div class="">
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
    <div class="col-12 d-flex justify-content-end mb-3">
        <form method="GET" action="{{ route('absensi.izin') }}" class="d-flex align-items-end gap-2">
            <div>
                <label for="leaveHistoryYearFilter" class="form-label mb-1">History Year</label>
                <select
                    id="leaveHistoryYearFilter"
                    name="history_year"
                    class="form-select form-select-sm"
                    onchange="this.form.submit()"
                >
                    <option value="">All Years</option>
                    @foreach (($leaveHistoryYearOptions ?? collect()) as $leaveHistoryYearOption)
                        <option value="{{ $leaveHistoryYearOption }}" @selected((int) ($selectedLeaveHistoryYear ?? 0) === (int) $leaveHistoryYearOption)>
                            {{ $leaveHistoryYearOption }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div class="col-12">
        <div class="row leave-history-mobile-slider">
            @forelse (($leaveHistoryCards ?? collect()) as $leaveHistoryCard)
                <div class="col-xxl-3 col-xl-4 col-sm-6 leave-history-mobile-slide">
                    <div class="card">
                        <div class="card-body">
                            <div class="clearfix d-flex">
                                <div class="avatar avatar-sm rounded me-3 p-2">
                                    <img src="{{ asset('assets/images/logo/figma.avif') }}" alt="Leave Type">
                                </div>
                                <div class="clearfix">
                                    <h6 class="mb-0 fw-semibold">{{ $leaveHistoryCard['title'] ?? 'Leave Request' }}</h6>
                                    <span class="small">{{ $leaveHistoryCard['period_label'] ?? '-' }}</span>
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
@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script>
        $(function () {
            $('.absensi-tab-btn').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var targetUrl = $(this).data('href');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });

            var specialLeaveTypeId = @json($specialLeaveTypeId ?? null);
            var sickLeaveTypeId = @json($sickLeaveTypeId ?? null);
            var leaveStoreUrl = @json(route('absensi.izin.store'));
            var attachmentPreviewPlaceholderUrl = @json(asset('assets/images/avatar/placeholder.avif'));

            var $leaveForm = $('#leaveRequestForm');
            var $leaveDateRangeInput = $('#leaveDateRangeInput');
            var $leaveStartDateInput = $('#leaveStartDateInput');
            var $leaveEndDateInput = $('#leaveEndDateInput');
            var $leaveTypeSelect = $('#leaveTypeSelect');
            var $specialLeaveTypeWrapper = $('#specialLeaveTypeWrapper');
            var $specialLeaveSubTypeSelect = $('#specialLeaveSubTypeSelect');
            var $sickAttachmentWrapper = $('#sickAttachmentWrapper');
            var $attachmentFileInput = $('#leaveAttachmentFileInput');
            var $attachmentUploadTrigger = $('#leaveAttachmentUploadTrigger');
            var $attachmentPreview = $('#leaveAttachmentPreview');
            var $submitButton = $('#leaveRequestSubmitButton');
            var $alertBox = $('#leaveRequestAlert');

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

            function toggleConditionalFields() {
                var selectedLeaveTypeId = $leaveTypeSelect.val();
                var isSpecialLeave = specialLeaveTypeId && selectedLeaveTypeId === String(specialLeaveTypeId);
                var isSickLeave = sickLeaveTypeId && selectedLeaveTypeId === String(sickLeaveTypeId);

                if (isSpecialLeave) {
                    $specialLeaveTypeWrapper.removeClass('d-none');
                    $specialLeaveSubTypeSelect.prop('required', true);
                } else {
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

            initLeaveDateRangePicker();
            initAttachmentPreview();
            initLeaveRequestSubmit();

            $leaveTypeSelect.on('changed.bs.select change', function () {
                toggleConditionalFields();
            });

            toggleConditionalFields();
        });
    </script>
@endsection
