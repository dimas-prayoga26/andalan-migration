<link rel="stylesheet" href="{{ asset('assets/vendor/select2/css/select2.min.css') }}">
<style>
    .mail-account-card {
        overflow: visible !important;
        position: relative;
        z-index: 5;
    }

    .mail-account-card .card-body {
        overflow: visible;
        position: relative;
        z-index: 2;
    }

    .mail-account-card .effect {
        display: none !important;
        height: 0 !important;
        width: 0 !important;
    }

    .mail-account-picker {
        max-width: 100%;
        position: relative;
    }

    .mail-account-trigger {
        align-items: center;
        background: transparent;
        border: 0;
        color: #000;
        display: inline-flex;
        gap: 8px;
        letter-spacing: 0;
        max-width: 100%;
        padding: 0;
        text-align: left;
        transition: color .2s ease;
    }

    .mail-account-trigger:hover {
        color: var(--bs-primary);
    }

    .mail-account-trigger:focus {
        outline: 0;
    }

    .mail-account-trigger:focus-visible {
        border-radius: 6px;
        box-shadow: 0 0 0 3px rgba(36, 69, 199, .16);
    }

    .mail-account-trigger span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mail-account-trigger i {
        color: #64748b;
        flex: 0 0 auto;
        font-size: 12px;
        transition: transform .2s ease, color .2s ease;
    }

    .mail-account-picker.is-open .mail-account-trigger i {
        color: var(--bs-primary);
        transform: rotate(180deg);
    }

    .mail-account-menu {
        background: #fff;
        border: 1px solid rgba(148, 163, 184, .24);
        border-radius: 12px;
        box-shadow: 0 22px 60px rgba(15, 23, 42, .16);
        left: 0;
        min-width: min(420px, calc(100vw - 48px));
        overflow: hidden;
        padding: 8px;
        position: absolute;
        top: calc(100% + 10px);
        z-index: 1060;
    }

    .mail-account-menu.d-none {
        display: none !important;
    }

    .mail-account-menu .select2-container {
        display: block;
        position: static !important;
        width: 100% !important;
    }

    .mail-account-menu .bootstrap-select {
        display: none !important;
    }

    .mail-account-menu .selection {
        display: none !important;
    }

    .mail-account-menu .select2-dropdown {
        background: transparent;
        border: 0;
        box-shadow: none;
        display: block;
        left: auto !important;
        margin-top: 0;
        position: static !important;
        top: auto !important;
        width: 100% !important;
    }

    .mail-account-menu .select2-search--dropdown {
        display: block;
        padding: 10px 10px 8px;
    }

    .mail-account-menu .select2-search--dropdown .select2-search__field {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        color: #0f172a;
        font-size: 14px;
        height: 42px;
        outline: 0;
        padding: 0 14px;
        transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        width: 100% !important;
    }

    .mail-account-menu .select2-search--dropdown .select2-search__field:focus {
        background: #fff;
        border-color: rgba(36, 69, 199, .5);
        box-shadow: 0 0 0 4px rgba(36, 69, 199, .1);
    }

    .mail-account-menu .select2-results {
        display: block;
        padding: 2px 4px 4px;
    }

    .mail-account-menu .select2-results__options {
        max-height: 240px;
        overflow-y: auto;
        padding: 2px;
    }

    .mail-account-menu .select2-results__option {
        border-radius: 10px;
        color: #64748b;
        font-size: 14px;
        line-height: 1.35;
        margin: 2px 0;
        padding: 10px 12px;
        transition: background .18s ease, color .18s ease;
    }

    .mail-account-menu .select2-results__option--highlighted[aria-selected] {
        background: #eef3ff;
        color: #2445c7;
    }

    .mail-account-menu .select2-container--default .select2-selection--single {
        align-items: center;
        border-color: #dbe3f0;
        border-radius: 8px;
        display: flex;
        min-height: 44px;
    }

    .mail-account-menu .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #111827;
        line-height: 42px;
        padding-left: 14px;
        padding-right: 34px;
    }

    .mail-account-menu .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px;
        right: 8px;
    }

    .mail-account-select2-dropdown {
        z-index: 1070;
    }

    .mail-account-select2-dropdown .select2-results__option[aria-selected="true"] {
        background: #eef3ff;
        color: #0f172a;
        font-weight: 700;
    }
</style>
