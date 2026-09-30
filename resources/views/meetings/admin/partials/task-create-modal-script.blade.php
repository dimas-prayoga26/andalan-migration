<div class="modal fade" id="create" tabindex="-1" aria-labelledby="createMeetingTaskLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('hr-meetings.tasks.store', $meeting) }}">
                @csrf
                <input type="hidden" name="category" id="meetingTaskCategory" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="createMeetingTaskLabel">Create Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="meetingTaskTitle">Judul Task</label>
                        <input type="text" class="form-control" id="meetingTaskTitle" name="title" placeholder="Judul task" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="meetingTaskAssignee">Assign to</label>
                        <select class="form-select" id="meetingTaskAssignee" name="assigned_to" required @disabled(($meetingTaskAssigneeOptions ?? collect())->isEmpty())>
                            @forelse ($meetingTaskAssigneeOptions ?? [] as $employee)
                                <option value="{{ $employee['id'] }}">{{ $employee['name'] }}</option>
                            @empty
                                <option value="">Belum ada staff yang joined di meeting ini</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="meetingTaskDateRangePicker">Date Range</label>
                        <input type="text" class="form-control js-meeting-task-date-range-picker" id="meetingTaskDateRangePicker" data-start-date-target="#meetingTaskStartDate" data-due-date-target="#meetingTaskDueDate" placeholder="dd/mm/yyyy - dd/mm/yyyy" autocomplete="off" required>
                        <input type="hidden" id="meetingTaskStartDate" name="start_date" value="">
                        <input type="hidden" id="meetingTaskDueDate" name="due_date" value="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" @disabled(($meetingTaskAssigneeOptions ?? collect())->isEmpty())>Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(function () {
        var meetingTaskCards = @json($meetingTaskCards ?? []);
        var cardByTitle = {};

        function escapeHtml(value) {
            return $('<div>').text(value === null || value === undefined ? '' : String(value)).html();
        }

        function renderTask(task) {
            var lineClass = task.is_completed ? 'bg-success' : 'bg-light';
            var statusText = task.is_completed ? 'Status: Completed' : 'Due ' + (task.due_label || '-');

            return ''
                + '<div class="d-flex align-items-center py-2">'
                + '<div class="timeline-vr-badge ' + lineClass + ' me-2"></div>'
                + '<div class="clearfix ms-2">'
                + '<h6 class="fs-13 mb-0 fw-semibold">' + escapeHtml(task.title || '-') + '</h6>'
                + '<span class="small">' + escapeHtml(statusText) + ' by <span class="text-primary">' + escapeHtml(task.assignee || '-') + '</span></span>'
                + '</div>'
                + '<div class="clearfix ms-auto">'
                + '<button type="button" class="btn btn-sm btn-light btn-square" disabled><i class="bi bi-grid"></i></button>'
                + '</div>'
                + '</div>';
        }

        meetingTaskCards.forEach(function (card) {
            cardByTitle[card.title] = card;
        });

        $('.card').each(function () {
            var cardElement = $(this);
            var title = $.trim(cardElement.find('.card-title').first().text());
            var cardData = cardByTitle[title];

            if (!cardData) {
                return;
            }

            var body = cardElement.find('.card-body').first();
            var headerRow = body.children('.d-flex.justify-content-between.mb-3').first();
            var completedText = cardData.completed + ' / ' + cardData.total + ' Completed <span class="text-success">(' + cardData.percentage + '%)</span>';
            var tasksHtml = cardData.tasks.length
                ? cardData.tasks.map(renderTask).join('')
                : '<div class="text-muted py-2">No task yet.</div>';

            headerRow.find('.text-gray').html(completedText);
            headerRow.find('[data-bs-target="#create"]').attr('href', 'javascript:void(0)').attr('data-meeting-task-category', cardData.key);
            body.children().not(headerRow).remove();
            body.append(tasksHtml);
        });

        $('#create').on('show.bs.modal', function (event) {
            var trigger = $(event.relatedTarget);
            var category = trigger.data('meeting-task-category') || '';
            var title = $.trim(trigger.closest('.card').find('.card-title').first().text());

            $('#meetingTaskCategory').val(category);
            $('#createMeetingTaskLabel').text(title ? 'Create Task - ' + title : 'Create Task');
        });

        $('.js-meeting-task-date-range-picker').each(function () {
            var input = $(this);
            var startDateInput = $(input.data('start-date-target'));
            var dueDateInput = $(input.data('due-date-target'));

            if (!$.fn.daterangepicker || !window.moment) {
                input.attr('type', 'date');
                input.on('change', function () {
                    startDateInput.val(input.val());
                    dueDateInput.val(input.val());
                });
                return;
            }

            input.daterangepicker({
                autoUpdateInput: false,
                showDropdowns: true,
                startDate: moment(),
                endDate: moment(),
                locale: {
                    format: 'DD/MM/YYYY',
                    separator: ' - ',
                    cancelLabel: 'Clear',
                },
            });

            input.on('apply.daterangepicker', function (event, picker) {
                input.val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
                startDateInput.val(picker.startDate.format('YYYY-MM-DD'));
                dueDateInput.val(picker.endDate.format('YYYY-MM-DD'));
                input.trigger('blur');
            });

            input.on('cancel.daterangepicker', function () {
                input.val('');
                startDateInput.val('');
                dueDateInput.val('');
            });
        });
    });
</script>
