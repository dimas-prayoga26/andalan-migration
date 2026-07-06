@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('navbarTitle', 'Attendance')

@section('content')
@include('admin_attendance.layout.navbar')

<!-- Start - Attendance -->
<div class="col-lg-12">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h5 class="mb-0">Attendance Details</h5>
	</div>
</div>		

<!-- Start - logs -->
<div id="recapAttendanceCaptureArea" class="card" data-capture-title="{{ $recapAttendanceDayLabel }}" data-capture-subtitle="">
	<div class="card-header border-0 align-items-center justify-content-end">
		<div class="d-flex align-items-center">
			<div class="clearfix">
				<button id="recapAttendanceCaptureButton" type="button" class="btn btn-sm btn-primary light ms-2">Capture</button>
			</div>	
		</div>	
	</div>
	<div class="card-body table-card-body p-0">
		<h6 class="text-center fw-bold mb-3">{{ $recapAttendanceDayLabel }}</h6>
		<div class="table-responsive">
			<table id="recapAttendanceCaptureTable" class="table table-sm mb-0 table-bottom-borderless">
				<thead>
					<tr>
						<th>Name</th>
						<th>Clock In</th>
						<th>Clock Out</th>
						<th>Note</th>
						<th>Address</th>
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
							<td data-capture-tone="{{ $row['attachment_badge'] }}">{{ $row['note'] }}</td>
							<td>{{ $row['location_address'] }}</td>
							<td>{{ $row['working_hours'] }}</td>
							<td>
								@if ($row['has_detail'])
									<button
									type="button"
									class="btn btn-square btn-{{ $row['attachment_badge'] }} light btn-xs"
									data-capture-tone="{{ $row['attachment_badge'] }}"
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
								@else
									<span>-</span>
								@endif
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="7" class="text-center text-muted py-4">No attendance log data available.</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>
</div>
<!-- End - logs -->

				<!-- Start - logs -->
