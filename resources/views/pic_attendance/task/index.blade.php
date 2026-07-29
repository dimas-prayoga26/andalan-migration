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

    <div class="modal fade" id="picTaskDetailModal" tabindex="-1" aria-labelledby="picTaskDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="picTaskDetailModalLabel">Task Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row gy-3">
                        <div class="col-md-4 text-muted">Task</div>
                        <div class="col-md-8 fw-semibold text-black" id="picTaskDetailTitle">-</div>

                        <div class="col-md-4 text-muted">Description</div>
                        <div class="col-md-8" id="picTaskDetailDescription">-</div>

                        <div class="col-md-4 text-muted">Staff</div>
                        <div class="col-md-8 fw-semibold" id="picTaskDetailStaff">-</div>

                        <div class="col-md-4 text-muted">Category</div>
                        <div class="col-md-8">
                            <span id="picTaskDetailCategory">-</span>
                            <div class="text-muted fs-13" id="picTaskDetailProject">-</div>
                        </div>

                        <div class="col-md-4 text-muted">Assigned By</div>
                        <div class="col-md-8" id="picTaskDetailAssignedBy">-</div>

                        <div class="col-md-4 text-muted">Due Date</div>
                        <div class="col-md-8" id="picTaskDetailDueDate">-</div>

                        <div class="col-md-4 text-muted">Priority</div>
                        <div class="col-md-8" id="picTaskDetailPriority">-</div>

                        <div class="col-md-4 text-muted">Status</div>
                        <div class="col-md-8" id="picTaskDetailStatus">-</div>

                        <div class="col-md-4 text-muted">Blockers</div>
                        <div class="col-md-8" id="picTaskDetailBlockers">-</div>

                        <div class="col-md-4 text-muted">Attachment</div>
                        <div class="col-md-8" id="picTaskDetailAttachment">No attachment</div>
                    </div>
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

                    return '<a href="' + escapeHtml(normalizedValue) + '" target="_blank" rel="noopener" class="text-primary fw-semibold">Open attachment</a>';
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
                    $('#picTaskDetailStaff').text(nullableText(row.staff));
                    $('#picTaskDetailCategory').text(nullableText(row.task_category));
                    $('#picTaskDetailProject').text(nullableText(row.project));
                    $('#picTaskDetailAssignedBy').text(nullableText(row.assigned_by));
                    $('#picTaskDetailDueDate').text(nullableText(row.due_date));
                    $('#picTaskDetailPriority').text(nullableText(row.priority));
                    $('#picTaskDetailBlockers').text(nullableText(row.blockers));
                    $('#picTaskDetailStatus').html('<span class="badge badge-' + escapeHtml(row.status_class) + ' light">' + escapeHtml(nullableText(row.status)) + '</span>');
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
