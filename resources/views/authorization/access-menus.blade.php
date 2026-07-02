@extends('layouts.main')

@section('title', 'Authorization - Assign Permission')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/select2/css/select2.min.css') }}">
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

        .authorization-access-form .select2-container {
            width: 100% !important;
        }

        .authorization-access-form .bootstrap-select {
            display: none !important;
        }

        .authorization-access-form .select2-container--default .select2-selection--single,
        .authorization-access-form .select2-container--default .select2-selection--multiple {
            min-height: 44px;
            border-color: #e5e7eb;
            border-radius: 8px;
        }

        .authorization-access-form .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 44px;
            padding-left: 14px;
        }

        .authorization-access-form .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px;
        }

        .authorization-access-form .select2-container--default .select2-selection--multiple {
            padding: 6px 10px;
        }

        .authorization-access-form .select2-container--default .select2-selection--multiple .select2-selection__choice {
            margin-top: 2px;
            margin-bottom: 2px;
            border-color: #d1d5db;
            background: #f3f4f6;
            border-radius: 6px;
        }

        .authorization-access-form .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            border-right-color: #d1d5db;
        }

        .authorization-select2-option {
            display: flex;
            align-items: center;
            width: 100%;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background: var(--bs-primary);
            color: #fff;
            font-weight: 600;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: var(--bs-primary);
            color: #fff;
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

<div class="card authorization-nav-card">
    <div class="card-header py-0">
        <ul class="nav nav-underline authorization-tabs gap-3">
            <li class="nav-item">
                <a class="nav-link py-3 px-1" href="{{ route('authorization') }}">List Employee</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 px-1 active" href="{{ route('authorization.access-menus') }}">Assign Permission</a>
            </li>
        </ul>
    </div>
</div>

<div class="card">
    <div class="card-header border-0 flex-wrap gap-3">
        <div>
            <h4 class="card-title mb-1">Assign Permission</h4>
            <p class="mb-0 text-muted fs-13">Assign position yang boleh mengakses setiap menu.</p>
        </div>
        <button type="submit" form="authorizationAccessForm" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-floppy-disk me-2"></i>Save Access
        </button>
    </div>
    <div class="card-body pt-0">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">Periksa kembali position yang dipilih.</div>
        @endif

        <form id="authorizationAccessForm" action="{{ route('authorization.position-permissions.update') }}" method="POST">
            @csrf
            <div class="authorization-access-form table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th style="min-width: 420px;">Assign Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($menuPermissions as $permission)
                            @php
                                $selectedPositionIds = old("permission_positions.{$permission['id']}", $permission['position_ids']);
                                $selectedPositionIds = is_array($selectedPositionIds) ? $selectedPositionIds : [];
                            @endphp
                            <tr>
                                <td>
                                    <h6 class="mb-1 text-black">{{ $permission['label'] }}</h6>
                                    <span class="text-muted fs-13">{{ $permission['name'] }}</span>
                                </td>
                                <td>
                                    <select
                                        class="form-control authorization-select2 js-skip-selectpicker authorization-position-select"
                                        id="authorizationPositionSelect-{{ $permission['id'] }}"
                                        name="permission_positions[{{ $permission['id'] }}][]"
                                        multiple
                                        data-placeholder="Select position"
                                    >
                                        @foreach ($positions as $position)
                                            <option value="{{ $position['id'] }}" @selected(in_array($position['id'], $selectedPositionIds, true))>{{ $position['name'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-4">No menu data available.</td>
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
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/vendor/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script>
        (function () {
            function formatPositionOption(option) {
                if (!option.id) {
                    return option.text;
                }

                var optionContent = $('<span class="authorization-select2-option"></span>');

                $('<span></span>').text(option.text).appendTo(optionContent);

                return optionContent;
            }

            function initializeAuthorizationSelect2() {
                $('.authorization-select2').each(function () {
                    var selectElement = $(this);

                    if ($.fn.selectpicker && selectElement.data('selectpicker')) {
                        selectElement.selectpicker('destroy');
                    }

                    selectElement.siblings('.bootstrap-select').remove();

                    if ($.fn.select2) {
                        if (selectElement.hasClass('select2-hidden-accessible')) {
                            selectElement.select2('destroy');
                        }

                        selectElement.select2({
                            placeholder: selectElement.data('placeholder'),
                            closeOnSelect: false,
                            templateResult: formatPositionOption,
                            width: '100%'
                        });
                    }
                });
            }

            $(initializeAuthorizationSelect2);
            $(window).on('load', initializeAuthorizationSelect2);
        })();
    </script>
@endsection
