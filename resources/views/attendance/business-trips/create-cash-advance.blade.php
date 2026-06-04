@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
@endsection

@section('navbarTitle', 'Attendances')

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Attendances',
    'current' => 'Business Trip - Cash Advance',
    'homeRoute' => 'dashboard',
])

@include('attendance.layouts.profile-index')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Business Trip - Cash Advance</h5>
        <div class="d-flex align-items-center">

        </div>
    </div>
</div>
<!-- End - My Projects -->

<div class="card">
    <div class="card-header border-0 pb-0">
        <div>
            <h4 class="card-title">Request Cash Advance</h4>
            <p class="fs-13 mb-0">Please submit your advance request at least 3 days before the funds are needed. Any unspent funds and valid receipts must be reported within 7 days after the trip concludes.</p>
        </div>
    </div>
    <div class="card-body">
        <div id="businessTripCashAdvanceRequestRows">
            <div class="row business-trip-cash-advance-request-row">
                <div class="col-12 col-md-2">
                    <div class="mb-3">
                        <label for="cashAdvanceRequestDate_0" class="form-label">Date</label>
                        <input type="text" class="form-control business-trip-cash-advance-date-picker" id="cashAdvanceRequestDate_0" name="request_dates[]" data-field-base-id="cashAdvanceRequestDate" placeholder="Date Needed" readonly>
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <div class="mb-3">
                        <label for="cashAdvanceRequestAmount_0" class="form-label">Amount</label>
                        <input type="text" class="form-control business-trip-cash-advance-currency-input" id="cashAdvanceRequestAmount_0" name="request_amounts[]" data-field-base-id="cashAdvanceRequestAmount" placeholder="Rp. 0" inputmode="numeric">
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <div class="mb-3">
                        <label for="cashAdvanceRequestBreakdown_0" class="form-label">Breakdown</label>
                        <select class="form-select" id="cashAdvanceRequestBreakdown_0" name="request_breakdowns[]" data-field-base-id="cashAdvanceRequestBreakdown" required>
                            <option value="accommodation">Accommodation</option>
                            <option value="transportation">Transportation</option>
                            <option value="meals_entertainment">Meals & Entertaintment</option>
                            <option value="local_transport">Local Transport</option>
                            <option value="others">Others</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="mb-3">
                        <label for="cashAdvanceRequestNotes_0" class="form-label">Notes</label>
                        <input type="text" class="form-control" id="cashAdvanceRequestNotes_0" name="request_notes[]" data-field-base-id="cashAdvanceRequestNotes" placeholder="Cash advance for local transportation (airport taxis), daily meals, and client entertainment">
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-success light me-2 business-trip-cash-advance-add">Add</button>
                            <button type="button" class="btn btn-danger light business-trip-cash-advance-remove">Remove</button>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <div class="mb-3">
                        <label for="cashAdvanceRequestAmountRealized_0" class="form-label">Amount Realized</label>
                        <input type="text" class="form-control business-trip-cash-advance-currency-input" id="cashAdvanceRequestAmountRealized_0" name="request_amount_realized[]" data-field-base-id="cashAdvanceRequestAmountRealized" placeholder="Rp. 0" inputmode="numeric">
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="mb-3">
                        <label for="cashAdvanceRequestAttachment_0" class="form-label">Attachments</label>
                        <input type="file" class="form-control" id="cashAdvanceRequestAttachment_0" name="request_attachments[]" data-field-base-id="cashAdvanceRequestAttachment">
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <h6 class="card-title">Approved By Finance</h6>
        <div id="businessTripCashAdvanceFinanceRows">
            <div class="row business-trip-cash-advance-finance-row">
                <div class="col-12 col-md-2">
                    <div class="mb-3">
                        <label for="cashAdvanceFinanceDate_0" class="form-label">Date</label>
                        <input type="text" class="form-control" id="cashAdvanceFinanceDate_0" data-field-base-id="cashAdvanceFinanceDate" placeholder="Date Needed" disabled>
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <div class="mb-3">
                        <label for="cashAdvanceFinanceAmount_0" class="form-label">Amount</label>
                        <input type="text" class="form-control business-trip-cash-advance-currency-input" id="cashAdvanceFinanceAmount_0" data-field-base-id="cashAdvanceFinanceAmount" placeholder="Rp. 0" inputmode="numeric" disabled>
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <div class="mb-3">
                        <label for="cashAdvanceFinanceAmountApproved_0" class="form-label">Amount Approved</label>
                        <input type="text" class="form-control business-trip-cash-advance-currency-input" id="cashAdvanceFinanceAmountApproved_0" data-field-base-id="cashAdvanceFinanceAmountApproved" placeholder="Rp. 0" inputmode="numeric" disabled>
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <div class="mb-3">
                        <label for="cashAdvanceFinanceBreakdown_0" class="form-label">Breakdown</label>
                        <select class="form-select" id="cashAdvanceFinanceBreakdown_0" data-field-base-id="cashAdvanceFinanceBreakdown" required disabled>
                            <option value="accommodation">Accommodation</option>
                            <option value="transportation">Transportation</option>
                            <option value="meals_entertainment">Meals & Entertaintment</option>
                            <option value="local_transport">Local Transport</option>
                            <option value="others">Others</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="mb-3">
                        <label for="cashAdvanceFinanceNotes_0" class="form-label">Notes</label>
                        <input type="text" class="form-control" id="cashAdvanceFinanceNotes_0" data-field-base-id="cashAdvanceFinanceNotes" placeholder="Cash advance for local transportation (airport taxis), daily meals, and client entertainment" disabled>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-3">
            <a class="btn light btn-danger me-2 mb-2 btn-lg" href="{{ isset($businessTrip) ? route('attendance.business-trips.show', $businessTrip) : route('attendance.business-trips') }}">Back</a>
            <a class="btn light btn-success mb-2 btn-lg" data-bs-toggle="modal" data-bs-target="#reimbursement">Submit</a>
        </div>
    </div>
