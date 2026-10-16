@extends('layouts.main')

@section('title', 'Director Task Management')

@section('navbarTitle', 'Task Management')

@section('content')
    <div class="row">
        <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center d-block mb-4">
            <div>
                <h4 class="fw-bold text-black">Task Management</h4>
                <p class="fs-13 mb-0 text-black">A comprehensive list of all tasks performed by the team.</p>
            </div>
            <div class="d-flex gap-3">
                <a class="btn btn-lg btn-primary fw-bold rounded" data-bs-toggle="modal" data-bs-target="#filterTaskAndStaff" id="directorTaskFilterButton">
                    <i class="fa-regular fa-calendar-days me-2"></i><span id="directorTaskFilterLabel">{{ $directorTaskFilterLabel }}</span>
                </a>
            </div>
        </div>

        @foreach ($directorTaskCategoryCards as $categoryKey => $categoryCard)
            @php
                $isSelectedCategory = $selectedDirectorTaskCategory === $categoryKey;
                $categoryColor = $categoryCard['color'];
            @endphp
            <div class="col-xl-4 col-md-6">
                <a
                    class="text-decoration-none director-task-category-link"
                    href="javascript:void(0)"
                    data-director-task-category="{{ $isSelectedCategory ? '' : $categoryKey }}"
                    data-director-task-category-key="{{ $categoryKey }}"
                    data-director-task-color="{{ $categoryColor }}"
                >
                    <div class="card overflow-hidden avtivity-card {{ $isSelectedCategory ? 'bg-' . $categoryColor : '' }}">
                        <div class="card-body">
                            <div class="d-flex gap-md-4 gap-3 align-items-center">
                                <span class="avatar avatar-lg {{ $isSelectedCategory ? 'bg-white' : 'avatar-' . $categoryColor }} rounded-circle border-0">
                                    <i class="fa-solid {{ $categoryCard['icon'] }} fs-28 text-{{ $categoryColor }}"></i>
                                </span>
                                <div>
                                    <p class="fs-14 mb-2 {{ $isSelectedCategory ? 'text-white' : '' }}">{{ $categoryCard['subtitle'] }}</p>
                                    <span class="title fs-18 fw-semibold {{ $isSelectedCategory ? 'text-white' : 'text-black' }}">{{ $categoryCard['title'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="effect bg-{{ $categoryColor }}"></div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="card overflow-hidden">
        <div class="table-responsive check-wrapper">
            <table id="directorTaskTable" class="table mb-0 table-bottom-borderless">
                <thead>
                    <tr>
                        <th class="mw-50">No</th>
                        <th class="mw-120">Task Name</th>
                        <th class="mw-120">Project</th>
                        <th class="mw-120">Assignee</th>
                        <th class="mw-120">Priority</th>
                        <th class="mw-120">Status</th>
                        <th class="mw-120">Due Date</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="filterTaskAndStaff" tabindex="-1" aria-labelledby="filterTaskAndStaffLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form class="modal-content" method="GET" action="{{ route('director-attendance.task') }}" id="directorTaskFilterForm">
                <input type="hidden" name="category" id="directorTaskCategoryInput" value="{{ $selectedDirectorTaskCategory ?? '' }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterTaskAndStaffLabel">Filter Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Staff</label>
                        <select class="form-control default-select" name="staff" id="directorTaskStaffFilter">
                            <option value="">All Staff</option>
                            @foreach ($directorTaskStaffOptions as $staffOption)
                                <option value="{{ $staffOption['id'] }}" @selected($selectedDirectorTaskStaff === $staffOption['id'])>{{ $staffOption['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Month</label>
                        <select class="form-control default-select" name="month" id="directorTaskMonthFilter">
                            @foreach ($directorTaskMonthOptions as $monthValue => $monthLabel)
                                <option value="{{ $monthValue }}" @selected($selectedDirectorTaskMonth === $monthValue)>{{ $monthLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <select class="form-control default-select" name="year" id="directorTaskYearFilter">
                            @foreach ($directorTaskYearOptions as $yearOption)
                                <option value="{{ $yearOption }}" @selected($selectedDirectorTaskYear === $yearOption)>{{ $yearOption }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Apply</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="directorTaskDetailModal" tabindex="-1" aria-labelledby="directorTaskDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="directorTaskDetailModalLabel">Task Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row py-2">
                        <div class="col-4"><span>Task Name</span></div>
                        <div class="col-8"><span class="text-gray fw-semibold" id="directorTaskDetailTitle">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Task Description</span></div>
                        <div class="col-8"><span class="text-gray fw-normal" id="directorTaskDetailDescription">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Date - Due Date</span></div>
                        <div class="col-8"><span class="text-gray fw-semibold" id="directorTaskDetailDate">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Attachment</span></div>
                        <div class="col-8"><span class="text-gray fw-semibold text-break" id="directorTaskDetailAttachment">No attachment</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Blockers</span></div>
                        <div class="col-8"><span class="text-gray fw-normal" id="directorTaskDetailBlockers">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Task Category</span></div>
                        <div class="col-8">
                            <span class="text-gray fw-semibold" id="directorTaskDetailCategory">-</span><br>
                            <span class="text-gray fw-normal" id="directorTaskDetailCategoryDescription">-</span>
                        </div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Assigned by</span></div>
                        <div class="col-8"><span class="text-primary fw-semibold" id="directorTaskDetailAssignedBy">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Task Status</span></div>
                        <div class="col-8"><span class="fw-semibold" id="directorTaskDetailStatus">-</span></div>
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
            var selectedCategory = @js($selectedDirectorTaskCategory ?? '');
            var monthLabels = @js($directorTaskMonthOptions);
            var staffNames = @js($directorTaskStaffOptions->pluck('name', 'id')->all());
            var visibleTaskCategories = [];
            var taskTable = null;

            function escapeHtml(value) {
                return String(value || '').replace(/[&<>"']/g, function (character) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    }[character];
                });
            }

            function filterValue(selector) {
                var element = document.querySelector(selector);

                return element ? element.value : '';
            }

            function updateUrl() {
                var url = new URL(window.location.href);
                var filters = {
                    category: selectedCategory,
                    staff: filterValue('#directorTaskStaffFilter'),
                    month: filterValue('#directorTaskMonthFilter'),
                    year: filterValue('#directorTaskYearFilter')
                };

                Object.keys(filters).forEach(function (key) {
                    if (String(filters[key] || '').trim() === '') {
                        url.searchParams.delete(key);
                        return;
                    }

                    url.searchParams.set(key, filters[key]);
                });

                window.history.replaceState({}, '', url.toString());
            }

            function updateFilterLabel() {
                var staff = filterValue('#directorTaskStaffFilter');
                var month = filterValue('#directorTaskMonthFilter');
                var year = filterValue('#directorTaskYearFilter');
                var label = (staffNames[staff] || 'All Staff') + ' | ' + (monthLabels[month] || '') + ' ' + year;
                var labelElement = document.querySelector('#directorTaskFilterLabel');

                if (labelElement) {
                    labelElement.textContent = label.trim();
                }
            }

            function updateCategoryCards(activeCategories) {
                var categories = activeCategories || visibleTaskCategories;
                var isAllStaff = filterValue('#directorTaskStaffFilter') === '';

                document.querySelectorAll('.director-task-category-link').forEach(function (link) {
                    var category = link.getAttribute('data-director-task-category-key') || '';
                    var color = link.getAttribute('data-director-task-color') || 'primary';
                    var isSelected = selectedCategory === category || (selectedCategory === '' && !isAllStaff && categories.indexOf(category) !== -1);
                    var card = link.querySelector('.card');
                    var avatar = link.querySelector('.avatar');
                    var subtitle = link.querySelector('p');
                    var title = link.querySelector('.title');

                    link.setAttribute('data-director-task-category', selectedCategory === category ? '' : category);

                    if (card) {
                        card.classList.toggle('bg-' + color, isSelected);
                    }

                    if (avatar) {
                        avatar.classList.toggle('bg-white', isSelected);
                        avatar.classList.toggle('avatar-' + color, !isSelected);
                    }

                    if (subtitle) {
                        subtitle.classList.toggle('text-white', isSelected);
                    }

                    if (title) {
                        title.classList.toggle('text-white', isSelected);
                        title.classList.toggle('text-black', !isSelected);
                    }
                });

                var categoryInput = document.querySelector('#directorTaskCategoryInput');

                if (categoryInput) {
                    categoryInput.value = selectedCategory;
                }
            }

            function reloadTaskTable() {
                updateCategoryCards();
                updateFilterLabel();
                updateUrl();

                if (taskTable) {
                    taskTable.ajax.reload();
                }
            }

            function closeFilterModal() {
                var modalElement = document.querySelector('#filterTaskAndStaff');

                if (!modalElement || typeof bootstrap === 'undefined') {
                    return;
                }

                var modal = bootstrap.Modal.getInstance(modalElement);

                if (modal) {
                    modal.hide();
                }
            }

            function initializeDirectorTaskTable() {
                if (!window.jQuery || typeof window.jQuery.fn.DataTable === 'undefined') {
                    return;
                }

                taskTable = window.jQuery('#directorTaskTable').DataTable({
                    processing: true,
                    serverSide: false,
                    searching: false,
                    lengthChange: false,
                    ordering: false,
                    pageLength: 10,
                    ajax: {
                        url: "{{ route('director-attendance.task.datatable') }}",
                        data: function (requestData) {
                            requestData.category = selectedCategory;
                            requestData.staff = filterValue('#directorTaskStaffFilter');
                            requestData.month = filterValue('#directorTaskMonthFilter');
                            requestData.year = filterValue('#directorTaskYearFilter');
                        },
                        dataSrc: function (json) {
                            visibleTaskCategories = (json.data || [])
                                .map(function (row) {
                                    return row.category || '';
                                })
                                .filter(function (category, index, categories) {
                                    return category !== '' && categories.indexOf(category) === index;
                                });
                            updateCategoryCards(visibleTaskCategories);

                            return json.data || [];
                        }
                    },
                    columns: [
                        {
                            data: null,
                            className: 'text-center',
                            render: function (data, type, row, meta) {
                                return (meta.row + 1) + '.';
                            }
                        },
                        {
                            data: 'task',
                            render: function (data) {
                                return '<span class="text-black fw-semibold">' + escapeHtml(data || '-') + '</span>';
                            }
                        },
                        {
                            data: 'project',
                            render: function (data) {
                                return escapeHtml(data || '-');
                            }
                        },
                        {
                            data: 'assignee',
                            render: function (data, type, row) {
                                return '<div class="d-flex align-items-center">'
                                    + renderAssigneeAvatar(row)
                                    + '<h6 class="mb-0">' + escapeHtml(data || '-') + '</h6>'
                                    + '</div>';
                            }
                        },
                        {
                            data: 'priority',
                            render: function (data, type, row) {
                                return '<span class="badge badge-sm badge-' + escapeHtml(row.priority_class || 'warning') + ' light fw-semibold">' + escapeHtml(data || '-') + '</span>';
                            }
                        },
                        {
                            data: 'status',
                            render: function (data, type, row) {
                                return '<span class="badge badge-sm badge-' + escapeHtml(row.status_class || 'warning') + ' light fw-semibold">' + escapeHtml(data || '-') + '</span>';
                            }
                        },
                        {
                            data: 'due_date',
                            render: function (data) {
                                return escapeHtml(data || '-');
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
                    createdRow: function (rowElement) {
                        rowElement.classList.add('director-task-row');
                        rowElement.style.cursor = 'pointer';
                    },
                    drawCallback: function () {
                        window.jQuery('#directorTaskTable tbody td.dataTables_empty').addClass('text-center py-4 text-muted');
                    }
                });

                window.jQuery('#directorTaskTable tbody').on('click', 'tr.director-task-row', function () {
                    var row = taskTable.row(this).data();

                    if (row) {
                        showTaskDetail(row);
                    }
                });
            }

            function renderAssigneeAvatar(row) {
                var initialAvatar = '<div class="avatar rounded-circle bg-primary text-white avatar-xs me-2"'
                    + (row.assignee_avatar_url ? ' style="display: none;"' : '')
                    + '>' + escapeHtml(row.assignee_initial || '-') + '</div>';

                if (!row.assignee_avatar_url) {
                    return initialAvatar;
                }

                return '<img src="' + escapeHtml(row.assignee_avatar_url) + '" alt="' + escapeHtml(row.assignee || 'Staff') + '"'
                    + ' class="avatar avatar-xs rounded-circle me-2" style="object-fit: cover;" loading="lazy"'
                    + ' onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'inline-flex\';">'
                    + initialAvatar;
            }

            function setDetailText(elementId, value) {
                var element = document.getElementById(elementId);
                var text = String(value || '').trim();

                if (element) {
                    element.textContent = text !== '' ? text : '-';
                }
            }

            function renderDetailAttachment(row) {
                var element = document.getElementById('directorTaskDetailAttachment');

                if (!element) {
                    return;
                }

                element.textContent = '';

                if (row.attachment_url) {
                    var link = document.createElement('a');
                    link.href = row.attachment_url;
                    link.className = 'text-primary';
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    link.textContent = 'Open attachment';
                    element.appendChild(link);

                    return;
                }

                element.textContent = String(row.attachment_path || '').trim() || 'No attachment';
            }

            function showTaskDetail(row) {
                var modalElement = document.getElementById('directorTaskDetailModal');
                var statusElement = document.getElementById('directorTaskDetailStatus');

                if (!modalElement || typeof bootstrap === 'undefined') {
                    return;
                }

                setDetailText('directorTaskDetailTitle', row.task);
                setDetailText('directorTaskDetailDescription', row.description);
                setDetailText('directorTaskDetailDate', row.date_range);
                renderDetailAttachment(row);
                setDetailText('directorTaskDetailBlockers', row.blockers);
                setDetailText('directorTaskDetailCategory', row.category_label);
                setDetailText('directorTaskDetailCategoryDescription', row.category_subtitle);
                setDetailText('directorTaskDetailAssignedBy', '@' + (String(row.assigned_by || '').trim() || 'self'));

                if (statusElement) {
                    statusElement.className = 'fw-semibold text-' + (row.status_class || 'warning');
                    statusElement.textContent = row.status || 'To Do';
                }

                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            }

            document.addEventListener('click', function (event) {
                var link = event.target.closest('.director-task-category-link');

                if (!link) {
                    return;
                }

                event.preventDefault();
                selectedCategory = link.getAttribute('data-director-task-category') || '';
                reloadTaskTable();
            });

            document.addEventListener('submit', function (event) {
                var form = event.target.closest('#directorTaskFilterForm');

                if (!form) {
                    return;
                }

                event.preventDefault();
                selectedCategory = filterValue('#directorTaskCategoryInput');
                closeFilterModal();
                reloadTaskTable();
            });

            initializeDirectorTaskTable();
            updateCategoryCards();
            updateFilterLabel();
        })();
    </script>
@endsection
