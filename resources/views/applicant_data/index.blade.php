@extends('layouts.main')

@section('title', 'Applicants')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <style>
        .talent-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: thin;
            gap: 0.25rem;
            white-space: nowrap;
        }

        .talent-tabs .nav-link {
            border: 0;
            background: transparent;
            white-space: nowrap;
        }

        .talent-header-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.85rem;
            border-bottom: 1px solid #eef0f4;
        }

        .talent-table-title {
            color: #25314c;
            font-size: 1rem;
            font-weight: 700;
        }

        .talent-filter-bar {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .talent-filter-bar label {
            margin: 0;
            color: #5f6b7a;
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
        }

        .talent-filter-bar select {
            min-height: 38px;
            width: 220px;
            max-width: 220px;
            border: 1px solid #d9dce5;
            border-radius: 0.5rem;
            background: #fff;
            color: #27334a;
            padding: 0.35rem 0.75rem;
        }

        .talent-status-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin: 0 0 1rem;
        }

        .talent-status-tab {
            min-height: 36px;
            border: 1px solid #d9dce5;
            border-radius: 0.45rem;
            background: #fff;
            color: #5f6b7a;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 0.35rem 0.75rem;
        }

        .talent-status-tab.active {
            border-color: #1d4ed8;
            background: #e8eefc;
            color: #1239b3;
        }

        .talent-photo {
            width: 34px;
            height: 34px;
            border-radius: 0.45rem;
            background: #e5e7eb;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            vertical-align: middle;
        }

        .talent-photo img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .talent-status-form {
            display: inline-flex;
            vertical-align: middle;
        }

        .talent-status-select {
            min-height: 28px;
            border: 1px solid #d9dce5;
            border-radius: 0.35rem;
            background: #fff;
            color: #27334a;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 0.15rem 1.75rem 0.15rem 0.5rem;
        }

        .talent-status-select.status-value-0 {
            background: #f3f4f6;
            border-color: #d1d5db;
            color: #4b5563;
        }

        .talent-status-select.status-value-1 {
            background: #eef2ff;
            border-color: #c7d2fe;
            color: #2448c7;
        }

        .talent-status-select.status-value-2 {
            background: #fff7ed;
            border-color: #fed7aa;
            color: #c2410c;
        }

        .talent-status-select.status-value-3 {
            background: #ecfeff;
            border-color: #a5f3fc;
            color: #0e7490;
        }

        .talent-status-select.status-value-4 {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #047857;
        }

        .talent-status-select.status-value-5 {
            background: #fff1f2;
            border-color: #fecdd3;
            color: #be123c;
        }

        .talent-action-group {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .talent-action-btn {
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 0.35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .talent-action-btn.view {
            background: #d9f2f4;
            color: #287b84;
        }

        .talent-action-btn.file {
            background: #f8e8b7;
            color: #b77900;
        }

        .talent-action-btn.delete {
            background: #ffe1e6;
            color: #c0263d;
        }

        #applicantsTable thead th {
            font-size: 1rem;
            font-weight: 600;
            padding: 1rem 0.75rem;
        }

        #applicantsTable tbody td {
            font-size: 0.95rem;
            padding: 0.9rem 0.75rem;
            vertical-align: top;
        }

        #applicantsTable thead th:first-child,
        #applicantsTable tbody td:first-child {
            text-align: center !important;
        }

        #applicantsTable_wrapper .dt-layout-row:first-child {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin: 0 0 1rem;
        }

        #applicantsTable_wrapper .dataTables_length,
        #applicantsTable_wrapper .dataTables_filter {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            margin-bottom: 1rem;
        }

        #applicantsTable_wrapper .dataTables_filter {
            float: right;
        }

        #applicantsTable_wrapper .dt-layout-row:first-child .dt-layout-cell {
            display: flex;
            align-items: center;
            width: auto;
        }

        #applicantsTable_wrapper .dt-layout-row:first-child .dt-layout-cell:first-child {
            justify-content: flex-start;
        }

        #applicantsTable_wrapper .dt-layout-row:first-child .dt-layout-cell:last-child {
            justify-content: flex-end;
        }

        #applicantsTable_wrapper .dt-length,
        #applicantsTable_wrapper .dt-search,
        #applicantsTable_wrapper .dataTables_length label,
        #applicantsTable_wrapper .dataTables_filter label {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            margin: 0;
        }

        #applicantsTable_wrapper .dt-length label,
        #applicantsTable_wrapper .dt-search label,
        #applicantsTable_wrapper .dataTables_length label,
        #applicantsTable_wrapper .dataTables_filter label {
            margin: 0;
            color: #5f6b7a;
            font-weight: 600;
        }

        #applicantsTable_wrapper .dt-length select,
        #applicantsTable_wrapper .dt-search input,
        #applicantsTable_wrapper .dataTables_length select,
        #applicantsTable_wrapper .dataTables_filter input {
            min-height: 38px;
            border: 1px solid #d9dce5;
            border-radius: 0.5rem;
            background: #fff;
            color: #27334a;
            box-shadow: none;
        }

        #applicantsTable_wrapper .dt-search input,
        #applicantsTable_wrapper .dataTables_filter input {
            width: 220px;
            max-width: 100%;
            padding: 0.35rem 0.75rem;
        }

        #applicantsTable_wrapper .dt-length select,
        #applicantsTable_wrapper .dataTables_length select {
            padding: 0.35rem 2rem 0.35rem 0.75rem;
        }

        @media only screen and (max-width: 767.98px) {
            .talent-header-bar {
                align-items: stretch;
                flex-direction: column;
            }

            .talent-filter-bar {
                justify-content: space-between;
                width: 100%;
            }

            .talent-filter-bar select {
                width: 100%;
                max-width: 100%;
            }

            #applicantsTable_wrapper .dt-layout-row:first-child {
                align-items: stretch;
                flex-direction: column;
            }

            #applicantsTable_wrapper .dt-layout-row:first-child .dt-layout-cell,
            #applicantsTable_wrapper .dt-length,
            #applicantsTable_wrapper .dt-search,
            #applicantsTable_wrapper .dataTables_length,
            #applicantsTable_wrapper .dataTables_filter,
            #applicantsTable_wrapper .dataTables_length label,
            #applicantsTable_wrapper .dataTables_filter label {
                justify-content: space-between;
                width: 100%;
            }

            #applicantsTable_wrapper .dataTables_filter {
                float: none;
            }

            #applicantsTable_wrapper .dt-search input,
            #applicantsTable_wrapper .dataTables_filter input {
                width: 100%;
            }
        }
    </style>
