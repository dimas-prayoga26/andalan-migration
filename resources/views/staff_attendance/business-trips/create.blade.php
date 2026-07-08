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
    'current' => 'Business Trip - Create',
    'homeRoute' => 'dashboard',
])

@include('staff_attendance.layouts.profile-index')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Business Trip - Create</h5>
        <div class="d-flex align-items-center">

        </div>
    </div>
</div>
<!-- End - My Projects -->

<div class="card">
    <div class="card-header border-0 pb-0">
        <div>
            <h4 class="card-title">Business Trip Request</h4>
            <p class="fs-13 mb-0">
                Please fill out the details below to request approval and arrange logistics for your upcoming trip.
            </p>
        </div>
    </div>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('attendance.business-trips.store') }}">
            @csrf
            <div class="row">
            <div class="col-12 col-md-12">
                <div class="mb-3">
                    <label for="businessTripPurposeInput" class="form-label">Purpose</label>
                    <input type="text" class="form-control" id="businessTripPurposeInput" name="purpose" value="{{ old('purpose') }}" placeholder="Purpose" required>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="mb-3">
                    <label for="businessTripDateRangeInput" class="form-label">Dates</label>
                    <input type="hidden" name="start_date" id="businessTripStartDateInput">
                    <input type="hidden" name="end_date" id="businessTripEndDateInput">
                    <input type="text" class="form-control" id="businessTripDateRangeInput" placeholder="Select date range" readonly>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="mb-3">
                    <label for="businessTripTypeSelect" class="form-label">Business Trip Type</label>
                    <select class="selectpicker form-select" id="businessTripTypeSelect" name="trip_type" required>
                        <option value="local" @selected(old('trip_type') === 'local')>Local (Dalam Kota)</option>
                        <option value="intercity" @selected(old('trip_type') === 'intercity')>Intercity (Luar Kota)</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="mb-3">
                    <label for="businessTripProvinceSelect" class="form-label">Province</label>
                    <input type="hidden" name="province_destination" id="businessTripProvinceDestinationInput">
                    <select class="selectpicker form-select" id="businessTripProvinceSelect" name="province_code" required>
                        <option value="">Loading provinces...</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="mb-3">
                    <label for="businessTripCityRegencySelect" class="form-label">City / Regency</label>
                    <input type="hidden" name="city_destination" id="businessTripCityDestinationInput">
                    <select class="selectpicker form-select" id="businessTripCityRegencySelect" name="city_regency_code" required disabled>
                        <option value="">Choose province first</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label for="businessTripTransportationSelect" class="form-label">Transportation</label>
                    <select class="selectpicker form-select" id="businessTripTransportationSelect" name="transportation_arrangement" required>
                        <option value="self_managed">Self-Managed</option>
                        <option value="booked_by_ga">Booked by GA</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label for="businessTripAccommodationSelect" class="form-label">Accommodation</label>
                    <select class="selectpicker form-select" id="businessTripAccommodationSelect" name="accommodation_arrangement" required>
                        <option value="self_managed">Self-Managed</option>
                        <option value="booked_by_ga">Booked by GA</option>
                        <option value="not_needed">Not Needed</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-12" id="businessTripTransportationModeWrapper">
                <div class="mb-3">
                    <label class="form-label">Transportation Mode</label>
                    <div class="form-group mt-1 mb-0">
                        <div class="form-check d-inline-block me-3">
                            <input class="form-check-input" type="radio" name="transportation_mode" id="businessTripTransportationModeFlight" value="flight" @checked(old('transportation_mode') === 'flight') required>
                            <label class="form-check-label" for="businessTripTransportationModeFlight">Flight</label>
                        </div>
                        <div class="form-check d-inline-block me-3">
                            <input class="form-check-input" type="radio" name="transportation_mode" id="businessTripTransportationModeBus" value="bus" @checked(old('transportation_mode') === 'bus') required>
                            <label class="form-check-label" for="businessTripTransportationModeBus">Bus</label>
                        </div>
                        <div class="form-check d-inline-block me-3">
                            <input class="form-check-input" type="radio" name="transportation_mode" id="businessTripTransportationModeTrain" value="train" @checked(old('transportation_mode') === 'train') required>
                            <label class="form-check-label" for="businessTripTransportationModeTrain">Train</label>
                        </div>
                        <div class="form-check d-inline-block me-3">
                            <input class="form-check-input" type="radio" name="transportation_mode" id="businessTripTransportationModeCar" value="car" @checked(old('transportation_mode') === 'car') required>
                            <label class="form-check-label" for="businessTripTransportationModeCar">Car</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3" id="businessTripDepartureDateWrapper">
                <div class="mb-3">
                    <label for="businessTripDepartureDateInput" class="form-label">Departure Date</label>
                    <input type="hidden" name="departure_date" id="businessTripDepartureDateValueInput">
                    <input type="text" class="form-control business-trip-single-date-picker" id="businessTripDepartureDateInput" data-date-target="#businessTripDepartureDateValueInput" placeholder="Select departure date" readonly>
                </div>
            </div>
            <div class="col-12 col-md-3" id="businessTripDepartureTimeWrapper">
                <div class="mb-3">
                    <label for="businessTripDepartureTimeSelect" class="form-label">Departure Time</label>
                    <select class="selectpicker form-select" id="businessTripDepartureTimeSelect" name="departure_time_window" required>
                        <option value="">Choose departure time</option>
                        <option value="morning" @selected(old('departure_time_window') === 'morning')>Morning (06:00 - 11:59)</option>
                        <option value="afternoon" @selected(old('departure_time_window') === 'afternoon')>Afternoon (12:00 - 17:59)</option>
                        <option value="evening" @selected(old('departure_time_window') === 'evening')>Evening (18:00 - 23:59)</option>
                        <option value="early_morning" @selected(old('departure_time_window') === 'early_morning')>Early Morning (00:00 - 05:59)</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-3" id="businessTripCheckInDateWrapper">
                <div class="mb-3">
                    <label for="businessTripCheckInDateInput" class="form-label">Date Check In</label>
                    <input type="hidden" name="check_in_date" id="businessTripCheckInDateValueInput">
                    <input type="text" class="form-control business-trip-single-date-picker" id="businessTripCheckInDateInput" data-date-target="#businessTripCheckInDateValueInput" placeholder="Select check in date" readonly>
                </div>
            </div>
            <div class="col-12 col-md-3" id="businessTripCheckOutDateWrapper">
                <div class="mb-3">
                    <label for="businessTripCheckOutDateInput" class="form-label">Date Check Out</label>
                    <input type="hidden" name="check_out_date" id="businessTripCheckOutDateValueInput">
                    <input type="text" class="form-control business-trip-single-date-picker" id="businessTripCheckOutDateInput" data-date-target="#businessTripCheckOutDateValueInput" placeholder="Select check out date" readonly>
                </div>
            </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <a class="btn light btn-danger me-2 mb-2 btn-lg" href="{{ route('attendance.business-trips') }}">Back</a>
                <button type="submit" class="btn light btn-success mb-2 btn-lg">Submit</button>
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
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script>
        $(function () {
            $('.attendance-tab-btn').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var targetUrl = $(this).data('href');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });

            var $businessTripDateRangeInput = $('#businessTripDateRangeInput');
            var $businessTripStartDateInput = $('#businessTripStartDateInput');
            var $businessTripEndDateInput = $('#businessTripEndDateInput');
            var $businessTripSingleDateInputs = $('.business-trip-single-date-picker');
            var businessTripProvinceUrl = @json(route('attendance.business-trips.provinces'));
            var businessTripRegencyUrlTemplate = @json(route('attendance.business-trips.regencies', ['provinceCode' => '__PROVINCE_CODE__']));
            var $businessTripProvinceSelect = $('#businessTripProvinceSelect');
            var $businessTripProvinceDestinationInput = $('#businessTripProvinceDestinationInput');
            var $businessTripCityRegencySelect = $('#businessTripCityRegencySelect');
            var $businessTripCityDestinationInput = $('#businessTripCityDestinationInput');
            var $businessTripTransportationSelect = $('#businessTripTransportationSelect');
            var $businessTripTransportationModeWrapper = $('#businessTripTransportationModeWrapper');
            var $businessTripTransportationModeInputs = $('input[name="transportation_mode"]');
            var $businessTripDepartureDateWrapper = $('#businessTripDepartureDateWrapper');
            var $businessTripDepartureDateInput = $('#businessTripDepartureDateInput');
            var $businessTripDepartureDateValueInput = $('#businessTripDepartureDateValueInput');
            var $businessTripDepartureTimeWrapper = $('#businessTripDepartureTimeWrapper');
            var $businessTripDepartureTimeSelect = $('#businessTripDepartureTimeSelect');
            var $businessTripAccommodationSelect = $('#businessTripAccommodationSelect');
            var $businessTripCheckInDateWrapper = $('#businessTripCheckInDateWrapper');
            var $businessTripCheckInDateInput = $('#businessTripCheckInDateInput');
            var $businessTripCheckInDateValueInput = $('#businessTripCheckInDateValueInput');
            var $businessTripCheckOutDateWrapper = $('#businessTripCheckOutDateWrapper');
            var $businessTripCheckOutDateInput = $('#businessTripCheckOutDateInput');
            var $businessTripCheckOutDateValueInput = $('#businessTripCheckOutDateValueInput');

            function refreshSelectPicker($select) {
                if ($.fn.selectpicker) {
                    $select.selectpicker('refresh');
                }
            }

            function replaceSelectOptions($select, options, placeholder, disabled) {
                $select.empty().append($('<option>', {
                    value: '',
                    text: placeholder
                }));

                options.forEach(function (option) {
                    $select.append($('<option>', {
                        value: option.code,
                        text: option.name
                    }));
                });

                $select.prop('disabled', disabled);
                refreshSelectPicker($select);
            }

            function clearCityRegencySelect(placeholder) {
                $businessTripCityDestinationInput.val('');
                replaceSelectOptions($businessTripCityRegencySelect, [], placeholder, true);
            }

            function setSelectedOptionText($select, $targetInput) {
                var selectedText = $select.find('option:selected').text();
                $targetInput.val($select.val() ? selectedText : '');
            }

            function loadProvinces() {
                fetch(businessTripProvinceUrl)
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Province API request failed');
                        }

                        return response.json();
                    })
                    .then(function (response) {
                        replaceSelectOptions($businessTripProvinceSelect, response.data || [], 'Choose Province', false);
                        clearCityRegencySelect('Choose province first');
                    })
                    .catch(function () {
                        replaceSelectOptions($businessTripProvinceSelect, [], 'Failed to load provinces', true);
                        clearCityRegencySelect('Choose province first');
                    });
            }

            function loadCityRegencies(provinceCode) {
                clearCityRegencySelect('Loading city / regency...');

                fetch(businessTripRegencyUrlTemplate.replace('__PROVINCE_CODE__', provinceCode))
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Regency API request failed');
                        }

                        return response.json();
                    })
                    .then(function (response) {
                        replaceSelectOptions($businessTripCityRegencySelect, response.data || [], 'Choose City / Regency', false);
                    })
                    .catch(function () {
                        replaceSelectOptions($businessTripCityRegencySelect, [], 'Failed to load city / regency', true);
                    });
            }

            function toggleTransportationFields() {
                var shouldShowTransportationBookingFields = $businessTripTransportationSelect.val() === 'booked_by_ga';

                $businessTripTransportationModeWrapper.toggleClass('d-none', !shouldShowTransportationBookingFields);
                $businessTripDepartureDateWrapper.toggleClass('d-none', !shouldShowTransportationBookingFields);
                $businessTripDepartureTimeWrapper.toggleClass('d-none', !shouldShowTransportationBookingFields);
                $businessTripTransportationModeInputs.prop('required', shouldShowTransportationBookingFields);
                $businessTripTransportationModeInputs.prop('disabled', !shouldShowTransportationBookingFields);
                $businessTripDepartureDateInput.prop('required', shouldShowTransportationBookingFields);
                $businessTripDepartureTimeSelect.prop('required', shouldShowTransportationBookingFields);
                $businessTripDepartureTimeSelect.prop('disabled', !shouldShowTransportationBookingFields);

                if (!shouldShowTransportationBookingFields) {
                    $businessTripTransportationModeInputs.prop('checked', false);
                    $businessTripDepartureDateInput.val('');
                    $businessTripDepartureDateValueInput.val('');
                    $businessTripDepartureTimeSelect.selectpicker('val', '');
                }

                refreshSelectPicker($businessTripDepartureTimeSelect);
            }

            function toggleAccommodationFields() {
                var shouldShowAccommodationBookingFields = $businessTripAccommodationSelect.val() === 'booked_by_ga';

                $businessTripCheckInDateWrapper.toggleClass('d-none', !shouldShowAccommodationBookingFields);
                $businessTripCheckOutDateWrapper.toggleClass('d-none', !shouldShowAccommodationBookingFields);
                $businessTripCheckInDateInput.prop('required', shouldShowAccommodationBookingFields);
                $businessTripCheckOutDateInput.prop('required', shouldShowAccommodationBookingFields);

                if (!shouldShowAccommodationBookingFields) {
                    $businessTripCheckInDateInput.val('');
                    $businessTripCheckInDateValueInput.val('');
                    $businessTripCheckOutDateInput.val('');
                    $businessTripCheckOutDateValueInput.val('');
                }
            }

            if ($.fn.daterangepicker && $businessTripDateRangeInput.length) {
                $businessTripDateRangeInput.daterangepicker({
                    autoApply: true,
                    autoUpdateInput: false,
                    locale: {
                        format: 'DD/MM/YYYY',
                        cancelLabel: 'Clear'
                    }
                });

                $businessTripDateRangeInput.on('apply.daterangepicker', function (event, picker) {
                    $(this).val(
                        picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY')
                    );
                    $businessTripStartDateInput.val(picker.startDate.format('YYYY-MM-DD'));
                    $businessTripEndDateInput.val(picker.endDate.format('YYYY-MM-DD'));
                });

                $businessTripDateRangeInput.on('cancel.daterangepicker', function () {
                    $(this).val('');
                    $businessTripStartDateInput.val('');
                    $businessTripEndDateInput.val('');
                });
            }

            if ($.fn.daterangepicker && $businessTripSingleDateInputs.length) {
                $businessTripSingleDateInputs.each(function () {
                    var $singleDateInput = $(this);

                    $singleDateInput.daterangepicker({
                        autoApply: true,
                        autoUpdateInput: false,
                        singleDatePicker: true,
                        locale: {
                            format: 'DD/MM/YYYY',
                            cancelLabel: 'Clear'
                        }
                    });

                    $singleDateInput.on('apply.daterangepicker', function (event, picker) {
                        var $targetInput = $($(this).data('date-target'));
                        $(this).val(picker.startDate.format('DD/MM/YYYY'));
                        $targetInput.val(picker.startDate.format('YYYY-MM-DD'));
                    });

                    $singleDateInput.on('cancel.daterangepicker', function () {
                        var $targetInput = $($(this).data('date-target'));
                        $(this).val('');
                        $targetInput.val('');
                    });
                });
            }

            $businessTripProvinceSelect.on('changed.bs.select change', function () {
                var provinceCode = $(this).val();
                setSelectedOptionText($businessTripProvinceSelect, $businessTripProvinceDestinationInput);

                if (provinceCode) {
                    loadCityRegencies(provinceCode);
                    return;
                }

                clearCityRegencySelect('Choose province first');
            });

            $businessTripCityRegencySelect.on('changed.bs.select change', function () {
                setSelectedOptionText($businessTripCityRegencySelect, $businessTripCityDestinationInput);
            });
            $businessTripTransportationSelect.on('changed.bs.select change', toggleTransportationFields);
            $businessTripAccommodationSelect.on('changed.bs.select change', toggleAccommodationFields);
            loadProvinces();
            toggleTransportationFields();
            toggleAccommodationFields();
        });
    </script>
@endsection
