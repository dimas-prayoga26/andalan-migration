@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('navbarTitle', 'Attendance')

@section('content')
@include('director_attendance.layout.navbar')

<style>
	.recap-attendance-date-filter {
		width: 220px;
	}

	.recap-attendance-date-filter .input-group-text,
	.recap-attendance-date-filter .btn,
	.recap-attendance-date-filter .form-control {
		min-height: 38px;
	}

	.recap-attendance-date-filter .form-control {
		padding-left: 12px;
		padding-right: 12px;
	}

	#recapMonthlyFilterButton {
		min-width: 48px;
		min-height: 44px;
	}

	.recap-attendance-card .table-responsive,
	.recap-monthly-card .table-responsive {
		overflow-x: auto;
	}

	#recapAttendanceCaptureTable {
		min-width: 980px;
	}

	#recapMonthlyTable {
		min-width: 1220px;
	}

	.recap-monthly-header {
		gap: 12px;
	}

	.recap-monthly-title {
		line-height: 1.25;
	}

	.recap-monthly-filter {
		gap: 8px;
	}

	.recap-monthly-select .bootstrap-select,
	.recap-monthly-select .selectpicker {
		width: 100% !important;
	}

	.recap-monthly-month {
		width: 150px;
	}

	.recap-monthly-year {
		width: 100px;
	}

	@media (max-width: 575.98px) {
		.recap-attendance-date-filter {
			width: 100%;
		}

		.recap-attendance-card .card-header,
		.recap-monthly-header {
			align-items: stretch !important;
			padding-left: 18px;
			padding-right: 18px;
		}

		.recap-attendance-card .card-body,
		.recap-monthly-card .card-body {
			padding-top: 0 !important;
		}

		.recap-monthly-title {
			width: 100%;
			font-size: 18px;
			margin-bottom: 4px !important;
		}

		.recap-monthly-filter-wrap,
		#recapMonthlyFilter {
			width: 100%;
		}

		#recapMonthlyFilter {
			display: grid !important;
			grid-template-columns: minmax(0, 1fr) 92px 48px;
			gap: 8px;
			align-items: stretch;
		}

		.recap-monthly-month,
		.recap-monthly-year {
			width: auto;
			min-width: 0;
		}

		#recapMonthlyFilterButton {
			margin-left: 0 !important;
			width: 48px;
		}
	}
</style>

<!-- Start - Attendance -->
<div class="col-lg-12">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h5 class="mb-0">Attendance Details</h5>
	</div>
</div>		

