<style>
    .hr-participant-picker {
        position: relative;
    }

    .hr-participant-toggle {
        align-items: center;
        background: #fff;
        border: 1px solid #e2e2e2;
        border-radius: 12px;
        color: #6f7483;
        display: flex;
        font-weight: 500;
        justify-content: space-between;
        min-height: 45px;
        text-align: left;
        width: 100%;
    }

    .hr-participant-toggle:focus,
    .hr-participant-picker.is-open .hr-participant-toggle {
        border-color: #2444c3;
        box-shadow: 0 0 0 0.2rem rgba(36, 68, 195, 0.12);
        outline: 0;
    }

    .hr-participant-menu {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 16px 38px rgba(15, 23, 42, 0.12);
        display: none;
        left: 0;
        max-height: 410px;
        overflow: auto;
        padding: 10px;
        position: absolute;
        right: 0;
        top: calc(100% + 6px);
        z-index: 1070;
    }

    .hr-participant-picker.is-open .hr-participant-menu {
        display: block;
    }

    .hr-participant-search {
        border: 1px solid #dbe1f1;
        border-radius: 10px;
        height: 42px;
        margin-bottom: 10px;
        padding: 0 12px;
        width: 100%;
    }

    .hr-participant-section-title {
        color: #7b8190;
        font-size: 12px;
        font-weight: 600;
        padding: 8px 8px 5px;
    }

    .hr-participant-divider {
        border-top: 1px solid #edf0f5;
        margin: 6px -10px;
    }

    .hr-participant-group-option,
    .hr-participant-employee-option {
        align-items: center;
        background: transparent;
        border: 0;
        border-radius: 8px;
        color: #222b40;
        display: flex;
        gap: 10px;
        min-height: 38px;
        padding: 8px 10px;
        width: 100%;
    }

    .hr-participant-group-option {
        justify-content: space-between;
        text-align: left;
    }

    .hr-participant-group-option:hover,
    .hr-participant-group-option.is-active,
    .hr-participant-employee-option:hover {
        background: #f4f6fb;
    }

    .hr-participant-employee-option input {
        flex: 0 0 auto;
        margin: 0;
    }

    .hr-participant-check {
        color: #2444c3;
        display: none;
        font-size: 12px;
        margin-left: auto;
    }

    .hr-participant-group-option.is-active .hr-participant-check {
        display: inline-block;
    }

    .hr-participant-empty {
        color: #9299a8;
        padding: 8px 10px;
    }
</style>
