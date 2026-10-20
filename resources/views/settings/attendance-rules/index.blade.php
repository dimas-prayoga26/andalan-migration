@extends('layouts.main')

@section('title', 'Setting - '.$pageTitle)
@section('navbarTitle', 'Setting')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}">
    <style>
        .settings-nav-card,
        .settings-table-card {
            border-radius: 8px;
        }

        .settings-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: thin;
            white-space: nowrap;
        }

        .settings-tabs .nav-link {
            border: 0;
            border-bottom: 3px solid transparent;
            color: #6b7280;
            background: transparent;
        }

        .settings-tabs .nav-link.active {
            color: var(--bs-primary);
            border-bottom-color: var(--bs-primary);
            background: transparent;
        }

        .settings-list-actions {
            display: grid;
            grid-template-columns: minmax(240px, 320px) auto;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .settings-list-actions .btn {
            min-height: 42px;
            white-space: nowrap;
        }

        .settings-table-footer.dataTables_wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 12px 30px;
            border-top: 1px solid var(--bs-border-color);
        }

        .settings-table-footer.dataTables_wrapper .dataTables_info,
        .settings-table-footer.dataTables_wrapper .dataTables_paginate {
            float: none;
            padding: 0;
        }

        .settings-table-footer .paginate_button.disabled {
            pointer-events: none;
            opacity: .35;
        }

        #datatable_wrapper .dataTables_filter,
        #datatable_wrapper .dataTables_length {
            display: none;
        }

        @media (max-width: 767.98px) {
            .settings-table-footer.dataTables_wrapper {
                flex-direction: column;
                align-items: stretch;
                padding: 12px 16px;
            }

            .settings-table-footer.dataTables_wrapper .dataTables_info,
            .settings-table-footer.dataTables_wrapper .dataTables_paginate {
                text-align: center;
            }
        }

        @media (max-width: 575.98px) {
            .settings-list-actions {
                grid-template-columns: minmax(0, 1fr);
                width: 100%;
            }

            .settings-list-actions .btn {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Setting',
    'current' => $pageTitle,
    'homeRoute' => 'dashboard',
])

@include('settings.partials.nav')

