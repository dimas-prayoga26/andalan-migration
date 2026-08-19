@extends('layouts.main')

@section('title', 'PIC Task')

@section('navbarTitle', 'Task')

@section('content')
    @php
        $picTaskStaffOptions = collect($picTaskStaffOptions ?? []);
    @endphp

    @include('pic_attendance.layout.navbar')

    <div class="card">
        <div class="card-header border-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <h4 class="card-title mb-0">Task Monitoring</h4>
            <div class="d-flex align-items-center gap-2">
                <label for="picTaskStaffFilter" class="mb-0 text-muted fs-13">Staff</label>
                <select id="picTaskStaffFilter" name="staff_filter" class="form-select form-select-sm w-auto">
                    <option value="" selected disabled>Pilih Staff</option>
                    @forelse ($picTaskStaffOptions as $staffOption)
                        <option value="{{ $staffOption['id'] }}">{{ $staffOption['name'] }}</option>
                    @empty
                        <option value="" disabled>Tidak ada staff</option>
                    @endforelse
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="picTaskTable" class="table table-sm align-middle mb-0">
                    <colgroup>
                        <col style="width: 20%;">
                        <col style="width: 42%;">
                        <col style="width: 18%;">
                        <col style="width: 12%;">
                        <col style="width: 8%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Staff</th>
                            <th>Task</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="picTaskDetailModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Task Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row py-2">
                        <div class="col-4"><span>Task Name</span></div>
                        <div class="col-8"><span class="text-gray fw-semibold" id="picTaskDetailTitle">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Task Description</span></div>
                        <div class="col-8"><span class="text-gray fw-normal" id="picTaskDetailDescription">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Date - Due Date</span></div>
                        <div class="col-8"><span class="text-gray fw-semibold" id="picTaskDetailDueDate">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Attachment</span></div>
                        <div class="col-8"><span class="text-gray fw-semibold" id="picTaskDetailAttachment">No attachment</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Blockers</span></div>
                        <div class="col-8"><span class="text-gray fw-normal" id="picTaskDetailBlockers">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Task Category</span></div>
                        <div class="col-8">
                            <span class="text-gray fw-semibold" id="picTaskDetailCategory">-</span><br>
                            <span class="text-gray fw-normal" id="picTaskDetailProject">-</span>
                        </div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Assigned by</span></div>
                        <div class="col-8"><span class="text-primary fw-semibold" id="picTaskDetailAssignedBy">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Task Status</span></div>
                        <div class="col-8"><span class="fw-semibold" id="picTaskDetailStatus">-</span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                </div>
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
            if (!window.jQuery || !window.jQuery.fn.DataTable) {
                return;
            }

            jQuery(function ($) {
                var staffFilter = document.getElementById('picTaskStaffFilter');
                var escapeHtml = function (value) {
                    return String(value || '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                };
                var nullableText = function (value, fallback) {
                    var normalizedValue = String(value || '').trim();

                    return normalizedValue !== '' ? normalizedValue : (fallback || '-');
                };
                var renderAttachment = function (value) {
                    var normalizedValue = String(value || '').trim();
                    if (normalizedValue === '') {
                        return 'No attachment';
                    }

                    return '<a href="' + escapeHtml(normalizedValue) + '" class="text-primary" target="_blank" rel="noopener noreferrer">Open attachment</a>';
                };
                var statusTextClass = function (value) {
                    return value === 'success' ? 'text-success' : 'text-warning';
                };
                var assignedByText = function (value) {
                    var normalizedValue = nullableText(value, 'self');

                    return normalizedValue.charAt(0) === '@' ? normalizedValue : '@' + normalizedValue;
                };

                var taskTable = $('#picTaskTable').DataTable({
                    ajax: {
                        url: @json(route('pic-attendance.task.datatable')),
                        data: function (requestData) {
                            requestData.staff = staffFilter ? staffFilter.value : '';
                        },
                        dataSrc: 'data'
                    },
                    autoWidth: false,
                    searching: false,
                    pageLength: 10,
                    lengthChange: false,
                    paging: true,
                    bInfo: true,
                    order: [],
                    columns: [
                        {
                            data: 'staff',
                            render: function (data) {
                                return '<span class="fw-semibold">' + escapeHtml(data) + '</span>';
                            }
                        },
                        {
                            data: null,
                            render: function (row) {
                                return '<span class="fw-semibold text-black">' + escapeHtml(row.task) + '</span>'
                                    + '<div class="text-muted fs-13">' + escapeHtml(row.project) + '</div>'
                                    + '<div class="text-muted fs-13">Assign by : <span class="fw-semibold">' + escapeHtml(row.assigned_by) + '</span></div>';
                            }
                        },
                        {
                            data: 'due_date',
                            defaultContent: '-'
                        },
                        {
                            data: null,
                            render: function (row) {
                                return '<span class="badge badge-' + escapeHtml(row.status_class) + ' light">' + escapeHtml(row.status) + '</span>';
                            }
                        },
                        {
                            data: null,
                            searchable: false,
                            orderable: false,
                            render: function () {
                                return '<button type="button" class="btn btn-xs btn-primary light pic-task-detail-button" data-bs-toggle="modal" data-bs-target="#picTaskDetailModal">Detail</button>';
                            }
                        }
                    ],
                    language: {
                        emptyTable: 'No task data available.',
                        paginate: {
                            next: '<i class="fa-solid fa-angle-right"></i>',
                            previous: '<i class="fa-solid fa-angle-left"></i>'
                        }
                    },
                    drawCallback: function () {
                        $('#picTaskTable tbody td.dataTables_empty')
                            .addClass('text-center py-4 text-muted');
                    }
                });

                $('#picTaskTable tbody').on('click', '.pic-task-detail-button', function () {
                    var row = taskTable.row($(this).closest('tr')).data() || {};
                    $('#picTaskDetailTitle').text(nullableText(row.task));
                    $('#picTaskDetailDescription').text(nullableText(row.description));
                    $('#picTaskDetailCategory').text(nullableText(row.task_category));
                    $('#picTaskDetailProject').text(nullableText(row.project));
                    $('#picTaskDetailAssignedBy').text(assignedByText(row.assigned_by));
                    $('#picTaskDetailDueDate').text(nullableText(row.due_date));
                    $('#picTaskDetailBlockers').text(nullableText(row.blockers));
                    $('#picTaskDetailStatus')
                        .removeClass('text-danger text-success text-warning')
                        .addClass(statusTextClass(row.status_class))
                        .text(nullableText(row.status));
                    $('#picTaskDetailAttachment').html(renderAttachment(row.attachment_path));
                });

                if (staffFilter) {
                    staffFilter.addEventListener('change', function () {
                        taskTable.ajax.reload();
                    });
                }
            });
        })();
    </script>
@endsection
