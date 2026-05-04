"use strict";

(function () {
    function buildDefaultEvents() {
        return [
            {
                title: 'All Day Event',
                start: '2021-02-01',
            },
            {
                title: 'Long Event',
                start: '2021-02-07',
                end: '2021-02-10',
                className: 'bg-danger',
            },
            {
                title: 'Conference',
                start: '2021-02-11',
                end: '2021-02-13',
                className: 'bg-danger',
            },
            {
                title: 'Lunch',
                start: '2021-02-12T12:00:00',
            },
            {
                title: 'Birthday Party',
                start: '2021-02-13T07:00:00',
                className: 'bg-secondary',
            },
        ];
    }

    function createCalendarOptions(calendarEl, containerEl, dropRemove) {
        const eventSourceUrl = calendarEl.dataset.eventSourceUrl;
        const timeZone = calendarEl.dataset.timeZone || 'local';
        const options = {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,dayGridWeek,dayGridDay,listSchedule',
            },
            views: {
                listSchedule: {
                    type: 'list',
                    duration: { months: 4 },
                    buttonText: 'Schedule',
                },
            },
            listDayFormat: { weekday: 'short', month: 'short', day: 'numeric' },
            listDaySideFormat: { year: 'numeric' },
            scrollTime: '07:30:00',
            slotMinTime: '06:00:00',
            slotMaxTime: '21:00:00',
            weekNumbers: true,
            navLinks: true,
            nowIndicator: true,
            longPressDelay: 0,
            datesSet: function (info) {
                mountDateFilterInToolbar(info.view.calendar);
            },
            dateClick: function (info) {
                if (info.view.type === 'dayGridMonth' || info.view.type === 'dayGridWeek') {
                    info.view.calendar.changeView('dayGridDay', info.date);
                }
            },
            editable: true,
            selectable: true,
            selectMirror: true,
            droppable: !!containerEl,
            drop: function (info) {
                if (dropRemove && dropRemove.checked) {
                    info.draggedEl.parentNode.removeChild(info.draggedEl);
                }
            },
            select: function (info) {
                const title = prompt('Event Title:');
                if (title) {
                    info.view.calendar.addEvent({
                        title: title,
                        start: info.start,
                        end: info.end,
                        allDay: info.allDay,
                    });
                }

                info.view.calendar.unselect();
            },
        };

        if (eventSourceUrl) {
            options.timeZone = timeZone;
            options.events = {
                url: eventSourceUrl,
                method: 'GET',
                failure: function () {
                    console.error('Failed to load calendar events');
                },
            };
            options.editable = false;
            options.selectable = false;
            options.droppable = false;
        } else {
            options.initialDate = '2021-02-13';
            options.events = buildDefaultEvents();
        }

        return options;
    }

    function initializeExternalEvents(containerEl) {
        if (!containerEl || typeof FullCalendar.Draggable !== 'function') {
            return;
        }

        new FullCalendar.Draggable(containerEl, {
            itemSelector: '.external-event',
            eventData: function (eventEl) {
                return {
                    title: eventEl.innerText.trim(),
                };
            },
        });
    }

    function initializeCalendar() {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl || typeof FullCalendar === 'undefined' || calendarEl.dataset.fcInitialized === '1') {
            return;
        }

        const externalEventsContainer = document.getElementById('external-events');
        const dropRemove = document.getElementById('drop-remove');

        initializeExternalEvents(externalEventsContainer);

        const calendar = new FullCalendar.Calendar(
            calendarEl,
            createCalendarOptions(calendarEl, externalEventsContainer, dropRemove)
        );

        calendar.render();
        window.activityScheduleCalendar = calendar;
        calendarEl.dataset.fcInitialized = '1';

        mountDateFilterInToolbar(calendar);
        bindDateFilter(calendar);
    }

    function mountDateFilterInToolbar(calendar) {
        const wrap = document.getElementById('calendar-date-filter-wrap');
        if (!wrap) {
            return;
        }

        const toolbarLeft = calendar.el.querySelector('.fc-header-toolbar .fc-toolbar-chunk:first-child');
        if (!toolbarLeft) {
            return;
        }

        if (!toolbarLeft.contains(wrap)) {
            wrap.classList.remove('d-none');
            wrap.classList.add('d-inline-flex', 'align-items-center', 'ms-2');
            toolbarLeft.appendChild(wrap);
        }
    }

    function bindDateFilter(calendar) {
        const dateInput = document.getElementById('calendar-date-filter');

        if (!dateInput || dateInput.dataset.bound === '1') {
            return;
        }

        const applyDateFilter = function () {
            if (!dateInput.value) {
                return;
            }

            calendar.changeView('dayGridMonth', dateInput.value);
        };

        dateInput.addEventListener('change', applyDateFilter);

        dateInput.dataset.bound = '1';
    }

    document.addEventListener('DOMContentLoaded', initializeCalendar);

    if (typeof jQuery !== 'undefined') {
        jQuery(window).on('load', function () {
            setTimeout(initializeCalendar, 300);
        });
    }

    window.fullCalender = initializeCalendar;
})();
