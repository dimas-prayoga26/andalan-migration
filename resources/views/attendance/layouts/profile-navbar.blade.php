<!-- Start - Profile Navigation -->
<div class="card-footer py-0 d-flex flex-wrap justify-content-between align-items-center mx-sm-4 px-0">
    <ul class="nav nav-underline gap-3 nav-scroll nav-scroll-auto-xl px-3 px-sm-0 attendance-tabs" id="tabMyProfileBottom" role="tablist">
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link py-3 px-1 border-3 attendance-tab-btn" aria-selected="false">Overview</button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" data-href="{{ route('attendance') }}" class="nav-link py-3 px-1 border-3 attendance-tab-btn {{ request()->routeIs('attendance') ? 'active' : '' }}" aria-selected="{{ request()->routeIs('attendance') ? 'true' : 'false' }}">Attendance</button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" data-href="{{ route('attendance.reports') }}" class="nav-link py-3 px-1 border-3 attendance-tab-btn {{ request()->routeIs('attendance.reports') ? 'active' : '' }}" aria-selected="{{ request()->routeIs('attendance.reports') ? 'true' : 'false' }}">Report</button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" data-href="{{ route('attendance.leave-requests') }}" class="nav-link py-3 px-1 border-3 attendance-tab-btn {{ request()->routeIs('attendance.leave-requests*') ? 'active' : '' }}" aria-selected="{{ request()->routeIs('attendance.leave-requests*') ? 'true' : 'false' }}">Leaves & Sick</button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" data-href="{{ route('attendance.business-trips') }}" class="nav-link py-3 px-1 border-3 attendance-tab-btn {{ request()->routeIs('attendance.business-trips') ? 'active' : '' }}" aria-selected="{{ request()->routeIs('attendance.business-trips') ? 'true' : 'false' }}">Business Trip</button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" data-href="{{ route('attendance.overtimes') }}" class="nav-link py-3 px-1 border-3 attendance-tab-btn {{ request()->routeIs('attendance.overtimes') ? 'active' : '' }}" aria-selected="{{ request()->routeIs('attendance.overtimes') ? 'true' : 'false' }}">Overtime</button>
        </li>
    </ul>
</div>
<!-- End - Profile Navigation -->
