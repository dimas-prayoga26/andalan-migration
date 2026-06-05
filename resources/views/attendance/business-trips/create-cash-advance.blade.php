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
    <form method="POST" action="{{ route('attendance.business-trips.cash-advances.store', $businessTrip) }}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div id="businessTripCashAdvanceRequestRows">
                @foreach (($businessTripCashAdvanceRows ?? collect()) as $cashAdvanceRowIndex => $cashAdvanceRow)
                    <div class="row business-trip-cash-advance-request-row">
                        <input type="hidden" name="cash_advance_ids[]" value="{{ $cashAdvanceRow['id'] ?? '' }}">
                        <div class="col-12 col-md-2">
                            <div class="mb-3">
                                <label for="cashAdvanceRequestDate_{{ $cashAdvanceRowIndex }}" class="form-label">Date Range</label>
                                <input type="text" class="form-control business-trip-cash-advance-date-picker" id="cashAdvanceRequestDate_{{ $cashAdvanceRowIndex }}" name="request_dates[]" data-field-base-id="cashAdvanceRequestDate" placeholder="Date Range Needed" value="{{ $cashAdvanceRow['date_needed'] ?? '' }}" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="mb-3">
                                <label for="cashAdvanceRequestAmount_{{ $cashAdvanceRowIndex }}" class="form-label">Amount</label>
                                <input type="text" class="form-control business-trip-cash-advance-currency-input" id="cashAdvanceRequestAmount_{{ $cashAdvanceRowIndex }}" name="request_amounts[]" data-field-base-id="cashAdvanceRequestAmount" placeholder="Rp. 0" value="{{ $cashAdvanceRow['amount_requested'] ?? '' }}" inputmode="numeric">
                            </div>
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="mb-3">
                                <label for="cashAdvanceRequestBreakdown_{{ $cashAdvanceRowIndex }}" class="form-label">Breakdown</label>
                                <select class="form-select" id="cashAdvanceRequestBreakdown_{{ $cashAdvanceRowIndex }}" name="request_breakdowns[]" data-field-base-id="cashAdvanceRequestBreakdown" required>
                                    <option value="accommodation" @selected(($cashAdvanceRow['category'] ?? '') === 'accommodation')>Accommodation</option>
                                    <option value="transportation" @selected(($cashAdvanceRow['category'] ?? '') === 'transportation')>Transportation</option>
                                    <option value="meals_entertainment" @selected(($cashAdvanceRow['category'] ?? '') === 'meals_entertainment')>Meals & Entertaintment</option>
                                    <option value="local_transport" @selected(($cashAdvanceRow['category'] ?? '') === 'local_transport')>Local Transport</option>
                                    <option value="others" @selected(($cashAdvanceRow['category'] ?? '') === 'others')>Others</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label for="cashAdvanceRequestNotes_{{ $cashAdvanceRowIndex }}" class="form-label">Notes</label>
                                <input type="text" class="form-control" id="cashAdvanceRequestNotes_{{ $cashAdvanceRowIndex }}" name="request_notes[]" data-field-base-id="cashAdvanceRequestNotes" value="{{ $cashAdvanceRow['notes'] ?? '' }}" placeholder="Cash advance for local transportation (airport taxis), daily meals, and client entertainment">
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
                                <label for="cashAdvanceRequestAmountRealized_{{ $cashAdvanceRowIndex }}" class="form-label">Amount Realized</label>
                                <input type="text" class="form-control business-trip-cash-advance-currency-input" id="cashAdvanceRequestAmountRealized_{{ $cashAdvanceRowIndex }}" name="request_amount_realized[]" data-field-base-id="cashAdvanceRequestAmountRealized" placeholder="Rp. 0" value="{{ $cashAdvanceRow['amount_realized'] ?? '' }}" inputmode="numeric">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label for="cashAdvanceRequestAttachment_{{ $cashAdvanceRowIndex }}" class="form-label">Attachment</label>
                                <input type="hidden" name="existing_attachment_paths[]" value="{{ $cashAdvanceRow['attachment_path'] ?? '' }}">
                                <input type="file" class="form-control" id="cashAdvanceRequestAttachment_{{ $cashAdvanceRowIndex }}" name="request_attachments[]" data-field-base-id="cashAdvanceRequestAttachment">
                                @if (! empty($cashAdvanceRow['attachment_url']))
                                    <a class="text-blue fw-semibold d-inline-block mt-1 business-trip-cash-advance-current-attachment" href="{{ $cashAdvanceRow['attachment_url'] }}" target="_blank" rel="noopener">Current attachment</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <hr>
            <h6 class="card-title">Approved By Finance</h6>
            <div id="businessTripCashAdvanceFinanceRows">
                @foreach (($businessTripCashAdvanceRows ?? collect()) as $cashAdvanceRowIndex => $cashAdvanceRow)
                    @php
                        $cashAdvanceFinanceApproved = (bool) ($cashAdvanceRow['is_approved'] ?? false);
                    @endphp
                    <div class="row business-trip-cash-advance-finance-row">
                        <div class="col-12 col-md-2">
                            <div class="mb-3">
                                <label for="cashAdvanceFinanceDate_{{ $cashAdvanceRowIndex }}" class="form-label">Date</label>
                                <input type="text" class="form-control" id="cashAdvanceFinanceDate_{{ $cashAdvanceRowIndex }}" data-field-base-id="cashAdvanceFinanceDate" placeholder="Date Approved" value="{{ $cashAdvanceFinanceApproved ? ($cashAdvanceRow['finance_date'] ?? '') : '' }}" disabled>
                            </div>
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="mb-3">
                                <label for="cashAdvanceFinanceAmount_{{ $cashAdvanceRowIndex }}" class="form-label">Amount</label>
                                <input type="text" class="form-control business-trip-cash-advance-currency-input" id="cashAdvanceFinanceAmount_{{ $cashAdvanceRowIndex }}" data-field-base-id="cashAdvanceFinanceAmount" placeholder="Rp. 0" value="{{ $cashAdvanceFinanceApproved ? ($cashAdvanceRow['amount_requested'] ?? '') : '' }}" inputmode="numeric" disabled>
                            </div>
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="mb-3">
                                <label for="cashAdvanceFinanceAmountApproved_{{ $cashAdvanceRowIndex }}" class="form-label">Amount Approved</label>
                                <input type="text" class="form-control business-trip-cash-advance-currency-input" id="cashAdvanceFinanceAmountApproved_{{ $cashAdvanceRowIndex }}" data-field-base-id="cashAdvanceFinanceAmountApproved" placeholder="Rp. 0" value="{{ $cashAdvanceFinanceApproved ? ($cashAdvanceRow['amount_approved'] ?? '') : '' }}" inputmode="numeric" disabled>
                            </div>
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="mb-3">
                                <label for="cashAdvanceFinanceBreakdown_{{ $cashAdvanceRowIndex }}" class="form-label">Breakdown</label>
                                <select class="form-select" id="cashAdvanceFinanceBreakdown_{{ $cashAdvanceRowIndex }}" data-field-base-id="cashAdvanceFinanceBreakdown" required disabled>
                                    <option value="" @selected(! $cashAdvanceFinanceApproved)>Breakdown</option>
                                    <option value="accommodation" @selected($cashAdvanceFinanceApproved && ($cashAdvanceRow['category'] ?? '') === 'accommodation')>Accommodation</option>
                                    <option value="transportation" @selected($cashAdvanceFinanceApproved && ($cashAdvanceRow['category'] ?? '') === 'transportation')>Transportation</option>
                                    <option value="meals_entertainment" @selected($cashAdvanceFinanceApproved && ($cashAdvanceRow['category'] ?? '') === 'meals_entertainment')>Meals & Entertaintment</option>
                                    <option value="local_transport" @selected($cashAdvanceFinanceApproved && ($cashAdvanceRow['category'] ?? '') === 'local_transport')>Local Transport</option>
                                    <option value="others" @selected($cashAdvanceFinanceApproved && ($cashAdvanceRow['category'] ?? '') === 'others')>Others</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label for="cashAdvanceFinanceNotes_{{ $cashAdvanceRowIndex }}" class="form-label">Notes</label>
                                <input type="text" class="form-control" id="cashAdvanceFinanceNotes_{{ $cashAdvanceRowIndex }}" data-field-base-id="cashAdvanceFinanceNotes" value="{{ $cashAdvanceFinanceApproved ? ($cashAdvanceRow['finance_notes'] ?? '') : '' }}" placeholder="Note" disabled>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-end mt-3">
                <a class="btn light btn-danger me-2 mb-2 btn-lg" href="{{ isset($businessTrip) ? route('attendance.business-trips.show', $businessTrip) : route('attendance.business-trips') }}">Back</a>
                <button type="submit" class="btn light btn-success mb-2 btn-lg">Submit</button>
            </div>
        </div>
    </form>
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

                    var dateRangeValue = String($dateInput.val() || '').split(' - ');
                    var datePickerOptions = {
                        autoApply: true,
                        autoUpdateInput: false,
                        locale: {
                            format: 'DD/MM/YYYY',
                            cancelLabel: 'Clear'
                        }
                    };

                    if (dateRangeValue[0]) {
                        datePickerOptions.startDate = dateRangeValue[0];
                        datePickerOptions.endDate = dateRangeValue[1] || dateRangeValue[0];
                    }

                    $dateInput.daterangepicker(datePickerOptions);

                    $dateInput.on('apply.daterangepicker', function (event, picker) {
                        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
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

            function resetClonedCashAdvanceSelectPickers($scope) {
                $scope.find('.bootstrap-select').each(function () {
                    var $wrapper = $(this);
                    var $select = $wrapper.find('select').first();

                    if ($select.length) {
                        $select.insertBefore($wrapper);
                    }

                    $wrapper.remove();
                });

                $scope.find('select').each(function () {
                    $(this)
                        .removeClass('bs-select-hidden')
                        .removeAttr('data-id tabindex aria-hidden')
                        .removeData('selectpicker')
                        .show();
                });
            }

            function initializeCashAdvanceSelectPickers($scope) {
                if (!$.fn.selectpicker) {
                    return;
                }

                $scope.find('select').selectpicker();
                $scope.find('select').selectpicker('refresh');
            }

            function clearCashAdvanceRow($row) {
                $row.find('.business-trip-cash-advance-current-attachment').remove();

                $row.find('input, select').each(function () {
                    var $field = $(this);

                    if ($field.is('select')) {
                        $field.prop('selectedIndex', 0).trigger('change');
                        return;
                    }

                    $field.val('');
                });

                initializeCashAdvanceSelectPickers($row);
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

                resetClonedCashAdvanceSelectPickers($requestRow);
                resetClonedCashAdvanceSelectPickers($financeRow);
                clearCashAdvanceRow($requestRow);
                clearCashAdvanceRow($financeRow);

                $cashAdvanceRequestRows.append($requestRow);
                $cashAdvanceFinanceRows.append($financeRow);

                renumberCashAdvanceRows();
                initializeCashAdvanceDatePickers($requestRow);
                initializeCashAdvanceCurrencyInputs($requestRow);
                initializeCashAdvanceCurrencyInputs($financeRow);
                initializeCashAdvanceSelectPickers($requestRow);
                initializeCashAdvanceSelectPickers($financeRow);
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
                initializeCashAdvanceSelectPickers($cashAdvanceRequestRows);
                initializeCashAdvanceSelectPickers($cashAdvanceFinanceRows);
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
            initializeCashAdvanceSelectPickers($cashAdvanceRequestRows);
            initializeCashAdvanceSelectPickers($cashAdvanceFinanceRows);

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
