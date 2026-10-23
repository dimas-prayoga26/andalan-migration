@extends('layouts.main')

@section('title', 'Job Vacancies')

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

        .talent-company-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            overflow-x: auto;
            padding-bottom: 0.15rem;
            scrollbar-width: thin;
            white-space: nowrap;
        }

        .talent-company-tab {
            align-items: center;
            background: #fff;
            border: 1px solid #d9dce5;
            border-radius: 0.45rem;
            color: #596579;
            cursor: pointer;
            display: inline-flex;
            font-size: 0.86rem;
            font-weight: 700;
            min-height: 36px;
            padding: 0.45rem 0.9rem;
            transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        }

        .talent-company-tab.active,
        .talent-company-tab:hover {
            background: #eef3ff;
            border-color: #2447c6;
            color: #2447c6;
        }

        .talent-vacancy-status-form {
            align-items: center;
            display: inline-flex;
            vertical-align: middle;
        }

        .talent-vacancy-status-select-shell {
            position: relative;
            display: inline-flex;
            align-items: center;
            min-width: 150px;
        }

        .talent-vacancy-status-select-shell::after {
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

        .talent-vacancy-status-select {
            appearance: none;
            min-height: 42px;
            width: 100%;
            border-radius: 0.6rem;
            cursor: pointer;
            font-size: 0.86rem;
            font-weight: 700;
            line-height: 1.2;
            padding: 0.45rem 2.25rem 0.45rem 0.9rem;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }

        .talent-vacancy-status-select.active {
            border: 1px solid #86efac;
            background: #f0fdf4;
            color: #15803d;
        }

        .talent-vacancy-status-select.inactive {
            border: 1px solid #fecdd3;
            background: #fff1f2;
            color: #e11d48;
        }

        .talent-vacancy-status-select:hover {
            border-color: #b8c0ce;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
        }

        .talent-vacancy-status-select:focus {
            border-color: #93a4c0;
            box-shadow: 0 0 0 0.18rem rgba(37, 99, 235, 0.12);
            outline: 0;
        }

        .talent-vacancy-status-select option {
            background: #fff;
            color: #111827;
            font-weight: 500;
        }

        #datatable_wrapper .dt-layout-row:first-child {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin: 0 0 1rem;
        }

        #datatable_wrapper .dt-layout-row:first-child .dt-layout-cell {
            display: flex;
            align-items: center;
            width: auto;
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

        #datatable thead th {
            font-size: 1rem;
            font-weight: 600;
            padding: 1rem 0.75rem;
        }

        #datatable tbody td {
            font-size: 0.95rem;
            padding: 0.9rem 0.75rem;
            vertical-align: middle;
        }

        #datatable thead th:first-child,
        #datatable tbody td:first-child {
            text-align: center !important;
        }

        @media only screen and (max-width: 767.98px) {
            #datatable_wrapper .dt-layout-row:first-child {
                align-items: stretch;
                flex-direction: column;
            }

            #datatable_wrapper .dt-layout-row:first-child .dt-layout-cell,
            #datatable_wrapper .dt-length,
            #datatable_wrapper .dt-search,
            #datatable_wrapper .dataTables_length label,
            #datatable_wrapper .dataTables_filter label {
                justify-content: space-between;
                width: 100%;
            }

            #datatable_wrapper .dt-search input,
            #datatable_wrapper .dataTables_filter input {
                width: 100%;
            }
        }
    </style>
@endsection

@section('navbarTitle', 'Job Vacancies')

