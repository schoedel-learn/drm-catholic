<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo (for light sidebar) -->
        <a href="{{ url('/dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('images/cw-icon-purple.svg') }}" alt="Catholic.Work" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('images/cw-icon-purple.svg') }}" alt="Catholic.Work" height="30">
                <span class="ms-2 fw-bold fs-16 text-dark">Catholic.Work</span>
            </span>
        </a>
        <!-- Light Logo (for dark sidebar) -->
        <a href="{{ url('/dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ URL::asset('images/cw-icon-white.svg') }}" alt="Catholic.Work" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('images/cw-icon-white.svg') }}" alt="Catholic.Work" height="30">
                <span class="ms-2 fw-bold fs-16 text-white">Catholic.Work</span>
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">

                {{-- ═══════════════════════════════════════
                OVERVIEW
                ═══════════════════════════════════════ --}}
                <li class="menu-title"><span>Overview</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        <i class="ri-dashboard-2-line"></i> <span>Chancery Dashboard</span>
                    </a>
                </li>

                {{-- ═══════════════════════════════════════
                PEOPLE & ORGANIZATIONS
                ═══════════════════════════════════════ --}}
                <li class="menu-title"><span>People & Organizations</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ url('/admin/contacts') }}">
                        <i class="ri-contacts-line"></i> <span>Contacts</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarOrganizations" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarOrganizations">
                        <i class="ri-building-2-line"></i> <span>Organizations</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarOrganizations">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ url('/admin/parishes') }}" class="nav-link">
                                    <i class="ri-community-line text-muted me-1"></i> Parishes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('/admin/schools') }}" class="nav-link">
                                    <i class="ri-school-line text-muted me-1"></i> Schools
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('/admin/charities') }}" class="nav-link">
                                    <i class="ri-hand-heart-line text-muted me-1"></i> Charities & Apostolates
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('/admin/religious-orders') }}" class="nav-link">
                                    <i class="ri-team-line text-muted me-1"></i> Religious Orders
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('/admin/organizations') }}" class="nav-link">
                                    <i class="ri-list-check text-muted me-1"></i> All Organizations
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarClergy" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarClergy">
                        <i class="ri-user-star-line"></i> <span>Clergy</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarClergy">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ url('/admin/clergy') }}" class="nav-link">Directory</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('/admin/clergy/assignments') }}" class="nav-link">Assignments</a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- ═══════════════════════════════════════
                CALENDAR & EVENTS
                ═══════════════════════════════════════ --}}
                <li class="menu-title"><span>Calendar & Events</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ url('/admin/calendar') }}">
                        <i class="ri-calendar-2-line"></i> <span>Calendar</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ url('/admin/liturgical') }}">
                        <i class="ri-book-open-line"></i> <span>Liturgical Calendar</span>
                    </a>
                </li>

                {{-- ═══════════════════════════════════════
                COMMUNICATIONS & FORMS
                ═══════════════════════════════════════ --}}
                <li class="menu-title"><span>Communications</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ url('/admin/communications') }}">
                        <i class="ri-mail-line"></i> <span>Communications Log</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ url('/admin/forms') }}">
                        <i class="ri-survey-line"></i> <span>Forms</span>
                    </a>
                </li>

                {{-- ═══════════════════════════════════════
                EXTERNAL BODIES
                ═══════════════════════════════════════ --}}
                <li class="menu-title"><span>External Bodies</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarExternalBodies" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarExternalBodies">
                        <i class="ri-global-line"></i> <span>Institutional Contacts</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarExternalBodies">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ url('/admin/institutions/usccb') }}" class="nav-link">
                                    <i class="ri-government-line text-muted me-1"></i> USCCB
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('/admin/institutions/dioceses') }}" class="nav-link">
                                    <i class="ri-map-pin-line text-muted me-1"></i> Other Dioceses
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('/admin/institutions/vatican') }}" class="nav-link">
                                    <i class="ri-vip-crown-line text-muted me-1"></i> Holy See / Dicasteries
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- ═══════════════════════════════════════
                ADMINISTRATION
                ═══════════════════════════════════════ --}}
                <li class="menu-title"><span>Administration</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarAdmin" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarAdmin">
                        <i class="ri-settings-3-line"></i> <span>Settings</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarAdmin">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ url('/admin/entity-types') }}" class="nav-link">Entity Types</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('/admin/users') }}" class="nav-link">Users & Roles</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('/admin/diocese-settings') }}" class="nav-link">Diocese Settings</a>
                            </li>
                        </ul>
                    </div>
                </li>

            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