<!-- Start - logs -->
<div class="card recap-attendance-card">
	<div class="card-header border-0 align-items-center justify-content-between flex-wrap gap-2">
		<div class="d-flex align-items-center flex-wrap gap-2">
			<form id="recapDailyAttendanceFilter" method="GET" action="{{ route('director-attendance.attendance') }}" class="d-flex align-items-center">
				<input type="hidden" name="month" value="{{ $recapMonthlySelectedMonth }}">
				<input type="hidden" name="year" value="{{ $recapMonthlySelectedYear }}">
				<div class="input-group input-group-sm recap-attendance-date-filter">
					<span class="input-group-text bg-white"><i class="fa-regular fa-calendar"></i></span>
					<input
						id="recapAttendanceDateFilter"
						type="text"
						name="date"
						class="form-control js-recap-attendance-date"
						value="{{ $recapAttendanceDateInput }}"
						placeholder="dd/mm/yyyy"
						autocomplete="off"
						aria-label="Select attendance date"
					>
					<button id="recapAttendanceDateButton" type="submit" class="btn btn-sm btn-primary light" title="Apply date" aria-label="Apply attendance date"><i class="fa-solid fa-filter"></i></button>
				</div>
			</form>
		</div>
		<div class="clearfix">
			<button class="btn btn-sm btn-primary light">Capture</button>
		</div>	
	</div>
	<div class="card-body table-card-body p-0">
		<h6 class="text-center fw-bold mb-3">{{ $recapAttendanceDayLabel }}</h6>
		<div class="table-responsive">
			<table class="table table-sm mb-0 table-bottom-borderless">
				<thead>
					<tr>
						<th>Name</th>
						<th>Clock In</th>
						<th>Clock Out</th>
						<th>Note</th>
						<th>Working Hours</th>
						<th>Attachment</th>
					</tr>
				</thead>
				<tbody>
					@forelse ($recapAttendanceLogRows as $row)
						<tr>
							<td class="fw-semibold">{{ $row['name'] }}</td>
							<td>
								@if ($row['clock_in_badge'] !== '')
									<span class="badge badge-sm badge-{{ $row['clock_in_badge'] }} light fw-bold">{{ $row['clock_in'] }}</span>
								@else
									<span class="fw-bold">{{ $row['clock_in'] }}</span>
								@endif
							</td>
							<td>
								@if ($row['clock_out_badge'] !== '')
									<span class="badge badge-sm badge-{{ $row['clock_out_badge'] }} light fw-bold">{{ $row['clock_out'] }}</span>
								@else
									<span class="fw-bold">{{ $row['clock_out'] }}</span>
								@endif
							</td>
							<td>{{ $row['note'] }}</td>
							<td>{{ $row['working_hours'] }}</td>
							<td>
								<button
									type="button"
									class="btn btn-square btn-{{ $row['attachment_badge'] }} light btn-xs"
									data-bs-toggle="modal"
									data-bs-target="#{{ $row['modal_id'] }}"
									data-recap-attendance-modal
									data-recap-name="{{ $row['name'] }}"
									data-recap-clock-in="{{ $row['clock_in'] }}"
									data-recap-clock-in-class="{{ $row['clock_in_class'] }}"
									data-recap-clock-out="{{ $row['clock_out'] }}"
									data-recap-working-hours="{{ $row['working_hours'] }}"
									data-recap-attendance-status="{{ $row['attendance_status'] }}"
									data-recap-attendance-status-class="{{ $row['attendance_status_class'] }}"
									data-recap-location-name="{{ $row['location_name'] }}"
									data-recap-location-address="{{ $row['location_address'] }}"
									data-recap-map-url="{{ $row['map_url'] }}"
									data-recap-deviation-title="{{ $row['deviation_title'] }}"
									data-recap-deviation-intro="{{ $row['deviation_intro'] }}"
									data-recap-deviation-request-type="{{ $row['deviation_request_type'] }}"
									data-recap-deviation-reason="{{ $row['deviation_reason'] }}"
									data-recap-deviation-time-variance="{{ $row['deviation_time_variance'] }}"
									data-recap-deviation-status="{{ $row['deviation_status'] }}"
									data-recap-deviation-status-date="{{ $row['deviation_status_date'] }}"
									data-recap-leave-type="{{ $row['leave_type'] }}"
									data-recap-leave-reason="{{ $row['leave_reason'] }}"
									data-recap-leave-duration="{{ $row['leave_duration'] }}"
									data-recap-leave-status="{{ $row['leave_status'] }}"
									data-recap-leave-status-date="{{ $row['leave_status_date'] }}"
									data-recap-leave-attachment-url="{{ $row['leave_attachment_url'] }}"
									aria-label="View {{ $row['name'] }} attendance details"
								>
									<i class="fa-regular fa-file-lines"></i>
								</button>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="6" class="text-center text-muted py-4">No attendance log data available.</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>
</div>
<!-- End - logs -->

				<!-- Start - logs -->
