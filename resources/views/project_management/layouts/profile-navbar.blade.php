<!-- Start - Profile Navigation -->
<div class="card-footer py-0 d-flex flex-wrap justify-content-between align-items-center mx-sm-4 px-0">
    <ul class="nav nav-underline gap-3 nav-scroll nav-scroll-auto-xl px-3 px-sm-0 attendance-tabs" id="tabMyProfileBottom" role="tablist">
        <li class="nav-item" role="presentation">
            <a href="{{ route('project_management') }}" class="nav-link py-3 px-1 border-3 {{ request()->routeIs('project_management') ? 'active' : '' }}" aria-selected="{{ request()->routeIs('project_management') ? 'true' : 'false' }}">Overview</a>
        </li>
        <li class="nav-item" role="presentation">
            <a href="#task-list" class="nav-link py-3 px-1 border-3" aria-selected="false">Task List</a>
        </li>
        <li class="nav-item" role="presentation">
            <a href="#project" class="nav-link py-3 px-1 border-3" aria-selected="false">Project</a>
        </li>
    </ul>
</div>
<!-- End - Profile Navigation -->
