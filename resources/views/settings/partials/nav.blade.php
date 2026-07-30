<div class="card settings-nav-card">
    <div class="card-header py-0">
        <ul class="nav nav-underline settings-tabs gap-3">
            <li class="nav-item">
                <a class="nav-link py-3 px-1 {{ request()->routeIs('settings.divisions*') ? 'active' : '' }}" href="{{ route('settings.divisions.index') }}">Division</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 px-1 {{ request()->routeIs('settings.positions*') ? 'active' : '' }}" href="{{ route('settings.positions.index') }}">Position</a>
            </li>
        </ul>
    </div>
</div>
