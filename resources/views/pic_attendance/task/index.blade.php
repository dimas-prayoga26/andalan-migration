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
                        <col style="width: 22%;">
                        <col style="width: 44%;">
                        <col style="width: 20%;">
                        <col style="width: 14%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Staff</th>
                            <th>Task</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
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

                if (staffFilter) {
                    staffFilter.addEventListener('change', function () {
                        taskTable.ajax.reload();
                    });
                }
            });
        })();
    </script>
@endsection
