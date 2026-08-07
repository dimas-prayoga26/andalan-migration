@extends('layouts.main')

@section('title', 'Job Vacancies')

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
            margin-bottom: 0.85rem;
        }

        .talent-table-title {
            color: #25314c;
            font-size: 1rem;
            font-weight: 700;
        }

        .talent-vacancy-status-form {
            display: inline-flex;
            vertical-align: middle;
        }

        .talent-vacancy-status-select {
            min-height: 30px;
            border-radius: 0.35rem;
            padding: 0.18rem 1.75rem 0.18rem 0.55rem;
            font-size: 0.9rem;
            font-weight: 700;
            line-height: 1.2;
            background: #fff;
        }

        .talent-vacancy-status-select.active {
            border: 1px solid #22c55e;
            color: #15803d;
        }

        .talent-vacancy-status-select.inactive {
            border: 1px solid #ff4f7b;
            color: #e11d48;
        }

        .talent-vacancy-status-select:focus {
            box-shadow: 0 0 0 0.15rem rgba(15, 23, 42, 0.08);
            outline: 0;
        }

        #jobVacanciesTable_wrapper .dt-layout-row:first-child {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin: 0 0 1rem;
        }

        #jobVacanciesTable_wrapper .dt-layout-row:first-child .dt-layout-cell {
            display: flex;
            align-items: center;
            width: auto;
        }

        #jobVacanciesTable_wrapper .dt-length,
        #jobVacanciesTable_wrapper .dt-search,
        #jobVacanciesTable_wrapper .dataTables_length label,
        #jobVacanciesTable_wrapper .dataTables_filter label {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            margin: 0;
        }

        #jobVacanciesTable_wrapper .dt-length label,
        #jobVacanciesTable_wrapper .dt-search label,
        #jobVacanciesTable_wrapper .dataTables_length label,
        #jobVacanciesTable_wrapper .dataTables_filter label {
            margin: 0;
            color: #5f6b7a;
            font-weight: 600;
        }

        #jobVacanciesTable_wrapper .dt-length select,
        #jobVacanciesTable_wrapper .dt-search input,
        #jobVacanciesTable_wrapper .dataTables_length select,
        #jobVacanciesTable_wrapper .dataTables_filter input {
            min-height: 38px;
            border: 1px solid #d9dce5;
            border-radius: 0.5rem;
            background: #fff;
            color: #27334a;
            box-shadow: none;
        }

        #jobVacanciesTable_wrapper .dt-search input,
        #jobVacanciesTable_wrapper .dataTables_filter input {
            width: 220px;
            max-width: 100%;
            padding: 0.35rem 0.75rem;
        }

        #jobVacanciesTable_wrapper .dt-length select,
        #jobVacanciesTable_wrapper .dataTables_length select {
            padding: 0.35rem 2rem 0.35rem 0.75rem;
        }

        #jobVacanciesTable thead th {
            font-size: 1rem;
            font-weight: 600;
            padding: 1rem 0.75rem;
        }

        #jobVacanciesTable tbody td {
            font-size: 0.95rem;
            padding: 0.9rem 0.75rem;
            vertical-align: middle;
        }

        #jobVacanciesTable thead th:first-child,
        #jobVacanciesTable tbody td:first-child {
            text-align: center !important;
        }

        @media only screen and (max-width: 767.98px) {
            #jobVacanciesTable_wrapper .dt-layout-row:first-child {
                align-items: stretch;
                flex-direction: column;
            }

            #jobVacanciesTable_wrapper .dt-layout-row:first-child .dt-layout-cell,
            #jobVacanciesTable_wrapper .dt-length,
            #jobVacanciesTable_wrapper .dt-search,
            #jobVacanciesTable_wrapper .dataTables_length label,
            #jobVacanciesTable_wrapper .dataTables_filter label {
                justify-content: space-between;
                width: 100%;
            }

            #jobVacanciesTable_wrapper .dt-search input,
            #jobVacanciesTable_wrapper .dataTables_filter input {
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
                    <div class="talent-table-title">Job Vacancy</div>
                </div>

                <div class="table-responsive">
                    <table id="jobVacanciesTable" class="display table">
                        <thead>
                        <tr>
                            <th class="mw-80">No</th>
                            <th class="mw-300">Lowongan Pekerjaan</th>
                            <th class="mw-160">Status</th>
                            <th class="mw-160">Total Pelamar</th>
                            <th class="mw-180">Legacy Created</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($jobVacancies as $jobVacancy)
                            <tr>
                                <td>{{ $loop->iteration }}.</td>
                                <td>{{ $jobVacancy->name }}</td>
                                <td>
                                    <form method="POST" action="{{ route('applicant.job_vacancies.status.update', ['jobVacancy' => $jobVacancy->id]) }}" class="talent-vacancy-status-form">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="talent-vacancy-status-select {{ $jobVacancy->status }}" onchange="updateJobVacancyStatusColor(this); this.form.submit()" aria-label="Update status {{ $jobVacancy->name }}">
                                            @foreach ($jobVacancyStatuses as $statusValue => $statusLabel)
                                                <option value="{{ $statusValue }}" @selected($jobVacancy->status === $statusValue)>
                                                    {{ $statusLabel }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td>{{ $jobVacancy->applicants_count }}</td>
                                <td>{{ $jobVacancy->legacy_created_at?->format('d M Y H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada data lowongan.</td>
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
        function updateJobVacancyStatusColor(selectElement) {
            selectElement.classList.remove('active', 'inactive');
            selectElement.classList.add(selectElement.value === 'active' ? 'active' : 'inactive');
        }

        $(function () {
            var jobVacancyTable = $('#jobVacanciesTable').DataTable({
                columnDefs: [
                    { targets: 0, orderable: false }
                ]
            });

            jobVacancyTable.on('order.dt search.dt draw.dt', function () {
                jobVacancyTable
                    .column(0, { search: 'applied', order: 'applied', page: 'current' })
                    .nodes()
                    .each(function (cell, index) {
                        cell.innerHTML = (index + 1) + '.';
                    });
            }).draw();
        });
    </script>
@endsection
