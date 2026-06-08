@extends('velzon.layouts.master')
@section('title')
    Chancery Dashboard
@endsection
@section('content')
    @component('velzon.components.breadcrumb')
    @slot('li_1')
    Dashboards
    @endslot
    @slot('title')
    Chancery Dashboard
    @endslot
    @endcomponent

    {{-- ══════════════════════════════════════════════════════════════
    ROW 1 — Today's Liturgy (with inline LOTH) + Quick Actions
    ══════════════════════════════════════════════════════════════ --}}
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            @include('velzon.partials._today-liturgy', [
                'todayEvent'  => $todayEvent ?? null,
                'lothRanges'  => $lothRanges ?? [],
            ])
        </div>
        <div class="col-xl-4 col-lg-5">
            {{-- Quick Actions Panel --}}
            <div class="card card-height-100">
                <div class="card-header">
                    <h4 class="card-title mb-0"><i class="ri-flashlight-line me-1 text-warning"></i> Quick Actions</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ url('/admin/contacts?action=create') }}" class="btn btn-soft-primary btn-sm text-start">
                            <i class="ri-user-add-line me-2"></i> Add Contact
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-soft-info btn-sm text-start w-100 dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-calendar-event-line me-2"></i> Add Event
                            </button>
                            <div class="dropdown-menu w-100">
                                <a class="dropdown-item" href="{{ url('/admin/calendar?action=create&type=diocesan') }}">
                                    <i class="ri-building-line me-2 text-primary"></i> Diocesan Event
                                </a>
                                <a class="dropdown-item" href="{{ url('/admin/calendar?action=create&type=parish') }}">
                                    <i class="ri-community-line me-2 text-success"></i> Parish Event
                                </a>
                                <a class="dropdown-item" href="{{ url('/admin/calendar?action=create&type=school') }}">
                                    <i class="ri-school-line me-2 text-warning"></i> School Event
                                </a>
                                <a class="dropdown-item" href="{{ url('/admin/calendar?action=create&type=apostolate') }}">
                                    <i class="ri-hand-heart-line me-2 text-danger"></i> Apostolate Event
                                </a>
                                <a class="dropdown-item" href="{{ url('/admin/calendar?action=create&type=liturgical') }}">
                                    <i class="ri-book-open-line me-2 text-info"></i> Liturgical Event
                                </a>
                            </div>
                        </div>
                        <a href="{{ url('/admin/forms?action=create') }}" class="btn btn-soft-success btn-sm text-start">
                            <i class="ri-survey-line me-2"></i> New Form
                        </a>
                        <a href="{{ url('/admin/communications?action=create') }}"
                            class="btn btn-soft-warning btn-sm text-start">
                            <i class="ri-mail-send-line me-2"></i> Send Communication
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
    ROW 2 — Diocesan KPI Metrics
    ══════════════════════════════════════════════════════════════ --}}
    <div class="row">
        <div class="col-xl-12">
            <div class="card crm-widget">
                <div class="card-body p-0">
                    <div class="row row-cols-xxl-5 row-cols-md-3 row-cols-1 g-0">
                        <div class="col">
                            <div class="py-4 px-3">
                                <h5 class="text-muted text-uppercase fs-13">Total Contacts <i
                                        class="ri-arrow-up-circle-line text-success fs-18 float-end align-middle"></i></h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ri-contacts-line display-6 text-muted"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h2 class="mb-0"><span class="counter-value"
                                                data-target="{{ $metrics['contacts'] ?? 0 }}">0</span></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mt-3 mt-md-0 py-4 px-3">
                                <h5 class="text-muted text-uppercase fs-13">Parishes <i
                                        class="ri-community-line text-primary fs-18 float-end align-middle"></i></h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ri-building-2-line display-6 text-muted"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h2 class="mb-0"><span class="counter-value"
                                                data-target="{{ $metrics['parishes'] ?? 0 }}">0</span></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mt-3 mt-md-0 py-4 px-3">
                                <h5 class="text-muted text-uppercase fs-13">Active Clergy <i
                                        class="ri-user-star-line text-info fs-18 float-end align-middle"></i></h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ri-user-star-line display-6 text-muted"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h2 class="mb-0"><span class="counter-value"
                                                data-target="{{ $metrics['clergy'] ?? 0 }}">0</span></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mt-3 mt-lg-0 py-4 px-3">
                                <h5 class="text-muted text-uppercase fs-13">Pending Forms <i
                                        class="ri-file-list-3-line text-warning fs-18 float-end align-middle"></i></h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ri-survey-line display-6 text-muted"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h2 class="mb-0"><span class="counter-value"
                                                data-target="{{ $metrics['pendingForms'] ?? 0 }}">0</span></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mt-3 mt-lg-0 py-4 px-3">
                                <h5 class="text-muted text-uppercase fs-13">Events This Week <i
                                        class="ri-calendar-check-line text-success fs-18 float-end align-middle"></i></h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ri-calendar-event-line display-6 text-muted"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h2 class="mb-0"><span class="counter-value"
                                                data-target="{{ $metrics['eventsThisWeek'] ?? 0 }}">0</span></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
    ROW 3 — Charts: Constituent Growth / Entities by Type / Clergy Status
    ══════════════════════════════════════════════════════════════ --}}
    <div class="row">
        <div class="col-xxl-4 col-md-6">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Constituent Growth</h4>
                    <div class="flex-shrink-0">
                        <div class="dropdown card-header-dropdown">
                            <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <span class="fw-bold text-uppercase fs-12">Period: </span><span class="text-muted">This
                                    Year<i class="mdi mdi-chevron-down ms-1"></i></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">Last 30 Days</a>
                                <a class="dropdown-item" href="#">Last 90 Days</a>
                                <a class="dropdown-item" href="#">This Year</a>
                                <a class="dropdown-item" href="#">All Time</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body pb-0">
                    <div id="constituent-growth-chart" data-colors='["--vz-primary", "--vz-success"]' class="apex-charts"
                        dir="ltr"></div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-md-6">
            <div class="card card-height-100">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Entities by Type</h4>
                </div>
                <div class="card-body pb-0">
                    <div id="entities-by-type-chart"
                        data-colors='["--vz-primary", "--vz-success", "--vz-warning", "--vz-info", "--vz-danger"]'
                        class="apex-charts" dir="ltr"></div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4">
            <div class="card card-height-100">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Clergy Status</h4>
                </div>
                <div class="card-body pb-0">
                    <div id="clergy-status-chart"
                        data-colors='["--vz-success", "--vz-secondary", "--vz-warning", "--vz-danger"]' class="apex-charts"
                        dir="ltr"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
    ROW 4 — Recent Contacts + My Action Items
    ══════════════════════════════════════════════════════════════ --}}
    <div class="row">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Recent Contacts</h4>
                    <div class="flex-shrink-0">
                        <a href="{{ url('/admin/contacts') }}" class="btn btn-soft-primary btn-sm">
                            View All <i class="ri-arrow-right-line ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive table-card">
                        <table class="table table-borderless table-hover table-nowrap align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-muted">
                                    <th scope="col">Name</th>
                                    <th scope="col">Parish / Organization</th>
                                    <th scope="col" style="width: 20%;">Last Contacted</th>
                                    <th scope="col" style="width: 16%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs me-2">
                                                <span
                                                    class="avatar-title rounded-circle bg-primary-subtle text-primary">JD</span>
                                            </div>
                                            <a href="#" class="text-body fw-semibold">John Doe</a>
                                        </div>
                                    </td>
                                    <td>St. Mary's Cathedral</td>
                                    <td>Feb 5, 2026</td>
                                    <td><span class="badge bg-success-subtle text-success p-2">Active</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs me-2">
                                                <span class="avatar-title rounded-circle bg-info-subtle text-info">MS</span>
                                            </div>
                                            <a href="#" class="text-body fw-semibold">Maria Santos</a>
                                        </div>
                                    </td>
                                    <td>Holy Family Parish</td>
                                    <td>Feb 3, 2026</td>
                                    <td><span class="badge bg-success-subtle text-success p-2">Active</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs me-2">
                                                <span
                                                    class="avatar-title rounded-circle bg-warning-subtle text-warning">RJ</span>
                                            </div>
                                            <a href="#" class="text-body fw-semibold">Robert Johnson</a>
                                        </div>
                                    </td>
                                    <td>Catholic Charities</td>
                                    <td>Jan 28, 2026</td>
                                    <td><span class="badge bg-warning-subtle text-warning p-2">Pending Review</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs me-2">
                                                <span
                                                    class="avatar-title rounded-circle bg-success-subtle text-success">SP</span>
                                            </div>
                                            <a href="#" class="text-body fw-semibold">Sr. Patricia Morales</a>
                                        </div>
                                    </td>
                                    <td>Sisters of Charity</td>
                                    <td>Jan 22, 2026</td>
                                    <td><span class="badge bg-success-subtle text-success p-2">Active</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs me-2">
                                                <span
                                                    class="avatar-title rounded-circle bg-danger-subtle text-danger">TW</span>
                                            </div>
                                            <a href="#" class="text-body fw-semibold">Thomas Walsh</a>
                                        </div>
                                    </td>
                                    <td>USCCB — Education Committee</td>
                                    <td>Jan 15, 2026</td>
                                    <td><span class="badge bg-info-subtle text-info p-2">External</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card card-height-100">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">My Action Items</h4>
                    <div class="flex-shrink-0">
                        <button type="button" class="btn btn-sm btn-primary"><i class="ri-add-line align-middle me-1"></i>
                            Add Item</button>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="align-items-center p-3 justify-content-between d-flex">
                        <div class="flex-shrink-0">
                            <div class="text-muted"><span class="fw-semibold">4</span> of <span class="fw-semibold">7</span>
                                remaining</div>
                        </div>
                    </div>

                    <div data-simplebar style="max-height: 280px;">
                        <ul class="list-group list-group-flush border-dashed px-3">
                            <li class="list-group-item ps-0">
                                <div class="d-flex align-items-start">
                                    <div class="form-check ps-0 flex-shrink-0">
                                        <input type="checkbox" class="form-check-input ms-0" id="task_one">
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-check-label mb-0 ps-2" for="task_one">Review clergy assignment
                                            proposal for St. Mary's</label>
                                    </div>
                                    <div class="flex-shrink-0 ms-2">
                                        <span class="badge bg-danger-subtle text-danger">Urgent</span>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item ps-0">
                                <div class="d-flex align-items-start">
                                    <div class="form-check ps-0 flex-shrink-0">
                                        <input type="checkbox" class="form-check-input ms-0" id="task_two">
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-check-label mb-0 ps-2" for="task_two">Follow up on USCCB annual
                                            report submission</label>
                                    </div>
                                    <div class="flex-shrink-0 ms-2">
                                        <span class="badge bg-warning-subtle text-warning">Due Feb 15</span>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item ps-0">
                                <div class="d-flex align-items-start">
                                    <div class="form-check ps-0 flex-shrink-0">
                                        <input type="checkbox" class="form-check-input ms-0" id="task_three">
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-check-label mb-0 ps-2" for="task_three">Verify sacramental
                                            records for Q1 parish transfers</label>
                                    </div>
                                    <div class="flex-shrink-0 ms-2">
                                        <span class="badge bg-info-subtle text-info">Records</span>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item ps-0">
                                <div class="d-flex align-items-start">
                                    <div class="form-check ps-0 flex-shrink-0">
                                        <input type="checkbox" class="form-check-input ms-0" id="task_four" checked>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-check-label mb-0 ps-2 text-decoration-line-through text-muted"
                                            for="task_four">Send meeting invite for Deanery gathering</label>
                                    </div>
                                    <div class="flex-shrink-0 ms-2">
                                        <span class="badge bg-success-subtle text-success">Done</span>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item ps-0">
                                <div class="d-flex align-items-start">
                                    <div class="form-check ps-0 flex-shrink-0">
                                        <input type="checkbox" class="form-check-input ms-0" id="task_five" checked>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-check-label mb-0 ps-2 text-decoration-line-through text-muted"
                                            for="task_five">Update Holy See dicastery contacts</label>
                                    </div>
                                    <div class="flex-shrink-0 ms-2">
                                        <span class="badge bg-success-subtle text-success">Done</span>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item ps-0">
                                <div class="d-flex align-items-start">
                                    <div class="form-check ps-0 flex-shrink-0">
                                        <input type="checkbox" class="form-check-input ms-0" id="task_six" checked>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-check-label mb-0 ps-2 text-decoration-line-through text-muted"
                                            for="task_six">Prepare Vicar General quarterly briefing</label>
                                    </div>
                                    <div class="flex-shrink-0 ms-2">
                                        <span class="badge bg-success-subtle text-success">Done</span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
    ROW 5 — Upcoming Events + Recent Communications
    ══════════════════════════════════════════════════════════════ --}}
    <div class="row">
        <div class="col-xxl-5">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Upcoming Events</h4>
                    <div class="flex-shrink-0">
                        <a href="{{ url('/admin/calendar') }}" class="btn btn-soft-primary btn-sm">
                            Full Calendar <i class="ri-arrow-right-line ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <ul class="list-group list-group-flush border-dashed">
                        @forelse($upcomingEvents as $event)
                            @php
                                $catColors = [
                                    'sacramental' => 'primary',
                                    'formation' => 'info',
                                    'parish' => 'success',
                                    'diocesan' => 'primary',
                                    'staff' => 'warning',
                                ];
                                $bsColor = $catColors[$event->category] ?? 'primary';
                                $timeStr = $event->all_day
                                    ? 'All Day'
                                    : $event->start_at->format('g:ia');
                                $locStr = $event->location ? ' — ' . $event->location : '';
                            @endphp
                            <li class="list-group-item ps-0">
                                <div class="row align-items-center g-3">
                                    <div class="col-auto">
                                        <div class="avatar-sm p-1 py-2 h-auto bg-{{ $bsColor }}-subtle rounded-3">
                                            <div class="text-center">
                                                <h5 class="mb-0 text-{{ $bsColor }}">{{ $event->start_at->format('d') }}</h5>
                                                <div class="text-{{ $bsColor }}">{{ $event->start_at->format('M') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h5 class="text-muted mt-0 mb-1 fs-14">{{ $timeStr }}{{ $locStr }}</h5>
                                        <a href="{{ url('/calendar') }}" class="text-reset fs-15 mb-0">{{ $event->title }}</a>
                                        <span class="badge bg-{{ $bsColor }}-subtle text-{{ $bsColor }} ms-1">{{ ucfirst($event->category) }}</span>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item ps-0 text-muted fst-italic">
                                No upcoming events — <a href="{{ url('/calendar') }}">create one</a>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-xxl-7">
            <div class="card card-height-100">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Recent Communications</h4>
                    <div class="flex-shrink-0">
                        <a href="{{ url('/admin/communications') }}" class="btn btn-soft-primary btn-sm">
                            View All <i class="ri-arrow-right-line ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-nowrap align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 30%;">Subject</th>
                                    <th scope="col" style="width: 20%;">From</th>
                                    <th scope="col" style="width: 20%;">To</th>
                                    <th scope="col" style="width: 15%;">Date</th>
                                    <th scope="col" style="width: 15%;">Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="#" class="text-body fw-semibold">Re: Clergy Transfer — Fr. Martinez</a>
                                    </td>
                                    <td>Bishop Thompson</td>
                                    <td>Vicar General</td>
                                    <td>Feb 6, 2026</td>
                                    <td><span class="badge bg-primary-subtle text-primary">Internal</span></td>
                                </tr>
                                <tr>
                                    <td><a href="#" class="text-body fw-semibold">Annual Report Draft Submission</a></td>
                                    <td>Chancellor's Office</td>
                                    <td>USCCB</td>
                                    <td>Feb 4, 2026</td>
                                    <td><span class="badge bg-info-subtle text-info">External</span></td>
                                </tr>
                                <tr>
                                    <td><a href="#" class="text-body fw-semibold">Parish Boundaries — Proposed Change</a>
                                    </td>
                                    <td>Planning Committee</td>
                                    <td>All Deans</td>
                                    <td>Feb 1, 2026</td>
                                    <td><span class="badge bg-warning-subtle text-warning">Circular</span></td>
                                </tr>
                                <tr>
                                    <td><a href="#" class="text-body fw-semibold">Confirmation Schedule — Spring 2026</a>
                                    </td>
                                    <td>Office of Worship</td>
                                    <td>All Parishes</td>
                                    <td>Jan 28, 2026</td>
                                    <td><span class="badge bg-success-subtle text-success">Sacramental</span></td>
                                </tr>
                                <tr>
                                    <td><a href="#" class="text-body fw-semibold">Dicastery Response — Dispensation
                                            Request</a></td>
                                    <td>Holy See</td>
                                    <td>Tribunal</td>
                                    <td>Jan 20, 2026</td>
                                    <td><span class="badge bg-danger-subtle text-danger">Canonical</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
    ROW 6 — Calendar Widget with Liturgical Overlay
    ══════════════════════════════════════════════════════════════ --}}
    <div class="row">
        <div class="col-xl-12">
            @include('velzon.partials._calendar-widget', ['todayEvent' => $todayEvent ?? null])
        </div>
    </div>
