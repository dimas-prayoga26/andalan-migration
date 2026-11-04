@extends('layouts.main')

@section('title', 'Authorization - Assign Permission')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <style>
        .authorization-nav-card {
            border-radius: 8px;
        }

        .authorization-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: thin;
            white-space: nowrap;
        }

        .authorization-tabs .nav-link {
            border: 0;
            border-bottom: 3px solid transparent;
            color: #6b7280;
            background: transparent;
        }

        .authorization-tabs .nav-link.active {
            color: var(--bs-primary);
            border-bottom-color: var(--bs-primary);
            background: transparent;
        }

        .authorization-level-legend {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }

        .authorization-level-legend-item {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }

        .authorization-level-legend-item h6 {
            margin-bottom: 0.25rem;
            color: var(--bs-primary);
        }

        .authorization-level-legend-item span {
            color: #6b7280;
            font-size: 0.8125rem;
        }

        .authorization-access-form .authorization-level-column {
            width: 130px;
            text-align: center;
        }

        .authorization-access-form .authorization-level-checkbox {
            width: 1.25rem;
            height: 1.25rem;
            float: none;
            margin: 0;
            cursor: pointer;
        }

        .authorization-level-partial {
            max-width: 130px;
            margin: 0.25rem auto 0;
            color: #b45309;
            font-size: 0.75rem;
            line-height: 1.3;
        }
    </style>
@endsection

@section('navbarTitle', 'Authorization')

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Authorization',
    'current' => 'Assign Permission',
    'homeRoute' => 'dashboard',
])

@php
    $hasEventDivisionRoute = \Illuminate\Support\Facades\Route::has('authorization.event-divisions');
@endphp

<div class="card authorization-nav-card">
    <div class="card-header py-0">
        <ul class="nav nav-underline authorization-tabs gap-3">
            <li class="nav-item">
                <a class="nav-link py-3 px-1" href="{{ route('authorization') }}">Employee List</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 px-1 active" href="{{ route('authorization.access-menus') }}">Assign Permission</a>
            </li>
            @if ($hasEventDivisionRoute)
                <li class="nav-item">
                    <a class="nav-link py-3 px-1" href="{{ route('authorization.event-divisions') }}">Assign Event Division</a>
                </li>
            @endif
        </ul>
    </div>
</div>

<div class="card">
    <div class="card-header border-0 flex-wrap gap-3">
        <div>
            <h4 class="card-title mb-1">Assign Permission</h4>
            <p class="mb-0 text-muted fs-13">Choose which position levels can access each menu.</p>
        </div>
        <button type="submit" form="authorizationAccessForm" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-floppy-disk me-2"></i>Save Access
        </button>
    </div>
    <div class="card-body pt-0">
        @include('partials.swal-alert', ['type' => 'success', 'message' => session('status')])

        @if ($errors->any())
            @include('partials.swal-alert', ['type' => 'error', 'message' => 'Periksa kembali level yang dipilih.'])
        @endif

        <div class="authorization-level-legend">
            @foreach ($levels as $level)
                <div class="authorization-level-legend-item">
                    <h6>{{ $level['label'] }}</h6>
                    <span>{{ $level['positions']->pluck('name')->implode(', ') ?: 'No positions yet' }}</span>
                </div>
            @endforeach
        </div>
        @if (auth()->user()?->hasAnyPositionPermission(['view-settings']))
            <p class="text-muted fs-13 mb-3">
                A position's level can be changed in <a href="{{ route('settings.positions.index') }}">Setting &rsaquo; Position</a>.
            </p>
        @endif

        <form id="authorizationAccessForm" action="{{ route('authorization.position-permissions.update') }}" method="POST">
            @csrf
            <div class="authorization-access-form table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            @foreach ($levels as $level)
                                <th class="authorization-level-column">{{ $level['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($menuPermissions as $permission)
                            <tr>
                                <td>
                                    <h6 class="mb-1 text-black">{{ $permission['section'] }} - {{ $permission['label'] }}</h6>
                                    <span class="text-muted fs-13">{{ $permission['name'] }}</span>
                                </td>
                                @foreach ($levels as $level)
                                    @php
                                        $levelState = $permission['levels'][$level['key']];
                                        $isPartialLevel = $levelState['state'] === \App\Services\PositionLevelPermissionService::STATE_PARTIAL;
                                        $levelInputName = "permission_levels[{$permission['id']}][{$level['key']}]";
                                    @endphp
                                    <td class="authorization-level-column">
                                        <input type="hidden" name="{{ $levelInputName }}" value="{{ $isPartialLevel ? 'partial' : 'none' }}" data-level-fallback>
                                        <input
                                            class="form-check-input authorization-level-checkbox"
                                            type="checkbox"
                                            name="{{ $levelInputName }}"
                                            value="all"
                                            aria-label="{{ $level['label'] }} - {{ $permission['section'] }} - {{ $permission['label'] }}"
                                            @checked($levelState['state'] === \App\Services\PositionLevelPermissionService::STATE_ALL)
                                            @disabled($level['positions']->isEmpty())
                                            @if ($isPartialLevel) data-partial-level @endif
                                        >
                                        @if ($isPartialLevel)
                                            <div class="authorization-level-partial" data-level-partial-note>
                                                Partial: {{ implode(', ', $levelState['granted_position_names']) }}
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $levels->count() + 1 }}" class="text-center text-muted py-4">No menu data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
    <script>
        (function () {
            // A partial level keeps its current positions until the checkbox is touched; after that it is all-or-none.
            document.querySelectorAll('[data-partial-level]').forEach(function (checkbox) {
                var fallbackInput = checkbox.parentElement.querySelector('[data-level-fallback]');
                var partialNote = checkbox.parentElement.querySelector('[data-level-partial-note]');

                checkbox.indeterminate = true;

                checkbox.addEventListener('change', function () {
                    fallbackInput.value = 'none';

                    if (partialNote) {
                        partialNote.remove();
                    }
                }, { once: true });
            });
        })();
    </script>
@endsection
