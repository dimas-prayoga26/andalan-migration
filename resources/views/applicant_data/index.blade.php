@extends('layouts.main')

@section('title', 'Applicants')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
        $sweetAlertCssPath = public_path('assets/vendor/sweetalert2/sweetalert2.min.css');
        $sweetAlertCssVersion = file_exists($sweetAlertCssPath) ? filemtime($sweetAlertCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}?v={{ $sweetAlertCssVersion }}">
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
            align-items: center;
            gap: 0.5rem;
            margin: 0 0 1rem;
        }

        .talent-status-tab {
            --talent-status-color: #1239b3;
            --talent-status-bg: #e8eefc;
            --talent-status-border: #1d4ed8;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            min-height: 36px;
            border: 1px solid #e2e5ec;
            border-radius: 999px;
            background: #fff;
            color: #5f6b7a;
            font-size: 0.85rem;
            font-weight: 700;
            line-height: 1.2;
            padding: 0.3rem 0.4rem 0.3rem 0.85rem;
            transition: border-color 0.15s ease, background-color 0.15s ease, color 0.15s ease;
        }

        .talent-status-tab:hover {
            border-color: var(--talent-status-border);
            color: var(--talent-status-color);
        }

        .talent-status-tab.active {
            border-color: var(--talent-status-border);
            background: var(--talent-status-bg);
            color: var(--talent-status-color);
        }

        .talent-status-tab.status-value-0 {
            --talent-status-color: #4b5563;
            --talent-status-bg: #f3f4f6;
            --talent-status-border: #9ca3af;
        }

        .talent-status-tab.status-value-1 {
            --talent-status-color: #2448c7;
            --talent-status-bg: #eef2ff;
            --talent-status-border: #818cf8;
        }

        .talent-status-tab.status-value-2 {
            --talent-status-color: #c2410c;
            --talent-status-bg: #fff7ed;
            --talent-status-border: #fb923c;
        }

        .talent-status-tab.status-value-3 {
            --talent-status-color: #0e7490;
            --talent-status-bg: #ecfeff;
            --talent-status-border: #22d3ee;
        }

        .talent-status-tab.status-value-4 {
            --talent-status-color: #047857;
            --talent-status-bg: #ecfdf5;
            --talent-status-border: #34d399;
        }

        .talent-status-tab.status-value-5 {
            --talent-status-color: #be123c;
            --talent-status-bg: #fff1f2;
            --talent-status-border: #fb7185;
        }

        .talent-status-tab-end {
            margin-left: auto;
        }

        .talent-status-dot {
            width: 8px;
            height: 8px;
            flex-shrink: 0;
            border-radius: 50%;
            background: var(--talent-status-color);
        }

        .talent-status-count {
            min-width: 1.6rem;
            border-radius: 999px;
            background: #f1f3f7;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
            padding: 0.3rem 0.45rem;
            text-align: center;
        }

        .talent-status-tab.active .talent-status-count {
            background: var(--talent-status-color);
            color: #fff;
        }

        .talent-status-divider {
            width: 1px;
            height: 22px;
            background: #e2e5ec;
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
            align-items: center;
            display: inline-flex;
            vertical-align: middle;
        }

        .talent-status-select-shell {
            position: relative;
            display: inline-flex;
            align-items: center;
            min-width: 160px;
        }

        .talent-status-select-shell::after {
            content: "";
            position: absolute;
            right: 0.85rem;
            width: 0.45rem;
            height: 0.45rem;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            color: #64748b;
            pointer-events: none;
            transform: translateY(-20%) rotate(45deg);
        }

        .talent-status-select {
            appearance: none;
            min-height: 42px;
            width: 100%;
            border: 1px solid #d9dce5;
            border-radius: 0.6rem;
            background: #fff;
            color: #27334a;
            cursor: pointer;
            font-size: 0.86rem;
            font-weight: 700;
            line-height: 1.2;
            padding: 0.45rem 2.25rem 0.45rem 0.9rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }

        .talent-status-select:hover {
            border-color: #b8c0ce;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
        }

        .talent-status-select:focus {
            border-color: #93a4c0;
            box-shadow: 0 0 0 0.18rem rgba(37, 99, 235, 0.12);
            outline: 0;
        }

        .talent-status-select option {
            background: #fff;
            color: #111827;
            font-weight: 500;
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

        .talent-assessment-summary {
            display: inline-grid;
            gap: 0.2rem;
            min-width: 150px;
        }

        .talent-assessment-badge {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            min-height: 32px;
            border: 1px solid #d9dce5;
            border-radius: 0.55rem;
            background: #f8fafc;
            color: #475569;
            font-size: 0.82rem;
            font-weight: 800;
            padding: 0.35rem 0.65rem;
        }

        .talent-assessment-badge.pending {
            background: #f8fafc;
            border-color: #d9dce5;
            color: #64748b;
        }

        .talent-assessment-badge.progress {
            background: #eef2ff;
            border-color: #c7d2fe;
            color: #2448c7;
        }

        .talent-assessment-badge.review {
            background: #fff7ed;
            border-color: #fed7aa;
            color: #c2410c;
        }

        .talent-assessment-badge.final {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #047857;
        }

        .talent-assessment-note {
            color: #94a3b8;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .talent-assessment-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            min-width: 34px;
            border: 1px solid #c7d2fe;
            border-radius: 0.35rem;
            background: #eef2ff;
            color: #2448c7;
            font-size: 1rem;
            padding: 0;
            text-decoration: none;
        }

        .talent-assessment-button:hover {
            border-color: #2448c7;
            background: #2448c7;
            color: #fff;
            text-decoration: none;
        }

        .talent-assessment-button small {
            color: currentColor;
            font-size: 0.72rem;
            font-weight: 600;
            opacity: 0.72;
        }

        #datatable thead th {
            font-size: 1rem;
            font-weight: 600;
            padding: 1rem 0.75rem;
        }

        #datatable tbody td {
            font-size: 0.95rem;
            padding: 0.9rem 0.75rem;
            vertical-align: top;
        }

        #datatable thead th:first-child,
        #datatable tbody td:first-child {
            text-align: center !important;
        }

        #datatable_wrapper .dt-layout-row:first-child {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin: 0 0 1rem;
        }

        #datatable_wrapper .dataTables_length,
        #datatable_wrapper .dataTables_filter {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            margin-bottom: 1rem;
        }

        #datatable_wrapper .dataTables_filter {
            float: right;
        }

        #datatable_wrapper .dt-layout-row:first-child .dt-layout-cell {
            display: flex;
            align-items: center;
            width: auto;
        }

        #datatable_wrapper .dt-layout-row:first-child .dt-layout-cell:first-child {
            justify-content: flex-start;
        }

        #datatable_wrapper .dt-layout-row:first-child .dt-layout-cell:last-child {
            justify-content: flex-end;
        }

        #datatable_wrapper .dt-length,
        #datatable_wrapper .dt-search,
        #datatable_wrapper .dataTables_length label,
        #datatable_wrapper .dataTables_filter label {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            margin: 0;
        }

        #datatable_wrapper .dt-length label,
        #datatable_wrapper .dt-search label,
        #datatable_wrapper .dataTables_length label,
        #datatable_wrapper .dataTables_filter label {
            margin: 0;
            color: #5f6b7a;
            font-weight: 600;
        }

        #datatable_wrapper .dt-length select,
        #datatable_wrapper .dt-search input,
        #datatable_wrapper .dataTables_length select,
        #datatable_wrapper .dataTables_filter input {
            min-height: 38px;
            border: 1px solid #d9dce5;
            border-radius: 0.5rem;
            background: #fff;
            color: #27334a;
            box-shadow: none;
        }

        #datatable_wrapper .dt-search input,
        #datatable_wrapper .dataTables_filter input {
            width: 220px;
            max-width: 100%;
            padding: 0.35rem 0.75rem;
        }

        #datatable_wrapper .dt-length select,
        #datatable_wrapper .dataTables_length select {
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

            .talent-status-tab-end {
                margin-left: 0;
            }

            #datatable_wrapper .dt-layout-row:first-child {
                align-items: stretch;
                flex-direction: column;
            }

            #datatable_wrapper .dt-layout-row:first-child .dt-layout-cell,
            #datatable_wrapper .dt-length,
            #datatable_wrapper .dt-search,
            #datatable_wrapper .dataTables_length,
            #datatable_wrapper .dataTables_filter,
            #datatable_wrapper .dataTables_length label,
            #datatable_wrapper .dataTables_filter label {
                justify-content: space-between;
                width: 100%;
            }

            #datatable_wrapper .dataTables_filter {
                float: none;
            }

            #datatable_wrapper .dt-search input,
            #datatable_wrapper .dataTables_filter input {
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
                    @include('partials.swal-alert', ['type' => 'success', 'message' => session('status')])
                @endif
                @if ($errors->any())
                    @include('partials.swal-alert', ['type' => 'error', 'message' => $errors->first()])
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
                    <button type="button" class="talent-status-tab active" data-status-value="" role="tab" aria-selected="true">
                        Semua
                        <span class="talent-status-count" data-status-count="all">&ndash;</span>
                    </button>
                    <span class="talent-status-divider" aria-hidden="true"></span>
                    @foreach ($applicantStatuses as $applicantStatus)
                        <button type="button" @class([
                            'talent-status-tab',
                            'status-value-'.$applicantStatus->value,
                            'talent-status-tab-end' => $applicantStatus->value === \App\Models\ApplicantStatus::VALUE_NOT_SUITABLE,
                        ]) data-status-value="{{ $applicantStatus->value }}" role="tab" aria-selected="false">
                            <span class="talent-status-dot" aria-hidden="true"></span>
                            {{ $applicantStatus->name }}
                            <span class="talent-status-count" data-status-count="{{ $applicantStatus->value }}">&ndash;</span>
                        </button>
                    @endforeach
                </div>

                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered text-nowrap w-100"></table>
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
    <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        var applicantStatuses = @json($applicantStatuses->map(fn ($status) => ['id' => (string) $status->id, 'value' => (int) $status->value, 'name' => (string) $status->name])->values());
        var csrfToken = @json(csrf_token());
        var applicantStatusUpdateUrlTemplate = @json(route('applicant.status.update', ['applicant' => '__APPLICANT_ID__']));
        var applicantShowUrlTemplate = @json(route('applicant.show', ['applicant' => '__APPLICANT_ID__']));
        var applicantAssessmentUrlTemplate = @json(route('applicant.assessment', ['applicant' => '__APPLICANT_ID__']));
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

            return '<div class="talent-status-form">'
                + '<span class="talent-status-select-shell">'
                + '<select name="applicant_status_id" class="talent-status-select status-value-' + Number(applicant.applicant_status_value || 0) + '" onchange="updateApplicantStatusColor(this); updateStatus(\'' + escapeHtml(applicant.id) + '\', this.value)" aria-label="Update status ' + escapeHtml(applicant.full_name) + '">'
                + options
                + '</select>'
                + '</span>'
                + '</div>';
        }

        function renderAssessmentSummary(applicant) {
            var assessmentUrl = applicantUrl(applicantAssessmentUrlTemplate, applicant.id);

            return '<div class="talent-assessment-summary">'
                + '<a href="' + assessmentUrl + '" class="talent-assessment-button" title="Form Penilaian" aria-label="Form Penilaian"><i class="bi bi-clipboard-check"></i></a>'
                + '</div>';
        }

        function renderApplicantAction(applicant) {
            return actionMenu([
                '<li><a class="dropdown-item" href="' + applicantUrl(applicantShowUrlTemplate, applicant.id) + '"><i class="bi bi-eye"></i>Detail</a></li>',
                '<li><button type="button" class="dropdown-item text-danger" onclick="deleteData(\'' + escapeHtml(applicant.id) + '\')"><i class="bi bi-trash"></i>Delete</button></li>'
            ]);
        }

        function findApplicantRow(id) {
            return $('#datatable').DataTable().rows().data().toArray().find(function (row) {
                return String(row.id) === String(id);
            });
        }

        function ajaxErrorMessage(xhr, fallbackMessage) {
            return xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : fallbackMessage;
        }

        function deleteData(id) {
            var applicant = findApplicantRow(id);

            Swal.fire({
                title: 'Hapus Data Pelamar?',
                text: 'Data ' + (applicant ? applicant.full_name : 'pelamar ini') + ' akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: applicantUrl(applicantDestroyUrlTemplate, id),
                    type: 'DELETE',
                    dataType: 'json',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                }).done(function (response) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message });
                    $('#datatable').DataTable().ajax.reload(null, false);
                }).fail(function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: ajaxErrorMessage(xhr, 'Data pelamar gagal dihapus.') });
                });
            });
        }

        function updateStatus(id, status) {
            $.ajax({
                url: applicantUrl(applicantStatusUpdateUrlTemplate, id),
                type: 'PATCH',
                dataType: 'json',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                data: { applicant_status_id: status }
            }).done(function (response) {
                Swal.fire({ icon: response.mail_sent === false ? 'warning' : 'success', title: response.mail_sent === false ? 'Perhatian' : 'Berhasil', text: response.message });
                $('#datatable').DataTable().ajax.reload(null, false);
            }).fail(function (xhr) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: ajaxErrorMessage(xhr, 'Status pelamar gagal diperbarui.') });
                $('#datatable').DataTable().ajax.reload(null, false);
            });
        }

        function updateApplicantStatusCounts(json) {
            if (!json || !json.status_counts) {
                return;
            }

            $('[data-status-count]').each(function () {
                var countKey = $(this).attr('data-status-count');
                var count = countKey === 'all' ? json.total_count : json.status_counts[countKey];

                $(this).text(Number(count || 0));
            });
        }

        $(function () {
            var selectedApplicantStatus = '';

            $('#datatable').on('xhr.dt', function (event, settings, json) {
                updateApplicantStatusCounts(json);
            });

            var applicantsTable = $('#datatable').DataTable({
                autoWidth: false,
                ajax: {
                    url: "{{ route('applicant.datatable') }}",
                    data: function (requestData) {
                        requestData.status_value = selectedApplicantStatus;
                        requestData.job_vacancy_name = $('#positionFilter').val();
                    },
                    dataSrc: 'data'
                },
                processing: true,
                serverSide: true,
                order: [],
                columns: [
                    {
                        title: 'No',
                        className: 'mw-80',
                        data: null,
                        searchable: false,
                        orderable: false,
                        render: function (data, type, row, meta) {
                            return (meta.row + meta.settings._iDisplayStart + 1) + '.';
                        }
                    },
                    {
                        title: 'Photo',
                        className: 'mw-100',
                        data: null,
                        searchable: false,
                        orderable: false,
                        render: function (data, type, row) {
                            return renderApplicantPhoto(row);
                        }
                    },
                    { title: 'Nama Lengkap', className: 'mw-220', data: 'full_name' },
                    { title: 'Posisi Dilamar', className: 'mw-220', data: 'job_vacancy_name' },
                    {
                        title: 'Keterangan',
                        className: 'mw-420',
                        data: null,
                        render: function (data, type, row) {
                            return renderApplicantStatus(row);
                        }
                    },
                    {
                        title: 'Penilaian',
                        className: 'mw-180',
                        data: null,
                        searchable: false,
                        orderable: false,
                        render: function (data, type, row) {
                            return renderAssessmentSummary(row);
                        }
                    },
                    {
                        title: 'Action',
                        className: 'mw-120',
                        data: null,
                        searchable: false,
                        orderable: false,
                        render: function (data, type, row) {
                            return renderApplicantAction(row);
                        }
                    }
                ],
                columnDefs: [
                    { targets: [0, 1, 5, 6], orderable: false }
                ]
            });

            $('.talent-status-tab').on('click', function () {
                var $tab = $(this);

                if ($tab.hasClass('active')) {
                    return;
                }

                selectedApplicantStatus = $tab.attr('data-status-value');

                $('.talent-status-tab')
                    .removeClass('active')
                    .attr('aria-selected', 'false');

                $tab.addClass('active').attr('aria-selected', 'true');

                applicantsTable.ajax.reload();
            });

            $('#positionFilter').on('change', function () {
                applicantsTable.ajax.reload();
            });
        });
    </script>
    @stack('scripts')
@endsection
