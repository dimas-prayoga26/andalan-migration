@extends('layouts.main')

@section('title', 'Director Task')

@section('navbarTitle', 'Task')

@section('content')
    @php
        $directorTaskCompanyOptions = collect($directorTaskCompanyOptions ?? []);
        $directorTaskStaffOptions = collect($directorTaskStaffOptions ?? []);
    @endphp

    @include('director_attendance.layout.navbar')

    <div class="card">
        <div class="card-header border-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <h4 class="card-title mb-0">Task Monitoring</h4>
            <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <label for="directorTaskCompanyFilter" class="mb-0 text-muted fs-13">Company</label>
                    <select id="directorTaskCompanyFilter" name="company_filter" class="form-select form-select-sm w-auto">
                        <option value="" selected>Semua Company</option>
                        @forelse ($directorTaskCompanyOptions as $companyOption)
                            <option value="{{ $companyOption['id'] }}">{{ $companyOption['name'] }}</option>
                        @empty
                            <option value="" disabled>Tidak ada company</option>
                        @endforelse
                    </select>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label for="directorTaskStaffFilter" class="mb-0 text-muted fs-13">Staff</label>
                    <select id="directorTaskStaffFilter" name="staff_filter" class="form-select form-select-sm w-auto">
                        <option value="" selected>Semua Staff</option>
                        @forelse ($directorTaskStaffOptions as $staffOption)
                            <option value="{{ $staffOption['id'] }}" data-company-id="{{ $staffOption['company_id'] }}">{{ $staffOption['name'] }}</option>
                        @empty
                            <option value="" disabled>Tidak ada staff</option>
                        @endforelse
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="directorTaskTable" class="table table-sm align-middle mb-0">
                    <colgroup>
                        <col style="width: 18%;">
                        <col style="width: 34%;">
                        <col style="width: 16%;">
                        <col style="width: 14%;">
                        <col style="width: 10%;">
                        <col style="width: 8%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Staff</th>
                            <th>Task</th>
                            <th>Company</th>
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

    <div class="modal fade" id="directorTaskDetailModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Task Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row py-2">
                        <div class="col-4"><span>Task Name</span></div>
                        <div class="col-8"><span class="text-gray fw-semibold" id="directorTaskDetailTitle">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Task Description</span></div>
                        <div class="col-8"><span class="text-gray fw-normal" id="directorTaskDetailDescription">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Date - Due Date</span></div>
                        <div class="col-8"><span class="text-gray fw-semibold" id="directorTaskDetailDueDate">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Attachment</span></div>
                        <div class="col-8"><span class="text-gray fw-semibold" id="directorTaskDetailAttachment">No attachment</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Blockers</span></div>
                        <div class="col-8"><span class="text-gray fw-normal" id="directorTaskDetailBlockers">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Task Category</span></div>
                        <div class="col-8">
                            <span class="text-gray fw-semibold" id="directorTaskDetailCategory">-</span><br>
                            <span class="text-gray fw-normal" id="directorTaskDetailProject">-</span>
                        </div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Assigned by</span></div>
                        <div class="col-8"><span class="text-primary fw-semibold" id="directorTaskDetailAssignedBy">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Task Status</span></div>
                        <div class="col-8"><span class="fw-semibold" id="directorTaskDetailStatus">-</span></div>
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
        function initDirectorTaskTable() {
            if (!window.jQuery || !window.jQuery.fn.DataTable) {
                return;
            }

            jQuery(function ($) {
                var companyFilter = document.getElementById('directorTaskCompanyFilter');
                var staffFilter = document.getElementById('directorTaskStaffFilter');
                var allStaffOptions = @js($directorTaskStaffOptions->values()->all());

                function escapeHtml(value) {
                    return String(value || '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                function nullableText(value, fallback) {
                    var normalizedValue = String(value || '').trim();

                    return normalizedValue !== '' ? normalizedValue : (fallback || '-');
                }

                function renderAttachment(value) {
                    var normalizedValue = String(value || '').trim();
                    if (normalizedValue === '') {
                        return 'No attachment';
                    }

                    return '<a href="' + escapeHtml(normalizedValue) + '" class="text-primary" target="_blank" rel="noopener noreferrer">Open attachment</a>';
                }

                function statusTextClass(value) {
                    return value === 'success' ? 'text-success' : 'text-warning';
                }

                function assignedByText(value) {
                    var normalizedValue = nullableText(value, 'self');

                    return normalizedValue.charAt(0) === '@' ? normalizedValue : '@' + normalizedValue;
                }

                function refreshSelectPlugin(selectElement) {
                    var select = $(selectElement);

                    if ($.fn.selectpicker && select.data('selectpicker')) {
                        select.selectpicker('refresh');
                    }
                }

                function refreshStaffOptions() {
                    if (!companyFilter || !staffFilter) {
                        return;
                    }

                    var selectedCompanyId = companyFilter.value || '';
                    var selectedStaffId = staffFilter.value || '';
                    var visibleStaffOptions = allStaffOptions.filter(function (staffOption) {
                        return selectedCompanyId === '' || String(staffOption.company_id || '') === selectedCompanyId;
                    });

                    staffFilter.innerHTML = '';

                    var allStaffOption = document.createElement('option');
                    allStaffOption.value = '';
                    allStaffOption.textContent = 'Semua Staff';
                    staffFilter.appendChild(allStaffOption);

                    visibleStaffOptions.forEach(function (staffOption) {
                        var option = document.createElement('option');
                        option.value = String(staffOption.id || '');
                        option.textContent = String(staffOption.name || 'Unknown Staff');
                        option.setAttribute('data-company-id', String(staffOption.company_id || ''));
                        staffFilter.appendChild(option);
                    });

                    var selectedStaffIsVisible = visibleStaffOptions.some(function (staffOption) {
                        return String(staffOption.id || '') === selectedStaffId;
                    });

                    staffFilter.value = selectedStaffIsVisible ? selectedStaffId : '';
                    refreshSelectPlugin(staffFilter);
                }

                var taskTable = $('#directorTaskTable').DataTable({
                    ajax: {
                        url: @json(route('director-attendance.task.datatable')),
                        data: function (requestData) {
                            requestData.company_id = companyFilter ? companyFilter.value : '';
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
                            data: 'company',
                            defaultContent: '-'
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
                                return '<button type="button" class="btn btn-xs btn-primary light director-task-detail-button" data-bs-toggle="modal" data-bs-target="#directorTaskDetailModal">Detail</button>';
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
                        $('#directorTaskTable tbody td.dataTables_empty')
                            .addClass('text-center py-4 text-muted');
                    }
                });

                $('#directorTaskTable tbody').on('click', '.director-task-detail-button', function () {
                    var row = taskTable.row($(this).closest('tr')).data() || {};
                    $('#directorTaskDetailTitle').text(nullableText(row.task));
                    $('#directorTaskDetailDescription').text(nullableText(row.description));
                    $('#directorTaskDetailCategory').text(nullableText(row.task_category));
                    $('#directorTaskDetailProject').text(nullableText(row.project));
                    $('#directorTaskDetailAssignedBy').text(assignedByText(row.assigned_by));
                    $('#directorTaskDetailDueDate').text(nullableText(row.due_date));
                    $('#directorTaskDetailBlockers').text(nullableText(row.blockers));
                    $('#directorTaskDetailStatus')
                        .removeClass('text-danger text-success text-warning')
                        .addClass(statusTextClass(row.status_class))
                        .text(nullableText(row.status));
                    $('#directorTaskDetailAttachment').html(renderAttachment(row.attachment_path));
                });

                if (companyFilter) {
                    companyFilter.addEventListener('change', function () {
                        refreshStaffOptions();
                        taskTable.ajax.reload();
                    });
                }

                if (staffFilter) {
                    staffFilter.addEventListener('change', function () {
                        taskTable.ajax.reload();
                    });
                }

                refreshStaffOptions();
            });
        }

        initDirectorTaskTable();
    </script>
@endsection
