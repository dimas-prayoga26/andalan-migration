<link rel="stylesheet" href="{{ asset('assets/vendor/jquery-smartwizard/dist/css/smart_wizard.min.css') }}">
<style>
    /* SmartWizard clips its panes (overflow: hidden), which cuts off open dropdown menus; transitions are disabled, so let them overflow. */
    #employeeDataWizard .tab-content {
        height: auto !important;
        overflow: visible !important;
    }

    /* Neutral step nav that follows the app theme instead of SmartWizard's teal/green defaults. */
    #employeeDataWizard.sw-theme-default > .nav {
        box-shadow: none !important;
        border-bottom: 1px solid var(--bs-border-color, #e7eaf3);
    }

    #employeeDataWizard.sw-theme-default > .nav .nav-link {
        color: var(--bs-body-color, #6b7280);
        font-weight: 600;
    }

    #employeeDataWizard.sw-theme-default > .nav .nav-link.done {
        color: var(--bs-body-color, #6b7280) !important;
    }

    #employeeDataWizard.sw-theme-default > .nav .nav-link.done::after {
        background: rgba(107, 114, 128, 0.35);
    }

    #employeeDataWizard.sw-theme-default > .nav .nav-link.active {
        color: var(--bs-primary) !important;
    }

    #employeeDataWizard.sw-theme-default > .nav .nav-link.active::after {
        background: var(--bs-primary) !important;
    }

    /* SmartWizard stacks the step nav vertically under 640px; keep the five numbered steps on one row instead. */
    @media screen and (max-width: 640px) {
        #employeeDataWizard.sw > .nav {
            flex-direction: row !important;
            flex-wrap: nowrap;
        }

        #employeeDataWizard.sw > .nav > li {
            flex: 1 1 0;
            min-width: 0;
        }

        #employeeDataWizard.sw > .nav .nav-link {
            padding: 0.6rem 0.25rem;
            text-align: center;
        }
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