</div>

@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script>
        $(function () {
            var $cashAdvanceRequestRows = $('#businessTripCashAdvanceRequestRows');
            var $cashAdvanceFinanceRows = $('#businessTripCashAdvanceFinanceRows');

            function formatRupiahInputValue(value) {
                var numericValue = String(value || '').replace(/\D/g, '');

                if (!numericValue) {
                    return '';
                }

                return 'Rp. ' + numericValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function initializeCashAdvanceDatePickers($scope) {
                if (!$.fn.daterangepicker) {
                    return;
                }

                $scope.find('.business-trip-cash-advance-date-picker').each(function () {
                    var $dateInput = $(this);

                    if ($dateInput.data('daterangepicker-initialized')) {
                        return;
                    }

                    $dateInput.daterangepicker({
                        autoApply: true,
                        autoUpdateInput: false,
                        singleDatePicker: true,
                        locale: {
                            format: 'DD/MM/YYYY',
                            cancelLabel: 'Clear'
                        }
                    });

                    $dateInput.on('apply.daterangepicker', function (event, picker) {
                        $(this).val(picker.startDate.format('DD/MM/YYYY'));
                    });

                    $dateInput.on('cancel.daterangepicker', function () {
                        $(this).val('');
                    });

                    $dateInput.data('daterangepicker-initialized', true);
                });
            }

            function initializeCashAdvanceCurrencyInputs($scope) {
                $scope.find('.business-trip-cash-advance-currency-input').each(function () {
                    var $currencyInput = $(this);

                    if ($currencyInput.data('rupiah-initialized')) {
                        return;
                    }

                    $currencyInput.on('focus', function () {
                        if (!$(this).val()) {
                            $(this).val('Rp. ');
                        }
                    });

                    $currencyInput.on('input', function () {
                        $(this).val(formatRupiahInputValue($(this).val()));
                    });

                    $currencyInput.on('blur', function () {
                        $(this).val(formatRupiahInputValue($(this).val()));
                    });

                    $currencyInput.data('rupiah-initialized', true);
                });
            }

            function clearCashAdvanceRow($row) {
                $row.find('input, select').each(function () {
                    var $field = $(this);

                    if ($field.is('select')) {
                        $field.prop('selectedIndex', 0);
                        return;
                    }

                    $field.val('');
                });
            }

            function renumberCashAdvanceRows() {
                $cashAdvanceRequestRows.find('.business-trip-cash-advance-request-row').each(function (index) {
                    $(this).find('[data-field-base-id]').each(function () {
                        var $field = $(this);
                        var fieldId = $field.data('field-base-id') + '_' + index;

                        $field.attr('id', fieldId);
                        $field.closest('.mb-3').find('label').attr('for', fieldId);
                    });
                });

                $cashAdvanceFinanceRows.find('.business-trip-cash-advance-finance-row').each(function (index) {
                    $(this).find('[data-field-base-id]').each(function () {
                        var $field = $(this);
                        var fieldId = $field.data('field-base-id') + '_' + index;

                        $field.attr('id', fieldId);
                        $field.closest('.mb-3').find('label').attr('for', fieldId);
                    });
                });
            }

            function addCashAdvanceRow() {
                var $requestRow = $cashAdvanceRequestRows.find('.business-trip-cash-advance-request-row').first().clone();
                var $financeRow = $cashAdvanceFinanceRows.find('.business-trip-cash-advance-finance-row').first().clone();

                $requestRow.find('.business-trip-cash-advance-date-picker').removeData('daterangepicker-initialized');
                $requestRow.find('.business-trip-cash-advance-currency-input').removeData('rupiah-initialized');
                $financeRow.find('.business-trip-cash-advance-currency-input').removeData('rupiah-initialized');

                clearCashAdvanceRow($requestRow);
                clearCashAdvanceRow($financeRow);

                $cashAdvanceRequestRows.append($requestRow);
                $cashAdvanceFinanceRows.append($financeRow);

                renumberCashAdvanceRows();
                initializeCashAdvanceDatePickers($requestRow);
                initializeCashAdvanceCurrencyInputs($requestRow);
                initializeCashAdvanceCurrencyInputs($financeRow);
            }

            function removeCashAdvanceRow($requestRow) {
                var rowIndex = $cashAdvanceRequestRows.find('.business-trip-cash-advance-request-row').index($requestRow);
                var $requestRows = $cashAdvanceRequestRows.find('.business-trip-cash-advance-request-row');
                var $financeRows = $cashAdvanceFinanceRows.find('.business-trip-cash-advance-finance-row');

                if ($requestRows.length === 1) {
                    clearCashAdvanceRow($requestRow);
                    clearCashAdvanceRow($financeRows.eq(0));
                    return;
                }

                $requestRow.remove();
                $financeRows.eq(rowIndex).remove();

                renumberCashAdvanceRows();
            }

            $cashAdvanceRequestRows.on('click', '.business-trip-cash-advance-add', function () {
                addCashAdvanceRow();
            });

            $cashAdvanceRequestRows.on('click', '.business-trip-cash-advance-remove', function () {
                removeCashAdvanceRow($(this).closest('.business-trip-cash-advance-request-row'));
            });

            initializeCashAdvanceDatePickers($cashAdvanceRequestRows);
            initializeCashAdvanceCurrencyInputs($cashAdvanceRequestRows);
            initializeCashAdvanceCurrencyInputs($cashAdvanceFinanceRows);

            $('.attendance-tab-btn').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var targetUrl = $(this).data('href');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });
        });
    </script>
@endsection
