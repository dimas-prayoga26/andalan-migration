<?php

namespace Tests\Feature;

use App\Http\Controllers\ProjectManagement\ProjectController;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\EmployeePicAssignment;
use App\Models\EventDivision;
use App\Models\GoogleOauthToken;
use App\Models\Position;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProjectManagementOverviewLayoutTest extends TestCase
{
    public function test_monthly_overview_cards_use_equal_height_layout(): void
    {
        $overview = File::get(resource_path('views/project_management/overview/index.blade.php'));
        $profileIndex = File::get(resource_path('views/project_management/layouts/profile-index.blade.php'));
        $profileHeader = File::get(resource_path('views/project_management/layouts/profile-header.blade.php'));
        $summaryCards = File::get(resource_path('views/project_management/overview/partials/summary-cards.blade.php'));
        $profileComposer = File::get(app_path('View/Composers/ProjectManagementProfileComposer.php'));
        $overviewController = File::get(app_path('Http/Controllers/ProjectManagement/OverviewController.php'));
        $taskListController = File::get(app_path('Http/Controllers/ProjectManagement/TaskListController.php'));
        $projectController = File::get(app_path('Http/Controllers/ProjectManagement/ProjectController.php'));
        $googleDriveOAuthController = File::get(app_path('Http/Controllers/GoogleDriveOAuthController.php'));
        $appServiceProvider = File::get(app_path('Providers/AppServiceProvider.php'));
        $servicesConfig = File::get(config_path('services.php'));
        $routes = File::get(base_path('routes/web.php'));
        $commonJs = File::get(resource_path('views/layouts/commonjs.blade.php'));
        $profileNavbar = File::get(resource_path('views/project_management/layouts/profile-navbar.blade.php'));
        $projectsIndex = File::get(resource_path('views/project_management/projects/index.blade.php'));
        $projectsDetail = File::get(resource_path('views/project_management/projects/detail.blade.php'));
        $taskList = File::get(resource_path('views/project_management/task_list/index.blade.php'));
        $taskListItemsPartial = File::get(resource_path('views/project_management/task_list/partials/task-list-items.blade.php'));
        $taskListWeekPlanPartial = File::get(resource_path('views/project_management/task_list/partials/week-plan.blade.php'));
        $taskListProjectGridPartial = File::get(resource_path('views/project_management/task_list/partials/project-grid.blade.php'));
        $projectModel = File::get(app_path('Models/Project.php'));
        $projectDivisionEventModel = File::get(app_path('Models/ProjectDivisionEvent.php'));
        $userModel = File::get(app_path('Models/User.php'));
        $googleOauthTokenModel = File::get(app_path('Models/GoogleOauthToken.php'));
        $projectDivisionEventMigration = File::get(database_path('migrations/2026_08_20_090001_replace_project_departments_with_project_division_event_table.php'));
        $projectDivisionEventFolderIdMigration = collect(File::glob(database_path('migrations/*_add_folder_id_to_project_division_event_table.php')))
            ->map(fn (string $path): string => File::get($path))
            ->implode("\n");
        $googleOauthTokenMigration = collect(File::glob(database_path('migrations/*_create_google_oauth_tokens_table.php')))
            ->map(fn (string $path): string => File::get($path))
            ->implode("\n");
        $liveEventDatesMigration = File::get(database_path('migrations/2026_06_28_234546_add_live_event_dates_to_projects_table.php'));
        $projectImagePathMigration = collect(File::glob(database_path('migrations/*_add_image_path_to_projects_table.php')))
            ->map(fn (string $path): string => File::get($path))
            ->implode("\n");
        $projectLocationMigration = collect(File::glob(database_path('migrations/*_add_location_fields_to_projects_table.php')))
            ->map(fn (string $path): string => File::get($path))
            ->implode("\n");
        $indonesiaProvinceMigration = collect(File::glob(database_path('migrations/*_create_indonesia_provinces_table.php')))
            ->map(fn (string $path): string => File::get($path))
            ->implode("\n");
        $indonesiaCityMigration = collect(File::glob(database_path('migrations/*_create_indonesia_cities_table.php')))
            ->map(fn (string $path): string => File::get($path))
            ->implode("\n");
        $indonesiaDistrictMigration = collect(File::glob(database_path('migrations/*_create_indonesia_districts_table.php')))
            ->map(fn (string $path): string => File::get($path))
            ->implode("\n");
        $indonesiaVillageMigration = collect(File::glob(database_path('migrations/*_create_indonesia_villages_table.php')))
            ->map(fn (string $path): string => File::get($path))
            ->implode("\n");
        $taskListSurface = $taskList.$taskListItemsPartial.$taskListWeekPlanPartial.$taskListProjectGridPartial;

        $this->assertTrue(View::exists('project_management.overview.index'));
        $this->assertTrue(View::exists('project_management.overview.partials.summary-cards'));
        $this->assertTrue(View::exists('project_management.task_list.index'));
        $this->assertTrue(View::exists('project_management.projects.index'));
        $this->assertTrue(View::exists('project_management.projects.detail'));
        $this->assertStringContainsString("asset('assets/default_user.jpg')", $profileHeader);
        $this->assertStringContainsString('overflow-wrap: anywhere;', $profileHeader);
        $this->assertStringContainsString('profile-contact-email', $profileHeader);
        $this->assertStringContainsString('word-break: break-word;', $profileHeader);
        $this->assertStringContainsString('class="project-progress-radial-chart"', $overview);
        $this->assertStringContainsString('parentHeightOffset: 0', $overview);
        $this->assertStringContainsString('employee.profile:id,employee_id,name,profile_picture_path', $profileComposer);
        $this->assertStringNotContainsString('userProfile', $profileComposer);
        $this->assertIsString(view('project_management.overview.index')->render());
        $this->assertStringContainsString('ProjectManagementOverviewController::class', $routes);
        $this->assertStringContainsString('ProjectManagementTaskListController::class', $routes);
        $this->assertStringContainsString('ProjectManagementProjectController::class', $routes);
        $this->assertStringContainsString("Route::get('/project-management/overview', ProjectManagementOverviewController::class)->name('project_management');", $routes);
        $this->assertStringNotContainsString("Route::get('/project-management', ProjectManagementOverviewController::class)->name('project_management');", $routes);
        $this->assertStringContainsString("Route::get('/project-management/task-list', [ProjectManagementTaskListController::class, 'index'])->name('project_management.task_list');", $routes);
        $this->assertStringContainsString("Route::get('/project-management/task-list/filter', [ProjectManagementTaskListController::class, 'filter'])->name('project_management.task_list.filter');", $routes);
        $this->assertStringContainsString("Route::post('/project-management/task-list/tasks', [ProjectManagementTaskListController::class, 'storeTask'])->name('project_management.task_list.tasks.store');", $routes);
        $this->assertStringContainsString("Route::put('/project-management/task-list/tasks/{projectTask}', [ProjectManagementTaskListController::class, 'updateTask'])->name('project_management.task_list.tasks.update');", $routes);
        $this->assertStringContainsString("Route::patch('/project-management/task-list/tasks/{projectTask}/status', [ProjectManagementTaskListController::class, 'updateTaskStatus'])->name('project_management.task_list.tasks.status.update');", $routes);
        $this->assertStringContainsString("Route::patch('/project-management/task-list/tasks/{projectTask}/complete', [ProjectManagementTaskListController::class, 'completeTask'])->name('project_management.task_list.tasks.complete');", $routes);
        $this->assertStringContainsString("Route::delete('/project-management/task-list/tasks/{projectTask}', [ProjectManagementTaskListController::class, 'destroyTask'])->name('project_management.task_list.tasks.destroy');", $routes);
        $this->assertStringContainsString("Route::get('/project-management/projects', [ProjectManagementProjectController::class, 'index'])->name('project_management.projects');", $routes);
        $this->assertStringContainsString("Route::post('/project-management/projects', [ProjectManagementProjectController::class, 'storeProject'])->name('project_management.projects.store');", $routes);
        $this->assertStringContainsString("Route::put('/project-management/projects/{project}', [ProjectManagementProjectController::class, 'updateProject'])->name('project_management.projects.update');", $routes);
        $this->assertStringContainsString("Route::delete('/project-management/projects/{project}', [ProjectManagementProjectController::class, 'destroyProject'])->name('project_management.projects.destroy');", $routes);
        $this->assertStringContainsString("Route::get('/project-management/projects/detail', [ProjectManagementProjectController::class, 'detailFallback'])->name('project_management.projects.detail.fallback');", $routes);
        $this->assertStringContainsString("Route::get('/project-management/projects/{project}', [ProjectManagementProjectController::class, 'detail'])->name('project_management.projects.detail');", $routes);
        $this->assertStringContainsString("Route::patch('/project-management/projects/{project}/event-divisions/{eventDivision}/google-drive', [ProjectManagementProjectController::class, 'updateEventDivisionGoogleDrive'])->name('project_management.projects.event-divisions.google-drive.update');", $routes);
        $this->assertStringContainsString("Route::get('/google-drive/oauth/access-token', [GoogleDriveOAuthController::class, 'accessToken'])->name('google-drive.oauth.access-token');", $routes);
        $this->assertStringContainsString("Route::post('/google-drive/oauth/exchange-code', [GoogleDriveOAuthController::class, 'exchangeCode'])->name('google-drive.oauth.exchange-code');", $routes);
        $this->assertStringContainsString("Route::post('/project-management/projects/{project}/tasks', [ProjectManagementProjectController::class, 'storeTask'])->name('project_management.projects.tasks.store');", $routes);
        $this->assertStringContainsString("Route::put('/project-management/projects/{project}/tasks/{projectTask}', [ProjectManagementProjectController::class, 'updateTask'])->name('project_management.projects.tasks.update');", $routes);
        $this->assertStringContainsString("Route::patch('/project-management/projects/{project}/tasks/{projectTask}/toggle', [ProjectManagementProjectController::class, 'toggleTask'])->name('project_management.projects.tasks.toggle');", $routes);
        $this->assertStringContainsString("Route::delete('/project-management/projects/{project}/tasks/{projectTask}', [ProjectManagementProjectController::class, 'destroyTask'])->name('project_management.projects.tasks.destroy');", $routes);
        $this->assertStringContainsString("Route::get('/project-management/detail', [ProjectManagementProjectController::class, 'detailFallback'])->name('project_management.detail');", $routes);
        $this->assertStringContainsString("Schema::create('project_division_event'", $projectDivisionEventMigration);
        $this->assertStringContainsString("\$table->foreignUuid('project_id')->constrained('projects', 'id')->cascadeOnDelete();", $projectDivisionEventMigration);
        $this->assertStringContainsString("\$table->foreignUuid('event_division_id')->constrained('event_divisions', 'id')->cascadeOnDelete();", $projectDivisionEventMigration);
        $this->assertStringContainsString("\$table->string('google_drive_url', 2048)->nullable();", $projectDivisionEventMigration);
        $this->assertStringContainsString("\$table->string('folder_id')->nullable()->after('google_drive_url')->index();", $projectDivisionEventFolderIdMigration);
        $this->assertStringContainsString("Schema::create('google_oauth_tokens'", $googleOauthTokenMigration);
        $this->assertStringContainsString("\$table->foreignUuid('user_id')->constrained('users', 'id')->cascadeOnDelete();", $googleOauthTokenMigration);
        $this->assertStringContainsString('google_oauth_tokens_user_provider_unique', $googleOauthTokenMigration);
        $this->assertStringContainsString('public function googleOauthTokens(): HasMany', $userModel);
        $this->assertStringContainsString("'access_token' => 'encrypted'", $googleOauthTokenModel);
        $this->assertStringContainsString("'refresh_token' => 'encrypted'", $googleOauthTokenModel);
        $this->assertStringContainsString('project_division_event_project_event_division_unique', $projectDivisionEventMigration);
        $this->assertStringContainsString('public function projectDivisionEvents(): HasMany', $projectModel);
        $this->assertStringContainsString('class ProjectDivisionEvent extends Model', $projectDivisionEventModel);
        $this->assertStringContainsString('return $this->belongsTo(Project::class', $projectDivisionEventModel);
        $this->assertStringContainsString('return $this->belongsTo(EventDivision::class', $projectDivisionEventModel);
        $this->assertStringContainsString("'projectDivisionEvents:id,project_id,event_division_id,google_drive_url,folder_id,status'", $projectController);
        $this->assertStringContainsString("->map(fn (ProjectDivisionEvent \$projectDivisionEvent): string => trim((string) (\$projectDivisionEvent->google_drive_url ?? '')));", $projectController);
        $this->assertStringContainsString("'folder_id' => ['nullable', 'string', 'max:255']", $projectController);
        $this->assertStringContainsString('https://oauth2.googleapis.com/token', $googleDriveOAuthController);
        $this->assertStringContainsString('updateOrCreate(', $googleDriveOAuthController);
        $this->assertStringContainsString("'google' => [", $servicesConfig);
        $this->assertStringContainsString("'api_key' => env('GOOGLE_API_KEY')", $servicesConfig);
        $this->assertStringContainsString("'client_id' => env('GOOGLE_CLIENT_ID')", $servicesConfig);
        $this->assertStringContainsString("'client_secret' => env('GOOGLE_CLIENT_SECRET')", $servicesConfig);
        $this->assertStringContainsString("href=\"{{ \$divisionGroup['google_drive_url'] }}\"", $projectsDetail);
        $this->assertStringContainsString('target="_blank"', $projectsDetail);
        $this->assertStringNotContainsString('public function taskList(Request $request): View', $overviewController);
        $this->assertStringNotContainsString('public function storeTask(Request $request): JsonResponse', $overviewController);
        $this->assertStringContainsString('public function index(Request $request): View', $taskListController);
        $this->assertStringContainsString('public function filter(Request $request): JsonResponse', $taskListController);
        $this->assertStringContainsString('$this->taskListData($request)', $taskListController);
        $this->assertStringContainsString('private function taskListFragments(array $taskListData): array', $taskListController);
        $this->assertStringContainsString("->orderByRaw('COALESCE(due_date, start_date, created_at) DESC')", $taskListController);
        $this->assertStringContainsString("->orderByDesc('created_at')", $taskListController);
        $this->assertStringContainsString('public function storeTask(Request $request): JsonResponse', $taskListController);
        $this->assertStringContainsString('public function updateTask(Request $request, ProjectTask $projectTask): JsonResponse', $taskListController);
        $this->assertStringContainsString('public function updateTaskStatus(Request $request, ProjectTask $projectTask): JsonResponse', $taskListController);
        $this->assertStringContainsString('public function completeTask(ProjectTask $projectTask): JsonResponse', $taskListController);
        $this->assertStringContainsString('public function destroyTask(ProjectTask $projectTask): JsonResponse', $taskListController);
        $this->assertStringContainsString("@include('project_management.layouts.profile-index')", $taskList);
        $this->assertStringContainsString("'current' => 'Task List'", $taskList);
        $this->assertStringContainsString("route('project_management.task_list')", $profileNavbar);
        $this->assertStringContainsString("request()->routeIs('project_management.task_list')", $profileNavbar);
        $this->assertStringContainsString("route('project_management.projects')", $profileNavbar);
        $this->assertStringContainsString("request()->routeIs('project_management.projects', 'project_management.projects.detail', 'project_management.detail')", $profileNavbar);
        $this->assertStringContainsString('project-kanban-page', $taskListProjectGridPartial);
        $this->assertStringContainsString('kanban-bx', $taskListProjectGridPartial);
        $this->assertStringContainsString('draggable-zone dropzoneContainer', $taskListProjectGridPartial);
        $this->assertStringContainsString('kanbanPreview-bx', $taskListProjectGridPartial);
        $this->assertStringContainsString('sub-card align-items-center d-flex justify-content-between mb-4', $taskListProjectGridPartial);
        $this->assertStringNotContainsString("<div class=\"draggable-zone dropzoneContainer\">\n                    <div class=\"sub-card", $taskListProjectGridPartial);
        $this->assertStringContainsString("{{ \$kanbanGroup['label'] }}", $taskListProjectGridPartial);
        $this->assertStringContainsString("'label' => 'To-Do List'", $taskListController);
        $this->assertStringContainsString("'label' => 'In Progress'", $taskListController);
        $this->assertStringContainsString("'label' => 'Done'", $taskListController);
        $this->assertStringNotContainsString('Review', $taskListProjectGridPartial);
        $this->assertStringNotContainsString('Backlog', $taskListProjectGridPartial);
        $this->assertStringContainsString('$taskListKanbanGroups = collect($taskListKanbanGroups ?? []);', $taskListProjectGridPartial);
        $this->assertStringContainsString("{{ \$task['title'] ?? '-' }}", $taskListProjectGridPartial);
        $this->assertStringContainsString("'status' => 'pending'", $taskListController);
        $this->assertStringContainsString("'status' => 'in_progress'", $taskListController);
        $this->assertStringContainsString("'status' => 'completed'", $taskListController);
        $this->assertStringContainsString("'dot_class' => 'text-secondary'", $taskListController);
        $this->assertStringContainsString("'dot_class' => 'text-warning'", $taskListController);
        $this->assertStringContainsString("'dot_class' => 'text-success'", $taskListController);
        $this->assertStringContainsString('View More', $taskListProjectGridPartial);
        $this->assertStringContainsString('Update Task', $taskListProjectGridPartial);
        $this->assertStringContainsString('Delete Task', $taskListProjectGridPartial);
        $this->assertStringContainsString('card project-kanban-card draggable-handle draggable', $taskListProjectGridPartial);
        $this->assertStringContainsString('project-kanban-card-actions', $taskListProjectGridPartial);
        $this->assertStringContainsString('project-kanban-dropdown-toggle', $taskListProjectGridPartial);
        $this->assertStringContainsString('type="button" class="btn btn-link p-0 border-0 project-kanban-dropdown-toggle"', $taskListProjectGridPartial);
        $this->assertStringContainsString('dropdown-menu dropdown-menu-end dropdown-menu-right', $taskListProjectGridPartial);
        $this->assertStringContainsString('data-kanban-status="{{ $kanbanGroup[\'status\'] }}"', $taskListProjectGridPartial);
        $this->assertStringContainsString('data-task-status="{{ $task[\'status\'] ?? \'\' }}"', $taskListProjectGridPartial);
        $this->assertStringContainsString('data-status-update-url="{{ $task[\'status_update_url\'] ?? \'\' }}"', $taskListProjectGridPartial);
        $this->assertStringNotContainsString('project-kanban-drag-area', $taskListProjectGridPartial);
        $this->assertStringNotContainsString("handle: '.project-kanban-drag-area'", $taskList);
        $this->assertStringContainsString('function eventStartedFromKanbanAction(event)', $taskList);
        $this->assertStringContainsString('function preventStaticKanbanActionDrag(sortableInstance)', $taskList);
        $this->assertStringContainsString("sortableInstance.on('drag:start'", $taskList);
        $this->assertStringContainsString('event.cancel();', $taskList);
        $this->assertStringNotContainsString('function bindStaticKanbanDropdownActions()', $taskList);
        $this->assertStringNotContainsString('pointerdown.projectKanbanDropdown mousedown.projectKanbanDropdown touchstart.projectKanbanDropdown', $taskList);
        $this->assertStringNotContainsString('window.bootstrap.Dropdown.getOrCreateInstance(this).toggle();', $taskList);
        $this->assertStringContainsString('function setTaskSelectValue(selector, value)', $taskList);
        $this->assertStringContainsString('function refreshDefaultSelect(selector)', $taskList);
        $this->assertStringContainsString('function refreshTaskFormSelects()', $taskList);
        $this->assertStringContainsString("var currentStatus = $(button).closest('.project-kanban-card').attr('data-task-status') || '';", $taskList);
        $this->assertStringContainsString('task.status = currentStatus;', $taskList);
        $this->assertStringContainsString("selectElement.selectpicker('val', value);", $taskList);
        $this->assertStringContainsString("selectElement.selectpicker('refresh');", $taskList);
        $this->assertStringContainsString('refreshTaskFormSelects();', $taskList);
        $this->assertStringContainsString('function syncMovedKanbanTaskStatuses()', $taskList);
        $this->assertStringContainsString('function attachStaticKanbanStatusSync(sortableInstance)', $taskList);
        $this->assertStringContainsString("type: 'PATCH'", $taskList);
        $this->assertStringContainsString('status: targetStatus', $taskList);
        $this->assertStringNotContainsString('Designer', $taskListProjectGridPartial);
        $this->assertStringNotContainsString("asset('assets-workload/images/contacts/pic11.jpg')", $taskListProjectGridPartial);
        $this->assertStringContainsString("asset('assets-workload/vendor/draggable/draggable.js')", $taskList);
        $this->assertStringContainsString('function initializeStaticKanbanBoard()', $taskList);
        $this->assertStringContainsString('function shouldUseMobileKanbanScroll()', $taskList);
        $this->assertStringContainsString("window.matchMedia('(max-width: 767.98px), (pointer: coarse)').matches", $taskList);
        $this->assertStringContainsString('shouldUseMobileKanbanScroll() || ! dropzones.length || ! draggableCards.length', $taskList);
        $this->assertStringContainsString('-webkit-overflow-scrolling: touch;', $taskList);
        $this->assertStringContainsString('touch-action: pan-x pan-y;', $taskList);
        $this->assertStringContainsString('new window.Sortable.default(dropzones', $taskList);
        $this->assertStringContainsString("draggable: '.draggable-handle'", $taskList);
        $this->assertStringContainsString('appendTo: document.body', $taskList);
        $this->assertStringContainsString('function attachStaticKanbanMirrorPositioning(sortableInstance)', $taskList);
        $this->assertStringContainsString("sortableInstance.on('mirror:created'", $taskList);
        $this->assertStringContainsString("dragMirror.style.position = 'fixed'", $taskList);
        $this->assertStringNotContainsString("asset('assets/images/files/folder.avif')", $taskListSurface);
        $this->assertStringNotContainsString('src="assets/images/files/', $taskListSurface);
        $this->assertStringNotContainsString("route('project_management.projects.detail', \$projectOption['id'])", $taskListProjectGridPartial);
        $this->assertStringContainsString('$projectCard[\'detail_url\']', $projectsIndex);
        $this->assertStringContainsString("route('project_management.projects')", $projectsDetail);
        $this->assertStringNotContainsString('report-project-details.html', $projectsIndex);
        $this->assertStringNotContainsString('report-project-details.html', $projectsDetail);
        $this->assertStringContainsString('vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js', $commonJs);
        $this->assertStringNotContainsString('assets/vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js', $commonJs);
        $this->assertStringContainsString("asset('assets/vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css')", $taskList);
        $this->assertStringContainsString("$('#datetimepicker1').datetimepicker", $taskList);
        $this->assertStringContainsString('inline: true', $taskList);
        $this->assertStringContainsString('project-task-calendar-widget', $taskList);
        $this->assertStringContainsString('.project-task-calendar-widget .datepicker-days .day.today:not(.active)', $taskList);
        $this->assertStringContainsString("var defaultCalendarDate = @json(now('Asia/Jakarta')->format('Y-m-d'));", $taskList);
        $this->assertStringContainsString('calendarSelectionForMonth', $taskList);
        $this->assertStringContainsString('class="feather feather-list"', $taskList);
        $this->assertStringContainsString('class="feather feather-grid"', $taskList);
        $this->assertStringNotContainsString('<i class="fa fa-list"></i>', $taskList);
        $this->assertStringNotContainsString('<i class="fa fa-th-large"></i>', $taskList);
        $this->assertStringContainsString('$taskListItems = collect($taskListItems ?? []);', $taskList);
        $this->assertStringContainsString('$taskListAssignableStaffOptions = collect($taskListAssignableStaffOptions ?? []);', $taskList);
        $this->assertStringContainsString('name="assigned_employee_id"', $taskList);
        $this->assertStringContainsString('taskProjectOptionsByEmployee', $taskList);
        $this->assertStringContainsString('private function validatedTaskEmployeeId(string $employeeId, array $validated): string|false', $taskListController);
        $this->assertStringContainsString('EmployeePicAssignment::query()', $taskListController);
        $this->assertStringContainsString("'is_assigned_by_other_user' =>", $taskListController);
        $this->assertStringContainsString('Assign by : <span class="fw-semibold">{{ $task[\'assigned_by_label\'] }}</span>', $taskListItemsPartial);
        $this->assertStringContainsString('Assign by : <span class="fw-semibold">{{ $task[\'assigned_by_label\'] }}</span>', $taskListWeekPlanPartial);
        $this->assertStringContainsString('$taskListPageSize = 5;', $taskListItemsPartial);
        $this->assertStringContainsString('list-row js-task-list-row {{ $loop->iteration > $taskListPageSize ? \'is-task-list-hidden\' : \'\' }}', $taskListItemsPartial);
        $this->assertStringContainsString('.project-task-list-card .js-task-list-row.is-task-list-hidden', $taskList);
        $this->assertStringContainsString('.project-task-list-footer.dataTables_wrapper', $taskList);
        $this->assertStringContainsString('display: none !important;', $taskList);
        $this->assertStringContainsString("toggleClass('is-task-list-hidden'", $taskList);
        $this->assertStringContainsString('js-task-list-pagination', $taskListItemsPartial);
        $this->assertStringContainsString('project-task-list-footer dataTables_wrapper no-footer', $taskListItemsPartial);
        $this->assertStringContainsString('dataTables_info js-task-list-page-summary', $taskListItemsPartial);
        $this->assertStringContainsString('dataTables_paginate paging_simple_numbers js-task-list-pagination', $taskListItemsPartial);
        $this->assertStringContainsString('Showing 1 to {{ min($taskListPageSize, $taskGroup[\'items\']->count()) }} of {{ $taskGroup[\'items\']->count() }} entries', $taskListItemsPartial);
        $this->assertStringContainsString('paginate_button previous disabled js-task-list-page-button', $taskListItemsPartial);
        $this->assertStringContainsString('paginate_button {{ $page === 1 ? \'current\' : \'\' }} js-task-list-page-button', $taskListItemsPartial);
        $this->assertStringContainsString('paginate_button next js-task-list-page-button', $taskListItemsPartial);
        $this->assertStringContainsString('fa-solid fa-angle-left', $taskListItemsPartial);
        $this->assertStringContainsString('fa-solid fa-angle-right', $taskListItemsPartial);
        $this->assertStringContainsString('data-task-page-action="previous"', $taskListItemsPartial);
        $this->assertStringContainsString('data-task-page-action="next"', $taskListItemsPartial);
        $this->assertStringContainsString('function initializeTaskListPagination()', $taskList);
        $this->assertStringContainsString("pane.find('[data-task-page]').removeAttr('aria-current');", $taskList);
        $this->assertStringContainsString('showTaskListPage(pane, nextPage);', $taskList);
        $this->assertStringContainsString('function currentTaskListTabId()', $taskList);
        $this->assertStringContainsString('function activateTaskListTab(tabId)', $taskList);
        $this->assertStringContainsString('var activeTabId = currentTaskListTabId();', $taskList);
        $this->assertStringContainsString('initializeTaskListPagination();', $taskList);
        $this->assertStringContainsString('activateTaskListTab(activeTabId);', $taskList);
        $this->assertStringContainsString('data-task-form-mode="create"', $taskListSurface);
        $this->assertStringContainsString('id="taskFilterForm"', $taskList);
        $this->assertStringContainsString('id="taskFilterMonth"', $taskList);
        $this->assertStringContainsString('id="taskFilterYear"', $taskList);
        $this->assertStringContainsString('taskFilterUrl', $taskList);
        $this->assertStringContainsString("route('project_management.task_list.filter')", $taskList);
        $this->assertStringContainsString('applyTaskListResponse', $taskList);
        $this->assertStringContainsString('refreshTaskList();', $taskList);
        $this->assertStringContainsString('isSyncingCalendar', $taskList);
        $this->assertStringContainsString('syncFiltersFromCalendar', $taskList);
        $this->assertStringContainsString("$('#datetimepicker1').on('dp.change dp.update'", $taskList);
        $this->assertStringContainsString("$('#taskListItemsPanel').html(response.fragments.task_list || '');", $taskList);
        $this->assertStringContainsString("$('#taskListWeekPlanPanel').html(response.fragments.week_plan || '');", $taskList);
        $this->assertStringContainsString("$('#taskListProjectGridPanel').html(response.fragments.project_grid || '');", $taskList);
        $this->assertStringNotContainsString('window.location.reload()', $taskList);
        $this->assertStringContainsString('id="taskFormModal"', $taskList);
        $this->assertStringContainsString('id="taskCompleteModal"', $taskList);
        $this->assertStringContainsString('id="taskDeleteModal"', $taskList);
        $this->assertStringContainsString('id="taskDetailsModal"', $taskList);
        $this->assertStringContainsString('$.ajax({', $taskList);
        $this->assertStringContainsString("type: 'POST',", $taskList);
        $this->assertStringContainsString("type: 'POST',", $projectsDetail);
        $this->assertStringNotContainsString("type: formData.get('_method') || 'POST',", $taskList);
        $this->assertStringNotContainsString("type: formData.get('_method') || 'POST',", $projectsDetail);
        $this->assertStringContainsString('text: response.message', $taskList);
        $this->assertStringContainsString("'X-Requested-With': 'XMLHttpRequest'", $taskList);
        $this->assertStringContainsString('Task berhasil ditambahkan.', $taskListController);
        $this->assertStringContainsString('Task berhasil diperbarui.', $taskListController);
        $this->assertStringContainsString('Task berhasil ditandai selesai.', $taskListController);
        $this->assertStringContainsString('Task berhasil dihapus.', $taskListController);
        $this->assertStringNotContainsString("->whereNull('overtime_id')", $taskListController);
        $this->assertStringContainsString("->whereNotNull('overtime_id')", $taskListController);
        $this->assertStringContainsString("'is_overtime_task' => \$isOvertimeTask", $taskListController);
        $this->assertStringContainsString("'can_manage_from_task_list' => true", $taskListController);
        $this->assertStringContainsString("'can_delete_from_task_list' => ! \$isOvertimeTask", $taskListController);
        $this->assertStringContainsString('badge badge-sm badge-danger light ms-2 align-middle', $taskListItemsPartial);
        $this->assertStringContainsString("{{ \$task['title'] }}</a>\n                            @if (\$task['is_overtime_task'] ?? false)", $taskListItemsPartial);
        $this->assertStringContainsString("{{ \$task['overtime_label'] ?? 'Overtime' }}", $taskListItemsPartial);
        $this->assertStringNotContainsString('badge badge-sm badge-warning light ms-2', $taskListItemsPartial);
        $this->assertStringContainsString("! \$task['is_completed'] && (\$task['can_manage_from_task_list'] ?? true)", $taskListItemsPartial);
        $this->assertStringContainsString("\$task['can_delete_from_task_list'] ?? true", $taskListItemsPartial);
        $this->assertStringContainsString('canManageTaskListTask', $taskListController);
        $this->assertStringContainsString('canDeleteTaskListTask', $taskListController);
        $this->assertStringContainsString('subordinateTaskEmployeeIdsForPic', $taskListController);
        $this->assertStringContainsString("->whereIn('employee_id', \$subordinateEmployeeIds->all())", $taskListController);
        $this->assertStringContainsString("->where('assigned_by', \$authenticatedUserId)", $taskListController);
        $this->assertStringContainsString("'is_assigned_to_other_employee' =>", $taskListController);
        $this->assertStringContainsString('Staff : <span class="fw-semibold">{{ $task[\'assignee_label\'] }}</span>', $taskListItemsPartial);
        $this->assertStringContainsString('Staff : <span class="fw-semibold">{{ $task[\'assignee_label\'] }}</span>', $taskListWeekPlanPartial);
        $this->assertStringContainsString('employeeIsProjectMember', $taskListController);
        $this->assertStringContainsString('public function index(): View', $projectController);
        $this->assertStringContainsString('public function storeProject(Request $request): JsonResponse', $projectController);
        $this->assertStringContainsString('private function validateProjectPayload(Request $request): array', $projectController);
        $this->assertStringContainsString('private function projectCompanyOptions(): Collection', $projectController);
        $this->assertStringContainsString('private function projectStaffOptions(): Collection', $projectController);
        $this->assertStringContainsString('private function employeeProjectOptionLabel(Employee $employee): string', $projectController);
        $this->assertStringContainsString('private function authenticatedEmployeeIsSupervisor(?User $authenticatedUser): bool', $projectController);
        $this->assertStringContainsString("hasPositionName('Supervisor')", $projectController);
        $this->assertStringContainsString("'projectStoreUrl' => route('project_management.projects.store')", $projectController);
        $this->assertStringContainsString('public function updateProject(Request $request, Project $project): JsonResponse', $projectController);
        $this->assertStringContainsString('public function destroyProject(Project $project): JsonResponse', $projectController);
        $this->assertStringContainsString("route('project_management.projects.update', \$project)", $projectController);
        $this->assertStringContainsString("route('project_management.projects.destroy', \$project)", $projectController);
        $this->assertStringContainsString("'staff_employee_ids' => ['required', 'array', 'min:1']", $projectController);
        $this->assertStringContainsString("'code' => \$this->generateProjectCodeFromName((string) \$validated['name'], (string) \$validated['company_id'])", $projectController);
        $this->assertStringContainsString('private function generateProjectCodeFromName(string $projectName, string $companyId, ?string $exceptProjectId = null): string', $projectController);
        $this->assertStringContainsString("'status' => strtolower(trim((string) (\$validated['status'] ?? 'active')))", $projectController);
        $this->assertStringContainsString("'status' => ['nullable', 'in:active,pending,completed,cancelled']", $projectController);
        $this->assertStringContainsString('ProjectMember::query()->create', $projectController);
        $this->assertStringContainsString("'image_path' => \$projectImagePath", $projectController);
        $this->assertStringContainsString('private function storeProjectImageFile(Request $request): ?string', $projectController);
        $this->assertStringContainsString("'project_image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']", $projectController);
        $this->assertStringContainsString("\$storedPath = \$projectImageFile->storeAs('project-images', \$storedFileName, 'public');", $projectController);
        $this->assertStringContainsString("Storage::disk('public')->delete(\$projectImagePath);", $projectController);
        $this->assertStringContainsString('private function projectImageUrl(mixed $imagePath): string', $projectController);
        $this->assertStringContainsString('public function detailFallback(): RedirectResponse', $projectController);
        $this->assertStringContainsString('public function detail(Project $project): View', $projectController);
        $this->assertStringContainsString('public function toggleTask(Request $request, Project $project, ProjectTask $projectTask): JsonResponse', $projectController);
        $this->assertStringContainsString('private function authenticatedUserId(?User $authenticatedUser): ?string', $projectController);
        $this->assertStringContainsString('private function authenticatedEmployeeIsEventProjectAdmin(?User $authenticatedUser): bool', $projectController);
        $this->assertStringContainsString('private function employeeCanViewProject(Project $project, string $employeeId, ?string $userId = null, bool $canManageEventProjects = false): bool', $projectController);
        $this->assertStringContainsString('if ($canManageEventProjects || $this->userIsProjectCreator($project, $userId))', $projectController);
        $this->assertStringContainsString('private function projectsForEmployee(string $employeeId, ?string $userId = null, bool $canManageEventProjects = false): Builder', $projectController);
        $this->assertStringContainsString("->orWhere('created_by', \$userId)", $projectController);
        $this->assertStringContainsString('private function projectDivisionGroups(Project $project, Collection $tasks, ?string $employeeId, bool $canManageProject, bool $canManageGoogleDrive): Collection', $projectController);
        $this->assertStringContainsString("'can_create_task' => (\$canManageGoogleDrive || \$employeeCanCreateOwnTask) && \$visibleTaskAssigneeOptions->isNotEmpty(),", $projectController);
        $this->assertStringContainsString("->where('project_id', \$project->id)", $projectController);
        $this->assertStringContainsString('event_division_id', $projectController);
        $this->assertStringContainsString('live_event_start_date', $projectController);
        $this->assertStringContainsString('live_event_end_date', $projectController);
        $this->assertStringContainsString('Laravolt\Indonesia\Models\Province', $projectController);
        $this->assertStringContainsString('Laravolt\Indonesia\Models\City', $projectController);
        $this->assertStringContainsString('projectProvinceOptions', $projectController);
        $this->assertStringContainsString('projectCityOptions', $projectController);
        $this->assertStringContainsString("'province_code' => ['nullable', 'required_with:city_code', 'string', 'size:2', 'exists:indonesia_provinces,code']", $projectController);
        $this->assertStringContainsString("'city_code' => ['nullable', 'required_with:province_code', 'string', 'size:4', 'exists:indonesia_cities,code']", $projectController);
        $this->assertStringContainsString("'address' => ['nullable', 'string', 'max:2000']", $projectController);
        $this->assertStringContainsString('Kabupaten/kota harus sesuai dengan provinsi yang dipilih.', $projectController);
        $this->assertStringContainsString("'subtitle' => trim((string) (\$project->description ?? \$project->client_name ?? '-'))", $projectController);
        $this->assertStringContainsString('live_event_date_label', $projectController);
        $this->assertStringContainsString('live_event_duration_label', $projectController);
        $this->assertStringContainsString("return \$durationInDays.'-Day Duration';", $projectController);
        $this->assertStringContainsString('projectTaskTimeline', $projectController);
        $this->assertStringContainsString('private function projectTaskTimelineValue(Project $project, Collection $tasks): array', $projectController);
        $this->assertStringContainsString('betweenIncluded($weekStart, $weekEnd)', $projectController);
        $this->assertStringContainsString('memberships.employee.profile:id,employee_id,name,profile_picture_path', $projectController);
        $this->assertStringContainsString('team_members', $projectController);
        $this->assertStringContainsString('private function teamMemberValue(?Employee $employee): array', $projectController);
        $this->assertStringContainsString('private function profilePictureUrl(mixed $profilePicturePath): string', $projectController);
        $this->assertStringContainsString("\$defaultAvatarUrl = asset('assets/default_user.jpg');", $projectController);
        $this->assertStringContainsString('return File::exists(public_path($publicPath)) ? asset($publicPath) : $defaultAvatarUrl;', $projectController);
        $this->assertStringContainsString('private function teamAvatarFallbackLabel(string $displayName): string', $projectController);
        $this->assertStringContainsString("preg_match('/\d+/'", $projectController);
        $this->assertStringContainsString("'live_event_start_date' => 'date'", $projectModel);
        $this->assertStringContainsString("'live_event_end_date' => 'date'", $projectModel);
        $this->assertStringContainsString("\$table->date('live_event_start_date')->nullable()", $liveEventDatesMigration);
        $this->assertStringContainsString("\$table->date('live_event_end_date')->nullable()", $liveEventDatesMigration);
        $this->assertStringContainsString('projects_live_event_dates_index', $liveEventDatesMigration);
        $this->assertStringContainsString("\$table->string('image_path', 2048)->nullable()->after('description');", $projectImagePathMigration);
        $this->assertStringContainsString("\$table->dropColumn('image_path');", $projectImagePathMigration);
        $this->assertStringContainsString("\$table->char('code', 2)->unique()", $indonesiaProvinceMigration);
        $this->assertStringContainsString("\$table->char('code', 4)->unique()", $indonesiaCityMigration);
        $this->assertStringContainsString("\$table->char('province_code', 2)", $indonesiaCityMigration);
        $this->assertStringContainsString("->on('indonesia_provinces')", $indonesiaCityMigration);
        $this->assertStringContainsString("\$table->char('code', 7)->unique()", $indonesiaDistrictMigration);
        $this->assertStringContainsString("\$table->char('city_code', 4)", $indonesiaDistrictMigration);
        $this->assertStringContainsString("->on('indonesia_cities')", $indonesiaDistrictMigration);
        $this->assertStringContainsString("\$table->char('code', 10)->unique()", $indonesiaVillageMigration);
        $this->assertStringContainsString("\$table->char('district_code', 7)", $indonesiaVillageMigration);
        $this->assertStringContainsString("->on('indonesia_districts')", $indonesiaVillageMigration);
        $this->assertStringContainsString("\$table->char('province_code', 2)->nullable()->after('client_name')", $projectLocationMigration);
        $this->assertStringContainsString("\$table->char('city_code', 4)->nullable()->after('province_code')", $projectLocationMigration);
        $this->assertStringContainsString("\$table->text('address')->nullable()->after('city_code')", $projectLocationMigration);
        $this->assertStringContainsString("->on('indonesia_provinces')", $projectLocationMigration);
        $this->assertStringContainsString("->on('indonesia_cities')", $projectLocationMigration);
        $this->assertStringContainsString("route('project_management.projects.tasks.toggle'", $projectController);
        $this->assertStringContainsString('public function storeTask(Request $request, Project $project): JsonResponse', $projectController);
        $this->assertStringContainsString('public function updateTask(Request $request, Project $project, ProjectTask $projectTask): JsonResponse', $projectController);
        $this->assertStringContainsString('public function destroyTask(Project $project, ProjectTask $projectTask): JsonResponse', $projectController);
        $this->assertStringContainsString("route('project_management.projects.tasks.store'", $projectController);
        $this->assertStringContainsString("route('project_management.projects.tasks.update'", $projectController);
        $this->assertStringContainsString("route('project_management.projects.tasks.destroy'", $projectController);
        $this->assertStringContainsString('array_merge($this->profileMetricData($employeeId), [', $projectController);
        $this->assertStringContainsString('private function profileMetricData(?string $employeeId): array', $projectController);
        $this->assertStringContainsString('private function defaultProfileMetricData(CarbonInterface $currentDate): array', $projectController);
        $this->assertStringContainsString('private function projectTaskQueryForEmployee(string $employeeId): Builder', $projectController);
        $this->assertStringContainsString("'team_members' => \$project->memberships", $projectController);
        $this->assertStringContainsString("profileMonthlyAttendanceSeries' => \$this->monthlyCompletedTaskSeries(\$taskQuery, (int) \$currentDate->year)", $projectController);
        $this->assertStringContainsString("'projectTasksCompletedCount' => \$completedTasksCount", $projectController);
        $this->assertStringContainsString("'projectTasksInProgressCount' => \$inProgressTasksCount", $projectController);
        $this->assertStringContainsString("'projectTotalTasksCount' => \$totalTasksCount", $projectController);
        $this->assertStringContainsString("'projectDailyTasksCount' => \$dailyTasksCount", $projectController);
        $this->assertStringContainsString("'projectProjectTasksCount' => \$projectTasksCount", $projectController);
        $this->assertStringContainsString("'projectWorkloadPercent' => \$this->percentage(\$completedTasksCount + \$inProgressTasksCount, \$totalTasksCount)", $projectController);
        $this->assertStringContainsString('$projectCards = collect($projectCards ?? []);', $projectsIndex);
        $this->assertStringContainsString('$projectCompanyOptions = collect($projectCompanyOptions ?? []);', $projectsIndex);
        $this->assertStringContainsString('$projectStaffOptions = collect($projectStaffOptions ?? []);', $projectsIndex);
        $this->assertStringContainsString('btn btn-primary btn-sm project-add-button', $projectsIndex);
        $this->assertStringContainsString('fa-solid fa-plus', $projectsIndex);
        $this->assertStringContainsString('<span>Add Project</span>', $projectsIndex);
        $this->assertStringContainsString('project-card-actions', $projectsIndex);
        $this->assertStringContainsString('project-card-action-button', $projectsIndex);
        $this->assertStringContainsString('js-project-edit', $projectsIndex);
        $this->assertStringContainsString('js-project-delete', $projectsIndex);
        $this->assertStringContainsString('fa-solid fa-pen-to-square', $projectsIndex);
        $this->assertStringContainsString('fa-solid fa-trash-can', $projectsIndex);
        $this->assertStringContainsString('id="projectCreateModal"', $projectsIndex);
        $this->assertStringContainsString('id="projectCreateModalTitle"', $projectsIndex);
        $this->assertStringContainsString('name="company_id" data-live-search="true"', $projectsIndex);
        $this->assertStringNotContainsString('Project Code', $projectsIndex);
        $this->assertStringNotContainsString('name="code"', $projectsIndex);
        $this->assertStringContainsString("asset('assets/vendor/select2/css/select2.min.css')", $projectsIndex);
        $this->assertStringContainsString("asset('assets/vendor/select2/js/select2.full.min.js')", $projectsIndex);
        $this->assertStringContainsString("asset('assets/vendor/sweetalert2/sweetalert2.min.css')", $projectsIndex);
        $this->assertStringContainsString("asset('assets/vendor/sweetalert2/sweetalert2.min.js')", $projectsIndex);
        $this->assertStringContainsString('showProjectCreateAlert', $projectsIndex);
        $this->assertStringContainsString('window.Swal && typeof window.Swal.fire === \'function\'', $projectsIndex);
        $this->assertStringContainsString('projectEditPayloads', $projectsIndex);
        $this->assertStringContainsString('fillProjectCreateForm', $projectsIndex);
        $this->assertStringContainsString('Update Project', $projectsIndex);
        $this->assertStringContainsString('_method: \'DELETE\'', $projectsIndex);
        $this->assertStringContainsString('class="form-control project-staff-select2 js-skip-selectpicker"', $projectsIndex);
        $this->assertStringContainsString('name="staff_employee_ids[]" multiple data-placeholder="Select staff"', $projectsIndex);
        $this->assertStringContainsString('selectElement.select2({', $projectsIndex);
        $this->assertStringContainsString("dropdownParent: $('#projectCreateModal')", $projectsIndex);
        $this->assertStringContainsString('select2-selection__choice__remove', $projectsIndex);
        $this->assertStringContainsString('.project-create-select2-dropdown .select2-results__option[aria-selected="true"]::after', $projectsIndex);
        $this->assertStringContainsString('content: "\f00c";', $projectsIndex);
        $this->assertStringNotContainsString('data-actions-box="true"', $projectsIndex);
        $this->assertStringContainsString('$projectProvinceOptions = collect($projectProvinceOptions ?? []);', $projectsIndex);
        $this->assertStringContainsString('$projectCityOptions = collect($projectCityOptions ?? []);', $projectsIndex);
        $this->assertStringContainsString('selectpicker form-select js-project-location-selectpicker" id="projectProvinceCode" name="province_code" data-live-search="true" data-width="100%" data-size="5"', $projectsIndex);
        $this->assertStringContainsString('selectpicker form-select js-project-location-selectpicker" id="projectCityCode" name="city_code" data-live-search="true" data-width="100%" data-size="5"', $projectsIndex);
        $this->assertStringContainsString('data-province-code="{{ $cityOption[\'province_code\'] }}"', $projectsIndex);
        $this->assertStringContainsString('id="projectAddress" name="address"', $projectsIndex);
        $this->assertStringContainsString('refreshProjectLocationSelectpickers', $projectsIndex);
        $this->assertStringContainsString("$(window).on('load.projectLocationSelectpicker'", $projectsIndex);
        $this->assertStringContainsString('syncProjectCityOptions', $projectsIndex);
        $this->assertStringContainsString('bindProjectLocationDefaults', $projectsIndex);
        $this->assertStringContainsString("String(option.attr('data-province-code')) === String(provinceCode)", $projectsIndex);
        $this->assertStringContainsString('Live Event Date', $projectsIndex);
        $this->assertStringContainsString('id="projectLiveEventDateRange"', $projectsIndex);
        $this->assertStringContainsString('id="projectDateRange"', $projectsIndex);
        $this->assertStringContainsString('name="live_event_start_date"', $projectsIndex);
        $this->assertStringContainsString('name="live_event_end_date"', $projectsIndex);
        $this->assertStringContainsString('name="start_date" required', $projectsIndex);
        $this->assertStringContainsString('name="end_date" required', $projectsIndex);
        $this->assertStringContainsString('class="form-control project-create-date-range-input js-project-create-date-range-input"', $projectsIndex);
        $this->assertStringContainsString('initializeProjectCreateDateRangePickers', $projectsIndex);
        $this->assertStringContainsString('hideProjectCreateDateRangePickers', $projectsIndex);
        $this->assertStringContainsString('bindProjectLifecycleDateDefaults', $projectsIndex);
        $this->assertStringContainsString('syncProjectLifecycleDateRange', $projectsIndex);
        $this->assertStringContainsString('$.fn.daterangepicker', $projectsIndex);
        $this->assertStringContainsString("format: 'DD/MM/YYYY'", $projectsIndex);
        $this->assertStringContainsString("picker.startDate.format('YYYY-MM-DD')", $projectsIndex);
        $this->assertStringContainsString("picker.endDate.format('YYYY-MM-DD')", $projectsIndex);
        $this->assertStringContainsString("parentEl: '#projectCreateModal'", $projectsIndex);
        $this->assertStringContainsString("$(document).on('select2:opening', '#projectStaffEmployeeIds', hideProjectCreateDateRangePickers);", $projectsIndex);
        $this->assertStringNotContainsString('type="date" class="form-control" id="projectLiveEventStartDate"', $projectsIndex);
        $this->assertStringNotContainsString('type="date" class="form-control" id="projectStartDate"', $projectsIndex);
        $this->assertStringNotContainsString('Live Event Start</label>', $projectsIndex);
        $this->assertStringNotContainsString('Live Event End</label>', $projectsIndex);
        $this->assertStringNotContainsString('Start Date <span class="required text-danger">*</span></label>', $projectsIndex);
        $this->assertStringNotContainsString('End Date <span class="required text-danger">*</span></label>', $projectsIndex);
        $this->assertStringNotContainsString('js-project-create-date-input', $projectsIndex);
        $this->assertStringNotContainsString('initializeProjectCreateDatePickers', $projectsIndex);
        $this->assertStringNotContainsString('hideProjectCreateDatePickers', $projectsIndex);
        $this->assertStringContainsString('enctype="multipart/form-data"', $projectsIndex);
        $this->assertStringContainsString('Project Image', $projectsIndex);
        $this->assertStringContainsString('type="file" class="form-control" id="projectImageFile" name="project_image"', $projectsIndex);
        $this->assertStringContainsString('accept="image/jpeg,image/png,image/webp"', $projectsIndex);
        $this->assertStringNotContainsString('id="projectImagePath"', $projectsIndex);
        $this->assertStringNotContainsString('name="image_path"', $projectsIndex);
        $this->assertStringNotContainsString('Image Path', $projectsIndex);
        $this->assertStringNotContainsString('id="projectStatus"', $projectsIndex);
        $this->assertStringNotContainsString('name="status" required', $projectsIndex);
        $this->assertStringNotContainsString('Status <span class="required text-danger">*</span>', $projectsIndex);
        $this->assertStringContainsString("\$projectCard['image_url']", $projectsIndex);
        $this->assertStringContainsString('projectCreateForm', $projectsIndex);
        $this->assertStringContainsString('No active project found', $projectsIndex);
        $this->assertStringContainsString('card project-card h-100', $projectsIndex);
        $this->assertStringContainsString('project-card-avatar', $projectsIndex);
        $this->assertStringContainsString('collect($projectCard[\'team_members\'] ?? [])->take(4)', $projectsIndex);
        $this->assertStringContainsString('$projectDivisionGroups = collect($projectDivisionGroups ?? []);', $projectsDetail);
        $this->assertStringContainsString("text-{{ \$projectDetail['status_class'] ?? 'primary' }}", $projectsDetail);
        $this->assertStringContainsString('project-card-command', $projectsDetail);
        $this->assertStringNotContainsString('badge badge-sm badge-{{ $projectDetail[\'status_class\'] ?? \'primary\' }} light', $projectsDetail);
        $this->assertStringContainsString('project-detail-overview-row', $projectsDetail);
        $this->assertStringNotContainsString('card project-detail-card h-100', $projectsDetail);
        $this->assertStringNotContainsString('project-tasks-over-time-card h-100', $projectsDetail);
        $this->assertStringContainsString('$summaryChartLabels = $projectDivisionGroups', $projectsDetail);
        $this->assertStringContainsString('$summaryChartSeries = $projectDivisionGroups', $projectsDetail);
        $this->assertStringContainsString('projectTasksSummaryChart', $projectsDetail);
        $this->assertStringContainsString('project-summary-chart', $projectsDetail);
        $this->assertStringContainsString('renderProjectTasksSummaryChart', $projectsDetail);
        $this->assertStringContainsString('width: 250', $projectsDetail);
        $this->assertStringContainsString("size: '90%'", $projectsDetail);
        $this->assertStringNotContainsString('project-summary-ring', $projectsDetail);
        $this->assertStringContainsString('project-team-stack', $projectsDetail);
        $this->assertStringContainsString('project-team-avatar', $projectsDetail);
        $this->assertStringContainsString('$projectTeamMembers = collect($projectDetail[\'team_members\'] ?? []);', $projectsDetail);
        $this->assertStringContainsString("\$projectDetail['image_url']", $projectsDetail);
        $this->assertStringContainsString('@forelse ($projectTeamMembers->take(6) as $teamMember)', $projectsDetail);
        $this->assertStringContainsString('$projectTaskTimeline = $projectTaskTimeline ?? [];', $projectsDetail);
        $this->assertStringContainsString('project-summary-legend', $projectsDetail);
        $this->assertStringContainsString('projectTasksOverTimeChart', $projectsDetail);
        $this->assertStringContainsString('<h4 class="card-title mb-0">Tasks</h4>', $projectsDetail);
        $this->assertStringNotContainsString('Tasks Over Time', $projectsDetail);
        $this->assertStringContainsString('width: 142px !important;', $projectsDetail);
        $this->assertStringContainsString('flex: 0 0 142px;', $projectsDetail);
        $this->assertStringContainsString("data-chart-labels='@json(\$projectTaskTimeline['labels'] ?? [])'", $projectsDetail);
        $this->assertStringContainsString("data-completed-series='@json(\$projectTaskTimeline['completed'] ?? [])'", $projectsDetail);
        $this->assertStringContainsString("data-incomplete-series='@json(\$projectTaskTimeline['incomplete'] ?? [])'", $projectsDetail);
        $this->assertStringContainsString("asset('assets/vendor/apexcharts/dist/apexcharts.min.js')", $projectsDetail);
        $this->assertStringContainsString('new ApexCharts(tasksOverTimeElement', $projectsDetail);
        $this->assertStringContainsString("type: 'area'", $projectsDetail);
        $this->assertStringContainsString('shouldUseTemplateScale = highestValue > 0 && highestValue < 30', $projectsDetail);
        $this->assertStringContainsString('normalizeSeriesForTemplateScale(incompleteSeries, 90, 120)', $projectsDetail);
        $this->assertStringContainsString('normalizeSeriesForTemplateScale(completedSeries, 50, 75)', $projectsDetail);
        $this->assertStringContainsString('yAxisMax = shouldUseTemplateScale ? 120', $projectsDetail);
        $this->assertStringContainsString('colorStops: [', $projectsDetail);
        $this->assertStringContainsString("color: '#ff5b8a'", $projectsDetail);
        $this->assertStringContainsString("color: '#2445c7'", $projectsDetail);
        $this->assertStringContainsString("return originalValue + ' Tasks';", $projectsDetail);
        $this->assertStringNotContainsString("asset('assets/vendor/chart-js/chart.bundle.min.js')", $projectsDetail);
        $this->assertStringNotContainsString('Department Scope', $projectsDetail);
        $this->assertStringContainsString('project-division-row', $projectsDetail);
        $this->assertStringContainsString("asset('assets/vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css')", $projectsDetail);
        $this->assertStringContainsString('project-division-view-all', $projectsDetail);
        $this->assertStringContainsString('>Drive</a>', $projectsDetail);
        $this->assertStringContainsString('data-drive-configured="true">Drive</button>', $projectsDetail);
        $this->assertStringContainsString('data-drive-configured="false">Konfigurasi Drive</button>', $projectsDetail);
        $this->assertStringContainsString('>Konfigurasi Drive</button>', $projectsDetail);
        $this->assertStringContainsString('@if (! empty($divisionGroup[\'google_drive_url\']))', $projectsDetail);
        $this->assertStringNotContainsString("{{ empty(\$divisionGroup['google_drive_url']) ? 'Add Drive' : 'Drive' }}</button>", $projectsDetail);
        $this->assertStringContainsString('<button type="button" class="btn btn-sm btn-light project-division-view-all" disabled>Konfigurasi Drive</button>', $projectsDetail);
        $this->assertStringNotContainsString('data-bs-toggle="modal" data-bs-target="#projectDivisionDriveModal" data-update-url="{{ $divisionGroup[\'drive_update_url\'] }}"', $projectsDetail);
        $this->assertStringContainsString('.project-division-view-all:disabled', $projectsDetail);
        $this->assertStringNotContainsString('View All', $projectsDetail);
        $this->assertStringContainsString('project-division-add-task', $projectsDetail);
        $this->assertStringContainsString('js-project-task-create', $projectsDetail);
        $this->assertStringContainsString('+ Add Task</button>', $projectsDetail);
        $this->assertStringContainsString('id="projectTaskFormModal"', $projectsDetail);
        $this->assertStringContainsString('Create New Task', $projectsDetail);
        $this->assertStringContainsString('Task Name <span class="required text-danger">*</span>', $projectsDetail);
        $this->assertStringContainsString('Task Description', $projectsDetail);
        $this->assertStringContainsString('Task Category <span class="required text-danger">*</span>', $projectsDetail);
        $this->assertStringContainsString('Project Report', $projectsDetail);
        $this->assertStringContainsString('Project Name', $projectsDetail);
        $this->assertStringContainsString('Assignee <span class="required text-danger">*</span>', $projectsDetail);
        $this->assertStringContainsString('class="form-control js-project-task-date-input"', $projectsDetail);
        $this->assertStringContainsString('initializeProjectTaskDatePickers', $projectsDetail);
        $this->assertStringContainsString('hideProjectTaskDatePickers', $projectsDetail);
        $this->assertStringContainsString("format: 'YYYY-MM-DD'", $projectsDetail);
        $this->assertStringContainsString("vertical: 'top'", $projectsDetail);
        $this->assertStringNotContainsString('type="date" class="form-control" id="projectTaskStartDate"', $projectsDetail);
        $this->assertStringNotContainsString('type="date" class="form-control" id="projectTaskDueDate"', $projectsDetail);
        $this->assertStringContainsString('id="projectTaskDeleteModal"', $projectsDetail);
        $this->assertStringContainsString('js-project-task-edit', $projectsDetail);
        $this->assertStringContainsString('js-project-task-delete', $projectsDetail);
        $this->assertStringContainsString('projectTaskDeleteButton', $projectsDetail);
        $this->assertStringContainsString('project-task-toggle', $projectsDetail);
        $this->assertStringContainsString('project-task-check-space', $projectsDetail);
        $this->assertStringContainsString('data-toggle-url', $projectsDetail);
        $this->assertStringContainsString('Live Event Dates', $projectsDetail);
        $this->assertStringContainsString("{{ \$projectDetail['live_event_date_label'] ?? '-' }}", $projectsDetail);
        $this->assertStringContainsString("{{ \$projectDetail['live_event_duration_label'] ?? '-' }}", $projectsDetail);
        $this->assertStringContainsString('id="projectDivisionDriveModal"', $projectsDetail);
        $this->assertStringContainsString("'Konfigurasi Drive ' +", $projectsDetail);
        $this->assertStringContainsString('https://accounts.google.com/gsi/client', $projectsDetail);
        $this->assertStringContainsString('https://apis.google.com/js/api.js', $projectsDetail);
        $this->assertStringContainsString('config(\'services.google.client_id\')', $projectsDetail);
        $this->assertStringContainsString('config(\'services.google.api_key\')', $projectsDetail);
        $this->assertStringContainsString('https://www.googleapis.com/auth/drive.file', $projectsDetail);
        $this->assertStringContainsString('projectDivisionDrivePickParent', $projectsDetail);
        $this->assertStringContainsString('projectDivisionDriveCreateFolder', $projectsDetail);
        $this->assertStringContainsString('projectDivisionDriveProjectFolderName', $projectsDetail);
        $this->assertStringContainsString('projectDivisionDriveDivisionFolderName', $projectsDetail);
        $this->assertStringContainsString('restoreProjectDivisionDriveModal', $projectsDetail);
        $this->assertStringContainsString('Buat Struktur Folder', $projectsDetail);
        $this->assertStringContainsString('Simpan URL', $projectsDetail);
        $this->assertStringContainsString('projectDivisionDriveOpenUrl', $projectsDetail);
        $this->assertStringContainsString('projectDivisionDriveStatusBadge', $projectsDetail);
        $this->assertStringContainsString('badge bg-danger', $projectsDetail);
        $this->assertStringContainsString('Folder siap', $projectsDetail);
        $this->assertStringContainsString('Belum siap', $projectsDetail);
        $this->assertStringContainsString("window.open(driveUrl, '_blank', 'noopener,noreferrer')", $projectsDetail);
        $this->assertStringContainsString(".toggleClass('bg-danger', ! hasDriveUrl)", $projectsDetail);
        $this->assertStringContainsString("text(hasDriveUrl ? 'Folder siap' : 'Belum siap')", $projectsDetail);
        $this->assertStringContainsString('application/vnd.google-apps.folder', $projectsDetail);
        $this->assertStringContainsString('https://www.googleapis.com/drive/v3/files?fields=id,name,webViewLink&supportsAllDrives=true', $projectsDetail);
        $this->assertStringContainsString('parents: [parentFolderId]', $projectsDetail);
        $this->assertStringContainsString('webViewLink', $projectsDetail);
        $this->assertStringContainsString('setSelectFolderEnabled(true)', $projectsDetail);
        $this->assertStringContainsString("hideModal('#projectDivisionDriveModal');", $projectsDetail);
        $this->assertStringContainsString('findOrCreateProjectDivisionDriveFolder(projectName, parentFolderId)', $projectsDetail);
        $this->assertStringContainsString('saveProjectDivisionDriveUrl(button, driveUrl, folder.id || \'\')', $projectsDetail);
        $this->assertStringContainsString('syncProjectDivisionDriveButton(button, response.google_drive_url || driveUrl, response.folder_id || folder.id || \'\')', $projectsDetail);
        $this->assertStringContainsString('syncProjectDivisionDriveButton(projectDivisionDriveActiveButton, response.google_drive_url || $(\'#projectDivisionDriveUrl\').val(), response.folder_id || $(\'#projectDivisionDriveFolderId\').val())', $projectsDetail);
        $this->assertStringContainsString("$(button).text(driveUrl ? 'Drive' : 'Konfigurasi Drive');", $projectsDetail);
        $this->assertStringContainsString("if ($(this).attr('data-drive-configured') === 'true')", $projectsDetail);
        $this->assertStringContainsString("text('Membuat struktur...')", $projectsDetail);
        $this->assertStringContainsString("text('Menyimpan link...')", $projectsDetail);
        $this->assertStringContainsString('google.accounts.oauth2.initCodeClient', $projectsDetail);
        $this->assertStringContainsString("prompt: 'consent'", $projectsDetail);
        $this->assertStringContainsString('projectDivisionDriveCodeClient.requestCode', $projectsDetail);
        $this->assertStringContainsString('projectDivisionDriveAccessTokenUrl', $projectsDetail);
        $this->assertStringContainsString('projectDivisionDriveExchangeCodeUrl', $projectsDetail);
        $this->assertStringNotContainsString('google.accounts.oauth2.initTokenClient', $projectsDetail);
        $this->assertStringNotContainsString('Your Department', $projectsDetail);
        $this->assertStringNotContainsString('View Only', $projectsDetail);
        $this->assertStringNotContainsString('Routine Cardio Burn Workout', $taskListSurface);
        $this->assertStringNotContainsString('workout-statistic.html', $taskListSurface);
        $this->assertStringNotContainsString('May 2026', $taskList);
        $this->assertStringContainsString('Date <span class="required text-danger">*</span>', $taskList);
        $this->assertStringContainsString('name="start_date" id="taskStartDate"', $taskList);
        $this->assertStringContainsString('name="due_date" id="taskDueDate"', $taskList);
        $this->assertStringContainsString('class="form-control js-task-date-range-input"', $taskList);
        $this->assertStringContainsString('id="taskDateRange"', $taskList);
        $this->assertStringContainsString('initializeTaskDateRangePicker', $taskList);
        $this->assertStringContainsString('formatTaskDateRangeDisplay', $taskList);
        $this->assertStringContainsString('$.fn.daterangepicker', $taskList);
        $this->assertStringContainsString("format: 'DD/MM/YYYY'", $taskList);
        $this->assertStringContainsString("picker.startDate.format('YYYY-MM-DD')", $taskList);
        $this->assertStringContainsString("picker.endDate.format('YYYY-MM-DD')", $taskList);
        $this->assertStringNotContainsString('class="form-control js-task-date-input"', $taskList);
        $this->assertStringNotContainsString('initializeTaskDatePickers', $taskList);
        $this->assertStringNotContainsString('hideTaskDatePickers', $taskList);
        $this->assertStringNotContainsString('showPicker', $taskList);
        $this->assertStringNotContainsString('type="date" class="form-control" name="start_date" id="taskStartDate"', $taskList);
        $this->assertStringNotContainsString('type="date" class="form-control" name="due_date" id="taskDueDate"', $taskList);
        $this->assertStringContainsString('Pending Tasks Reminder!', $taskListSurface);
        $this->assertStringContainsString('Ready for a Great Day?', $taskListSurface);
        preg_match_all('/data-bs-target="#([^"]+)"/', $taskList, $taskListTargets);

        foreach (array_unique($taskListTargets[1]) as $taskListTarget) {
            $this->assertStringContainsString('id="'.$taskListTarget.'"', $taskList);
        }

        $this->assertStringContainsString("@include('project_management.overview.partials.summary-cards')", $overview);
        $this->assertStringContainsString("View::composer('project_management.layouts.profile-header'", $appServiceProvider);
        $this->assertStringNotContainsString("'project_management.overview.index'", $appServiceProvider);
        $this->assertStringContainsString('Task Completion Rate', $summaryCards);
        $this->assertStringContainsString('Task In Progress', $summaryCards);
        $this->assertStringContainsString('{{ $projectTaskCompletionRate }}%', $summaryCards);
        $this->assertStringContainsString('{{ $projectTotalTasksCount }} Task', $summaryCards);
        $this->assertStringContainsString('{{ $projectTasksCompletedCount }} Task', $summaryCards);
        $this->assertStringContainsString('{{ $projectTasksInProgressCount }} Task', $summaryCards);
        $this->assertStringContainsString('fa-solid fa-chart-line text-secondary', $summaryCards);
        $this->assertStringContainsString('fa-solid fa-list-check text-success', $summaryCards);
        $this->assertStringContainsString('fa-solid fa-square-check text-danger', $summaryCards);
        $this->assertStringContainsString('fa-solid fa-hourglass-half text-info', $summaryCards);
        $this->assertStringContainsString('project-summary-metric-icon', $summaryCards);
        $this->assertStringContainsString('row g-4 mb-4 project-summary-mobile-slider', $summaryCards);
        $this->assertStringContainsString('project-summary-mobile-slide', $summaryCards);
        $this->assertSame(4, substr_count($summaryCards, 'col-md-3 col-sm-6 project-summary-mobile-slide'));
        $this->assertStringNotContainsString('>100%</span>', $summaryCards);
        $this->assertStringNotContainsString('>40 Task</span>', $summaryCards);
        $this->assertStringNotContainsString('>30 Task</span>', $summaryCards);
        $this->assertStringNotContainsString('>10 Task</span>', $summaryCards);
        $this->assertStringContainsString('project-monthly-overview-row', $overview);
        $this->assertStringContainsString('row align-items-stretch project-monthly-overview-row mb-4', $overview);
        $this->assertStringContainsString('project-monthly-overview-row > .project-monthly-overview-column', $overview);
        $this->assertStringContainsString('.project-summary-mobile-slider {', $overview);
        $this->assertStringContainsString('.project-summary-mobile-slide {', $overview);
        $this->assertStringContainsString('.project-overview-chart-wrapper {', $overview);
        $this->assertStringContainsString('.project-overview-chart-wrapper canvas {', $overview);
        $this->assertStringContainsString('.project-summary-metric-icon svg', $overview);
        $this->assertStringContainsString('.project-progress-metric-icon svg', $overview);
        $this->assertSame(2, substr_count($overview, '<div class="project-overview-chart-wrapper">'));
        $this->assertStringContainsString('var isMobileChartViewport = function(){', $overview);
        $this->assertStringContainsString('maxTicksLimit: isMobileChartViewport() ? 6 : 12', $overview);
        $this->assertStringContainsString('maintainAspectRatio: false', $overview);
        $this->assertStringContainsString('if(!Chart.registry.controllers.items.shadowLine){', $overview);
        $this->assertStringContainsString("$('#projectMonthlyOverviewMonthFilter').off('change.projectMonthlyOverview').on('change.projectMonthlyOverview'", $overview);
        $this->assertStringContainsString('col-md-3 col-12 project-monthly-overview-column', $overview);
        $this->assertStringContainsString('col-md-9 col-12 project-monthly-overview-column', $overview);
        $this->assertStringContainsString('card project-task-overview-card h-100', $overview);
        $this->assertStringContainsString('margin-top: .75rem;', $overview);
        $this->assertStringContainsString('card project-progress-card h-100', $overview);
        $this->assertStringContainsString('.project-progress-card .card-body > .row', $overview);
        $this->assertStringContainsString("'projectCurrentMonthLabel' => \$currentDate->format('F')", $overviewController);
        $this->assertStringContainsString('Task Overview ({{ $projectCurrentMonthLabel }})', $overview);
        $this->assertStringContainsString('Progress ({{ $projectCurrentMonthLabel }})', $overview);
        $this->assertStringContainsString('Task Overview ({{ $projectCurrentMonthYearLabel }})', $overview);
        $this->assertStringContainsString('Task Overview ({{ $projectCurrentYearLabel }})', $overview);
        $this->assertStringContainsString('id="projectMonthlyOverviewMonthFilter"', $overview);
        $this->assertStringContainsString('$projectMonthlyOverviewFilterOptions as $projectMonthlyOverviewFilterOption', $overview);
        $this->assertStringContainsString('data-monthly-charts=\'@json($projectMonthlyOverviewChartsByMonth)\'', $overview);
        $this->assertStringContainsString('monthlyOverviewChartsByMonth', $overviewController);
        $this->assertStringContainsString('monthlyOverviewFilterOptions', $overviewController);
        $this->assertStringContainsString("'value' => \$monthDate->format('Y-m')", $overviewController);
        $this->assertStringContainsString("'label' => \$monthDate->format('F Y')", $overviewController);
        $this->assertStringContainsString('data-completed-series=\'@json($projectMonthlyOverviewCompletedSeries)\'', $overview);
        $this->assertStringContainsString('data-incomplete-series=\'@json($projectMonthlyOverviewIncompleteSeries)\'', $overview);
        $this->assertStringContainsString('data-daily-series=\'@json($projectYearlyOverviewDailySeries)\'', $overview);
        $this->assertStringContainsString('data-project-series=\'@json($projectYearlyOverviewProjectSeries)\'', $overview);
        $this->assertStringContainsString('monthlyOverviewChartData', $overviewController);
        $this->assertStringContainsString('yearlyOverviewChartData', $overviewController);
        $this->assertStringContainsString('$this->tasksForYear($taskQuery, $year)', $overviewController);
        $this->assertStringContainsString('use App\Models\AttendanceHoliday;', $overviewController);
        $this->assertStringContainsString('$holidayDateMap = $this->holidayDateMap($date, $endDate);', $overviewController);
        $this->assertStringContainsString('! isset($holidayDateMap[$date->toDateString()])', $overviewController);
        $this->assertStringContainsString('AttendanceHoliday::query()', $overviewController);
        $this->assertStringContainsString('style="--project-task-completed-rate: {{ $projectTaskCompletionRate }}%;"', $overview);
        $this->assertStringContainsString('data-progress-rate="{{ $projectTaskCompletionRate }}"', $overview);
        $this->assertStringContainsString('Tasks Completed ({{ $projectTasksCompletedCount }}/{{ $projectTotalTasksCount }} Completed)', $overview);
        $this->assertStringContainsString('Daily Tasks ({{ $projectDailyTasksRate }}%)', $overview);
        $this->assertStringContainsString('fa-solid fa-calendar-day text-info', $overview);
        $this->assertStringContainsString('{{ $projectDailyTasksCompletedCount }} Task / {{ $projectDailyTasksCount }} Daily Tasks', $overview);
        $this->assertStringContainsString("'projectDailyTasksRate' => \$this->percentage(\$dailyTasksCompletedCount, \$dailyTasksCount)", $overviewController);
        $this->assertStringContainsString('Project Tasks ({{ $projectProjectTasksRate }}%)', $overview);
        $this->assertStringContainsString('fa-solid fa-diagram-project text-secondary', $overview);
        $this->assertStringContainsString('{{ $projectProjectTasksCompletedCount }} Task / {{ $projectProjectTasksCount }} Project Tasks', $overview);
        $this->assertStringContainsString("'projectProjectTasksRate' => \$this->percentage(\$projectTasksCompletedCount, \$projectTasksCount)", $overviewController);
        $this->assertStringContainsString('Completed Tasks This Week ({{ $projectWeeklyTasksCompletedRate }}%)', $overview);
        $this->assertStringContainsString('Incomplete Tasks This Week ({{ $projectWeeklyTasksIncompleteRate }}%)', $overview);
        $this->assertStringContainsString('fa-solid fa-calendar-check text-success', $overview);
        $this->assertStringContainsString('fa-solid fa-circle-exclamation text-danger', $overview);
        $this->assertStringContainsString('projectTaskQueryForDateRange', $overviewController);
        $this->assertStringContainsString("'projectWeeklyTasksCompletedRate' => 0", $overviewController);
        $this->assertStringContainsString('projectTaskQueryForMonth', $overviewController);
        $this->assertStringContainsString('YEAR(COALESCE(due_date, start_date, created_at))', $overviewController);
        $this->assertStringContainsString('$totalTasksCount = (clone $monthlyTaskQuery)->count();', $overviewController);
        $this->assertStringContainsString('monthlyCompletedTaskSeries', $overviewController);
        $this->assertStringContainsString('CarbonImmutable::create($year, $month, 1', $overviewController);
        $this->assertStringContainsString('range(1, 12)', $overviewController);
        $this->assertStringContainsString("'profileMonthlyAttendanceDelta' => \$taskCompletionRate", $overviewController);
        $this->assertStringContainsString('$monthlyAttendanceDeltaLabel = number_format($monthlyAttendanceDelta).\'%\';', $profileHeader);
        $this->assertStringContainsString('id="chartProfileProgressDesktop"', $profileHeader);
        $this->assertStringContainsString('id="chartProfileProgress"', $profileHeader);
        $this->assertStringContainsString("data-progress-series='@json(\$monthlyAttendanceSeries)'", $profileHeader);
        $this->assertStringContainsString('fa-solid fa-square-check fs-4 text-primary', $profileHeader);
        $this->assertStringContainsString('fa-solid fa-hourglass-half fs-4 text-primary', $profileHeader);
        $this->assertStringContainsString('fa-solid fa-list-check fs-4 text-primary', $profileHeader);
        $this->assertStringContainsString('fa-solid fa-layer-group fs-4 text-primary', $profileHeader);
        $this->assertStringContainsString('fa-solid fa-chart-pie fs-4 text-primary', $profileHeader);
        $this->assertStringNotContainsString('$profileStatsModeValue', $profileHeader);
        $this->assertStringNotContainsString('managementTotalEmployees', $profileHeader);
        $this->assertStringNotContainsString('managementPresentToday', $profileHeader);
        $this->assertStringNotContainsString('managementLateToday', $profileHeader);
        $this->assertStringNotContainsString('managementLeaveToday', $profileHeader);
        $this->assertStringNotContainsString('Staff Presence (Today)', $profileHeader);
        $this->assertStringNotContainsString('Staff Late (Today)', $profileHeader);
        $this->assertStringNotContainsString('Staff Leave (Today)', $profileHeader);
        $this->assertStringNotContainsString('profileStatsMode', $profileComposer);
        $this->assertStringContainsString('assets/vendor/apexcharts/dist/apexcharts.min.js', $profileIndex);
        $this->assertStringContainsString('function renderProfileProgressChart(chartElement)', $profileIndex);
        $this->assertStringContainsString('typeof window.ApexCharts', $profileIndex);
        $this->assertStringContainsString('data: progressSeries', $profileIndex);
        $this->assertStringContainsString('categories: progressLabels', $profileIndex);
        $this->assertStringContainsString('Array.from({ length: 12 }', $profileIndex);
        $this->assertStringContainsString("toLocaleString('en-US', { month: 'long' })", $profileIndex);
        $this->assertStringNotContainsString('dzProfile', $overview);
        $this->assertStringContainsString('parseChartObject', $overview);
        $this->assertStringContainsString("$('#projectMonthlyOverviewMonthFilter').off('change.projectMonthlyOverview').on('change.projectMonthlyOverview'", $overview);
        $this->assertStringContainsString('monthlyOverviewChart.update();', $overview);
        $this->assertStringContainsString('stepSize: stepSize', $overview);
        $this->assertStringContainsString('ticks: chartYAxisTickOptions(1)', $overview);
        $this->assertStringContainsString('precision: 0', $overview);
        $this->assertStringContainsString("return Number.isInteger(value) ? value : '';", $overview);
        $this->assertStringContainsString('visibleChartTarget', $profileIndex);
        $this->assertStringContainsString("dataset.profileProgressRendered = 'true'", $profileIndex);
        $this->assertStringNotContainsString('chartBar();', $overview);
        $this->assertStringNotContainsString('chartBar2();', $overview);
        $this->assertStringNotContainsString('chartBar3();', $overview);
        $this->assertStringNotContainsString('data: [18, 18, 18, 20, 20, 22, 13, 15, 16, 17, 18, 12]', $overview);
        $this->assertStringNotContainsString("'01-Monday', '02-Tuesday', '03-Wednesday', '04-Thursday', '05-Friday'", $overview);
        $this->assertStringNotContainsString("data: ['18', '17', '15', '18', '16']", $overview);
        $this->assertStringNotContainsString('data: [15, 12, 18, 20, 25, 45, 55, 50, 20, 15, 22, 60]', $overview);
        $this->assertStringNotContainsString('Week 23 (01-07 June 2026)', $overview);
        $this->assertStringNotContainsString('Tasks Completed (30/10 Completed)', $overview);
        $this->assertStringNotContainsString('Daily Tasks (45%)', $overview);
        $this->assertStringNotContainsString('Project Tasks (78%)', $overview);
        $this->assertStringNotContainsString('32 Hrs / 40 Hrs Per Week', $overview);
        $this->assertStringNotContainsString('18 Hrs / 18 Hrs Per Week', $overview);
        $this->assertStringNotContainsString('lineChartSecuritySummary', $overview);
        $this->assertStringNotContainsString('$monthlyAttendanceDelta >= 0 ? \'+\' : \'\'', $profileHeader);
        $this->assertStringNotContainsString('monthlyProgressDelta', $profileComposer);
        $this->assertStringNotContainsString('ProjectTask', $profileComposer);
        $this->assertStringNotContainsString('monthlyOverviewChartData', $profileComposer);
        $this->assertStringNotContainsString('projectTaskQueryForMonth', $profileComposer);
    }

    public function test_task_list_endpoints_manage_authenticated_employee_tasks(): void
    {
        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('SQLite PDO driver is not available for this database behavior test.');
        }

        $this->createProjectTaskListTestSchema();

        [$user, $employee] = $this->createProjectTaskListUser('staff_task_list');
        [$otherUser, $otherEmployee] = $this->createProjectTaskListUser('other_staff_task_list');

        $project = Project::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Task List Project',
            'code' => 'TASK-LIST',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        ProjectMember::query()->create([
            'id' => (string) Str::uuid(),
            'project_id' => $project->id,
            'employee_id' => $employee->id,
            'joined_at' => '2026-06-01',
            'status' => 'active',
        ]);

        $this->withoutMiddleware();

        $storeResponse = $this
            ->actingAs($user)
            ->postJson(route('project_management.task_list.tasks.store'), [
                'title' => 'Prepare weekly project recap',
                'description' => 'Summarize all active task progress.',
                'start_date' => '2026-06-22',
                'due_date' => '2026-06-24',
                'priority' => 'high',
                'task_category' => 'project',
                'project_id' => $project->id,
                'status' => 'in_progress',
            ]);

        $storeResponse->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Task berhasil ditambahkan.',
            ]);

        $projectTask = ProjectTask::query()
            ->where('employee_id', $employee->id)
            ->where('title', 'Prepare weekly project recap')
            ->firstOrFail();

        $this->assertSame((string) $project->id, (string) $projectTask->project_id);
        $this->assertSame((string) $user->id, (string) $projectTask->assigned_by);
        $this->assertSame('in_progress', $projectTask->status);

        $updateResponse = $this
            ->actingAs($user)
            ->putJson(route('project_management.task_list.tasks.update', $projectTask), [
                'title' => 'Prepare weekly project recap update',
                'description' => 'Updated progress summary.',
                'start_date' => '2026-06-22',
                'due_date' => '2026-06-25',
                'priority' => 'medium',
                'task_category' => 'daily',
                'status' => 'pending',
            ]);

        $updateResponse->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Task berhasil diperbarui.',
            ]);

        $projectTask->refresh();
        $this->assertNull($projectTask->project_id);
        $this->assertSame('Prepare weekly project recap update', $projectTask->title);
        $this->assertSame('pending', $projectTask->status);

        $completeResponse = $this
            ->actingAs($user)
            ->patchJson(route('project_management.task_list.tasks.complete', $projectTask));

        $completeResponse->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Task berhasil ditandai selesai.',
            ]);

        $projectTask->refresh();
        $this->assertSame('completed', $projectTask->status);
        $this->assertNotNull($projectTask->completed_at);

        $statusInProgressResponse = $this
            ->actingAs($user)
            ->patchJson(route('project_management.task_list.tasks.status.update', $projectTask), [
                'status' => 'in_progress',
            ]);

        $statusInProgressResponse->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Status task berhasil diperbarui.',
            ]);

        $projectTask->refresh();
        $this->assertSame('in_progress', $projectTask->status);
        $this->assertNull($projectTask->completed_at);

        $statusCompletedResponse = $this
            ->actingAs($user)
            ->patchJson(route('project_management.task_list.tasks.status.update', $projectTask), [
                'status' => 'completed',
            ]);

        $statusCompletedResponse->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Status task berhasil diperbarui.',
            ]);

        $projectTask->refresh();
        $this->assertSame('completed', $projectTask->status);
        $this->assertNotNull($projectTask->completed_at);

        $overtimeTask = ProjectTask::query()->create([
            'id' => (string) Str::uuid(),
            'employee_id' => $employee->id,
            'assigned_by' => $user->id,
            'overtime_id' => (string) Str::uuid(),
            'title' => 'Overtime task',
            'status' => 'pending',
            'priority' => 'high',
            'start_date' => '2026-06-22',
            'due_date' => '2026-06-23',
        ]);

        $overtimeUpdateResponse = $this
            ->actingAs($user)
            ->putJson(route('project_management.task_list.tasks.update', $overtimeTask), [
                'title' => 'Overtime task update',
                'description' => 'Updated overtime task summary.',
                'start_date' => '2026-06-22',
                'due_date' => '2026-06-25',
                'priority' => 'medium',
                'task_category' => 'daily',
                'status' => 'pending',
            ]);

        $overtimeUpdateResponse->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Task berhasil diperbarui.',
            ]);

        $overtimeCompleteResponse = $this
            ->actingAs($user)
            ->patchJson(route('project_management.task_list.tasks.complete', $overtimeTask));

        $overtimeCompleteResponse->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Task berhasil ditandai selesai.',
            ]);

        $overtimeTask->refresh();
        $this->assertSame('completed', $overtimeTask->status);
        $this->assertNotNull($overtimeTask->completed_at);

        $overtimeDeleteResponse = $this
            ->actingAs($user)
            ->deleteJson(route('project_management.task_list.tasks.destroy', $overtimeTask));

        $overtimeDeleteResponse->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk menghapus task ini.',
            ]);

        $otherTask = ProjectTask::query()->create([
            'id' => (string) Str::uuid(),
            'employee_id' => $otherEmployee->id,
            'assigned_by' => $otherUser->id,
            'title' => 'Other employee task',
            'status' => 'pending',
            'priority' => 'medium',
            'start_date' => '2026-06-22',
            'due_date' => '2026-06-23',
        ]);

        $forbiddenResponse = $this
            ->actingAs($user)
            ->deleteJson(route('project_management.task_list.tasks.destroy', $otherTask));

        $forbiddenResponse->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk menghapus task ini.',
            ]);

        $forbiddenStatusResponse = $this
            ->actingAs($user)
            ->patchJson(route('project_management.task_list.tasks.status.update', $otherTask), [
                'status' => 'in_progress',
            ]);

        $forbiddenStatusResponse->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk mengubah status task ini.',
            ]);

        $deleteResponse = $this
            ->actingAs($user)
            ->deleteJson(route('project_management.task_list.tasks.destroy', $projectTask));

        $deleteResponse->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Task berhasil dihapus.',
            ]);

        $this->assertSoftDeleted('project_tasks', [
            'id' => $projectTask->id,
        ]);
        $this->assertDatabaseHas('project_tasks', [
            'id' => $otherTask->id,
            'deleted_at' => null,
        ]);
    }

    public function test_task_list_shows_latest_tasks_first(): void
    {
        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('SQLite PDO driver is not available for this database behavior test.');
        }

        $this->createProjectTaskListTestSchema();

        [$user, $employee] = $this->createProjectTaskListUser('latest_task_list');

        foreach ([
            ['title' => 'Older completed task', 'date' => '2026-08-21'],
            ['title' => 'Middle completed task', 'date' => '2026-08-24'],
            ['title' => 'Newest completed task', 'date' => '2026-08-26'],
        ] as $task) {
            ProjectTask::query()->create([
                'employee_id' => $employee->id,
                'assigned_by' => $user->id,
                'title' => $task['title'],
                'status' => 'completed',
                'priority' => 'medium',
                'start_date' => $task['date'],
                'due_date' => $task['date'],
                'completed_at' => $task['date'].' 17:00:00',
            ]);
        }

        $this->withoutMiddleware();

        $response = $this
            ->actingAs($user)
            ->getJson(route('project_management.task_list.filter', [
                'month' => '2026-08',
            ]));

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $taskListHtml = (string) $response->json('fragments.task_list');
        $newestPosition = strpos($taskListHtml, 'Newest completed task');
        $middlePosition = strpos($taskListHtml, 'Middle completed task');
        $olderPosition = strpos($taskListHtml, 'Older completed task');

        $this->assertIsInt($newestPosition);
        $this->assertIsInt($middlePosition);
        $this->assertIsInt($olderPosition);
        $this->assertLessThan($middlePosition, $newestPosition);
        $this->assertLessThan($olderPosition, $middlePosition);
    }

    public function test_project_staff_employee_ids_include_pic_without_duplicates(): void
    {
        $staffEmployeeId = (string) Str::uuid();
        $picEmployeeId = (string) Str::uuid();
        $method = new \ReflectionMethod(
            ProjectController::class,
            'projectStaffEmployeeIdsWithPic',
        );

        $employeeIds = $method->invoke(
            new ProjectController,
            collect([$staffEmployeeId, $picEmployeeId, $staffEmployeeId, ' ']),
            $picEmployeeId,
        );

        $this->assertSame([$staffEmployeeId, $picEmployeeId], $employeeIds->all());

        $employeeIds = $method->invoke(
            new ProjectController,
            collect([$staffEmployeeId]),
            $picEmployeeId,
        );

        $this->assertSame([$staffEmployeeId, $picEmployeeId], $employeeIds->all());
    }

    public function test_project_creator_is_added_as_active_project_member(): void
    {
        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('SQLite PDO driver is not available for this database behavior test.');
        }

        $this->createProjectTaskListTestSchema();

        [$picUser, $picEmployee] = $this->createProjectTaskListUser('project_creator_pic');
        [, $staffEmployee] = $this->createProjectTaskListUser('project_creator_staff');
        $company = Company::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'RNB Test Company',
        ]);
        $supervisorPosition = Position::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Supervisor',
        ]);
        EmployeeDeployment::query()->create([
            'id' => (string) Str::uuid(),
            'employee_id' => $picEmployee->id,
            'current_position_id' => $supervisorPosition->id,
        ]);

        DB::table('indonesia_provinces')->insert([
            'code' => '32',
            'name' => 'JAWA BARAT',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('indonesia_cities')->insert([
            'code' => '3273',
            'province_code' => '32',
            'name' => 'KOTA BANDUNG',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withoutMiddleware();

        $response = $this
            ->actingAs($picUser)
            ->postJson(route('project_management.projects.store'), [
                'company_id' => $company->id,
                'staff_employee_ids' => [$staffEmployee->id],
                'name' => 'Creator Member Project',
                'description' => 'Project creator should become PIC and active team member.',
                'client_name' => 'RNB',
                'province_code' => '32',
                'city_code' => '3273',
                'address' => 'Jl. Asia Afrika No. 1',
                'start_date' => '2026-08-18',
                'end_date' => '2026-08-20',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Project berhasil ditambahkan.',
            ]);

        $project = Project::query()
            ->where('name', 'Creator Member Project')
            ->firstOrFail();

        $this->assertSame((string) $picUser->id, (string) $project->created_by);
        $this->assertSame('32', $project->province_code);
        $this->assertSame('3273', $project->city_code);
        $this->assertSame('Jl. Asia Afrika No. 1', $project->address);

        foreach ([$picEmployee, $staffEmployee] as $employee) {
            $this->assertDatabaseHas('project_members', [
                'project_id' => $project->id,
                'employee_id' => $employee->id,
                'left_at' => null,
                'status' => 'active',
            ]);
        }
    }

    public function test_pic_can_assign_task_list_task_to_active_staff_only(): void
    {
        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('SQLite PDO driver is not available for this database behavior test.');
        }

        $this->createProjectTaskListTestSchema();

        [$picUser, $picEmployee] = $this->createProjectTaskListUser('pic_task_list');
        [, $staffEmployee] = $this->createProjectTaskListUser('assigned_staff_task_list');
        [, $outsideEmployee] = $this->createProjectTaskListUser('outside_staff_task_list');

        EmployeePicAssignment::query()->create([
            'id' => (string) Str::uuid(),
            'supervisor_employee_id' => $picEmployee->id,
            'staff_employee_id' => $staffEmployee->id,
            'is_active' => true,
        ]);

        $project = Project::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Assigned Staff Project',
            'code' => 'ASSIGNED-STAFF',
            'status' => 'active',
            'created_by' => $picUser->id,
        ]);

        ProjectMember::query()->create([
            'id' => (string) Str::uuid(),
            'project_id' => $project->id,
            'employee_id' => $staffEmployee->id,
            'joined_at' => '2026-07-01',
            'status' => 'active',
        ]);

        $this->withoutMiddleware();

        $assignResponse = $this
            ->actingAs($picUser)
            ->postJson(route('project_management.task_list.tasks.store'), [
                'title' => 'Assigned task from PIC',
                'description' => 'Task assigned to subordinate staff.',
                'start_date' => '2026-07-01',
                'due_date' => '2026-07-02',
                'priority' => 'medium',
                'task_category' => 'project',
                'project_id' => $project->id,
                'assigned_employee_id' => $staffEmployee->id,
                'status' => 'pending',
            ]);

        $assignResponse->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Task berhasil ditambahkan.',
            ]);

        $assignedTask = ProjectTask::query()
            ->where('title', 'Assigned task from PIC')
            ->firstOrFail();

        $this->assertSame((string) $staffEmployee->id, (string) $assignedTask->employee_id);
        $this->assertSame((string) $picUser->id, (string) $assignedTask->assigned_by);
        $this->assertSame((string) $project->id, (string) $assignedTask->project_id);

        $outsideResponse = $this
            ->actingAs($picUser)
            ->postJson(route('project_management.task_list.tasks.store'), [
                'title' => 'Invalid outside assignment',
                'start_date' => '2026-07-01',
                'due_date' => '2026-07-02',
                'priority' => 'medium',
                'task_category' => 'daily',
                'assigned_employee_id' => $outsideEmployee->id,
                'status' => 'pending',
            ]);

        $outsideResponse->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Staff yang dipilih tidak berada di bawah PIC login.',
            ]);
    }

    public function test_event_project_admin_can_update_project_event_division_google_drive(): void
    {
        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('SQLite PDO driver is not available for this database behavior test.');
        }

        $this->createProjectTaskListTestSchema();

        [$adminUser, $adminEmployee] = $this->createProjectTaskListUser('event_admin_drive');
        [$staffUser, $staffEmployee] = $this->createProjectTaskListUser('staff_drive');
        $eventDivision = $this->createProjectTaskListEventDivision('Operations');

        $adminEmployee->update(['is_event_project_admin' => true]);

        $project = Project::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Google Drive Event',
            'code' => 'DRIVE-EVENT',
            'status' => 'active',
            'created_by' => $staffUser->id,
        ]);

        ProjectMember::query()->create([
            'id' => (string) Str::uuid(),
            'project_id' => $project->id,
            'employee_id' => $staffEmployee->id,
            'joined_at' => '2026-08-01',
            'status' => 'active',
        ]);

        $this->withoutMiddleware();

        $response = $this
            ->actingAs($adminUser)
            ->patchJson(route('project_management.projects.event-divisions.google-drive.update', [$project, $eventDivision]), [
                'google_drive_url' => 'https://drive.google.com/drive/folders/event-admin-drive',
                'folder_id' => 'event-admin-drive',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Google Drive division berhasil diperbarui.',
                'google_drive_url' => 'https://drive.google.com/drive/folders/event-admin-drive',
                'folder_id' => 'event-admin-drive',
            ]);

        $this->assertDatabaseHas('project_division_event', [
            'project_id' => $project->id,
            'event_division_id' => $eventDivision->id,
            'google_drive_url' => 'https://drive.google.com/drive/folders/event-admin-drive',
            'folder_id' => 'event-admin-drive',
            'status' => 'active',
        ]);

        $forbiddenResponse = $this
            ->actingAs($staffUser)
            ->patchJson(route('project_management.projects.event-divisions.google-drive.update', [$project, $eventDivision]), [
                'google_drive_url' => 'https://drive.google.com/drive/folders/staff-drive',
            ]);

        $forbiddenResponse->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk memperbarui Google Drive division project ini.',
            ]);
    }

    public function test_google_drive_oauth_code_exchange_stores_token_for_authenticated_user(): void
    {
        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('SQLite PDO driver is not available for this database behavior test.');
        }

        $this->createProjectTaskListTestSchema();

        [$user] = $this->createProjectTaskListUser('google_drive_oauth_user');

        config([
            'services.google.client_id' => 'local-client-id.apps.googleusercontent.com',
            'services.google.client_secret' => 'local-client-secret',
        ]);

        Http::fake([
            'oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'server-access-token',
                'refresh_token' => 'server-refresh-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
                'scope' => 'https://www.googleapis.com/auth/drive.file',
            ]),
        ]);

        $response = $this
            ->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('google-drive.oauth.exchange-code'), [
                'code' => 'google-auth-code',
                'redirect_uri' => 'http://127.0.0.1:8000',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'access_token' => 'server-access-token',
            ]);

        $googleOauthToken = GoogleOauthToken::query()
            ->where('user_id', $user->id)
            ->where('provider', 'google_drive')
            ->firstOrFail();

        $this->assertSame('server-access-token', $googleOauthToken->access_token);
        $this->assertSame('server-refresh-token', $googleOauthToken->refresh_token);

        $tokenResponse = $this
            ->actingAs($user)
            ->getJson(route('google-drive.oauth.access-token'));

        $tokenResponse->assertOk()
            ->assertJson([
                'success' => true,
                'access_token' => 'server-access-token',
            ]);
    }

    public function test_event_project_admin_can_assign_detail_project_task_to_event_division(): void
    {
        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('SQLite PDO driver is not available for this database behavior test.');
        }

        $this->createProjectTaskListTestSchema();

        [$picUser, $picEmployee] = $this->createProjectTaskListUser('event_pic_task');
        [, $staffEmployee] = $this->createProjectTaskListUser('event_member_task');
        $eventDivision = $this->createProjectTaskListEventDivision('Information and Communications Technology');
        $this->assignEmployeeToEventDivision($staffEmployee, $eventDivision);
        $picEmployee->update(['is_event_project_admin' => true]);

        $project = Project::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'PIC Detail Project',
            'code' => 'PIC-DETAIL',
            'status' => 'active',
            'created_by' => $picUser->id,
        ]);

        foreach ([$picEmployee, $staffEmployee] as $employee) {
            ProjectMember::query()->create([
                'id' => (string) Str::uuid(),
                'project_id' => $project->id,
                'employee_id' => $employee->id,
                'joined_at' => '2026-08-01',
                'status' => 'active',
            ]);
        }

        $this->withoutMiddleware();

        $response = $this
            ->actingAs($picUser)
            ->postJson(route('project_management.projects.tasks.store', $project), [
                'title' => 'Prepare event dashboard access',
                'description' => 'Grant dashboard access for live event operators.',
                'start_date' => '2026-08-10',
                'due_date' => '2026-08-12',
                'priority' => 'high',
                'status' => 'pending',
                'event_division_id' => $eventDivision->id,
                'assigned_employee_id' => $staffEmployee->id,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Task project berhasil ditambahkan.',
            ]);

        $this->assertDatabaseHas('project_tasks', [
            'project_id' => $project->id,
            'event_division_id' => $eventDivision->id,
            'employee_id' => $staffEmployee->id,
            'assigned_by' => $picUser->id,
            'title' => 'Prepare event dashboard access',
            'status' => 'pending',
        ]);

        $picEmployee->update(['is_event_project_admin' => false]);

        $invalidAssignmentResponse = $this
            ->actingAs($picUser)
            ->postJson(route('project_management.projects.tasks.store', $project), [
                'title' => 'Invalid non admin event task',
                'start_date' => '2026-08-10',
                'due_date' => '2026-08-12',
                'priority' => 'high',
                'status' => 'pending',
                'event_division_id' => $eventDivision->id,
                'assigned_employee_id' => $staffEmployee->id,
            ]);

        $invalidAssignmentResponse->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Staff task harus merupakan member aktif project ini pada event division yang dipilih.',
            ]);
    }

    public function test_project_member_can_create_own_detail_project_task_for_event_division(): void
    {
        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('SQLite PDO driver is not available for this database behavior test.');
        }

        $this->createProjectTaskListTestSchema();

        [$staffUser, $staffEmployee] = $this->createProjectTaskListUser('event_member_own_task');
        $eventDivision = $this->createProjectTaskListEventDivision('Graphic Design');
        $this->assignEmployeeToEventDivision($staffEmployee, $eventDivision);

        $project = Project::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Staff Own Event Task',
            'code' => 'STAFF-OWN-TASK',
            'status' => 'active',
            'created_by' => $staffUser->id,
        ]);

        ProjectMember::query()->create([
            'id' => (string) Str::uuid(),
            'project_id' => $project->id,
            'employee_id' => $staffEmployee->id,
            'joined_at' => '2026-08-01',
            'status' => 'active',
        ]);

        $this->withoutMiddleware();

        $response = $this
            ->actingAs($staffUser)
            ->postJson(route('project_management.projects.tasks.store', $project), [
                'title' => 'Create own division task',
                'description' => 'Staff creates a task for their own event division.',
                'start_date' => '2026-08-10',
                'due_date' => '2026-08-12',
                'priority' => 'medium',
                'status' => 'pending',
                'event_division_id' => $eventDivision->id,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Task project berhasil ditambahkan.',
            ]);

        $this->assertDatabaseHas('project_tasks', [
            'project_id' => $project->id,
            'event_division_id' => $eventDivision->id,
            'employee_id' => $staffEmployee->id,
            'assigned_by' => $staffUser->id,
            'title' => 'Create own division task',
            'status' => 'pending',
        ]);
    }

    private function createProjectTaskListTestSchema(): void
    {
        foreach ([
            'project_tasks',
            'project_division_event',
            'google_oauth_tokens',
            'event_divisions',
            'project_members',
            'projects',
            'employee_pic_assignments',
            'employee_profiles',
            'employee_deployment_positions',
            'employee_deployments',
            'employees',
            'positions',
            'departments',
            'companies',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('username')->nullable()->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('event_divisions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('sub_title')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('positions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained('users', 'id')->cascadeOnDelete();
            $table->string('status')->default('Active');
            $table->boolean('is_event_project_admin')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('employee_profiles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees', 'id')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('profile_picture_path')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_deployments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->unique()->constrained('employees', 'id')->cascadeOnDelete();
            $table->foreignUuid('current_department_id')->nullable()->constrained('departments', 'id')->nullOnDelete();
            $table->foreignUuid('current_event_division_id')->nullable()->constrained('event_divisions', 'id')->nullOnDelete();
            $table->foreignUuid('current_position_id')->nullable()->constrained('positions', 'id')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('employee_deployment_positions', function (Blueprint $table): void {
            $table->foreignUuid('employee_deployment_id')->constrained('employee_deployments', 'id')->cascadeOnDelete();
            $table->foreignUuid('position_id')->constrained('positions', 'id')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->string('status')->default('active');
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->timestamps();
            $table->primary(['employee_deployment_id', 'position_id']);
        });

        Schema::create('employee_pic_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('supervisor_employee_id')->constrained('employees', 'id')->cascadeOnDelete();
            $table->foreignUuid('staff_employee_id')->constrained('employees', 'id')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('indonesia_provinces', function (Blueprint $table): void {
            $table->id();
            $table->char('code', 2)->unique();
            $table->string('name');
            $table->text('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('indonesia_cities', function (Blueprint $table): void {
            $table->id();
            $table->char('code', 4)->unique();
            $table->char('province_code', 2);
            $table->string('name');
            $table->text('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->nullable()->constrained('companies', 'id')->nullOnDelete();
            $table->string('code')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('client_name')->nullable();
            $table->char('province_code', 2)->nullable();
            $table->char('city_code', 4)->nullable();
            $table->text('address')->nullable();
            $table->date('live_event_start_date')->nullable();
            $table->date('live_event_end_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('active');
            $table->foreignUuid('created_by')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('project_members', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects', 'id')->cascadeOnDelete();
            $table->foreignUuid('employee_id')->constrained('employees', 'id')->cascadeOnDelete();
            $table->date('joined_at')->nullable();
            $table->date('left_at')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('project_division_event', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects', 'id')->cascadeOnDelete();
            $table->foreignUuid('event_division_id')->constrained('event_divisions', 'id')->cascadeOnDelete();
            $table->string('google_drive_url', 2048)->nullable();
            $table->string('folder_id')->nullable()->index();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('google_oauth_tokens', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->string('provider')->default('google_drive');
            $table->text('scopes')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->string('token_type')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('refresh_token_expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'provider'], 'google_oauth_tokens_user_provider_unique');
        });

        Schema::create('project_tasks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->nullable()->constrained('projects', 'id')->nullOnDelete();
            $table->foreignUuid('event_division_id')->nullable()->constrained('event_divisions', 'id')->nullOnDelete();
            $table->foreignUuid('employee_id')->constrained('employees', 'id')->cascadeOnDelete();
            $table->foreignUuid('assigned_by')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->foreignUuid('overtime_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('blockers')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status')->default('pending');
            $table->string('priority')->default('medium');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * @return array{0: User, 1: Employee}
     */
    private function createProjectTaskListUser(string $username): array
    {
        $user = User::query()->create([
            'id' => (string) Str::uuid(),
            'username' => $username,
            'email' => $username.'@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $employee = Employee::query()->create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'status' => 'Active',
        ]);

        return [$user, $employee];
    }

    private function createProjectTaskListEventDivision(string $title): EventDivision
    {
        return EventDivision::query()->create([
            'id' => (string) Str::uuid(),
            'title' => $title,
            'status' => 'active',
        ]);
    }

    private function assignEmployeeToEventDivision(Employee $employee, EventDivision $eventDivision): EmployeeDeployment
    {
        return EmployeeDeployment::query()->updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'id' => (string) Str::uuid(),
                'current_event_division_id' => $eventDivision->id,
            ],
        );
    }

    private function createProjectTaskListDepartment(string $name): Department
    {
        return Department::query()->create([
            'id' => (string) Str::uuid(),
            'name' => $name,
            'status' => 'active',
        ]);
    }

    private function assignEmployeeToDepartment(Employee $employee, Department $department): EmployeeDeployment
    {
        return EmployeeDeployment::query()->create([
            'id' => (string) Str::uuid(),
            'employee_id' => $employee->id,
            'current_department_id' => $department->id,
        ]);
    }
}
