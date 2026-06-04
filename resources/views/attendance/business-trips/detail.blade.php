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
    'current' => 'Business Trip Details',
    'homeRoute' => 'dashboard',
])

@include('attendance.layouts.profile-index')

@php
    $canViewBusinessTripExpenseValues = (bool) ($businessTripDetailPermissions['can_view_trip_expense_values'] ?? false);
    $canViewBusinessTripCashAdvanceValues = (bool) ($businessTripDetailPermissions['can_view_cash_advance_values'] ?? false);
    $canViewBusinessTripReimbursementValues = (bool) ($businessTripDetailPermissions['can_view_reimbursement_values'] ?? false);
    $canUseBusinessTripActionButtons = (bool) ($businessTripDetailPermissions['can_use_action_buttons'] ?? false);
@endphp

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Business Trip Details</h5>
        <div class="d-flex align-items-center"></div>
    </div>
</div>

<div class="row">
    <div class="col-xxl-5 col-xl-5 col-md-5">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <div>
                    <h4 class="card-title">Business Trip Request Details</h4>
                    <p class="fs-13 mb-0">Please ensure your travel dates and cash advance requests align with the company's travel policy.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="row py-2">
                    <div class="col-md-6 col-12"><span>Full Name</span></div>
                    <div class="col-md-6 col-12"><span class="text-gray fw-semibold">{{ $businessTripRequestDetails['full_name'] ?? '-' }}</span></div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12"><span>Supervisor</span></div>
                    <div class="col-md-6 col-12"><span class="text-gray fw-semibold">{{ $businessTripRequestDetails['supervisor_name'] ?? '-' }}</span></div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12"><span>Business Trip Purpose</span></div>
                    <div class="col-md-6 col-12"><span class="text-gray">{{ $businessTripRequestDetails['purpose'] ?? '-' }}</span></div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12"><span>Business Trip Type</span></div>
                    <div class="col-md-6 col-12"><span class="text-gray">{{ $businessTripRequestDetails['trip_type'] ?? '-' }}</span></div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12"><span>Trip Destination</span></div>
                    <div class="col-md-6 col-12"><span class="text-gray">{{ $businessTripRequestDetails['destination'] ?? '-' }}</span></div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12"><span>Trip Dates</span></div>
                    <div class="col-md-6 col-12"><span class="text-gray fw-semibold">{{ $businessTripRequestDetails['date_range'] ?? '-' }}</span></div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12"><span>Trip Duration</span></div>
                    <div class="col-md-6 col-12"><span class="text-gray fw-semibold">{{ $businessTripRequestDetails['duration'] ?? '-' }}</span></div>
                </div>
                @foreach ([
                    'Transportation' => [
                        'amount' => 'Rp. 3.000.000',
                        'attachment' => 'Ticket',
                        'description' => 'Kereta Api Taksaka <br> 01 June 2026 - 20:00 WIB',
                    ],
                    'Local Transportation' => [
                        'amount' => 'Rp. 500.000',
                        'description' => 'Taxi to Airport etc',
                    ],
                    'Accommodation' => [
                        'amount' => 'Rp. 1.000.000',
                        'attachment' => 'Receipt',
                        'description' => 'POP Hotel <br> 01 June 2026 - 02 June 2026',
                    ],
                    'Meals & Entertainment' => [
                        'amount' => 'Rp. 1.000.000',
                        'description' => 'Client dinner & daily meals',
                    ],
                    'Others' => [
                        'amount' => 'Rp. 500.000',
                        'description' => 'Others',
                    ],
                ] as $businessTripExpenseLabel => $businessTripExpenseValue)
                    <div class="row py-2">
                        <div class="col-md-6 col-12"><span>{{ $businessTripExpenseLabel }}</span></div>
                        <div class="col-md-6 col-12">
                            @if ($canViewBusinessTripExpenseValues)
                                <span class="text-gray fw-semibold">{{ $businessTripExpenseValue['amount'] }}</span> <br>
                                @if (isset($businessTripExpenseValue['attachment']))
                                    <a href=""><span class="text-blue fw-semibold">{{ $businessTripExpenseValue['attachment'] }}</span></a> <br>
                                @endif
                                <span class="text-gray">{!! $businessTripExpenseValue['description'] !!}</span>
                            @else
                                <span class="text-gray">-</span>
                            @endif
                        </div>
                    </div>
                @endforeach
                <hr>
                <div class="row py-2">
                    <div class="col-md-6 col-12"><span>Requested Cash Advance</span></div>
                    <div class="col-md-6 col-12">
                        @if ($canViewBusinessTripExpenseValues)
                            <span class="text-gray fw-semibold">Rp. 2.500.000</span> <br>
                            <span class="text-gray">For local transport, meals, and client entertainment</span>
                        @else
                            <span class="badge badge-sm badge-warning light">Pending</span>
                        @endif
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12"><span>Business Trip Incentive</span></div>
                    <div class="col-md-6 col-12">
                        @if ($canViewBusinessTripExpenseValues)
                            <span class="text-gray fw-semibold">Rp. 300.000</span> <br>
                            <span class="text-gray">Rp. 100.000 x 3 days</span>
                        @else
                            <span class="badge badge-sm badge-warning light">Pending</span>
                        @endif
                    </div>
                </div>
                @foreach (['Status Cash Advance', 'Status Reimbursement', 'Status Incentive'] as $businessTripStatusLabel)
                    <div class="row py-2">
                        <div class="col-md-6 col-12"><span>{{ $businessTripStatusLabel }}</span></div>
                        <div class="col-md-6 col-12">
                            @if ($canViewBusinessTripExpenseValues)
                                <span class="badge badge-sm badge-success light">Paid</span>
                            @else
                                <span class="badge badge-sm badge-warning light">Pending</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($canUseBusinessTripActionButtons)
                <a class="btn light btn-warning m-3 mb-2 btn-lg" data-bs-toggle="modal" data-bs-target="#details">Update Details</a>
            @else
                <a class="btn light btn-warning m-3 mb-2 btn-lg disabled" aria-disabled="true" tabindex="-1">Update Details</a>
            @endif
            <div class="mb-3"></div>
        </div>
    </div>

    <div class="col-xxl-4 col-xl-4 col-md-4">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <div>
                    <h4 class="card-title">Trip Expense & Reimbursement</h4>
                    <p class="fs-13 mb-0">Please ensure all declared expenses match the attached receipts and comply with the finance policy.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-start align-items-center mb-3">
                    <ul class="nav nav-pills nav-pills-sm nav-pills-bg gap-2" id="myTab3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="expense-tab3" data-bs-toggle="tab" data-bs-target="#tabExpense3" type="button" role="tab" aria-controls="tabExpense3" aria-selected="true">Expense</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="cash-advance-tab3" data-bs-toggle="tab" data-bs-target="#tabCashAdvance3" type="button" role="tab" aria-controls="tabCashAdvance3" aria-selected="false">Cash Advance</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="reimbursement-tab3" data-bs-toggle="tab" data-bs-target="#tabReimbursement3" type="button" role="tab" aria-controls="tabReimbursement3" aria-selected="false">Reimbursement</button>
                        </li>
                    </ul>
                </div>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="tabExpense3" role="tabpanel" aria-labelledby="expense-tab3" tabindex="0">
                        @if ($canViewBusinessTripExpenseValues)
                            @foreach ([
                                ['date' => '10 Jun 2026', 'title' => 'Transportation', 'description' => 'Flight ticket (Round trip - Economy)', 'amount' => 'Rp. 3.000.000'],
                                ['date' => '12 Jun 2026', 'title' => 'Accommodation', 'description' => 'Hotel (2 Nights @ Rp 500.000)', 'amount' => 'Rp. 1.000.000'],
                                ['date' => '10-12 Jun 2026', 'title' => 'Meals & Entertaintment', 'description' => 'Client dinner & daily meals', 'amount' => 'Rp. 800.000'],
                                ['date' => '10-12 Jun 2026', 'title' => 'Local Transport', 'description' => 'Taxi to/from airport', 'amount' => 'Rp. 200.000'],
                            ] as $expenseItem)
                                <div class="row py-2">
                                    <div class="col-md-4 col-12"><span>{{ $expenseItem['date'] }}</span></div>
                                    <div class="col-md-8 col-12">
                                        <span class="text-gray fw-semibold">{{ $expenseItem['title'] }}</span> <br>
                                        <span class="text-gray">{{ $expenseItem['description'] }}</span> <br>
                                        <span class="text-gray">{{ $expenseItem['amount'] }}</span> <br>
                                        <a href=""><span class="text-blue fw-semibold">Attachment</span></a>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="border rounded p-5 text-center bg-light d-flex flex-column justify-content-center" style="min-height: 220px;">
                                <span class="text-gray">Expense details will appear after cash advance approval.</span>
                            </div>
                        @endif
                        <hr>
                        @foreach (['Total Expenses', 'Cash Advance', 'Balance Due', 'Trip Incentive', 'Total Payment'] as $expenseSummaryLabel)
                            <div class="row py-2">
                                <div class="col-md-4 col-12"><span>{{ $expenseSummaryLabel }}</span></div>
                                <div class="col-md-8 col-12">
                                    <span class="badge badge-sm badge-warning light">Pending</span> <br>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="tab-pane fade" id="tabCashAdvance3" role="tabpanel" aria-labelledby="cash-advance-tab3" tabindex="0">
                        @if ($canViewBusinessTripCashAdvanceValues)
                            <div class="row py-2">
                                <div class="col-md-4 col-12"><span>10 Jun 2026</span></div>
                                <div class="col-md-8 col-12">
                                    <span class="text-gray fw-semibold">Local Transport</span> <br>
                                    <span class="text-gray">Taxi from Airport etc</span> <br>
                                    <span class="text-decoration-line-through">Rp. 1.000.000</span>
                                    <span class="text-gray">Rp. 500.000</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-md-4 col-12"><span>10 Jun 2026</span></div>
                                <div class="col-md-8 col-12">
                                    <span class="text-gray fw-semibold">Meals & Entertaintment</span> <br>
                                    <span class="text-gray">Meals, and Client Entertainment</span> <br>
                                    <span class="text-gray">Rp. 2.000.000</span>
                                </div>
                            </div>
                        @else
                            <div class="border rounded p-5 text-center bg-light d-flex flex-column justify-content-center" style="min-height: 220px;">
                                <span class="text-gray">Cash advance details will appear after staff submits the cash advance request in Phase 2.</span>
                            </div>
                        @endif
                        <hr>
                        <div class="row py-2">
                            <div class="col-md-4 col-12"><span>Total Payment</span></div>
                            <div class="col-md-8 col-12">
                                @if ($canViewBusinessTripCashAdvanceValues)
                                    <span class="text-gray fw-semibold">Rp. 2.800.000</span> <br>
                                    <span class="text-success">Approved cash advance</span> <br>
                                @else
                                    <span class="badge badge-sm badge-warning light">Pending</span> <br>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tabReimbursement3" role="tabpanel" aria-labelledby="reimbursement-tab3" tabindex="0">
                        @if ($canViewBusinessTripReimbursementValues)
                            @foreach ([
                                ['date' => '10 Jun 2026', 'title' => 'Transportation', 'description' => 'Flight ticket', 'amount' => 'Rp. 1.500.000'],
                                ['date' => '12 Jun 2026', 'title' => 'Accommodation', 'description' => 'Hotel (2 Nights @ Rp 500.000)', 'amount' => 'Rp. 1.000.000'],
                            ] as $reimbursementItem)
                                <div class="row py-2">
                                    <div class="col-md-4 col-12"><span>{{ $reimbursementItem['date'] }}</span></div>
                                    <div class="col-md-8 col-12">
                                        <span class="text-gray fw-semibold">{{ $reimbursementItem['title'] }}</span> <br>
                                        <span class="text-gray">{{ $reimbursementItem['description'] }}</span> <br>
                                        <span class="text-gray">{{ $reimbursementItem['amount'] }}</span> <br>
                                        <a href=""><span class="text-blue fw-semibold">Attachment</span></a>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="border rounded p-5 text-center bg-light d-flex flex-column justify-content-center" style="min-height: 220px;">
                                <span class="text-gray">Reimbursement details will appear after staff submits the required report and attachments.</span>
                            </div>
                        @endif
                        <hr>
                        <div class="row py-2">
                            <div class="col-md-4 col-12"><span>Total</span></div>
                            <div class="col-md-8 col-12">
                                @if ($canViewBusinessTripReimbursementValues)
                                    <span class="text-gray fw-semibold">Rp. 2.500.000</span> <br>
                                @else
                                    <span class="badge badge-sm badge-warning light">Pending</span> <br>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                @if ($canUseBusinessTripActionButtons)
                    <a class="btn light btn-secondary ms-3 mb-2 btn-lg" href="{{ isset($businessTrip) ? route('attendance.business-trips.cash-advances.create', $businessTrip) : '#' }}">Cash Advance</a>
                    <a class="btn light btn-success me-3 mb-2 btn-lg" href="{{ isset($businessTrip) ? route('attendance.business-trips.reimbursements.create', $businessTrip) : '#' }}">Reimbursement</a>
                @else
                    <a class="btn light btn-secondary ms-3 mb-2 btn-lg disabled" aria-disabled="true" tabindex="-1">Cash Advance</a>
                    <a class="btn light btn-success me-3 mb-2 btn-lg disabled" aria-disabled="true" tabindex="-1">Reimbursement</a>
                @endif
            </div>
            <div class="mb-3"></div>
        </div>
    </div>

    <div class="col-xxl-3 col-xl-6 col-lg-6">
        <div class="card">
            <div class="card-body dz-scroll height380 p-3 pb-1">
                <div class="clearfix text-center">
                    <h4>Trip Lifecycle Tracker</h4>
                    <p>Track the real-time status of your business trip from the initial request to the final financial settlement.</p>
                </div>
                <div class="widget-timeline1 mb-3">
                    <ul class="timeline">
                        @forelse (($businessTripLifecycleTracker ?? collect()) as $lifecyclePhase)
                            <li>
                                <span class="timeline-status">{{ $lifecyclePhase['date_label'] ?? '-' }}</span>
                                <div class="timeline-badge {{ $lifecyclePhase['marker_class'] ?? 'border-secondary' }}"></div>
                                <div class="timeline-panel">
                                    <span class="text-black fs-14 fw-semibold">{{ $lifecyclePhase['title'] ?? '-' }}</span> <br>
                                </div>
                            </li>
                            @foreach (($lifecyclePhase['items'] ?? collect()) as $lifecycleItem)
                                <li>
                                    <span class="timeline-status">{{ $lifecycleItem['date_label'] ?? '-' }}</span>
                                    <div class="timeline-badge {{ $lifecycleItem['marker_class'] ?? 'border-secondary' }}"></div>
                                    <div class="timeline-panel">
                                        <span>
                                            {{ $lifecycleItem['step_order'] ?? '-' }}. {{ $lifecycleItem['title'] ?? '-' }} <br>
                                            Datetime : {{ $lifecycleItem['datetime_label'] ?? '-' }} <br>
                                            Actor : {{ $lifecycleItem['actor_label'] ?? '-' }} <br>
                                            Status : <span class="badge badge-xs {{ $lifecycleItem['badge_class'] ?? 'badge-secondary light' }} fw-semibold">{{ $lifecycleItem['status_label'] ?? '-' }}</span>
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        @empty
                            <li>
                                <span class="timeline-status">-</span>
                                <div class="timeline-badge border-secondary"></div>
                                <div class="timeline-panel"><span>No lifecycle logs recorded.</span></div>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
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
