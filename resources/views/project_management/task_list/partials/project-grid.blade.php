@php
    $taskListItems = collect($taskListItems ?? []);
    $taskListProjectOptions = collect($taskListProjectOptions ?? []);
@endphp

<div class="row">
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card project-grid-file-card">
            <div class="card-body d-flex align-items-center gap-3">
                <img src="{{ asset('assets/images/files/folder.avif') }}" alt="" width="52">
                <div>
                    <h5 class="mb-1">Daily Tasks</h5>
                    <span class="fs-14">{{ $taskListItems->where('task_category', 'daily')->count() }} task this month</span>
                </div>
            </div>
        </div>
    </div>
    @forelse ($taskListProjectOptions as $projectOption)
        <div class="col-xl-3 col-lg-4 col-md-6">
            <a href="{{ route('project_management.projects.detail', $projectOption['id']) }}" class="text-reset text-decoration-none d-block h-100">
                <div class="card project-grid-file-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <img src="{{ asset('assets/images/files/folder.avif') }}" alt="" width="52">
                        <div>
                            <h5 class="mb-1">{{ $projectOption['name'] }}</h5>
                            <span class="fs-14">{{ $taskListItems->where('project_id', $projectOption['id'])->count() }} task this month</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="mb-1">No active project folder</h5>
                    <p class="mb-0 fs-14">Project folders appear after your employee is added as an active project member.</p>
                </div>
            </div>
        </div>
    @endforelse
</div>