@endsection
@section('css')
    <style>
        /* FullCalendar overrides for Velzon theme */
        #calendar-widget .fc {
            font-family: inherit;
        }

        #calendar-widget .fc-toolbar-title {
            font-size: 1.1rem;
            font-weight: 600;
        }

        #calendar-widget .fc-button {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }

        #calendar-widget .fc-event {
            border: none;
            border-radius: 3px;
            font-size: 0.75rem;
            padding: 1px 4px;
            cursor: pointer;
        }

        #calendar-widget .fc-daygrid-event {
            margin-bottom: 1px;
        }

        /* Color band on today's liturgy card */
        #today-liturgy-card .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, .05);
        }

        /* Quick Actions hover effects */
        .card-body .btn-soft-primary:hover,
        .card-body .btn-soft-info:hover,
        .card-body .btn-soft-success:hover,
        .card-body .btn-soft-warning:hover,
        .card-body .btn-soft-secondary:hover {
            transform: translateX(3px);
            transition: transform 0.15s ease;
        }
    </style>
@endsection
@section('script')
    <!-- apexcharts -->
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <!-- FullCalendar -->
    <script src="{{ URL::asset('build/libs/fullcalendar/index.global.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/calendar-widget.init.js') }}"></script>

    {{-- DRM Dashboard Charts --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // ── Constituent Growth (Area Chart) ──
            var constituentEl = document.querySelector("#constituent-growth-chart");
            if (constituentEl) {
                var colors = constituentEl.dataset.colors ? JSON.parse(constituentEl.dataset.colors) : [];
                var resolvedColors = colors.map(function (c) {
                    var cssVar = getComputedStyle(document.documentElement).getPropertyValue(c.trim());
                    return cssVar ? cssVar.trim() : c;
                });

                new ApexCharts(constituentEl, {
                    series: [{
                        name: 'Contacts Added',
                        data: [12, 18, 24, 15, 28, 35, 22, 30, 42, 38, 45, 52]
                    }],
                    chart: { type: 'area', height: 290, toolbar: { show: false } },
                    colors: resolvedColors,
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 2 },
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
                    xaxis: { categories: ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb'] },
                    tooltip: { y: { formatter: function (val) { return val + ' contacts'; } } }
                }).render();
            }

            // ── Entities by Type (Donut Chart) ──
            var entitiesEl = document.querySelector("#entities-by-type-chart");
            if (entitiesEl) {
                var colors2 = entitiesEl.dataset.colors ? JSON.parse(entitiesEl.dataset.colors) : [];
                var resolvedColors2 = colors2.map(function (c) {
                    var cssVar = getComputedStyle(document.documentElement).getPropertyValue(c.trim());
                    return cssVar ? cssVar.trim() : c;
                });

                new ApexCharts(entitiesEl, {
                    series: [42, 18, 12, 8, 5],
                    chart: { type: 'donut', height: 290 },
                    labels: ['Parishes', 'Schools', 'Charities', 'Religious Orders', 'Hospitals'],
                    colors: resolvedColors2,
                    legend: { position: 'bottom' },
                    plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Total Entities' } } } } }
                }).render();
            }

            // ── Clergy Status (Pie Chart) ──
            var clergyEl = document.querySelector("#clergy-status-chart");
            if (clergyEl) {
                var colors3 = clergyEl.dataset.colors ? JSON.parse(clergyEl.dataset.colors) : [];
                var resolvedColors3 = colors3.map(function (c) {
                    var cssVar = getComputedStyle(document.documentElement).getPropertyValue(c.trim());
                    return cssVar ? cssVar.trim() : c;
                });

                new ApexCharts(clergyEl, {
                    series: [68, 15, 8, 3],
                    chart: { type: 'pie', height: 290 },
                    labels: ['Active', 'Retired', 'On Leave', 'Suspended'],
                    colors: resolvedColors3,
                    legend: { position: 'bottom' },
                    responsive: [{ breakpoint: 480, options: { chart: { width: 200 }, legend: { position: 'bottom' } } }]
                }).render();
            }

            // ── Counter Animation (reuse Velzon's counter-value pattern) ──
            var counters = document.querySelectorAll('.counter-value');
            counters.forEach(function (counter) {
                var target = parseFloat(counter.getAttribute('data-target'));
                var duration = 1500;
                var start = 0;
                var startTime = null;

                function animate(timestamp) {
                    if (!startTime) startTime = timestamp;
                    var progress = Math.min((timestamp - startTime) / duration, 1);
                    counter.textContent = Math.floor(progress * target);
                    if (progress < 1) requestAnimationFrame(animate);
                    else counter.textContent = target;
                }
                requestAnimationFrame(animate);
            });
        });
    </script>
@endsection
