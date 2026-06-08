@extends('velzon.layouts.master')
@section('title')
    Calendar
@endsection
@section('css')
    <style>
        /* Remove gap between breadcrumb ribbon and calendar */
        .page-title-box {
            margin-bottom: 0 !important;
            padding-bottom: 0.75rem;
        }

        #calendarWrapper .card {
            border-top: none;
            border-radius: 0 0 0.25rem 0.25rem;
            box-shadow: none;
        }

        /* FullCalendar overrides for Velzon theme */
        #calendar-full .fc {
            font-family: inherit;
        }

        #calendar-full .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        #calendar-full .fc-event {
            border: none;
            border-radius: 3px;
            font-size: 0.8rem;
            padding: 2px 6px;
            cursor: pointer;
        }

        #calendar-full .fc-daygrid-event {
            margin-bottom: 1px;
        }

        #calendar-full .fc-daygrid-day-frame {
            min-height: 140px;
        }

        /* ── Full-screen mode ── */
        .calendar-wrapper.fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1055;
            background: var(--vz-body-bg, #f3f3f9);
            overflow-y: auto;
            padding: 0;
        }

        .calendar-wrapper.fullscreen .card {
            margin-bottom: 0;
            min-height: 100vh;
            border-radius: 0;
            border: none;
        }
        .calendar-wrapper.fullscreen .card-body {
            padding: 0;
            padding-top: 10px;
        }

        .calendar-wrapper.fullscreen:fullscreen .card-body {
            padding-top: 20px;
        }

        .calendar-wrapper.fullscreen #calendar-full .fc-daygrid-day-frame {
            min-height: 160px;
        }

        .calendar-wrapper.fullscreen .fc-col-header {
            position: sticky;
            top: 0;
            z-index: 10;
            background: var(--vz-card-bg, #fff);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }

        .fullscreen-backdrop {
            display: none;
        }

        .calendar-wrapper.fullscreen ~ .fullscreen-backdrop {
            display: block;
            position: fixed;
            inset: 0;
            z-index: 1054;
            background: rgba(0, 0, 0, 0.5);
        }

        /* ── Day hover popover ── */
        .day-popover {
            position: fixed;
            z-index: 1060;
            min-width: 260px;
            max-width: 340px;
            background: var(--vz-card-bg, #fff);
            border: 1px solid var(--vz-border-color, #e9ebec);
            border-radius: 0.375rem;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.15);
            padding: 0;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s ease;
        }

        .day-popover.visible {
            opacity: 1;
            pointer-events: auto;
        }

        .day-popover-header {
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid var(--vz-border-color, #e9ebec);
            font-weight: 600;
            font-size: 0.85rem;
            background: var(--vz-light, #f3f6f9);
            border-radius: 0.375rem 0.375rem 0 0;
        }

        .day-popover-body {
            padding: 0.5rem 0.75rem;
            max-height: 220px;
            overflow-y: auto;
        }

        .day-popover-item {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            padding: 0.3rem 0;
            font-size: 0.8rem;
        }

        .day-popover-item + .day-popover-item {
            border-top: 1px solid var(--vz-border-color, #e9ebec);
        }

        .day-popover-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
            margin-top: 4px;
        }

        .day-popover-time {
            font-size: 0.75rem;
            color: var(--vz-secondary-color, #878a99);
            white-space: nowrap;
        }

        .day-popover-empty {
            color: var(--vz-secondary-color, #878a99);
            font-size: 0.8rem;
            font-style: italic;
            padding: 0.25rem 0;
        }

        /* Full-screen button style */
        .btn-fullscreen {
            border: none;
            background: transparent;
            font-size: 1.1rem;
            color: var(--vz-secondary-color, #878a99);
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            transition: color 0.15s, background 0.15s;
        }

        .btn-fullscreen:hover {
            color: var(--vz-primary, #405189);
            background: var(--vz-light, #f3f6f9);
        }

        /* ── Category filter pills ── */
        .category-pills {
            display: flex;
            gap: 0.35rem;
            align-items: center;
        }

        .category-pill {
            font-size: 0.72rem;
            padding: 0.15rem 0.5rem;
            border-radius: 1rem;
            border: 1px solid var(--vz-border-color, #e9ebec);
            background: transparent;
            cursor: pointer;
            transition: all 0.15s;
            white-space: nowrap;
        }

        .category-pill.active {
            color: #fff;
            border-color: transparent;
        }

        /* ── Event modal ── */
        .event-modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1070;
            background: rgba(0, 0, 0, 0.4);
        }

        .event-modal-backdrop.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .event-modal {
            background: var(--vz-card-bg, #fff);
            border-radius: 0.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 480px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .event-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--vz-border-color, #e9ebec);
        }

        .event-modal-header h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
        }

        .event-modal-body {
            padding: 1.25rem;
        }

        .event-modal-body .form-group {
            margin-bottom: 1rem;
        }

        .event-modal-body label {
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            display: block;
        }

        .event-modal-footer {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 1.25rem;
            border-top: 1px solid var(--vz-border-color, #e9ebec);
        }
    </style>
@endsection
@section('content')
    {{-- Custom breadcrumb ribbon with calendar controls --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Calendar</h4>
                <div class="d-flex align-items-center gap-3">
                    <div class="category-pills" id="categoryPills">
                        <button class="category-pill active" data-category="all" style="background:#405189;border-color:#405189;">All</button>
                        <button class="category-pill" data-category="sacramental" data-color="#6f42c1">Sacram.</button>
                        <button class="category-pill" data-category="formation" data-color="#0d6efd">Form.</button>
                        <button class="category-pill" data-category="parish" data-color="#198754">Parish</button>
                        <button class="category-pill" data-category="diocesan" data-color="#4F46E5">Diocesan</button>
                        <button class="category-pill" data-category="staff" data-color="#fd7e14">Staff</button>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="toggleLiturgicalOverlay" checked>
                        <label class="form-check-label fs-13" for="toggleLiturgicalOverlay">
                            Liturgical
                        </label>
                    </div>
                    <button class="btn btn-sm btn-primary" id="btnAddEvent" title="Create Event">
                        <i class="ri-add-line me-1"></i>Event
                    </button>
                    <button class="btn-fullscreen" id="btnFullscreen" title="Expand in browser">
                        <i class="ri-fullscreen-line"></i>
                    </button>
                    <button class="btn-fullscreen" id="btnTrueFullscreen" title="True full screen">
                        <i class="ri-computer-line"></i>
                    </button>
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboards</a></li>
                        <li class="breadcrumb-item active">Calendar</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="calendar-wrapper" id="calendarWrapper">
                <div class="card">
                    <div class="card-body">
                        <div id="calendar-full"></div>
                    </div>
                </div>
            </div>
            <div class="fullscreen-backdrop" id="fullscreenBackdrop"></div>
        </div>
    </div>

    {{-- Floating popover for day hover --}}
    <div class="day-popover" id="dayPopover">
        <div class="day-popover-header" id="dayPopoverHeader"></div>
        <div class="day-popover-body" id="dayPopoverBody"></div>
    </div>

    {{-- Create / Edit Event Modal --}}
    <div class="event-modal-backdrop" id="eventModalBackdrop">
        <div class="event-modal">
            <div class="event-modal-header">
                <h5 id="eventModalTitle">Create Event</h5>
                <button type="button" class="btn-close" id="eventModalClose"></button>
            </div>
            <div class="event-modal-body">
                <input type="hidden" id="eventId" value="">
                <div class="form-group">
                    <label for="eventTitle">Title</label>
                    <input type="text" class="form-control form-control-sm" id="eventTitle" placeholder="Event title" required>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="eventStart">Start</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="eventStart">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="eventEnd">End</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="eventEnd">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="eventAllDay" checked>
                        <label class="form-check-label" for="eventAllDay">All day</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="eventLocation">Location</label>
                    <input type="text" class="form-control form-control-sm" id="eventLocation" placeholder="e.g. Cathedral, Chancery Office">
                </div>
                <div class="form-group">
                    <label for="eventCategory">Category</label>
                    <select class="form-select form-select-sm" id="eventCategory">
                        <option value="sacramental">Sacramental</option>
                        <option value="formation">Formation</option>
                        <option value="parish" selected>Parish</option>
                        <option value="diocesan">Diocesan</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label for="eventDescription">Description</label>
                    <textarea class="form-control form-control-sm" id="eventDescription" rows="3" placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div class="event-modal-footer">
                <div>
                    <button type="button" class="btn btn-sm btn-soft-danger d-none" id="eventDeleteBtn">
                        <i class="ri-delete-bin-line me-1"></i>Delete
                    </button>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-light" id="eventCancelBtn">Cancel</button>
                    <button type="button" class="btn btn-sm btn-primary" id="eventSaveBtn">
                        <i class="ri-check-line me-1"></i>Save
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/fullcalendar/index.global.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar-full');
            if (!calendarEl) return;

            var toggle = document.getElementById('toggleLiturgicalOverlay');
            var STORAGE_KEY = 'liturgicalOverlayEnabled';
            var saved = localStorage.getItem(STORAGE_KEY);
            if (saved !== null) toggle.checked = saved === 'true';

            // ── Full-screen toggle ──
            var wrapper = document.getElementById('calendarWrapper');
            var backdrop = document.getElementById('fullscreenBackdrop');
            var btnFs = document.getElementById('btnFullscreen');
            var isFullscreen = false;

            function toggleFullscreen() {
                isFullscreen = !isFullscreen;
                wrapper.classList.toggle('fullscreen', isFullscreen);
                btnFs.querySelector('i').className = isFullscreen
                    ? 'ri-fullscreen-exit-line'
                    : 'ri-fullscreen-line';
                btnFs.title = isFullscreen ? 'Exit full screen' : 'Toggle full screen';
                // Re-render calendar to adjust sizes
                setTimeout(function () { calendar.updateSize(); }, 100);
            }

            btnFs.addEventListener('click', toggleFullscreen);
            backdrop.addEventListener('click', function () {
                if (isFullscreen) toggleFullscreen();
            });

            // Escape key exits full screen
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && isFullscreen && !document.fullscreenElement) toggleFullscreen();
            });

            // ── True full-screen (OS-level via Fullscreen API) ──
            var btnTrueFs = document.getElementById('btnTrueFullscreen');

            btnTrueFs.addEventListener('click', function () {
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                } else {
                    // Enter in-browser expand first if not already
                    if (!isFullscreen) toggleFullscreen();
                    wrapper.requestFullscreen().catch(function () {});
                }
            });

            document.addEventListener('fullscreenchange', function () {
                var inTrue = !!document.fullscreenElement;
                btnTrueFs.querySelector('i').className = inTrue
                    ? 'ri-logout-box-line'
                    : 'ri-computer-line';
                btnTrueFs.title = inTrue ? 'Exit true full screen' : 'True full screen';
                if (!inTrue && isFullscreen) {
                    // Exited true fullscreen — keep in-browser expand
                }
                setTimeout(function () { calendar.updateSize(); }, 100);
            });

            // ── Day popover ──
            var popover = document.getElementById('dayPopover');
            var popoverHeader = document.getElementById('dayPopoverHeader');
            var popoverBody = document.getElementById('dayPopoverBody');
            var hideTimeout = null;

            function showPopover(dayEl, dateStr, events) {
                clearTimeout(hideTimeout);

                // Format date nicely
                var d = new Date(dateStr + 'T12:00:00');
                var opts = { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' };
                popoverHeader.textContent = d.toLocaleDateString('en-US', opts);

                // Build event list
                if (events.length === 0) {
                    popoverBody.innerHTML = '<div class="day-popover-empty">No events</div>';
                } else {
                    var html = '';
                    events.forEach(function (ev) {
                        var color = ev.backgroundColor || ev.color || '#405189';
                        var timeStr = '';
                        if (!ev.allDay && ev.start) {
                            var start = new Date(ev.start);
                            timeStr = start.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                            if (ev.end) {
                                var end = new Date(ev.end);
                                timeStr += ' – ' + end.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                            }
                        } else {
                            timeStr = 'All day';
                        }
                        html += '<div class="day-popover-item">' +
                            '<span class="day-popover-dot" style="background:' + color + '"></span>' +
                            '<div class="flex-grow-1">' +
                                '<div class="fw-medium">' + (ev.title || 'Untitled') + '</div>' +
                                '<div class="day-popover-time">' + timeStr + '</div>' +
                            '</div>' +
                        '</div>';
                    });
                    popoverBody.innerHTML = html;
                }

                // Position near the hovered day cell (fixed positioning)
                var rect = dayEl.getBoundingClientRect();
                var pw = 300; // approx popover width
                var ph = 260; // approx popover height

                // Horizontal: prefer right of cell, flip left if no room
                var left = rect.right + 8;
                if (left + pw > window.innerWidth) {
                    left = rect.left - pw - 8;
                }
                // Clamp horizontal
                left = Math.max(8, Math.min(left, window.innerWidth - pw - 8));

                // Vertical: prefer below the top of the cell
                var top = rect.top;
                // If popover would go below viewport, show it above the bottom of the cell
                if (top + ph > window.innerHeight) {
                    top = rect.bottom - ph;
                }
                // Clamp vertical — never go above viewport
                top = Math.max(8, Math.min(top, window.innerHeight - ph - 8));

                popover.style.left = left + 'px';
                popover.style.top = top + 'px';
                popover.classList.add('visible');
            }

            function hidePopover() {
                hideTimeout = setTimeout(function () {
                    popover.classList.remove('visible');
                }, 200);
            }

            // Keep popover open when mouse enters it
            popover.addEventListener('mouseenter', function () {
                clearTimeout(hideTimeout);
            });
            popover.addEventListener('mouseleave', function () {
                hidePopover();
            });

            // ── Calendar init with dual event sources ──
            var CSRF = document.querySelector('meta[name="csrf-token"]').content;
            var activeCategory = 'all'; // 'all' or specific category

            // Category pill click handler
            document.getElementById('categoryPills').addEventListener('click', function (e) {
                var pill = e.target.closest('.category-pill');
                if (!pill) return;
                // Deactivate all pills
                document.querySelectorAll('.category-pill').forEach(function (p) {
                    p.classList.remove('active');
                    p.style.background = '';
                    p.style.borderColor = '';
                });
                // Activate clicked pill
                pill.classList.add('active');
                var color = pill.dataset.color || '#405189';
                pill.style.background = color;
                pill.style.borderColor = color;
                activeCategory = pill.dataset.category;
                calendar.refetchEvents();
            });

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                firstDay: 0,
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                buttonText: { today: 'Today', month: 'Month', week: 'Week', day: 'Day', list: 'List' },

                eventSources: [
                    // Source 1: Liturgical events
                    {
                        id: 'liturgical',
                        events: function (info, successCallback, failureCallback) {
                            if (!toggle.checked) { successCallback([]); return; }
                            fetch('/api/liturgical-calendar/events?' + new URLSearchParams({
                                start: info.startStr, end: info.endStr, overlay: 'true'
                            }), { credentials: 'same-origin' })
                                .then(function (r) { return r.json(); })
                                .then(function (d) { successCallback(d); })
                                .catch(function (e) { failureCallback(e); });
                        }
                    },
                    // Source 2: Diocesan/parish events
                    {
                        id: 'events',
                        events: function (info, successCallback, failureCallback) {
                            var params = { start: info.startStr, end: info.endStr };
                            if (activeCategory !== 'all') params.category = activeCategory;
                            fetch('/api/calendar/events?' + new URLSearchParams(params), {
                                credentials: 'same-origin'
                            })
                                .then(function (r) { return r.json(); })
                                .then(function (d) { successCallback(d); })
                                .catch(function (e) { failureCallback(e); });
                        }
                    }
                ],

                eventDisplay: 'block',
                editable: false,
                selectable: true,

                // Click on a day → open create modal
                dateClick: function (info) {
                    openCreateModal(info.dateStr);
                },

                // Click on an event → open edit modal (only for diocesan events)
                eventClick: function (info) {
                    var p = info.event.extendedProps;
                    if (p.type === 'event') {
                        info.jsEvent.preventDefault();
                        openEditModal(info.event);
                    }
                },

                eventDidMount: function (info) {
                    var p = info.event.extendedProps;
                    var lines = [info.event.title];
                    if (p.rank) lines.push('Rank: ' + p.rank.replace('_', ' '));
                    if (p.season) lines.push('Season: ' + p.season.replace('_', ' '));
                    if (p.color_name) lines.push('Color: ' + p.color_name);
                    if (p.readings && p.readings.citations) lines.push('Readings: ' + p.readings.citations.join('; '));
                    if (p.category) lines.push('Category: ' + p.category);
                    if (p.location) lines.push('Location: ' + p.location);
                    if (p.description) lines.push(p.description);
                    info.el.setAttribute('title', lines.join('\n'));
                    if (p.rankIcon) {
                        var te = info.el.querySelector('.fc-event-title');
                        if (te) te.innerHTML = p.rankIcon + ' ' + te.innerHTML;
                    }
                },
                dayMaxEventRows: 4,
                moreLinkText: 'more',

                // Day hover handlers
                dayCellDidMount: function (arg) {
                    arg.el.addEventListener('mouseenter', function () {
                        var dateStr = arg.date.toISOString().slice(0, 10);
                        var dayEvents = calendar.getEvents().filter(function (ev) {
                            var evStart = ev.start ? ev.start.toISOString().slice(0, 10) : '';
                            return evStart === dateStr;
                        }).map(function (ev) {
                            return {
                                title: ev.title,
                                start: ev.start,
                                end: ev.end,
                                allDay: ev.allDay,
                                backgroundColor: ev.backgroundColor,
                                color: ev.extendedProps.color_hex || ev.backgroundColor
                            };
                        });
                        showPopover(arg.el, dateStr, dayEvents);
                    });
                    arg.el.addEventListener('mouseleave', function () {
                        hidePopover();
                    });
                }
            });
            calendar.render();

            toggle.addEventListener('change', function () {
                localStorage.setItem(STORAGE_KEY, toggle.checked);
                calendar.refetchEvents();
            });

            // ── Event Modal CRUD ──
            var modalBackdrop = document.getElementById('eventModalBackdrop');
            var modalTitle    = document.getElementById('eventModalTitle');
            var eventIdField  = document.getElementById('eventId');
            var fTitle        = document.getElementById('eventTitle');
            var fStart        = document.getElementById('eventStart');
            var fEnd          = document.getElementById('eventEnd');
            var fAllDay       = document.getElementById('eventAllDay');
            var fLocation     = document.getElementById('eventLocation');
            var fCategory     = document.getElementById('eventCategory');
            var fDescription  = document.getElementById('eventDescription');
            var deleteBtn     = document.getElementById('eventDeleteBtn');

            function openCreateModal(dateStr) {
                eventIdField.value = '';
                modalTitle.textContent = 'Create Event';
                fTitle.value = '';
                fStart.value = dateStr ? dateStr + 'T09:00' : '';
                fEnd.value = '';
                fAllDay.checked = true;
                fLocation.value = '';
                fCategory.value = 'parish';
                fDescription.value = '';
                deleteBtn.classList.add('d-none');
                modalBackdrop.classList.add('show');
                setTimeout(function () { fTitle.focus(); }, 100);
            }

            function openEditModal(fcEvent) {
                var p = fcEvent.extendedProps;
                eventIdField.value = fcEvent.id;
                modalTitle.textContent = 'Edit Event';
                fTitle.value = fcEvent.title;
                fAllDay.checked = fcEvent.allDay;
                if (fcEvent.start) {
                    fStart.value = fcEvent.allDay
                        ? fcEvent.start.toISOString().slice(0, 10) + 'T00:00'
                        : toLocalDatetimeStr(fcEvent.start);
                }
                if (fcEvent.end) {
                    fEnd.value = fcEvent.allDay
                        ? new Date(fcEvent.end.getTime() - 86400000).toISOString().slice(0, 10) + 'T23:59'
                        : toLocalDatetimeStr(fcEvent.end);
                } else {
                    fEnd.value = '';
                }
                fLocation.value = p.location || '';
                fCategory.value = p.category || 'parish';
                fDescription.value = p.description || '';
                deleteBtn.classList.remove('d-none');
                modalBackdrop.classList.add('show');
                setTimeout(function () { fTitle.focus(); }, 100);
            }

            function closeModal() {
                modalBackdrop.classList.remove('show');
            }

            function toLocalDatetimeStr(d) {
                var pad = function (n) { return n < 10 ? '0' + n : n; };
                return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) +
                    'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
            }

            // Open from header button
            document.getElementById('btnAddEvent').addEventListener('click', function () {
                openCreateModal(new Date().toISOString().slice(0, 10));
            });

            // Close handlers
            document.getElementById('eventModalClose').addEventListener('click', closeModal);
            document.getElementById('eventCancelBtn').addEventListener('click', closeModal);
            modalBackdrop.addEventListener('click', function (e) {
                if (e.target === modalBackdrop) closeModal();
            });

            // Save (create or update)
            document.getElementById('eventSaveBtn').addEventListener('click', function () {
                var id = eventIdField.value;
                var data = {
                    title: fTitle.value.trim(),
                    start_at: fStart.value ? fStart.value.replace('T', ' ') + ':00' : null,
                    end_at: fEnd.value ? fEnd.value.replace('T', ' ') + ':00' : null,
                    all_day: fAllDay.checked,
                    location: fLocation.value.trim() || null,
                    category: fCategory.value,
                    description: fDescription.value.trim() || null
                };

                if (!data.title || !data.start_at) {
                    alert('Title and Start date are required.');
                    return;
                }

                var url = id ? '/api/calendar/events/' + id : '/api/calendar/events';
                var method = id ? 'PUT' : 'POST';

                fetch(url, {
                    method: method,
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(function (r) {
                    if (!r.ok) return r.json().then(function (e) { throw e; });
                    return r.json();
                })
                .then(function () {
                    closeModal();
                    calendar.refetchEvents();
                })
                .catch(function (err) {
                    var msg = 'Error saving event.';
                    if (err && err.errors) {
                        msg = Object.values(err.errors).flat().join('\n');
                    }
                    alert(msg);
                });
            });

            // Delete
            deleteBtn.addEventListener('click', function () {
                var id = eventIdField.value;
                if (!id) return;
                if (!confirm('Delete this event?')) return;

                fetch('/api/calendar/events/' + id, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    }
                })
                .then(function () {
                    closeModal();
                    calendar.refetchEvents();
                })
                .catch(function () {
                    alert('Error deleting event.');
                });
            });
        });
    </script>
@endsection