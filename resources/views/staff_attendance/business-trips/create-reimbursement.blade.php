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
    'current' => 'Business Trip - Reimbursement',
    'homeRoute' => 'dashboard',
])

@include('staff_attendance.layouts.profile-index')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Business Trip - Reimbursement</h5>
        <div class="d-flex align-items-center">

        </div>
    </div>
</div>
<!-- End - My Projects -->

<div class="card">
    <div class="card-header border-0 pb-0">
        <div>
            <h4 class="card-title">Reimbursement Form</h4>
            <p class="fs-13 mb-0">Please itemize your trip expenses below and attach clear photos or PDFs of all receipts. Claims must be submitted within 7 days of returning from your trip.</p>
        </div>
    </div>
    <form method="POST" action="{{ route('attendance.business-trips.reimbursements.store', $businessTrip) }}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div id="businessTripReimbursementRows">
                @foreach (($businessTripReimbursementRows ?? collect()) as $reimbursementRowIndex => $reimbursementRow)
                    <div class="row business-trip-reimbursement-row">
                        <input type="hidden" name="reimbursement_ids[]" value="{{ $reimbursementRow['id'] ?? '' }}">
                        <input type="hidden" class="business-trip-reimbursement-existing-receipt" name="existing_receipt_paths[]" value="{{ $reimbursementRow['receipt_path'] ?? '' }}">
                        <div class="col-12 col-md-2">
                            <div class="mb-3">
                                <label for="businessTripReimbursementDate_{{ $reimbursementRowIndex }}" class="form-label">Date</label>
                                <input type="text" class="form-control business-trip-reimbursement-date-picker" id="businessTripReimbursementDate_{{ $reimbursementRowIndex }}" name="reimbursement_dates[]" data-field-base-id="businessTripReimbursementDate" placeholder="Date" value="{{ $reimbursementRow['expense_date'] ?? '' }}" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="mb-3">
                                <label for="businessTripReimbursementAmount_{{ $reimbursementRowIndex }}" class="form-label">Amount</label>
                                <input type="text" class="form-control business-trip-reimbursement-currency-input" id="businessTripReimbursementAmount_{{ $reimbursementRowIndex }}" name="reimbursement_amounts[]" data-field-base-id="businessTripReimbursementAmount" placeholder="Rp. 0" value="{{ $reimbursementRow['amount'] ?? '' }}" inputmode="numeric">
                            </div>
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="mb-3">
                                <label for="businessTripReimbursementCategory_{{ $reimbursementRowIndex }}" class="form-label">Category</label>
                                <select class="form-select" id="businessTripReimbursementCategory_{{ $reimbursementRowIndex }}" name="reimbursement_categories[]" data-field-base-id="businessTripReimbursementCategory" required>
                                    <option value="accommodation" @selected(($reimbursementRow['category'] ?? '') === 'accommodation')>Accommodation</option>
                                    <option value="transportation" @selected(($reimbursementRow['category'] ?? '') === 'transportation')>Transportation</option>
                                    <option value="meals_entertainment" @selected(($reimbursementRow['category'] ?? '') === 'meals_entertainment')>Meals & Entertaintment</option>
                                    <option value="local_transport" @selected(($reimbursementRow['category'] ?? '') === 'local_transport')>Local Transport</option>
                                    <option value="others" @selected(($reimbursementRow['category'] ?? '') === 'others')>Others</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="mb-3">
                                <label for="businessTripReimbursementNotes_{{ $reimbursementRowIndex }}" class="form-label">Notes</label>
                                <input type="text" class="form-control" id="businessTripReimbursementNotes_{{ $reimbursementRowIndex }}" name="reimbursement_notes[]" data-field-base-id="businessTripReimbursementNotes" value="{{ $reimbursementRow['notes'] ?? '' }}" placeholder="Reimbursement Notes">
                            </div>
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="mb-3">
                                <label for="businessTripReimbursementReceipt_{{ $reimbursementRowIndex }}" class="form-label">Receipt</label>
                                <input type="file" class="form-control" id="businessTripReimbursementReceipt_{{ $reimbursementRowIndex }}" name="reimbursement_receipts[]" data-field-base-id="businessTripReimbursementReceipt">
                                @if (! empty($reimbursementRow['receipt_url']))
                                    <a class="business-trip-reimbursement-current-receipt text-blue fw-semibold d-inline-block mt-2" href="{{ $reimbursementRow['receipt_url'] }}" target="_blank" rel="noopener">Current receipt</a>
                                @else
                                    <span class="business-trip-reimbursement-current-receipt text-danger fw-semibold d-inline-block mt-2 d-none">Current receipt</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="mb-3">
                                <label class="form-label">Action</label>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-success light me-2 business-trip-reimbursement-add">Add</button>
                                    <button type="button" class="btn btn-danger light business-trip-reimbursement-remove">Remove</button>
                                </div>
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
            var $reimbursementRows = $('#businessTripReimbursementRows');

            function formatRupiahInputValue(value) {
                var numericValue = String(value || '').replace(/\D/g, '');

                if (!numericValue) {
                    return '';
                }

                return 'Rp. ' + numericValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function initializeReimbursementDatePickers($scope) {
                if (!$.fn.daterangepicker) {
                    return;
                }

                $scope.find('.business-trip-reimbursement-date-picker').each(function () {
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

            function initializeReimbursementCurrencyInputs($scope) {
                $scope.find('.business-trip-reimbursement-currency-input').each(function () {
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

            function clearReimbursementRow($row) {
                $row.find('input, select').each(function () {
                    var $field = $(this);

                    if ($field.attr('type') === 'hidden') {
                        $field.val('');
                        return;
                    }

                    if ($field.is('select')) {
                        $field.prop('selectedIndex', 0);
                        return;
                    }

                    $field.val('');
                });

                $row.find('.business-trip-reimbursement-current-receipt')
                    .addClass('d-none')
                    .removeAttr('href target rel');
            }

            function renumberReimbursementRows() {
                $reimbursementRows.find('.business-trip-reimbursement-row').each(function (index) {
                    $(this).find('[data-field-base-id]').each(function () {
                        var $field = $(this);
                        var fieldId = $field.data('field-base-id') + '_' + index;

                        $field.attr('id', fieldId);
                        $field.closest('.mb-3').find('label').attr('for', fieldId);
                    });
                });
            }

            function addReimbursementRow() {
                var $row = $reimbursementRows.find('.business-trip-reimbursement-row').first().clone();

                $row.find('.business-trip-reimbursement-date-picker').removeData('daterangepicker-initialized');
                $row.find('.business-trip-reimbursement-currency-input').removeData('rupiah-initialized');

                clearReimbursementRow($row);

                $reimbursementRows.append($row);
                renumberReimbursementRows();
                initializeReimbursementDatePickers($row);
                initializeReimbursementCurrencyInputs($row);
            }

            function removeReimbursementRow($row) {
                var $rows = $reimbursementRows.find('.business-trip-reimbursement-row');

                if ($rows.length === 1) {
                    clearReimbursementRow($row);
                    return;
                }

                $row.remove();
                renumberReimbursementRows();
            }

            $reimbursementRows.on('click', '.business-trip-reimbursement-add', function () {
                addReimbursementRow();
            });

            $reimbursementRows.on('click', '.business-trip-reimbursement-remove', function () {
                removeReimbursementRow($(this).closest('.business-trip-reimbursement-row'));
            });

            initializeReimbursementDatePickers($reimbursementRows);
            initializeReimbursementCurrencyInputs($reimbursementRows);

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
