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

        .talent-photo {
            width: 34px;
            height: 34px;
            border-radius: 0.45rem;
            background: #e5e7eb;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
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
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #047857;
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
                @if (! ($syncResult['available'] ?? true))
                    <div class="alert alert-warning mb-3" role="alert">
                        {{ $syncResult['message'] ?? 'Koneksi database legacy belum tersedia.' }}
                    </div>
                @endif
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
                        <tbody>
                        @forelse ($applicants as $applicant)
                            <tr>
                                <td>{{ $loop->iteration }}.</td>
                                <td><span class="talent-photo" title="{{ $applicant->photo ?: 'No photo' }}"><i class="bi bi-person-fill"></i></span></td>
                                <td>{{ $applicant->full_name }}</td>
                                <td>{{ $applicant->jobVacancy?->name ?? '-' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('applicant.status.update', ['applicant' => $applicant->id]) }}" class="talent-status-form">
                                        @csrf
                                        @method('PATCH')
                                        <select name="applicant_status_id" class="talent-status-select status-value-{{ $applicantStatuses->firstWhere('id', $applicant->applicant_status_id)?->value ?? 0 }}" onchange="updateApplicantStatusColor(this); this.form.submit()" aria-label="Update status {{ $applicant->full_name }}">
                                            @foreach ($applicantStatuses as $applicantStatus)
                                                <option value="{{ $applicantStatus->id }}" data-status-value="{{ $applicantStatus->value }}" @selected($applicant->applicant_status_id === $applicantStatus->id)>
                                                    {{ $applicantStatus->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <div class="talent-action-group">
                                        <a href="{{ route('applicant.show', ['applicant' => $applicant->id]) }}" class="talent-action-btn view" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <form method="POST" action="{{ route('applicant.destroy', ['applicant' => $applicant->id]) }}" onsubmit="return confirm('Hapus data pelamar ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="talent-action-btn delete" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada data pelamar.</td>
                            </tr>
                        @endforelse
                        </tbody>
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
        function updateApplicantStatusColor(selectElement) {
            var selectedOption = selectElement.options[selectElement.selectedIndex];
            var statusValue = selectedOption ? selectedOption.dataset.statusValue : '0';

            selectElement.classList.remove('status-value-0', 'status-value-1', 'status-value-2');
            selectElement.classList.add('status-value-' + statusValue);
        }

        $(function () {
            var applicantsTable = $('#applicantsTable').DataTable({
                order: [],
                columnDefs: [
                    { targets: [0, 1, 5], orderable: false }
                ],
                drawCallback: function () {
                    var tableApi = this.api();
                    var pageInfo = tableApi.page.info();

                    tableApi.column(0, { page: 'current' }).nodes().each(function (cell, index) {
                        cell.innerHTML = (pageInfo.start + index + 1) + '.';
                    });
                }
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
