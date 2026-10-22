<link rel="stylesheet" href="{{ asset('assets/vendor/jquery-smartwizard/dist/css/smart_wizard.min.css') }}">
<style>
    /* SmartWizard clips its panes (overflow: hidden), which cuts off open dropdown menus; transitions are disabled, so let them overflow. */
    #employeeDataWizard .tab-content {
        height: auto !important;
        overflow: visible !important;
    }

    .employee-data-row-actions {
        display: flex;
        flex-wrap: nowrap;
        gap: 0.5rem;
    }

    .employee-data-row-actions .btn {
        width: 2.75rem;
        height: 2.75rem;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .employee-data-required-mark {
        color: #dc3545;
        margin-left: 0.15rem;
    }

    .employee-data-save-error {
        color: #dc3545;
    }

    #employeeDataWizard fieldset[disabled] .employee-data-row-actions {
        display: none;
    }

    #employeeDataWizard .js-repeatable-row + .js-repeatable-row {
        border-top: 1px dashed #e7eaf3;
        padding-top: 0.75rem;
    }
</style>