<div class="card recap-monthly-card">
	<div class="card-header border-0 align-items-center justify-content-between flex-wrap recap-monthly-header">
		<h4 id="recapMonthlyPeriodLabel" class="card-title m-0 recap-monthly-title">Attendance Logs Monthly ({{ $recapMonthlyPeriodLabel }})</h4>
		<div class="d-flex align-items-center recap-monthly-filter-wrap">
			<form id="recapMonthlyFilter" method="GET" action="{{ route('director-attendance.attendance') }}" class="clearfix d-flex align-items-center recap-monthly-filter">
				<input type="hidden" name="date" value="{{ $recapAttendanceDateInput }}">
				<div class="clearfix recap-monthly-select recap-monthly-month">
					<select id="recapMonthlyMonthFilter" name="month" class="selectpicker form-select form-select-sm" aria-label="Select month">
						@foreach ($recapMonthlyMonthOptions as $monthOption)
							<option value="{{ $monthOption['value'] }}" @selected($recapMonthlySelectedMonth === $monthOption['value'])>{{ $monthOption['label'] }}</option>
						@endforeach
					</select>
				</div>
				<div class="clearfix recap-monthly-select recap-monthly-year">
					<select id="recapMonthlyYearFilter" name="year" class="selectpicker form-select form-select-sm" aria-label="Select year">
						@foreach ($recapMonthlyYearOptions as $yearOption)
							<option value="{{ $yearOption }}" @selected($recapMonthlySelectedYear === $yearOption)>{{ $yearOption }}</option>
						@endforeach
					</select>
				</div>
				<button id="recapMonthlyFilterButton" type="button" class="btn btn-sm btn-primary light" title="Apply period" aria-label="Apply period"><i class="fa-solid fa-filter"></i></button>
			</form>	
		</div>	
	</div>
	<div class="card-body table-card-body p-0">
		<div class="table-responsive">
			<table id="recapMonthlyTable" class="table table-sm mb-0 table-bottom-borderless table-striped">
				<thead>
					<tr>
						<th>Name</th>
						<th>Working Days</th>
						<th>Working Hours</th>
						<th>On Time</th>
						<th>Late</th>
						<th>Leave</th>
						<th>Deviation</th>
						<th>Alpha</th>
						<th>Trip</th>
						<th>Overtimes</th>
						<th>Leave 2026</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>
<!-- End - logs -->

<!-- Modal Box Start -->
<div class="modal fade" id="attendanceLogDetailModal" tabindex="-1" aria-labelledby="attendanceLogDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="attendanceLogDetailModalLabel">Attendance Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="mb-3">
                                <label class="form-label">Clock In Location</label>
                                <iframe class="border-0 rounded" height="250" width="100%" id="attendanceLogMap" src="about:blank" title="Clock in location"></iframe>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Location</span>
                                </div>
                                <div class="col-8">
                                    <span id="attendanceLogLocationName" class="text-gray fw-semibold">-</span> <br>
                                    <span id="attendanceLogLocationAddress" class="text-gray">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Attendance Status</span>
                                </div>
                                <div class="col-8">
                                    <span id="attendanceLogStatus" class="fw-semibold">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Clock In</span>
                                </div>
                                <div class="col-8">
                                    <span id="attendanceLogClockIn" class="fw-semibold">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Clock Out</span>
                                </div>
                                <div class="col-8">
                                    <span id="attendanceLogClockOut" class="text-gray fw-semibold">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Working Hours</span>
                                </div>
                                <div class="col-8">
                                    <span id="attendanceLogWorkingHours" class="text-gray fw-semibold">-</span>
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
<div class="modal fade" id="attendanceDeviationModal" tabindex="-1" aria-labelledby="attendanceDeviationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="attendanceDeviationModalLabel">Attendance Deviation</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <h5 id="attendanceDeviationTitle" class="text-muted mb-0 fw-bold">-</h5>
                            <p id="attendanceDeviationIntro" class="form-label text-muted mb-3">-</p>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Request Type</span>
                                </div>
                                <div class="col-8">
                                    <span id="attendanceDeviationRequestType" class="text-gray fw-semibold">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Reason</span>
                                </div>
                                <div class="col-8">
                                    <span id="attendanceDeviationReason" class="text-gray">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Time Variance</span>
                                </div>
                                <div class="col-8">
                                    <span id="attendanceDeviationTimeVariance" class="text-gray fw-semibold">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Status</span>
                                </div>
                                <div class="col-8">
                                    <span id="attendanceDeviationStatus" class="text-success fw-semibold">-</span> <span id="attendanceDeviationStatusDate" class="text-gray"></span>
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