<div class="card">
	<div class="card-header border-0 align-items-center">
		<h4 id="recapMonthlyPeriodLabel" class="card-title m-0">Attendance Logs Monthly ({{ $recapMonthlyPeriodLabel }})</h4>
		<div class="d-flex align-items-center">
			<form id="recapMonthlyFilter" method="GET" action="{{ route('admin-attendance.recap') }}" class="clearfix d-flex align-items-center">
				<div class="clearfix me-1">
					<select id="recapMonthlyMonthFilter" name="month" class="selectpicker form-select form-select-sm" aria-label="Select month">
						@foreach ($recapMonthlyMonthOptions as $monthOption)
							<option value="{{ $monthOption['value'] }}" @selected($recapMonthlySelectedMonth === $monthOption['value'])>{{ $monthOption['label'] }}</option>
						@endforeach
					</select>
				</div>
				<div class="clearfix">
					<select id="recapMonthlyYearFilter" name="year" class="selectpicker form-select form-select-sm" aria-label="Select year">
						@foreach ($recapMonthlyYearOptions as $yearOption)
							<option value="{{ $yearOption }}" @selected($recapMonthlySelectedYear === $yearOption)>{{ $yearOption }}</option>
						@endforeach
					</select>
				</div>
				<button id="recapMonthlyFilterButton" type="button" class="btn btn-sm btn-primary light ms-2" title="Apply period" aria-label="Apply period"><i class="fa-solid fa-filter"></i></button>
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
        function setText(id, value) {
            var element = document.getElementById(id);
            if (element) {
                element.textContent = value || '-';
            }
        }

        function normalizedTableText(element) {
            var text = element ? element.textContent : '';
            text = String(text || '').replace(/\s+/g, ' ').trim();

            return text !== '' ? text : '-';
        }

        function wrapCanvasText(context, text, maxWidth) {
            var words = String(text || '-').split(' ');
            var lines = [];
            var currentLine = '';

            words.forEach(function (word) {
                var testLine = currentLine === '' ? word : currentLine + ' ' + word;

                if (context.measureText(testLine).width <= maxWidth || currentLine === '') {
                    currentLine = testLine;
                    return;
                }

                lines.push(currentLine);
                currentLine = word;
            });

            if (currentLine !== '') {
                lines.push(currentLine);
            }

            return lines.length > 0 ? lines : ['-'];
        }

        function drawRoundedRectangle(context, x, y, width, height, radius) {
            context.beginPath();
            context.moveTo(x + radius, y);
            context.lineTo(x + width - radius, y);
            context.quadraticCurveTo(x + width, y, x + width, y + radius);
            context.lineTo(x + width, y + height - radius);
            context.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
            context.lineTo(x + radius, y + height);
            context.quadraticCurveTo(x, y + height, x, y + height - radius);
            context.lineTo(x, y + radius);
            context.quadraticCurveTo(x, y, x + radius, y);
            context.closePath();
        }

        function captureToneFromElement(element) {
            if (!element) {
                return '';
            }

            var tone = element.dataset ? (element.dataset.captureTone || '') : '';
            var toneElement = element.querySelector('[data-capture-tone], .badge, .btn');
            if (tone === '' && toneElement && toneElement.dataset) {
                tone = toneElement.dataset.captureTone || '';
            }

            var className = toneElement ? toneElement.className : element.className;
            className = typeof className === 'string' ? className : '';
            ['success', 'danger', 'secondary', 'info', 'warning', 'primary'].some(function (candidate) {
                if (tone !== '') {
                    return true;
                }

                if (className.indexOf('badge-' + candidate) !== -1 || className.indexOf('btn-' + candidate) !== -1) {
                    tone = candidate;
                    return true;
                }

                return false;
            });

            if (tone === '') {
                var text = normalizedTableText(element).toLowerCase();
                if (text.indexOf('late') !== -1 || text.indexOf('terlambat') !== -1) {
                    tone = 'danger';
                } else if (text.indexOf('on time') !== -1 || text.indexOf('masuk') !== -1) {
                    tone = 'success';
                } else if (text.indexOf('deviation') !== -1 || text.indexOf('exception') !== -1 || text.indexOf('arrival') !== -1 || text.indexOf('departure') !== -1) {
                    tone = 'secondary';
                }
            }

            return tone;
        }

        function captureTonePalette(tone) {
            var palettes = {
                success: { background: '#dcfce7', border: '#bbf7d0', text: '#16a34a' },
                danger: { background: '#ffe4e6', border: '#fecdd3', text: '#e11d48' },
                secondary: { background: '#f3e8ff', border: '#e9d5ff', text: '#7c3aed' },
                info: { background: '#e0f2fe', border: '#bae6fd', text: '#0284c7' },
                warning: { background: '#fef3c7', border: '#fde68a', text: '#d97706' },
                primary: { background: '#dbeafe', border: '#bfdbfe', text: '#1d4ed8' },
            };

            return palettes[tone] || null;
        }

        function captureCellPayload(cell) {
            return {
                text: normalizedTableText(cell),
                tone: captureToneFromElement(cell),
            };
        }

        function drawCaptureBadge(context, text, palette, x, y, maxWidth) {
            var textWidth = Math.min(context.measureText(text).width, maxWidth - 28);
            var badgeWidth = Math.min(Math.max(textWidth + 24, 34), maxWidth);
            var badgeHeight = 26;

            drawRoundedRectangle(context, x, y, badgeWidth, badgeHeight, 8);
            context.fillStyle = palette.background;
            context.fill();
            context.strokeStyle = palette.border;
            context.stroke();
            context.fillStyle = palette.text;
            context.fillText(text, x + 12, y + 18);
        }

        function downloadRecapAttendanceImage() {
            var captureArea = document.getElementById('recapAttendanceCaptureArea');
            var table = document.getElementById('recapAttendanceCaptureTable');

            if (!captureArea || !table) {
                return;
            }

            var title = captureArea.dataset.captureTitle || 'Attendance Logs';
            var subtitle = captureArea.dataset.captureSubtitle || '';
            var headers = Array.prototype.slice.call(table.querySelectorAll('thead th'))
                .map(normalizedTableText);
            var columnWidths = [240, 130, 130, 260, 360, 160, 120];
            var margin = 48;
            var tableWidth = columnWidths.reduce(function (total, width) {
                return total + width;
            }, 0);
            var canvasWidth = tableWidth + (margin * 2);
            var scratchCanvas = document.createElement('canvas');
            var scratchContext = scratchCanvas.getContext('2d');
            scratchContext.font = '14px Arial';

            var rows = Array.prototype.slice.call(table.querySelectorAll('tbody tr')).map(function (row) {
                var cells = Array.prototype.slice.call(row.querySelectorAll('td'));
                var emptyCell = cells.find(function (cell) {
                    return cell.hasAttribute('colspan');
                });

                if (emptyCell) {
                    return {
                        empty: normalizedTableText(emptyCell),
                    };
                }

                return {
                    cells: cells.map(captureCellPayload),
                };
            });

            var headerHeight = 46;
            var titleHeight = 118;
            var rowLayouts = rows.map(function (row) {
                if (row.empty) {
                    return {
                        empty: row.empty,
                        height: 64,
                    };
                }

                var linesByCell = row.cells.map(function (cell, index) {
                    return wrapCanvasText(scratchContext, cell.text, columnWidths[index] - 28);
                });
                var maxLines = linesByCell.reduce(function (highest, lines) {
                    return Math.max(highest, lines.length);
                }, 1);

                return {
                    linesByCell: linesByCell,
                    height: Math.max(54, 24 + (maxLines * 20)),
                };
            });
            var tableHeight = headerHeight + rowLayouts.reduce(function (total, row) {
                return total + row.height;
            }, 0);
            var canvasHeight = titleHeight + tableHeight + margin;
            var scale = Math.max(1, window.devicePixelRatio || 1);
            var canvas = document.createElement('canvas');
            canvas.width = canvasWidth * scale;
            canvas.height = canvasHeight * scale;
            canvas.style.width = canvasWidth + 'px';
            canvas.style.height = canvasHeight + 'px';

            var context = canvas.getContext('2d');
            context.scale(scale, scale);
            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, canvasWidth, canvasHeight);

            context.fillStyle = '#0f172a';
            context.font = '700 28px Arial';
            context.fillText(title, margin, 48);
            context.fillStyle = '#334155';
            context.font = '600 18px Arial';
            context.fillText(subtitle, margin, 82);

            var tableX = margin;
            var tableY = titleHeight;
            drawRoundedRectangle(context, tableX, tableY, tableWidth, tableHeight, 12);
            context.fillStyle = '#ffffff';
            context.fill();
            context.strokeStyle = '#e5e7eb';
            context.lineWidth = 1;
            context.stroke();

            context.save();
            drawRoundedRectangle(context, tableX, tableY, tableWidth, tableHeight, 12);
            context.clip();

            context.fillStyle = '#f8fafc';
            context.fillRect(tableX, tableY, tableWidth, headerHeight);
            context.strokeStyle = '#e5e7eb';
            context.beginPath();
            context.moveTo(tableX, tableY + headerHeight);
            context.lineTo(tableX + tableWidth, tableY + headerHeight);
            context.stroke();

            var x = tableX;
            context.fillStyle = '#0f172a';
            context.font = '700 14px Arial';
            headers.forEach(function (header, index) {
                context.fillText(header, x + 14, tableY + 29);
                x += columnWidths[index];
            });

            var y = tableY + headerHeight;
            context.font = '14px Arial';
            rowLayouts.forEach(function (layout, rowIndex) {
                context.fillStyle = rowIndex % 2 === 0 ? '#ffffff' : '#f8fafc';
                context.fillRect(tableX, y, tableWidth, layout.height);
                context.strokeStyle = '#e5e7eb';
                context.beginPath();
                context.moveTo(tableX, y + layout.height);
                context.lineTo(tableX + tableWidth, y + layout.height);
                context.stroke();

                if (layout.empty) {
                    context.fillStyle = '#64748b';
                    context.font = '14px Arial';
                    context.textAlign = 'center';
                    context.fillText(layout.empty, tableX + (tableWidth / 2), y + 38);
                    context.textAlign = 'start';
                    y += layout.height;
                    return;
                }

                x = tableX;
                layout.linesByCell.forEach(function (lines, columnIndex) {
                    var cell = rows[rowIndex].cells[columnIndex];
                    var palette = captureTonePalette(cell.tone);
                    context.fillStyle = columnIndex === 0 ? '#334155' : '#475569';
                    context.font = columnIndex === 0 ? '700 14px Arial' : '14px Arial';

                    if (palette && lines.length === 1) {
                        drawCaptureBadge(context, lines[0], palette, x + 14, y + 14, columnWidths[columnIndex] - 28);
                        x += columnWidths[columnIndex];
                        return;
                    }

                    if (palette) {
                        context.fillStyle = palette.text;
                    }

                    lines.forEach(function (line, lineIndex) {
                        context.fillText(line, x + 14, y + 28 + (lineIndex * 20));
                    });
                    x += columnWidths[columnIndex];
                });
                y += layout.height;
            });

            context.restore();

            var link = document.createElement('a');
            var filename = title.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '') || 'attendance-logs';
            link.download = filename + '.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
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

        var captureButton = document.getElementById('recapAttendanceCaptureButton');
        if (captureButton) {
            captureButton.addEventListener('click', downloadRecapAttendanceImage);
        }

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
                var employeeDetailBaseUrl = @json(url('/admin-attendance/recap-attendance'));
                var monthlyTable = $('#recapMonthlyTable').DataTable({
                    ajax: {
                        url: @json(route('admin-attendance.recap.monthly-datatable')),
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
