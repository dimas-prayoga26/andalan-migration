@extends('layouts.main')

@section('title', 'PIC Task')

@section('navbarTitle', 'Task')

@section('content')
    @php
        $picTaskStaffOptions = collect($picTaskStaffOptions ?? []);
    @endphp

    @include('pic_attendance.layout.navbar')

    <div class="row">
        <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center d-block mb-4">
                <div>
                    <h4 class="fw-bold text-black">Team Task</h4>
                    <p class="fs-13 mb-0 text-black">A comprehensive list of all tasks performed by the team.</p>
                </div>
            </div>
        <div class="col-md-3 col-sm-6">
            <div class="card overflow-hidden avtivity-card">
                <div class="card-body">
                    <div class="d-flex gap-md-4 gap-3 align-items-center">
                        <span class="avatar avatar-lg avatar-secondary rounded-circle border-0">
                            <svg width="40" height="37" viewBox="0 0 40 37" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1.64826 26.5285C0.547125 26.7394 -0.174308 27.8026 0.0366371 28.9038C0.222269 29.8741 1.07449 30.5491 2.02796 30.5491C2.15453 30.5491 2.28531 30.5364 2.41188 30.5112L10.7653 28.908C11.242 28.8152 11.6682 28.5578 11.9719 28.1781L15.558 23.6554L14.3599 23.0437C13.4739 22.5965 12.8579 21.7865 12.6469 20.8035L9.26338 25.0688L1.64826 26.5285Z" fill="#A02CFA"/>
                                <path d="M31.3999 8.89345C33.8558 8.89345 35.8467 6.90258 35.8467 4.44673C35.8467 1.99087 33.8558 0 31.3999 0C28.9441 0 26.9532 1.99087 26.9532 4.44673C26.9532 6.90258 28.9441 8.89345 31.3999 8.89345Z" fill="#A02CFA"/>
                                <path d="M21.6965 3.33297C21.2282 2.85202 20.7937 2.66217 20.3169 2.66217C20.1439 2.66217 19.971 2.68748 19.7853 2.72967L12.1534 4.53958C11.0986 4.78849 10.4489 5.84744 10.6979 6.89795C10.913 7.80079 11.7146 8.40831 12.6048 8.40831C12.7567 8.40831 12.9086 8.39144 13.0605 8.35347L19.5618 6.81357C19.9837 7.28187 22.0974 9.57273 22.4813 9.97775C19.7938 12.855 17.1064 15.7281 14.4189 18.6054C14.3767 18.6519 14.3388 18.6982 14.3008 18.7446C13.5161 19.7445 13.7566 21.3139 14.9379 21.9088L23.1774 26.1151L18.8994 33.0467C18.313 34.0002 18.6083 35.249 19.5618 35.8396C19.8951 36.0464 20.2621 36.1434 20.6249 36.1434C21.3042 36.1434 21.9707 35.8017 22.3547 35.1815L27.7886 26.3766C28.0882 25.8915 28.1683 25.305 28.0122 24.7608C27.8561 24.2123 27.4806 23.7567 26.9702 23.4993L21.3885 20.66L27.2571 14.3823L31.6869 18.1371C32.0539 18.4493 32.5054 18.6012 32.9526 18.6012C33.4335 18.6012 33.9145 18.424 34.2899 18.078L39.3737 13.3402C40.1669 12.6019 40.2133 11.3615 39.475 10.5684C39.0868 10.1549 38.5637 9.944 38.0406 9.944C37.5638 9.944 37.0829 10.117 36.7074 10.4671L32.9019 14.0068C32.8977 14.011 23.363 5.04163 21.6965 3.33297Z" fill="#A02CFA"/>
                            </svg>
                        </span>
                        <div>
                            <p class="fs-14 mb-2">Task Completion Rate</p>
                            <span class="title text-black fs-28 fw-semibold">100%</span>
                        </div>
                    </div>
                    <div>
                        <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                            <div class="progress-bar rounded bg-secondary" style="width: 100%; height:5px;" aria-label="Progess-secondary" role="progressbar">
                                <span class="sr-only">100% Complete</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="effect bg-secondary"></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card overflow-hidden avtivity-card">
                <div class="card-body">
                    <div class="d-flex gap-md-4 gap-3 align-items-center">
                        <span class="avatar avatar-lg avatar-danger rounded-circle border-0">
                            <svg width="40" height="39" viewBox="0 0 40 39" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18.0977 7.90402L9.78535 16.7845C9.17929 17.6683 9.40656 18.872 10.2862 19.4738L18.6574 25.2104V30.787C18.6574 31.8476 19.4992 32.7357 20.5598 32.7568C21.6456 32.7735 22.5295 31.9023 22.5295 30.8207V24.1961C22.5295 23.5564 22.2138 22.9588 21.6877 22.601L16.3174 18.9184L20.8376 14.1246L23.1524 19.3982C23.4596 20.101 24.1582 20.5556 24.9243 20.5556H31.974C33.0346 20.5556 33.9226 19.7139 33.9437 18.6532C33.9605 17.5674 33.0893 16.6835 32.0076 16.6835H26.1953C25.4293 14.9411 24.6128 13.2155 23.9015 11.4478C23.5395 10.5556 23.3376 10.1684 22.6726 9.55389C22.5379 9.42763 21.5993 8.56904 20.7618 7.80305C19.9916 7.10435 18.8047 7.15065 18.0977 7.90402Z" fill="#FF3282"/>
                                <path d="M26.0269 8.87206C28.4769 8.87206 30.463 6.88598 30.463 4.43603C30.463 1.98608 28.4769 0 26.0269 0C23.577 0 21.5909 1.98608 21.5909 4.43603C21.5909 6.88598 23.577 8.87206 26.0269 8.87206Z" fill="#FF3282"/>
                                <path d="M8.16498 38.388C12.6744 38.388 16.33 34.7325 16.33 30.2231C16.33 25.7137 12.6744 22.0581 8.16498 22.0581C3.65559 22.0581 0 25.7137 0 30.2231C0 34.7325 3.65559 38.388 8.16498 38.388Z" fill="#FF3282"/>
                                <path d="M31.835 38.388C36.3444 38.388 40 34.7325 40 30.2231C40 25.7137 36.3444 22.0581 31.835 22.0581C27.3256 22.0581 23.67 25.7137 23.67 30.2231C23.67 34.7325 27.3256 38.388 31.835 38.388Z" fill="#FF3282"/>
                            </svg>
                        </span>
                        <div>
                            <p class="fs-14 mb-2">Total Tasks</p>
                            <span class="title text-black fs-28 fw-semibold">10 Tasks</span>
                        </div>
                    </div>
                    <div>
                        <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                            <div class="progress-bar rounded bg-danger" style="width: 10%; height:5px;" aria-label="Progess-danger"  role="progressbar">
                                <span class="sr-only">10% Complete</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="effect bg-danger"></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card overflow-hidden avtivity-card">
                <div class="card-body">
                    <div class="d-flex gap-md-4 gap-3 align-items-center">
                        <span class="avatar avatar-lg avatar-info rounded-circle border-0">
                            <svg width="40" height="37" viewBox="0 0 40 37" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1.64826 26.5285C0.547125 26.7394 -0.174308 27.8026 0.0366371 28.9038C0.222269 29.8741 1.07449 30.5491 2.02796 30.5491C2.15453 30.5491 2.28531 30.5364 2.41188 30.5112L10.7653 28.908C11.242 28.8152 11.6682 28.5578 11.9719 28.1781L15.558 23.6554L14.3599 23.0437C13.4739 22.5965 12.8579 21.7865 12.6469 20.8035L9.26338 25.0688L1.64826 26.5285Z" fill="#A02CFA"/>
                                <path d="M31.3999 8.89345C33.8558 8.89345 35.8467 6.90258 35.8467 4.44673C35.8467 1.99087 33.8558 0 31.3999 0C28.9441 0 26.9532 1.99087 26.9532 4.44673C26.9532 6.90258 28.9441 8.89345 31.3999 8.89345Z" fill="#A02CFA"/>
                                <path d="M21.6965 3.33297C21.2282 2.85202 20.7937 2.66217 20.3169 2.66217C20.1439 2.66217 19.971 2.68748 19.7853 2.72967L12.1534 4.53958C11.0986 4.78849 10.4489 5.84744 10.6979 6.89795C10.913 7.80079 11.7146 8.40831 12.6048 8.40831C12.7567 8.40831 12.9086 8.39144 13.0605 8.35347L19.5618 6.81357C19.9837 7.28187 22.0974 9.57273 22.4813 9.97775C19.7938 12.855 17.1064 15.7281 14.4189 18.6054C14.3767 18.6519 14.3388 18.6982 14.3008 18.7446C13.5161 19.7445 13.7566 21.3139 14.9379 21.9088L23.1774 26.1151L18.8994 33.0467C18.313 34.0002 18.6083 35.249 19.5618 35.8396C19.8951 36.0464 20.2621 36.1434 20.6249 36.1434C21.3042 36.1434 21.9707 35.8017 22.3547 35.1815L27.7886 26.3766C28.0882 25.8915 28.1683 25.305 28.0122 24.7608C27.8561 24.2123 27.4806 23.7567 26.9702 23.4993L21.3885 20.66L27.2571 14.3823L31.6869 18.1371C32.0539 18.4493 32.5054 18.6012 32.9526 18.6012C33.4335 18.6012 33.9145 18.424 34.2899 18.078L39.3737 13.3402C40.1669 12.6019 40.2133 11.3615 39.475 10.5684C39.0868 10.1549 38.5637 9.944 38.0406 9.944C37.5638 9.944 37.0829 10.117 36.7074 10.4671L32.9019 14.0068C32.8977 14.011 23.363 5.04163 21.6965 3.33297Z" fill="#A02CFA"/>
                            </svg>
                        </span>
                        <div>
                            <p class="fs-14 mb-2">In Progress</p>
                            <span class="title text-black fs-28 fw-semibold">2 Tasks</span>
                        </div>
                    </div>
                    <div>
                        <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                            <div class="progress-bar rounded bg-info" style="width: 82%; height:5px;" aria-label="Progess-info" role="progressbar">
                                <span class="sr-only">42% Complete</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="effect bg-info"></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card overflow-hidden avtivity-card">
                <div class="card-body">
                    <div class="d-flex gap-md-4 gap-3 align-items-center">
                        <span class="avatar avatar-lg avatar-success rounded-circle border-0">
                            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip2)">
                                <path d="M14.6406 24.384C14.4639 24.1871 14.421 23.904 14.5305 23.6633C15.9635 20.513 14.4092 18.7501 14.564 11.6323C14.5713 11.2944 14.8346 10.9721 15.2564 10.9801C15.6201 10.987 15.905 11.2962 15.8971 11.6598C15.8902 11.9762 15.8871 12.2939 15.8875 12.6123C15.888 12.9813 16.1893 13.2826 16.5583 13.2776C17.6426 13.2628 19.752 12.9057 20.5684 10.4567L20.9744 9.23876C21.7257 6.9847 20.4421 4.55115 18.1335 3.91572L13.9816 2.77294C12.3274 2.31768 10.5363 2.94145 9.52387 4.32498C4.66826 10.9599 1.44452 18.5903 0.0754914 26.6727C-0.300767 28.8937 0.754757 31.1346 2.70222 32.2488C13.6368 38.5051 26.6023 39.1113 38.35 33.6379C39.3524 33.1709 40.0002 32.1534 40.0002 31.0457V19.1321C40.0002 18.182 39.5322 17.2976 38.7484 16.7664C34.5339 13.91 29.1672 14.2521 25.5723 18.0448C25.2519 18.3828 25.3733 18.937 25.8031 19.1166C27.4271 19.7957 28.9625 20.7823 30.2439 21.9475C30.5225 22.2008 30.542 22.6396 30.2654 22.9155C30.0143 23.1658 29.6117 23.1752 29.3485 22.9376C25.9907 19.9053 21.4511 18.5257 16.935 19.9686C16.658 20.0571 16.4725 20.3193 16.477 20.61C16.496 21.8194 16.294 22.9905 15.7421 24.2172C15.5453 24.6544 14.9607 24.7409 14.6406 24.384Z" fill="#27BC48"/>
                                </g>
                                <defs>
                                <clipPath id="clip2">
                                <rect width="40" height="40" fill="white"/>
                                </clipPath>
                                </defs>
                            </svg>
                        </span>
                        <div>
                            <p class="fs-14 mb-2">Completed</p>
                            <span class="title text-black fs-28 fw-semibold">8 Tasks</span>
                        </div>
                    </div>
                    <div>
                        <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                            <div class="progress-bar bg-success position-absolute rounded bootom-0" style="width: 95%; height:5px;" aria-label="Progess-success" role="progressbar">
                                <span class="sr-only">95% Complete</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="effect bg-success"></div>
            </div>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="table-responsive check-wrapper">
            <table class="table mb-0 table-bottom-borderless">
                <thead>
                    <tr>
                        <th class="mw-50">No</th>
                        <th class="mw-120">Task Name</th>
                        <th class="mw-120">Project</th>
                        <th class="mw-120">Assignee</th>
                        <th class="mw-120">Priority</th>
                        <th class="mw-120">Status</th>
                        <th class="mw-120">Due Date</th>
                        <!-- <th class="no-sort"></th> -->
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1.</td>
                        <td><a data-bs-toggle="modal" data-bs-target="#details">Mengerjakan Website Company Profile Rajawali Inti Coal Halmahera</a></td>
                        <td>Daily Task</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar rounded-circle bg-primary text-white avatar-xs me-2">A</div>
                                <h6 class="mb-0">Amelia Rose</h6>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-sm badge-danger light fw-semibold">High</span>
                        </td>
                        <td><span class="badge badge-sm badge-success light fw-semibold">Completed</span></td>
                        <td>Kamis, 17 Sep 2026</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <h4 class="card-title mb-0">Task Monitoring</h4>
            <div class="d-flex align-items-center gap-2">
                <label for="picTaskStaffFilter" class="mb-0 text-muted fs-13">Staff</label>
                <select id="picTaskStaffFilter" name="staff_filter" class="form-select form-select-sm w-auto">
                    <option value="" selected disabled>Pilih Staff</option>
                    @forelse ($picTaskStaffOptions as $staffOption)
                        <option value="{{ $staffOption['id'] }}">{{ $staffOption['name'] }}</option>
                    @empty
                        <option value="" disabled>Tidak ada staff</option>
                    @endforelse
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="picTaskTable" class="table table-sm align-middle mb-0">
                    <colgroup>
                        <col style="width: 20%;">
                        <col style="width: 42%;">
                        <col style="width: 18%;">
                        <col style="width: 12%;">
                        <col style="width: 8%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Staff</th>
                            <th>Task</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="picTaskDetailModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Task Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row py-2">
                        <div class="col-4"><span>Task Name</span></div>
                        <div class="col-8"><span class="text-gray fw-semibold" id="picTaskDetailTitle">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Task Description</span></div>
                        <div class="col-8"><span class="text-gray fw-normal" id="picTaskDetailDescription">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Date - Due Date</span></div>
                        <div class="col-8"><span class="text-gray fw-semibold" id="picTaskDetailDueDate">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Attachment</span></div>
                        <div class="col-8"><span class="text-gray fw-semibold" id="picTaskDetailAttachment">No attachment</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Blockers</span></div>
                        <div class="col-8"><span class="text-gray fw-normal" id="picTaskDetailBlockers">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Task Category</span></div>
                        <div class="col-8">
                            <span class="text-gray fw-semibold" id="picTaskDetailCategory">-</span><br>
                            <span class="text-gray fw-normal" id="picTaskDetailProject">-</span>
                        </div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Assigned by</span></div>
                        <div class="col-8"><span class="text-primary fw-semibold" id="picTaskDetailAssignedBy">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Task Status</span></div>
                        <div class="col-8"><span class="fw-semibold" id="picTaskDetailStatus">-</span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @php
        $dataTablesJsPath = public_path('assets/vendor/datatables/js/jquery.dataTables.bundle.min.js');
        $dataTablesJsVersion = file_exists($dataTablesJsPath) ? filemtime($dataTablesJsPath) : time();
    @endphp
    <script src="{{ asset('assets/vendor/datatables/js/jquery.dataTables.bundle.min.js') }}?v={{ $dataTablesJsVersion }}"></script>
    <script>
        (function () {
            if (!window.jQuery || !window.jQuery.fn.DataTable) {
                return;
            }

            jQuery(function ($) {
                var staffFilter = document.getElementById('picTaskStaffFilter');
                var escapeHtml = function (value) {
                    return String(value || '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                };
                var nullableText = function (value, fallback) {
                    var normalizedValue = String(value || '').trim();

                    return normalizedValue !== '' ? normalizedValue : (fallback || '-');
                };
                var renderAttachment = function (value) {
                    var normalizedValue = String(value || '').trim();
                    if (normalizedValue === '') {
                        return 'No attachment';
                    }

                    return '<a href="' + escapeHtml(normalizedValue) + '" class="text-primary" target="_blank" rel="noopener noreferrer">Open attachment</a>';
                };
                var statusTextClass = function (value) {
                    return value === 'success' ? 'text-success' : 'text-warning';
                };
                var assignedByText = function (value) {
                    var normalizedValue = nullableText(value, 'self');

                    return normalizedValue.charAt(0) === '@' ? normalizedValue : '@' + normalizedValue;
                };
                var renderTaskContext = function (row) {
                    var contextLabel = nullableText(row.task_context || row.project);

                    return '<div class="text-muted fs-13">' + escapeHtml(contextLabel) + '</div>';
                };
                var renderTaskTitle = function (row) {
                    var title = '<span class="fw-semibold text-black">' + escapeHtml(row.task) + '</span>';

                    if (row.task_context_type === 'overtime') {
                        return title + ' <span class="badge badge-info light ms-1 align-middle">Overtime</span>';
                    }

                    return title;
                };

                var taskTable = $('#picTaskTable').DataTable({
                    ajax: {
                        url: @json(route('pic-attendance.task.datatable')),
                        data: function (requestData) {
                            requestData.staff = staffFilter ? staffFilter.value : '';
                        },
                        dataSrc: 'data'
                    },
                    autoWidth: false,
                    searching: false,
                    pageLength: 10,
                    lengthChange: false,
                    paging: true,
                    bInfo: true,
                    order: [],
                    columns: [
                        {
                            data: 'staff',
                            render: function (data) {
                                return '<span class="fw-semibold">' + escapeHtml(data) + '</span>';
                            }
                        },
                        {
                            data: null,
                            render: function (row) {
                                return renderTaskTitle(row)
                                    + renderTaskContext(row)
                                    + '<div class="text-muted fs-13">Assign by : <span class="fw-semibold">' + escapeHtml(row.assigned_by) + '</span></div>';
                            }
                        },
                        {
                            data: 'due_date',
                            defaultContent: '-'
                        },
                        {
                            data: null,
                            render: function (row) {
                                return '<span class="badge badge-' + escapeHtml(row.status_class) + ' light">' + escapeHtml(row.status) + '</span>';
                            }
                        },
                        {
                            data: null,
                            searchable: false,
                            orderable: false,
                            render: function () {
                                return '<button type="button" class="btn btn-xs btn-primary light pic-task-detail-button" data-bs-toggle="modal" data-bs-target="#picTaskDetailModal">Detail</button>';
                            }
                        }
                    ],
                    language: {
                        emptyTable: 'No task data available.',
                        paginate: {
                            next: '<i class="fa-solid fa-angle-right"></i>',
                            previous: '<i class="fa-solid fa-angle-left"></i>'
                        }
                    },
                    drawCallback: function () {
                        $('#picTaskTable tbody td.dataTables_empty')
                            .addClass('text-center py-4 text-muted');
                    }
                });

                $('#picTaskTable tbody').on('click', '.pic-task-detail-button', function () {
                    var row = taskTable.row($(this).closest('tr')).data() || {};
                    $('#picTaskDetailTitle').text(nullableText(row.task));
                    $('#picTaskDetailDescription').text(nullableText(row.description));
                    $('#picTaskDetailCategory').text(nullableText(row.task_category));
                    $('#picTaskDetailProject').text(nullableText(row.task_context || row.project));
                    $('#picTaskDetailAssignedBy').text(assignedByText(row.assigned_by));
                    $('#picTaskDetailDueDate').text(nullableText(row.due_date));
                    $('#picTaskDetailBlockers').text(nullableText(row.blockers));
                    $('#picTaskDetailStatus')
                        .removeClass('text-danger text-success text-warning')
                        .addClass(statusTextClass(row.status_class))
                        .text(nullableText(row.status));
                    $('#picTaskDetailAttachment').html(renderAttachment(row.attachment_path));
                });

                if (staffFilter) {
                    staffFilter.addEventListener('change', function () {
                        taskTable.ajax.reload();
                    });
                }
            });
        })();
    </script>
@endsection