<div class="modal fade" id="attendanceLeaveDetailModal" tabindex="-1" aria-labelledby="attendanceLeaveDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="attendanceLeaveDetailModalLabel">Leave Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <h5 class="text-muted mb-0 fw-bold">Out of Office mode: ON</h5>
                            <p class="form-label text-muted mb-3">
                                Whether you’re traveling the globe or just relaxing on the couch, enjoy your well-deserved break. Disconnect, recharge, and have fun!
                            </p>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Leave Type</span>
                                </div>
                                <div class="col-8">
                                    <span id="attendanceLeaveType" class="text-gray fw-semibold">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Reason</span>
                                </div>
                                <div class="col-8">
                                    <span id="attendanceLeaveReason" class="text-gray">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Leave Duration</span>
                                </div>
                                <div class="col-8">
                                    <span id="attendanceLeaveDuration" class="text-gray fw-semibold">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Status</span>
                                </div>
                                <div class="col-8">
                                    <span id="attendanceLeaveStatus" class="text-success fw-semibold">-</span> <span id="attendanceLeaveStatusDate" class="text-gray"></span>
                                </div>
                            </div>
                            <div class="row py-2 d-none" id="attendanceLeaveAttachmentRow">
                                <div class="col-4">
                                    <span>Attachment</span>
                                </div>
                                <div class="col-8">
                                    <a id="attendanceLeaveAttachment" class="text-primary fw-semibold" href="#" target="_blank" rel="noopener">Open attachment</a>
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

<div class="modal fade" id="attendanceSickLeaveDetailModal" tabindex="-1" aria-labelledby="attendanceSickLeaveDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="attendanceSickLeaveDetailModalLabel">Sick Leave Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 class="text-muted mb-0 fw-bold">Your health comes first</h5>
                <p class="form-label text-muted mb-3">Take the time needed to rest and recover.</p>
                <div class="row py-2"><div class="col-4"><span>Leave Type</span></div><div class="col-8"><span id="attendanceSickLeaveType" class="text-gray fw-semibold">-</span></div></div>
                <div class="row py-2"><div class="col-4"><span>Reason</span></div><div class="col-8"><span id="attendanceSickLeaveReason" class="text-gray">-</span></div></div>
                <div class="row py-2"><div class="col-4"><span>Leave Duration</span></div><div class="col-8"><span id="attendanceSickLeaveDuration" class="text-gray fw-semibold">-</span></div></div>
                <div class="row py-2"><div class="col-4"><span>Status</span></div><div class="col-8"><span id="attendanceSickLeaveStatus" class="text-success fw-semibold">-</span> <span id="attendanceSickLeaveStatusDate" class="text-gray"></span></div></div>
                <div class="row py-2 d-none" id="attendanceSickLeaveAttachmentRow"><div class="col-4"><span>Medical Note</span></div><div class="col-8"><a id="attendanceSickLeaveAttachment" class="text-primary fw-semibold" href="#" target="_blank" rel="noopener">Open attachment</a></div></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>

@endsection

@section('script')
@php
    $dataTablesJsPath = public_path('assets/vendor/datatables/js/jquery.dataTables.bundle.min.js');
    $dataTablesJsVersion = file_exists($dataTablesJsPath) ? filemtime($dataTablesJsPath) : time();