@section('content')
<div class="page-title">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li><h1>Job Vacancies</h1></li>
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Main</a></li>
            <li class="breadcrumb-item active" aria-current="page">Job Vacancies</li>
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
                    <div class="talent-table-title">Job Vacancy</div>
                    <a href="{{ route('applicant.job_vacancies.create') }}" class="btn light btn-success btn-sm d-inline-flex align-items-center justify-content-center gap-1 talent-create-job-btn">
                        <i class="bi bi-plus-lg"></i>
                        <span>Create Job</span>
                    </a>
                </div>

                <div class="talent-company-tabs" aria-label="Filter company">
                    <button type="button" class="talent-company-tab active" data-company-filter="">Semua Company</button>
                    @foreach ($companyOptions as $companyOption)
                        <button type="button" class="talent-company-tab" data-company-filter="{{ $companyOption['id'] }}">
                            {{ $companyOption['name'] }}
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
        var jobVacancyStatuses = @json(collect($jobVacancyStatuses)->map(fn ($label, $value) => ['value' => (int) $value, 'label' => (string) $label])->values());
        var csrfToken = @json(csrf_token());
        var jobVacancyStatusUpdateUrlTemplate = @json(route('applicant.job_vacancies.status.update', ['jobVacancy' => '__JOB_VACANCY_ID__']));
        var jobVacancyEditUrlTemplate = @json(route('applicant.job_vacancies.edit', ['jobVacancy' => '__JOB_VACANCY_ID__']));
        var jobVacancyDestroyUrlTemplate = @json(route('applicant.job_vacancies.destroy', ['jobVacancy' => '__JOB_VACANCY_ID__']));

        function escapeHtml(value) {
            return $('<div>').text(value === null || value === undefined ? '' : String(value)).html();
        }

        function jobVacancyUrl(template, jobVacancyId) {
            return template.replace('__JOB_VACANCY_ID__', encodeURIComponent(jobVacancyId));
        }

        function updateJobVacancyStatusColor(selectElement) {
            selectElement.classList.remove('active', 'inactive');
            selectElement.classList.add(selectElement.value === '1' ? 'active' : 'inactive');
        }

        function renderJobVacancyStatus(jobVacancy) {
            var options = jobVacancyStatuses.map(function (status) {
                return '<option value="' + status.value + '"' + (Number(jobVacancy.status) === Number(status.value) ? ' selected' : '') + '>'
                    + escapeHtml(status.label)
                    + '</option>';
            }).join('');

            return '<div class="talent-vacancy-status-form">'
                + '<span class="talent-vacancy-status-select-shell">'
                + '<select name="status" class="talent-vacancy-status-select ' + escapeHtml(jobVacancy.status_css_class) + '" onchange="updateJobVacancyStatusColor(this); updateStatus(\'' + escapeHtml(jobVacancy.id) + '\', this.value)" aria-label="Update status ' + escapeHtml(jobVacancy.name) + '">'
                + options
                + '</select>'
                + '</span>'
                + '</div>';
        }

        function renderJobVacancyAction(jobVacancy) {
            return actionMenu([
                '<li><a class="dropdown-item" href="' + jobVacancyUrl(jobVacancyEditUrlTemplate, jobVacancy.id) + '"><i class="bi bi-pencil-square"></i>Update</a></li>',
                '<li><button type="button" class="dropdown-item text-danger" onclick="deleteData(\'' + escapeHtml(jobVacancy.id) + '\')"><i class="bi bi-trash"></i>Delete</button></li>'
            ]);
        }

        function findJobVacancyRow(id) {
            return $('#datatable').DataTable().rows().data().toArray().find(function (row) {
                return String(row.id) === String(id);
            });
        }

        function ajaxErrorMessage(xhr, fallbackMessage) {
            return xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : fallbackMessage;
        }

        function deleteData(id) {
            var jobVacancy = findJobVacancyRow(id);

            Swal.fire({
                title: 'Hapus Lowongan?',
                text: 'Lowongan ' + (jobVacancy ? jobVacancy.name : 'ini') + ' akan dihapus.',
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
                    url: jobVacancyUrl(jobVacancyDestroyUrlTemplate, id),
                    type: 'DELETE',
                    dataType: 'json',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                }).done(function (response) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message });
                    $('#datatable').DataTable().ajax.reload(null, false);
                }).fail(function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: ajaxErrorMessage(xhr, 'Lowongan gagal dihapus.') });
                });
            });
        }

        function updateStatus(id, status) {
            $.ajax({
                url: jobVacancyUrl(jobVacancyStatusUpdateUrlTemplate, id),
                type: 'PATCH',
                dataType: 'json',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                data: { status: status }
            }).done(function (response) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message });
                $('#datatable').DataTable().ajax.reload(null, false);
            }).fail(function (xhr) {
                var redirectUrl = xhr && xhr.responseJSON ? xhr.responseJSON.redirect_url : null;

                $('#datatable').DataTable().ajax.reload(null, false);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: ajaxErrorMessage(xhr, 'Status lowongan gagal diperbarui.'),
                    showCancelButton: !!redirectUrl,
                    confirmButtonText: redirectUrl ? 'Isi Kriteria' : 'OK',
                    cancelButtonText: 'Tutup'
                }).then(function (result) {
                    if (redirectUrl && result.isConfirmed) {
                        window.location.href = redirectUrl;
                    }
                });
            });
        }

        $(function () {
            var selectedCompanyId = '';
            var jobVacancyTable = null;

            jobVacancyTable = $('#datatable').DataTable({
                autoWidth: false,
                ajax: {
                    url: "{{ route('applicant.job_vacancies.datatable') }}",
                    data: function (requestData) {
                        requestData.company_id = selectedCompanyId;
                    },
                    dataSrc: 'data'
                },
                processing: true,
                serverSide: true,
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
                    { title: 'Lowongan Pekerjaan', className: 'mw-300', data: 'name' },
                    { title: 'Company', className: 'mw-220', data: 'company_name' },
                    {
                        title: 'Status',
                        className: 'mw-160',
                        data: null,
                        render: function (data, type, row) {
                            return renderJobVacancyStatus(row);
                        }
                    },
                    { title: 'Total Pelamar', className: 'mw-160', data: 'applicants_count' },
                    { title: 'Created At', className: 'mw-180', data: 'created_at' },
                    {
                        title: 'Action',
                        className: 'mw-120',
                        data: null,
                        searchable: false,
                        orderable: false,
                        render: function (data, type, row) {
                            return renderJobVacancyAction(row);
                        }
                    }
                ],
                columnDefs: [
                    { targets: [0, 6], orderable: false }
                ]
            });

            document.querySelectorAll('[data-company-filter]').forEach(function (tab) {
                tab.addEventListener('click', function () {
                    document.querySelectorAll('[data-company-filter]').forEach(function (item) {
                        item.classList.remove('active');
                    });

                    tab.classList.add('active');
                    selectedCompanyId = String(tab.getAttribute('data-company-filter') || '');
                    jobVacancyTable.ajax.reload();
                });
            });
        });
    </script>
    @stack('scripts')
@endsection