<div class="card settings-table-card">
    <div class="card-header border-0 flex-wrap gap-3">
        <div>
            <h4 class="card-title mb-1">{{ $pageTitle }}</h4>
            <p class="mb-0 text-muted fs-13">Manage attendance time, IP, and location radius rules.</p>
        </div>
        <div class="settings-list-actions">
            <div class="input-group">
                <span class="input-group-text bg-white" aria-hidden="true">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input
                    id="attendanceRulesTableSearch"
                    type="search"
                    class="form-control"
                    placeholder="Search attendance rules"
                    autocomplete="off"
                    aria-label="Search attendance rules"
                >
            </div>
            <a href="{{ route('settings.attendance-rules.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>Add Rule
            </a>
        </div>
    </div>

    @include('partials.swal-alert', ['type' => 'success', 'message' => session('status')])

    @include('partials.swal-alert', ['type' => 'error', 'message' => session('error')])

    <div class="card-body table-card-body p-0">
        <div class="table-responsive">
            <table id="datatable" class="table table-bordered text-nowrap w-100"></table>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script src="{{ asset('assets/vendor/datatables/js/jquery.dataTables.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        var csrfToken = @json(csrf_token());
        var deleteUrlTemplate = @json(route('settings.attendance-rules.destroy', ['attendanceRule' => '__ID__']));

        function deleteData(id) {
            var attendanceRule = $('#datatable').DataTable().rows().data().toArray().find(function (row) {
                return String(row.id) === String(id);
            });

            Swal.fire({
                title: 'Delete Attendance Rule',
                text: 'Delete the attendance rule for ' + (attendanceRule ? attendanceRule.office_location_name : 'this work location') + '?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: deleteUrlTemplate.replace('__ID__', encodeURIComponent(id)),
                    type: 'DELETE',
                    dataType: 'json',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                }).done(function (response) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message });
                    $('#datatable').DataTable().ajax.reload(null, false);
                }).fail(function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Attendance rule gagal dihapus.' });
                });
            });
        }

        $(function () {
            if (!window.jQuery || !jQuery.fn.DataTable) {
                return;
            }

            var editUrlTemplate = @json(route('settings.attendance-rules.edit', ['attendanceRule' => '__ID__']));

            function escapeHtml(value) {
                return String(value === null || value === undefined ? '' : value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function routeFor(template, id) {
                return template.replace('__ID__', encodeURIComponent(id));
            }

            function isTruthy(value) {
                return value === true || value === 1 || value === '1' || value === 'true';
            }

            function workLocationLabel(row) {
                var name = escapeHtml(row.office_location_name || '-');
                var address = escapeHtml(row.office_location_address || '');
                var html = '<span class="fw-semibold text-black">' + name + '</span>';

                if (address) {
                    html += '<br><span class="text-muted fs-12">' + address + '</span>';
                }

                return html;
            }

            function attendanceTypeBadge(type) {
                var normalizedType = String(type || 'fixed').toLowerCase();
                var badgeClass = normalizedType === 'flexible' ? 'badge-info' : 'badge-primary';
                var label = normalizedType.charAt(0).toUpperCase() + normalizedType.slice(1);

                return '<span class="badge badge-sm light ' + badgeClass + '">' + escapeHtml(label) + '</span>';
            }

            function positionsLabel(value) {
                if (!value) {
                    return '<span class="text-muted">Fallback office rule</span>';
                }

                return '<span class="text-black">' + escapeHtml(value) + '</span>';
            }

            function statusBadge(isActive) {
                var active = isTruthy(isActive);
                var statusClass = active ? 'badge-success' : 'badge-danger';
                var statusLabel = active ? 'Active' : 'Inactive';

                return '<span class="badge badge-sm light ' + statusClass + '">' + statusLabel + '</span>';
            }

            function actionButtons(row) {
                var editUrl = routeFor(editUrlTemplate, row.id);

                return '' +
                    '<div class="d-inline-flex gap-1">' +
                        '<a href="' + editUrl + '" class="btn btn-primary light btn-sm">Update</a>' +
                        '<button type="button" class="btn btn-danger light btn-sm" onclick="deleteData(\'' + escapeHtml(row.id) + '\')">Delete</button>' +
                    '</div>';
            }

            var attendanceRulesTable = jQuery('#datatable').DataTable({
                ajax: '{{ route('settings.attendance-rules.datatable') }}',
                autoWidth: false,
                order: [],
                pageLength: 10,
                processing: true,
                serverSide: true,
                columns: [
                    {
                        title: 'Work Location',
                        data: null,
                        name: 'officeLocation.name',
                        orderable: false,
                        render: function (data, type, row) {
                            return workLocationLabel(row);
                        }
                    },
                    {
                        title: 'IP Range',
                        data: 'ip_range',
                        name: 'ip_range'
                    },
                    {
                        title: 'Radius',
                        data: 'radius',
                        name: 'radius'
                    },
                    {
                        title: 'Type',
                        data: 'attendance_type',
                        name: 'attendance_type',
                        render: function (data) {
                            return attendanceTypeBadge(data);
                        }
                    },
                    {
                        title: 'Positions',
                        data: 'positions_label',
                        name: 'positions.name',
                        orderable: false,
                        render: function (data) {
                            return positionsLabel(data);
                        }
                    },
                    {
                        title: 'Clock In',
                        data: 'office_start_time',
                        name: 'office_start_time'
                    },
                    {
                        title: 'Clock Out',
                        data: 'office_end_time',
                        name: 'office_end_time'
                    },
                    {
                        title: 'Status',
                        data: 'is_active',
                        name: 'is_active',
                        searchable: false,
                        render: function (data) {
                            return statusBadge(data);
                        }
                    },
                    {
                        title: 'Action',
                        data: null,
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        render: function (data, type, row) {
                            return actionButtons(row);
                        }
                    }
                ],
                language: {
                    emptyTable: 'No attendance rule data available.',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'Showing 0 to 0 of 0 entries',
                    infoFiltered: '(filtered from _MAX_ total entries)',
                    processing: 'Loading...',
                    zeroRecords: 'No matching attendance rule found.'
                }
            });

            var searchInput = document.getElementById('attendanceRulesTableSearch');
            var searchTimeout = null;

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    window.clearTimeout(searchTimeout);

                    searchTimeout = window.setTimeout(function () {
                        attendanceRulesTable.search(searchInput.value).draw();
                    }, 400);
                });
            }
        });
    </script>
    @stack('scripts')
@endsection