@endphp
<script src="{{ asset('assets/vendor/datatables/js/jquery.dataTables.bundle.min.js') }}?v={{ $dataTablesJsVersion }}"></script>
<script>
    (function () {
        function initializeRecapAttendanceDatePicker() {
            if (!window.jQuery || !jQuery.fn.daterangepicker || typeof moment === 'undefined') {
                return;
            }

            var input = jQuery('.js-recap-attendance-date');
            if (input.length === 0) {
                return;
            }

            input.daterangepicker({
                autoUpdateInput: false,
                singleDatePicker: true,
                showDropdowns: true,
                maxDate: moment(),
                locale: {
                    cancelLabel: 'Clear',
                    format: 'DD/MM/YYYY'
                }
            });

            input.on('apply.daterangepicker', function (event, picker) {
                jQuery(this).val(picker.startDate.format('DD/MM/YYYY'));
                var form = document.getElementById('recapDailyAttendanceFilter');

                if (form) {
                    form.submit();
                }
            });

            input.on('cancel.daterangepicker', function () {
                jQuery(this).val('');
            });
        }

        initializeRecapAttendanceDatePicker();

        function setText(id, value) {
            var element = document.getElementById(id);
            if (element) {
                element.textContent = value || '-';
            }
        }

        function setLeaveModal(prefix, button) {
            setText(prefix + 'Type', button.dataset.recapLeaveType);
            setText(prefix + 'Reason', button.dataset.recapLeaveReason);
            setText(prefix + 'Duration', button.dataset.recapLeaveDuration);
            setText(prefix + 'Status', button.dataset.recapLeaveStatus);

            var statusDateElement = document.getElementById(prefix + 'StatusDate');
            if (statusDateElement) {
                var statusDate = button.dataset.recapLeaveStatusDate || '';
                statusDateElement.textContent = statusDate !== '' ? 'on ' + statusDate : '';
            }

            var attachmentUrl = button.dataset.recapLeaveAttachmentUrl || '';
            var attachmentRow = document.getElementById(prefix + 'AttachmentRow');
            var attachment = document.getElementById(prefix + 'Attachment');
            if (attachmentRow && attachment) {
                attachmentRow.classList.toggle('d-none', attachmentUrl === '');
                attachment.href = attachmentUrl || '#';
            }
        }

        document.querySelectorAll('[data-recap-attendance-modal]').forEach(function (button) {
            button.addEventListener('click', function () {
                var modalId = (button.dataset.bsTarget || '').replace('#', '');

                if (modalId === 'attendanceLogDetailModal') {
                    setText('attendanceLogLocationName', button.dataset.recapLocationName);
                    setText('attendanceLogLocationAddress', button.dataset.recapLocationAddress);
                    setText('attendanceLogStatus', button.dataset.recapAttendanceStatus);
                    setText('attendanceLogClockIn', button.dataset.recapClockIn);
                    setText('attendanceLogClockOut', button.dataset.recapClockOut);
                    setText('attendanceLogWorkingHours', button.dataset.recapWorkingHours);

                    var status = document.getElementById('attendanceLogStatus');
                    if (status) {
                        status.classList.remove('text-success', 'text-danger');
                        status.classList.add(button.dataset.recapAttendanceStatusClass || 'text-success');
                    }

                    var clockIn = document.getElementById('attendanceLogClockIn');
                    if (clockIn) {
                        clockIn.classList.remove('text-success', 'text-danger');
                        clockIn.classList.add(button.dataset.recapClockInClass || 'text-success');
                    }

                    var map = document.getElementById('attendanceLogMap');
                    if (map) {
                        map.src = button.dataset.recapMapUrl || 'about:blank';
                    }
                }

                if (modalId === 'attendanceDeviationModal') {
                    setText('attendanceDeviationTitle', button.dataset.recapDeviationTitle);
                    setText('attendanceDeviationIntro', button.dataset.recapDeviationIntro);
                    setText('attendanceDeviationRequestType', button.dataset.recapDeviationRequestType);
                    setText('attendanceDeviationReason', button.dataset.recapDeviationReason);
                    setText('attendanceDeviationTimeVariance', button.dataset.recapDeviationTimeVariance);
                    setText('attendanceDeviationStatus', button.dataset.recapDeviationStatus);
                    setText('attendanceDeviationStatusDate', button.dataset.recapDeviationStatusDate);
                }

                if (modalId === 'attendanceLeaveDetailModal') {
                    setLeaveModal('attendanceLeave', button);
                }

                if (modalId === 'attendanceSickLeaveDetailModal') {
                    setLeaveModal('attendanceSickLeave', button);
                }
            });
        });

        if (window.jQuery && jQuery.fn.DataTable) {
            jQuery(function ($) {
                var monthlyFilter = document.getElementById('recapMonthlyFilter');
                var monthlyMonthFilter = document.getElementById('recapMonthlyMonthFilter');
                var monthlyYearFilter = document.getElementById('recapMonthlyYearFilter');
                var monthlyFilterButton = document.getElementById('recapMonthlyFilterButton');
                var monthlyPeriodLabel = document.getElementById('recapMonthlyPeriodLabel');

                if (!monthlyFilter || !monthlyMonthFilter || !monthlyYearFilter) {
                    return;
                }

                var escapeHtml = function (value) {
                    return String(value || '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                };
                var employeeDetailBaseUrl = @json(url('/director-attendance/attendance'));
                var monthlyTable = $('#recapMonthlyTable').DataTable({
                    ajax: {
                        url: @json(route('director-attendance.attendance.monthly-datatable')),
                        data: function (requestData) {
                            requestData.month = monthlyMonthFilter.value;
                            requestData.year = monthlyYearFilter.value;
                        },
                        dataSrc: function (response) {
                            if (monthlyPeriodLabel && response.period_label) {
                                monthlyPeriodLabel.textContent = 'Attendance Logs Monthly (' + response.period_label + ')';
                            }

                            return response.data || [];
                        }
                    },
                    autoWidth: false,
                    searching: false,
                    pageLength: 10,
                    select: false,
                    lengthChange: false,
                    paging: true,
                    bInfo: true,
                    columns: [
                        { data: 'name', render: function (data) { return '<span class="fw-semibold">' + escapeHtml(data) + '</span>'; } },
                        { data: 'working_days', defaultContent: '-' },
                        { data: 'working_hours', defaultContent: '-' },
                        { data: 'on_time', defaultContent: '-' },
                        { data: 'late', defaultContent: '-' },
                        { data: 'leave', defaultContent: '-' },
                        { data: 'deviation', defaultContent: '-' },
                        { data: 'alpha', defaultContent: '-' },
                        { data: 'trip', defaultContent: '-' },
                        { data: 'overtimes', defaultContent: '-' },
                        { data: 'year_leave', defaultContent: '-' },
                        {
                            data: 'employee_id',
                            orderable: false,
                            searchable: false,
                            render: function (employeeId, type) {
                                if (type !== 'display' || !employeeId) {
                                    return employeeId || '';
                                }

                                return '<a href="' + employeeDetailBaseUrl + '/' + encodeURIComponent(employeeId) + '"><span class="badge badge-xs light badge-primary fw-semibold">View More</span></a>';
                            }
                        }
                    ],
                    language: {
                        emptyTable: 'No employee data available for this period.',
                        paginate: {
                            next: '<i class="fa-solid fa-angle-right"></i>',
                            previous: '<i class="fa-solid fa-angle-left"></i>'
                        }
                    }
                });
                var reloadMonthlyTable = function () {
                    monthlyTable.ajax.reload();
                };

                $(monthlyFilter).on('submit', function (event) {
                    event.preventDefault();
                    reloadMonthlyTable();
                });
                monthlyMonthFilter.addEventListener('change', reloadMonthlyTable);
                monthlyYearFilter.addEventListener('change', reloadMonthlyTable);
                if (monthlyFilterButton) {
                    monthlyFilterButton.addEventListener('click', reloadMonthlyTable);
                }
            });
        }
    })();
</script>
@endsection
