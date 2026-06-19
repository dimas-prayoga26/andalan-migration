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

@include('staff_attendance.layouts.profile-index')

@php
    $canViewBusinessTripExpenseValues = (bool) ($businessTripDetailPermissions['can_view_trip_expense_values'] ?? false);
    $canViewBusinessTripCashAdvanceValues = (bool) ($businessTripDetailPermissions['can_view_cash_advance_values'] ?? false);
    $canViewBusinessTripReimbursementValues = (bool) ($businessTripDetailPermissions['can_view_reimbursement_values'] ?? false);
    $canUseBusinessTripActionButtons = (bool) ($businessTripDetailPermissions['can_use_action_buttons'] ?? false);
    $canUseBusinessTripReimbursementButton = (bool) ($businessTripDetailPermissions['can_use_reimbursement_button'] ?? false);
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
                @foreach (($businessTripApprovedExpenseBreakdownRows ?? collect()) as $businessTripExpenseValue)
                    <div class="row py-2">
                        <div class="col-md-6 col-12"><span>{{ $businessTripExpenseValue['label'] ?? '-' }}</span></div>
                        <div class="col-md-6 col-12">
                            @if ($canViewBusinessTripExpenseValues && ! empty($businessTripExpenseValue['has_value']))
                                <span class="text-gray fw-semibold">{{ $businessTripExpenseValue['amount_label'] ?? '-' }}</span> <br>
                                @foreach (($businessTripExpenseValue['description_lines'] ?? []) as $businessTripExpenseDescription)
                                    <span class="text-gray">{{ $businessTripExpenseDescription }}</span> <br>
                                @endforeach
                            @else
                                <span class="text-gray">-</span>
                            @endif
                        </div>
                    </div>
                @endforeach
                <hr>
                @foreach (($businessTripRequestFinancialRows ?? collect()) as $businessTripRequestFinancialRow)
                    <div class="row py-2">
                        <div class="col-md-6 col-12"><span>{{ $businessTripRequestFinancialRow['label'] ?? '-' }}</span></div>
                        <div class="col-md-6 col-12">
                            @if ($canViewBusinessTripExpenseValues)
                                <span class="text-gray fw-semibold">{{ $businessTripRequestFinancialRow['amount_label'] ?? '-' }}</span> <br>
                                @foreach (($businessTripRequestFinancialRow['description_lines'] ?? []) as $businessTripRequestFinancialDescription)
                                    <span class="text-gray">{{ $businessTripRequestFinancialDescription }}</span> <br>
                                @endforeach
                            @else
                                <span class="badge badge-sm badge-warning light">Pending</span>
                            @endif
                        </div>
                    </div>
                @endforeach
                @foreach (($businessTripRequestStatusRows ?? collect()) as $businessTripStatusRow)
                    <div class="row py-2">
                        <div class="col-md-6 col-12"><span>{{ $businessTripStatusRow['label'] ?? '-' }}</span></div>
                        <div class="col-md-6 col-12">
                            @if ($canViewBusinessTripExpenseValues)
                                <span class="badge badge-sm {{ $businessTripStatusRow['badge_class'] ?? 'badge-warning light' }}">{{ $businessTripStatusRow['status_label'] ?? 'Pending' }}</span>
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
                            @forelse (($businessTripExpenseRows ?? collect()) as $expenseItem)
                                <div class="row py-2">
                                    <div class="col-md-4 col-12"><span>{{ $expenseItem['date_label'] ?? '-' }}</span></div>
                                    <div class="col-md-8 col-12">
                                        <span class="text-gray fw-semibold">{{ $expenseItem['category_label'] ?? '-' }}</span> <br>
                                        <span class="text-gray">{{ $expenseItem['description'] ?? '-' }}</span> <br>
                                        <span class="text-gray">{{ $expenseItem['amount_label'] ?? '-' }}</span> <br>
                                        @if (! empty($expenseItem['attachment_url']))
                                            <a href="#" class="js-business-trip-attachment-preview" data-bs-toggle="modal" data-bs-target="#businessTripAttachmentPreviewModal" data-attachment-url="{{ $expenseItem['attachment_url'] }}" data-attachment-title="Expense Attachment">
                                                <span class="text-blue fw-semibold">Attachment</span>
                                            </a>
                                        @else
                                            <span class="text-danger fw-semibold">Belum mengupload attachment.</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="border rounded p-5 text-center bg-light d-flex flex-column justify-content-center" style="min-height: 220px;">
                                    <span class="text-gray">Expense details will appear after cash advance approval.</span>
                                </div>
                            @endforelse
                        @else
                            <div class="border rounded p-5 text-center bg-light d-flex flex-column justify-content-center" style="min-height: 220px;">
                                <span class="text-gray">Expense details will appear after cash advance approval.</span>
                            </div>
                        @endif
                        <hr>
                        @foreach (($businessTripExpenseSummaryRows ?? collect()) as $expenseSummaryRow)
                            <div class="row py-2">
                                <div class="col-md-4 col-12"><span>{{ $expenseSummaryRow['label'] ?? '-' }}</span></div>
                                <div class="col-md-8 col-12">
                                    @if ($canViewBusinessTripExpenseValues)
                                        <span class="{{ $expenseSummaryRow['amount_class'] ?? 'text-gray' }} fw-semibold">{{ $expenseSummaryRow['amount_label'] ?? '-' }}</span> <br>
                                        @if (! empty($expenseSummaryRow['description']))
                                            <span class="text-gray">{{ $expenseSummaryRow['description'] }}</span> <br>
                                        @endif
                                    @else
                                        <span class="badge badge-sm badge-warning light">Pending</span> <br>
                                    @endif
                                </div>
                            </div>
                            @if (! empty($expenseSummaryRow['has_bottom_divider']))
                                <hr class="my-2">
                            @endif
                        @endforeach
                    </div>
                    <div class="tab-pane fade" id="tabCashAdvance3" role="tabpanel" aria-labelledby="cash-advance-tab3" tabindex="0">
                        @if ($canViewBusinessTripCashAdvanceValues)
                            @forelse (($businessTripCashAdvanceRows ?? collect()) as $cashAdvanceRow)
                                <div class="row py-2">
                                    <div class="col-md-4 col-12"><span>{{ $cashAdvanceRow['date_label'] ?? '-' }}</span></div>
                                    <div class="col-md-8 col-12">
                                        <span class="text-gray fw-semibold">{{ $cashAdvanceRow['category_label'] ?? '-' }}</span> <br>
                                        <span class="text-gray">{{ $cashAdvanceRow['notes'] ?? '-' }}</span> <br>
                                        @if (! empty($cashAdvanceRow['has_approved_amount']))
                                            <span class="text-decoration-line-through">{{ $cashAdvanceRow['amount_requested_label'] ?? '-' }}</span>
                                            <span class="text-gray">{{ $cashAdvanceRow['amount_approved_label'] ?? ($cashAdvanceRow['payment_amount_label'] ?? '-') }}</span> <br>
                                        @else
                                            <span class="text-gray">{{ $cashAdvanceRow['amount_requested_label'] ?? '-' }}</span> <br>
                                        @endif
                                        <span class="badge badge-xs {{ $cashAdvanceRow['status_badge_class'] ?? 'badge-warning light' }} fw-semibold">{{ $cashAdvanceRow['status_label'] ?? 'Pending' }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="border rounded p-5 text-center bg-light d-flex flex-column justify-content-center" style="min-height: 220px;">
                                    <span class="text-gray">Cash advance details will appear after staff submits the cash advance request in Phase 2.</span>
                                </div>
                            @endforelse
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
                                    <span class="text-gray fw-semibold">{{ $businessTripCashAdvanceSummary['total_payment_label'] ?? 'Rp 0' }}</span> <br>
                                    <span class="badge badge-sm {{ $businessTripCashAdvanceSummary['status_badge_class'] ?? 'badge-warning light' }}">{{ $businessTripCashAdvanceSummary['status_label'] ?? 'Pending' }}</span> <br>
                                @else
                                    <span class="badge badge-sm badge-warning light">Pending</span> <br>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tabReimbursement3" role="tabpanel" aria-labelledby="reimbursement-tab3" tabindex="0">
                        @if ($canViewBusinessTripReimbursementValues)
                            @forelse (($businessTripReimbursementRows ?? collect()) as $reimbursementItem)
                                <div class="row py-2">
                                    <div class="col-md-4 col-12"><span>{{ $reimbursementItem['date_label'] ?? '-' }}</span></div>
                                    <div class="col-md-8 col-12">
                                        <span class="text-gray fw-semibold">{{ $reimbursementItem['category_label'] ?? '-' }}</span> <br>
                                        <span class="text-gray">{{ $reimbursementItem['notes'] ?? '-' }}</span> <br>
                                        <span class="text-gray">{{ $reimbursementItem['amount_label'] ?? '-' }}</span> <br>
                                        @if (! empty($reimbursementItem['receipt_url']))
                                            <a href="#" class="js-business-trip-attachment-preview" data-bs-toggle="modal" data-bs-target="#businessTripAttachmentPreviewModal" data-attachment-url="{{ $reimbursementItem['receipt_url'] }}" data-attachment-title="Reimbursement Receipt"><span class="text-blue fw-semibold">Receipt</span></a> <br>
                                        @else
                                            <span class="text-danger fw-semibold">Receipt belum diupload</span> <br>
                                        @endif
                                        <span class="badge badge-xs {{ $reimbursementItem['status_badge_class'] ?? 'badge-warning light' }} fw-semibold">{{ $reimbursementItem['status_label'] ?? 'Pending' }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="border rounded p-5 text-center bg-light d-flex flex-column justify-content-center" style="min-height: 220px;">
                                    <span class="text-gray">Reimbursement details will appear after staff submits the required report and attachments.</span>
                                </div>
                            @endforelse
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
                                    <span class="text-gray fw-semibold">{{ $businessTripReimbursementSummary['total_label'] ?? 'Rp 0' }}</span> <br>
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
                @else
                    <a class="btn light btn-secondary ms-3 mb-2 btn-lg disabled" aria-disabled="true" tabindex="-1">Cash Advance</a>
                @endif

                @if ($canUseBusinessTripReimbursementButton)
                    <a class="btn light btn-success me-3 mb-2 btn-lg" href="{{ isset($businessTrip) ? route('attendance.business-trips.reimbursements.create', $businessTrip) : '#' }}">Reimbursement</a>
                @else
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

<div class="modal fade" id="businessTripAttachmentPreviewModal" tabindex="-1" aria-labelledby="businessTripAttachmentPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="businessTripAttachmentPreviewModalLabel">Attachment Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="businessTripAttachmentPreviewFrame" title="Business trip attachment preview" class="w-100 border-0 d-block" style="min-height: 70vh;"></iframe>
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

            $('.js-business-trip-attachment-preview').on('click', function (event) {
                event.preventDefault();

                var attachmentUrl = $(this).data('attachment-url') || '';
                var attachmentTitle = $(this).data('attachment-title') || 'Attachment Preview';

                $('#businessTripAttachmentPreviewModalLabel').text(attachmentTitle);
                $('#businessTripAttachmentPreviewFrame').attr('src', attachmentUrl);
            });

            $('#businessTripAttachmentPreviewModal').on('hidden.bs.modal', function () {
                $('#businessTripAttachmentPreviewFrame').attr('src', '');
            });
        });
    </script>
@endsection
