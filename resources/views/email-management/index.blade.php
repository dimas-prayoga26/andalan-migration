@extends('layouts.main')

@section('title', 'Email Management')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <style>
        .email-management-table-card {
            border-radius: 8px;
        }

        .email-management-list-actions {
            display: grid;
            grid-template-columns: minmax(240px, 320px) auto;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .email-management-list-actions .btn {
            min-height: 42px;
            white-space: nowrap;
        }

        .email-management-table-card .table-card-body {
            padding: 0;
        }

        .email-management-table-card .table-responsive {
            margin: 0;
        }

        .email-management-table-card table {
            margin-bottom: 0;
        }

        .email-management-table-card table th,
        .email-management-table-card table td {
            vertical-align: middle;
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
            .email-management-list-actions {
                grid-template-columns: minmax(0, 1fr);
                width: 100%;
            }

            .email-management-list-actions .btn {
                width: 100%;
            }
        }
    </style>
@endsection

@section('navbarTitle', 'Email Management')

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Email Management',
    'current' => 'Accounts & Takeovers',
    'homeRoute' => 'dashboard',
])

@include('partials.swal-alert', ['type' => 'success', 'message' => session('status')])
@include('partials.swal-alert', ['type' => 'error', 'message' => session('error')])

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Data belum valid.</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card email-management-table-card">
    <div class="card-header border-0 flex-wrap gap-3">
        <div>
            <h4 class="card-title mb-1">Mail Access Accounts</h4>
            <p class="mb-0 text-muted fs-13">Kelola akun email yang bisa dipakai dan ditakeover.</p>
        </div>
        <div class="email-management-list-actions">
            <form method="GET" action="{{ route('email-management.index') }}" class="mb-0">
                <div class="input-group">
                    <button type="submit" class="input-group-text bg-white" aria-label="Search email account">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    <input
                        name="search"
                        type="search"
                        class="form-control"
                        value="{{ $search }}"
                        placeholder="Search email"
                        autocomplete="off"
                        aria-label="Search email"
                    >
                </div>
            </form>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createMailAccountModal">
                <i class="fa-solid fa-plus me-1"></i>Create
            </button>
        </div>
    </div>

    <div class="card-body table-card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0 table-bottom-borderless table-striped align-middle">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Company</th>
                        <th>Owner Staff</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($accounts as $account)
                        @php
                            $ownerName = $account->employee?->profile?->name
                                ?? $account->employee?->profile?->nickname
                                ?? $account->employee?->user?->email
                                ?? '-';
                            $accountStatusClass = $account->is_active ? 'badge-success' : 'badge-danger';
                            $isConfigManagedAccount = $protectedMailAccountEmails->contains(mb_strtolower((string) $account->email));
                        @endphp
                        <tr>
                            <td class="fw-semibold text-black">{{ $account->email }}</td>
                            <td>{{ $mailTypeLabels[$account->type] ?? ucfirst((string) $account->type) }}</td>
                            <td>{{ $account->company?->name ?? '-' }}</td>
                            <td>{{ $ownerName }}</td>
                            <td>
                                <span class="badge badge-sm light {{ $accountStatusClass }}">{{ $account->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn btn-primary light btn-sm" data-bs-toggle="modal" data-bs-target="#editMailAccountModal{{ $account->id }}">Update</button>
                                    @if ($isConfigManagedAccount)
                                        <button type="button" class="btn btn-danger light btn-sm" disabled>Delete</button>
                                    @else
                                        <button type="button" class="btn btn-danger light btn-sm" data-bs-toggle="modal" data-bs-target="#deleteMailAccountModal{{ $account->id }}">Delete</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No email account data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('settings.partials.pagination', ['items' => $accounts])
    </div>
</div>

<div class="card email-management-table-card">
    <div class="card-header border-0 flex-wrap gap-3">
        <div>
            <h4 class="card-title mb-1">Takeover Access</h4>
            <p class="mb-0 text-muted fs-13">Akses baca email staff lama untuk staff pengganti.</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#createTakeoverModal">
            <i class="fa-solid fa-user-check me-1"></i>Create
        </button>
    </div>
    <div class="card-body table-card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0 table-bottom-borderless table-striped align-middle">
                <thead>
                    <tr>
                        <th>Staff Lama</th>
                        <th>Staff Pengganti</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($takeovers as $takeover)
                        @php
                            $isReadable = $takeover->can_read && $takeover->revoked_at === null;
                            $sourceName = $takeover->sourceEmployee?->profile?->name
                                ?? $takeover->sourceEmployee?->profile?->nickname
                                ?? '';
                            $sourceEmail = $takeover->mailAccessAccount?->email
                                ?? $takeover->sourceEmployee?->user?->email
                                ?? $takeover->sourceEmployee?->user?->username
                                ?? '';
                            $sourceLabel = trim((string) $sourceName) !== '' && trim((string) $sourceEmail) !== ''
                                ? "{$sourceName} - {$sourceEmail}"
                                : ($sourceName ?: ($sourceEmail ?: '-'));
                            $targetName = $takeover->targetEmployee?->profile?->name
                                ?? $takeover->targetEmployee?->profile?->nickname
                                ?? '';
                            $targetEmail = $takeover->targetEmployee?->user?->email
                                ?? $takeover->targetEmployee?->user?->username
                                ?? '';
                            $targetLabel = trim((string) $targetName) !== '' && trim((string) $targetEmail) !== ''
                                ? "{$targetName} - {$targetEmail}"
                                : ($targetName ?: ($targetEmail ?: '-'));
                        @endphp
                        <tr>
                            <td class="fw-semibold text-black">{{ $sourceLabel }}</td>
                            <td>{{ $targetLabel }}</td>
                            <td>
                                @if ($isReadable)
                                    <span class="badge badge-sm light badge-success">Readable</span>
                                @else
                                    <span class="badge badge-sm light badge-danger">Closed</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn btn-primary light btn-sm" data-bs-toggle="modal" data-bs-target="#editTakeoverModal{{ $takeover->id }}">Update</button>
                                    <button type="button" class="btn btn-danger light btn-sm" data-bs-toggle="modal" data-bs-target="#deleteTakeoverModal{{ $takeover->id }}">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No takeover data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('settings.partials.pagination', ['items' => $takeovers])
    </div>
</div>

<div class="modal fade" id="createMailAccountModal" tabindex="-1" aria-labelledby="createMailAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="{{ route('email-management.accounts.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="createMailAccountModalLabel">Create Email Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('email-management.partials.account-form', [
                    'account' => null,
                    'companies' => $companies,
                    'employeeOptions' => $employeeOptions,
                    'mailTypeOptions' => $mailTypeOptions,
                ])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="createTakeoverModal" tabindex="-1" aria-labelledby="createTakeoverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="{{ route('email-management.takeovers.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="createTakeoverModalLabel">Create Takeover Access</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('email-management.partials.takeover-form', [
                    'takeover' => null,
                    'accountOptions' => $accountOptions,
                    'employeeOptions' => $takeoverEmployeeOptions,
                ])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

@foreach ($accounts as $account)
    @php
        $isConfigManagedAccount = $protectedMailAccountEmails->contains(mb_strtolower((string) $account->email));
    @endphp

    <div class="modal fade" id="editMailAccountModal{{ $account->id }}" tabindex="-1" aria-labelledby="editMailAccountModalLabel{{ $account->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" action="{{ route('email-management.accounts.update', $account) }}" class="modal-content">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title" id="editMailAccountModalLabel{{ $account->id }}">Update Email Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('email-management.partials.account-form', [
                        'account' => $account,
                        'companies' => $companies,
                        'employeeOptions' => $employeeOptions,
                        'mailTypeOptions' => $mailTypeOptions,
                    ])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    @if (! $isConfigManagedAccount)
        <div class="modal fade" id="deleteMailAccountModal{{ $account->id }}" tabindex="-1" aria-labelledby="deleteMailAccountModalLabel{{ $account->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('email-management.accounts.destroy', $account) }}" class="modal-content">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteMailAccountModalLabel{{ $account->id }}">Delete Email Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Delete <strong>{{ $account->email }}</strong> from email management?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endforeach

@foreach ($takeovers as $takeover)
    <div class="modal fade" id="editTakeoverModal{{ $takeover->id }}" tabindex="-1" aria-labelledby="editTakeoverModalLabel{{ $takeover->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" action="{{ route('email-management.takeovers.update', $takeover) }}" class="modal-content">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title" id="editTakeoverModalLabel{{ $takeover->id }}">Update Takeover Access</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('email-management.partials.takeover-form', [
                        'takeover' => $takeover,
                        'accountOptions' => $accountOptions,
                        'employeeOptions' => $takeoverEmployeeOptions,
                    ])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="deleteTakeoverModal{{ $takeover->id }}" tabindex="-1" aria-labelledby="deleteTakeoverModalLabel{{ $takeover->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('email-management.takeovers.destroy', $takeover) }}" class="modal-content">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteTakeoverModalLabel{{ $takeover->id }}">Delete Takeover Access</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Delete takeover access for <strong>{{ $takeover->mailAccessAccount?->email ?? '-' }}</strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
@endforeach
@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script src="{{ asset('assets/js/email-management.js') }}?v={{ file_exists(public_path('assets/js/email-management.js')) ? filemtime(public_path('assets/js/email-management.js')) : time() }}"></script>
@endsection