@endsection

@section('navbarTitle', 'Applicants')

@section('content')
<div class="page-title">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li><h1>Applicants</h1></li>
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Main</a></li>
            <li class="breadcrumb-item active" aria-current="page">Applicants</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card h-auto">
            <div class="card-header">
                <ul class="nav nav-underline card-header-tabs talent-tabs" role="tablist">
                    <li class="nav-item">
                        <a href="{{ route('applicant') }}" class="nav-link {{ request()->routeIs('applicant') ? 'active' : '' }}">Applicants</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('applicant.job_vacancies') }}" class="nav-link {{ request()->routeIs('applicant.job_vacancies') ? 'active' : '' }}">Job Vacancies</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success mb-3" role="alert">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger mb-3" role="alert">{{ $errors->first() }}</div>
                @endif

                <div class="talent-header-bar">
                    <div class="talent-table-title">Data Pelamar</div>
                    <div class="talent-filter-bar">
                        <label for="positionFilter">Filter Posisi:</label>
                        <select id="positionFilter">
                            <option value="">Semua Posisi</option>
                            @foreach ($jobVacancies as $jobVacancy)
                                <option value="{{ $jobVacancy->name }}">{{ $jobVacancy->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="talent-status-tabs" role="tablist" aria-label="Filter status pelamar">
                    @foreach ($applicantStatuses as $applicantStatus)
                        <button type="button" class="talent-status-tab" data-status-value="{{ $applicantStatus->value }}" role="tab" aria-selected="false">
                            {{ $applicantStatus->name }}
                        </button>
                    @endforeach
                </div>

                <div class="table-responsive">
                    <table id="applicantsTable" class="display table">
                        <thead>
                        <tr>
                            <th class="mw-80">No</th>
                            <th class="mw-100">Photo</th>
                            <th class="mw-220">Nama Lengkap</th>
                            <th class="mw-220">Posisi Dilamar</th>
                            <th class="mw-420">Keterangan</th>
                            <th class="mw-120">Action</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
        $dataTablesJsPath = public_path('assets/vendor/datatables/js/jquery.dataTables.bundle.min.js');
        $dataTablesJsVersion = file_exists($dataTablesJsPath) ? filemtime($dataTablesJsPath) : time();
    @endphp
    <script src="{{ asset('assets/vendor/datatables/js/jquery.dataTables.bundle.min.js') }}?v={{ $dataTablesJsVersion }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script>
        var applicantStatuses = @json($applicantStatuses->map(fn ($status) => ['id' => (string) $status->id, 'value' => (int) $status->value, 'name' => (string) $status->name])->values());
        var csrfToken = @json(csrf_token());
        var applicantStatusUpdateUrlTemplate = @json(route('applicant.status.update', ['applicant' => '__APPLICANT_ID__']));
        var applicantShowUrlTemplate = @json(route('applicant.show', ['applicant' => '__APPLICANT_ID__']));
        var applicantDestroyUrlTemplate = @json(route('applicant.destroy', ['applicant' => '__APPLICANT_ID__']));

        function escapeHtml(value) {
            return $('<div>').text(value === null || value === undefined ? '' : String(value)).html();
        }

        function applicantUrl(template, applicantId) {
            return template.replace('__APPLICANT_ID__', encodeURIComponent(applicantId));
        }

        function updateApplicantStatusColor(selectElement) {
            var selectedOption = selectElement.options[selectElement.selectedIndex];
            var statusValue = selectedOption ? selectedOption.dataset.statusValue : '0';

            selectElement.classList.remove('status-value-0', 'status-value-1', 'status-value-2', 'status-value-3', 'status-value-4', 'status-value-5');
            selectElement.classList.add('status-value-' + statusValue);
        }

        function renderApplicantPhoto(applicant) {
            var title = escapeHtml(applicant.photo || 'No photo');

            if (!applicant.photo_url) {
                return '<span class="talent-photo" title="' + title + '"><i class="bi bi-person-fill"></i></span>';
            }

            return '<span class="talent-photo" title="' + title + '">'
                + '<img src="' + escapeHtml(applicant.photo_url) + '" alt="' + escapeHtml(applicant.full_name) + '" loading="lazy" decoding="async" onload="this.style.display=\'block\'; this.nextElementSibling.style.display=\'none\';" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'inline-block\';">'
                + '<i class="bi bi-person-fill" style="display: none;"></i>'
                + '</span>';
        }

        function renderApplicantStatus(applicant) {
            var options = applicantStatuses.map(function (status) {
                return '<option value="' + escapeHtml(status.id) + '" data-status-value="' + status.value + '"' + (String(applicant.applicant_status_id) === String(status.id) ? ' selected' : '') + '>'
                    + escapeHtml(status.name)
                    + '</option>';
            }).join('');

            return '<form method="POST" action="' + applicantUrl(applicantStatusUpdateUrlTemplate, applicant.id) + '" class="talent-status-form">'
                + '<input type="hidden" name="_token" value="' + escapeHtml(csrfToken) + '">'
                + '<input type="hidden" name="_method" value="PATCH">'
                + '<select name="applicant_status_id" class="talent-status-select status-value-' + Number(applicant.applicant_status_value || 0) + '" onchange="updateApplicantStatusColor(this); this.form.submit()" aria-label="Update status ' + escapeHtml(applicant.full_name) + '">'
                + options
                + '</select>'
                + '</form>';
        }

        function renderApplicantAction(applicant) {
            return '<div class="talent-action-group">'
                + '<a href="' + applicantUrl(applicantShowUrlTemplate, applicant.id) + '" class="talent-action-btn view" title="Detail"><i class="bi bi-eye"></i></a>'
                + '<form method="POST" action="' + applicantUrl(applicantDestroyUrlTemplate, applicant.id) + '" onsubmit="return confirm(\'Hapus data pelamar ini?\')">'
                + '<input type="hidden" name="_token" value="' + escapeHtml(csrfToken) + '">'
                + '<input type="hidden" name="_method" value="DELETE">'
                + '<button type="submit" class="talent-action-btn delete" title="Delete"><i class="bi bi-trash"></i></button>'
                + '</form>'
                + '</div>';
        }

        $(function () {
            var selectedApplicantStatus = '';

            var applicantsTable = $('#applicantsTable').DataTable({
                ajax: {
                    url: "{{ route('applicant.datatable') }}",
                    dataSrc: 'data'
                },
                order: [],
                columns: [
                    {
                        data: null,
                        searchable: false,
                        orderable: false,
                        render: function (data, type, row, meta) {
                            return (meta.row + meta.settings._iDisplayStart + 1) + '.';
                        }
                    },
                    {
                        data: null,
                        searchable: false,
                        orderable: false,
                        render: function (data, type, row) {
                            return renderApplicantPhoto(row);
                        }
                    },
                    { data: 'full_name' },
                    { data: 'job_vacancy_name' },
                    {
                        data: null,
                        render: function (data, type, row) {
                            return renderApplicantStatus(row);
                        }
                    },
                    {
                        data: null,
                        searchable: false,
                        orderable: false,
                        render: function (data, type, row) {
                            return renderApplicantAction(row);
                        }
                    }
                ],
                columnDefs: [
                    { targets: [0, 1, 5], orderable: false }
                ]
            });

            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                if (settings.nTable.id !== 'applicantsTable' || !selectedApplicantStatus) {
                    return true;
                }

                var applicant = settings.aoData[dataIndex] ? settings.aoData[dataIndex]._aData : null;

                return applicant && String(applicant.applicant_status_value) === selectedApplicantStatus;
            });

            $('.talent-status-tab').on('click', function () {
                var $tab = $(this);
                var statusValue = String($tab.data('status-value'));
                var isActive = $tab.hasClass('active');

                selectedApplicantStatus = isActive ? '' : statusValue;

                $('.talent-status-tab')
                    .removeClass('active')
                    .attr('aria-selected', 'false');

                if (!isActive) {
                    $tab.addClass('active').attr('aria-selected', 'true');
                }

                applicantsTable.draw();
            });

            $('#positionFilter').on('change', function () {
                var selectedPosition = $(this).val();
                var escapedPosition = $.fn.dataTable.util.escapeRegex(selectedPosition);

                applicantsTable
                    .column(3)
                    .search(selectedPosition ? '^' + escapedPosition + '$' : '', true, false)
                    .draw();
            });
        });
    </script>
@endsection
