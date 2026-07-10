<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeePicAssignment;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
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
        $appServiceProvider = File::get(app_path('Providers/AppServiceProvider.php'));
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
        $projectTaskSeeder = File::get(database_path('seeders/ProjectTaskSeeder.php'));
        $liveEventDatesMigration = File::get(database_path('migrations/2026_06_28_234546_add_live_event_dates_to_projects_table.php'));
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
        $this->assertStringContainsString("Route::patch('/project-management/task-list/tasks/{projectTask}/complete', [ProjectManagementTaskListController::class, 'completeTask'])->name('project_management.task_list.tasks.complete');", $routes);
        $this->assertStringContainsString("Route::delete('/project-management/task-list/tasks/{projectTask}', [ProjectManagementTaskListController::class, 'destroyTask'])->name('project_management.task_list.tasks.destroy');", $routes);
        $this->assertStringContainsString("Route::get('/project-management/projects', [ProjectManagementProjectController::class, 'index'])->name('project_management.projects');", $routes);
        $this->assertStringContainsString("Route::get('/project-management/projects/detail', [ProjectManagementProjectController::class, 'detailFallback'])->name('project_management.projects.detail.fallback');", $routes);
        $this->assertStringContainsString("Route::get('/project-management/projects/{project}', [ProjectManagementProjectController::class, 'detail'])->name('project_management.projects.detail');", $routes);
        $this->assertStringContainsString("Route::post('/project-management/projects/{project}/tasks', [ProjectManagementProjectController::class, 'storeTask'])->name('project_management.projects.tasks.store');", $routes);
        $this->assertStringContainsString("Route::put('/project-management/projects/{project}/tasks/{projectTask}', [ProjectManagementProjectController::class, 'updateTask'])->name('project_management.projects.tasks.update');", $routes);
        $this->assertStringContainsString("Route::patch('/project-management/projects/{project}/tasks/{projectTask}/toggle', [ProjectManagementProjectController::class, 'toggleTask'])->name('project_management.projects.tasks.toggle');", $routes);
        $this->assertStringContainsString("Route::delete('/project-management/projects/{project}/tasks/{projectTask}', [ProjectManagementProjectController::class, 'destroyTask'])->name('project_management.projects.tasks.destroy');", $routes);
        $this->assertStringContainsString("Route::get('/project-management/detail', [ProjectManagementProjectController::class, 'detailFallback'])->name('project_management.detail');", $routes);
        $this->assertStringNotContainsString('public function taskList(Request $request): View', $overviewController);
        $this->assertStringNotContainsString('public function storeTask(Request $request): JsonResponse', $overviewController);
        $this->assertStringContainsString('public function index(Request $request): View', $taskListController);
        $this->assertStringContainsString('public function filter(Request $request): JsonResponse', $taskListController);
        $this->assertStringContainsString('$this->taskListData($request)', $taskListController);
        $this->assertStringContainsString('private function taskListFragments(array $taskListData): array', $taskListController);
        $this->assertStringContainsString('public function storeTask(Request $request): JsonResponse', $taskListController);
        $this->assertStringContainsString('public function updateTask(Request $request, ProjectTask $projectTask): JsonResponse', $taskListController);
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
        $this->assertStringContainsString("<div class=\"kanbanPreview-bx\">\n                <div class=\"sub-card", $taskListProjectGridPartial);
        $this->assertStringNotContainsString("<div class=\"draggable-zone dropzoneContainer\">\n                    <div class=\"sub-card", $taskListProjectGridPartial);
        $this->assertStringContainsString('To-Do List', $taskListProjectGridPartial);
        $this->assertStringContainsString('In Progress', $taskListProjectGridPartial);
        $this->assertStringContainsString('Review', $taskListProjectGridPartial);
        $this->assertStringContainsString('Done', $taskListProjectGridPartial);
        $this->assertStringContainsString('Backlog', $taskListProjectGridPartial);
        $this->assertStringContainsString("asset('assets-workload/images/contacts/pic11.jpg')", $taskListProjectGridPartial);
        $this->assertStringContainsString("asset('assets-workload/vendor/draggable/draggable.js')", $taskList);
        $this->assertStringContainsString('function initializeStaticKanbanBoard()', $taskList);
        $this->assertStringContainsString('function shouldUseMobileKanbanScroll()', $taskList);
        $this->assertStringContainsString("window.matchMedia('(max-width: 767.98px), (pointer: coarse)').matches", $taskList);
        $this->assertStringContainsString('shouldUseMobileKanbanScroll() || ! dropzones.length', $taskList);
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
        $this->assertStringContainsString('employeeIsProjectMember', $taskListController);
        $this->assertStringContainsString('public function index(): View', $projectController);
        $this->assertStringContainsString('public function detailFallback(): RedirectResponse', $projectController);
        $this->assertStringContainsString('public function detail(Project $project): View', $projectController);
        $this->assertStringContainsString('public function toggleTask(Request $request, Project $project, ProjectTask $projectTask): JsonResponse', $projectController);
        $this->assertStringContainsString('private function employeeCanViewProject(Project $project, string $employeeId): bool', $projectController);
        $this->assertStringContainsString('private function projectDepartmentGroups(Project $project, Collection $tasks, ?string $ownDepartmentId): Collection', $projectController);
        $this->assertStringContainsString("->where('project_id', \$project->id)", $projectController);
        $this->assertStringContainsString('current_department_id', $projectController);
        $this->assertStringContainsString('live_event_start_date', $projectController);
        $this->assertStringContainsString('live_event_end_date', $projectController);
        $this->assertStringContainsString("'subtitle' => trim((string) (\$project->description ?? \$project->client_name ?? '-'))", $projectController);
        $this->assertStringContainsString('live_event_date_label', $projectController);
        $this->assertStringContainsString('live_event_duration_label', $projectController);
        $this->assertStringContainsString('projectTaskTimeline', $projectController);
        $this->assertStringContainsString('private function projectTaskTimelineValue(Project $project, Collection $tasks): array', $projectController);
        $this->assertStringContainsString('betweenIncluded($weekStart, $weekEnd)', $projectController);
        $this->assertStringContainsString('memberships.employee.profile:id,employee_id,name,profile_picture_path', $projectController);
        $this->assertStringContainsString('team_members', $projectController);
        $this->assertStringContainsString('private function teamMemberValue(?Employee $employee): array', $projectController);
        $this->assertStringContainsString('private function profilePictureUrl(mixed $profilePicturePath): ?string', $projectController);
        $this->assertStringContainsString('private function teamAvatarFallbackLabel(string $displayName): string', $projectController);
        $this->assertStringContainsString("preg_match('/\d+/'", $projectController);
        $this->assertStringContainsString("'live_event_start_date' => 'date'", $projectModel);
        $this->assertStringContainsString("'live_event_end_date' => 'date'", $projectModel);
        $this->assertStringContainsString("\$table->date('live_event_start_date')->nullable()", $liveEventDatesMigration);
        $this->assertStringContainsString("\$table->date('live_event_end_date')->nullable()", $liveEventDatesMigration);
        $this->assertStringContainsString('projects_live_event_dates_index', $liveEventDatesMigration);
        $this->assertStringContainsString("'live_event_start_date' => Carbon::create(2026, 6, 18", $projectTaskSeeder);
        $this->assertStringContainsString("'live_event_end_date' => Carbon::create(2026, 6, 20", $projectTaskSeeder);
        $this->assertStringContainsString("private const CROSS_COMPANY_PROJECT_CODE = 'GROUP-COLLAB-2026';", $projectTaskSeeder);
        $this->assertStringContainsString('private const CROSS_COMPANY_STAFF_USERNAMES = [', $projectTaskSeeder);
        $this->assertStringContainsString("'staff11'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff12'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff13'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff21'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff22'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff23'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff31'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff33'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff14'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff45'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff44'", $projectTaskSeeder);
        $this->assertStringContainsString('private const CROSS_COMPANY_DEPARTMENT_ASSIGNMENTS = [', $projectTaskSeeder);
        $this->assertStringContainsString("'staff11' => 'Marketing and Promotion'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff12' => 'Marketing and Promotion'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff13' => 'Marketing and Promotion'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff21' => 'Information and Communications Technology'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff22' => 'Information and Communications Technology'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff23' => 'Information and Communications Technology'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff31' => 'Administration, Finance and Legal'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff33' => 'Project Planning and Development'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff14' => 'Project Planning and Development'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff45' => 'Project Planning and Development'", $projectTaskSeeder);
        $this->assertStringContainsString("'staff44' => 'Operations'", $projectTaskSeeder);
        $this->assertStringContainsString('Muktamar ke VI PKB 2024', $projectTaskSeeder);
        $this->assertStringContainsString('Bali Nusa Dua Convention Center, Badung, Bali', $projectTaskSeeder);
        $this->assertStringContainsString('$this->ensureCrossCompanyDepartmentAssignments($crossCompanyStaffUsers);', $projectTaskSeeder);
        $this->assertStringContainsString('$crossCompanyProject = $this->seedCrossCompanyProject($groupOwnerCompanyId, $crossCompanyCreatorUserId);', $projectTaskSeeder);
        $this->assertStringContainsString("route('project_management.projects.tasks.toggle'", $projectController);
        $this->assertStringContainsString('public function storeTask(Request $request, Project $project): JsonResponse', $projectController);
        $this->assertStringContainsString('public function updateTask(Request $request, Project $project, ProjectTask $projectTask): JsonResponse', $projectController);
        $this->assertStringContainsString('public function destroyTask(Project $project, ProjectTask $projectTask): JsonResponse', $projectController);
        $this->assertStringContainsString("route('project_management.projects.tasks.store'", $projectController);
        $this->assertStringContainsString("route('project_management.projects.tasks.update'", $projectController);
        $this->assertStringContainsString("route('project_management.projects.tasks.destroy'", $projectController);
        $this->assertStringContainsString('$projectCards = collect($projectCards ?? []);', $projectsIndex);
        $this->assertStringContainsString('No active project found', $projectsIndex);
        $this->assertStringContainsString('$projectDepartmentGroups = collect($projectDepartmentGroups ?? []);', $projectsDetail);
        $this->assertStringContainsString('<span class="project-meta-value">{{ $projectDetail[\'status_label\'] ?? \'-\' }}</span>', $projectsDetail);
        $this->assertStringNotContainsString('badge badge-sm badge-{{ $projectDetail[\'status_class\'] ?? \'primary\' }} light', $projectsDetail);
        $this->assertStringContainsString('project-detail-overview-row', $projectsDetail);
        $this->assertStringContainsString('project-summary-ring', $projectsDetail);
        $this->assertStringContainsString('project-team-stack', $projectsDetail);
        $this->assertStringContainsString('project-team-avatar', $projectsDetail);
        $this->assertStringContainsString('$projectTeamMembers = collect($projectDetail[\'team_members\'] ?? []);', $projectsDetail);
        $this->assertStringContainsString('@forelse ($projectTeamMembers->take(6) as $teamMember)', $projectsDetail);
        $this->assertStringContainsString('$projectTaskTimeline = $projectTaskTimeline ?? [];', $projectsDetail);
        $this->assertStringContainsString('project-summary-legend', $projectsDetail);
        $this->assertStringContainsString('projectTasksOverTimeChart', $projectsDetail);
        $this->assertStringContainsString('Tasks Over Time', $projectsDetail);
        $this->assertStringContainsString("data-chart-labels='@json(\$projectTaskTimeline['labels'] ?? [])'", $projectsDetail);
        $this->assertStringContainsString("data-completed-series='@json(\$projectTaskTimeline['completed'] ?? [])'", $projectsDetail);
        $this->assertStringContainsString("data-incomplete-series='@json(\$projectTaskTimeline['incomplete'] ?? [])'", $projectsDetail);
        $this->assertStringNotContainsString('Department Scope', $projectsDetail);
        $this->assertStringContainsString('project-department-row', $projectsDetail);
        $this->assertStringContainsString("asset('assets/vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css')", $projectsDetail);
        $this->assertStringContainsString('project-department-add-task', $projectsDetail);
        $this->assertStringContainsString('+ Add Task', $projectsDetail);
        $this->assertStringContainsString('id="projectTaskFormModal"', $projectsDetail);
        $this->assertStringContainsString('Create New Task', $projectsDetail);
        $this->assertStringContainsString('Task Name <span class="required text-danger">*</span>', $projectsDetail);
        $this->assertStringContainsString('Task Description', $projectsDetail);
        $this->assertStringContainsString('Task Category <span class="required text-danger">*</span>', $projectsDetail);
        $this->assertStringContainsString('Project Report', $projectsDetail);
        $this->assertStringContainsString('Project Name', $projectsDetail);
        $this->assertStringNotContainsString('Assignee <span class="text-danger">*</span>', $projectsDetail);
        $this->assertStringContainsString('class="form-control js-project-task-date-input"', $projectsDetail);
        $this->assertStringContainsString('initializeProjectTaskDatePickers', $projectsDetail);
        $this->assertStringContainsString('hideProjectTaskDatePickers', $projectsDetail);
        $this->assertStringContainsString("format: 'YYYY-MM-DD'", $projectsDetail);
        $this->assertStringContainsString("vertical: 'top'", $projectsDetail);
        $this->assertStringNotContainsString('type="date" class="form-control" id="projectTaskStartDate"', $projectsDetail);
        $this->assertStringNotContainsString('type="date" class="form-control" id="projectTaskDueDate"', $projectsDetail);
        $this->assertStringContainsString('id="projectTaskDeleteModal"', $projectsDetail);
        $this->assertStringContainsString('js-project-task-create', $projectsDetail);
        $this->assertStringContainsString('js-project-task-edit', $projectsDetail);
        $this->assertStringContainsString('js-project-task-delete', $projectsDetail);
        $this->assertStringContainsString('projectTaskDeleteButton', $projectsDetail);
        $this->assertStringContainsString('project-task-toggle', $projectsDetail);
        $this->assertStringContainsString('project-task-check-space', $projectsDetail);
        $this->assertStringContainsString('data-toggle-url', $projectsDetail);
        $this->assertStringContainsString('Live Event Dates', $projectsDetail);
        $this->assertStringContainsString("{{ \$projectDetail['live_event_date_label'] ?? '-' }}", $projectsDetail);
        $this->assertStringContainsString("{{ \$projectDetail['live_event_duration_label'] ?? '-' }}", $projectsDetail);
        $this->assertStringContainsString('project-department-drive', $projectsDetail);
        $this->assertStringContainsString('Drive</button>', $projectsDetail);
        $this->assertStringNotContainsString('Your Department', $projectsDetail);
        $this->assertStringContainsString('View Only', $projectsDetail);
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

    private function createProjectTaskListTestSchema(): void
    {
        foreach ([
            'project_tasks',
            'project_members',
            'projects',
            'employee_pic_assignments',
            'employee_profiles',
            'employees',
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

        Schema::create('employees', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained('users', 'id')->cascadeOnDelete();
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('employee_profiles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees', 'id')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('profile_picture_path')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_pic_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('supervisor_employee_id')->constrained('employees', 'id')->cascadeOnDelete();
            $table->foreignUuid('staff_employee_id')->constrained('employees', 'id')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('client_name')->nullable();
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

        Schema::create('project_tasks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->nullable()->constrained('projects', 'id')->nullOnDelete();
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
}
