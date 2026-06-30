<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeIdentity;
use App\Models\EmployeePicAssignment;
use App\Models\EmployeeProfile;
use App\Models\Permission;
use App\Models\Position;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthorizationController extends Controller
{
    public function index(Request $request): View
    {
        $authenticatedUser = $request->user();

        abort_unless($authenticatedUser instanceof User, 403);

        return view('authorization.index', [
            'users' => $this->authorizationUsersFor($authenticatedUser),
            'canManageDataEmployee' => $this->canManageAuthorization($authenticatedUser),
        ]);
    }

    public function create(Request $request): View
    {
        $authenticatedUser = $request->user();

        abort_unless($authenticatedUser instanceof User && $this->canManageAuthorization($authenticatedUser), 403);

        return view('authorization.form', [
            'mode' => 'create',
            'employee' => null,
        ] + $this->dataEmployeeFormOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $authenticatedUser = $request->user();

        abort_unless($authenticatedUser instanceof User && $this->canManageAuthorization($authenticatedUser), 403);

        $validated = $this->validatedDataEmployee($request);

        $employee = DB::transaction(function () use ($validated): Employee {
            $user = User::query()->create([
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'company_id' => $validated['current_company_id'] ?? null,
                'password' => Hash::make((string) $validated['password']),
                'is_active' => (bool) ($validated['is_active'] ?? true),
            ]);

            $employee = Employee::query()->create([
                'user_id' => $user->id,
                'employee_code' => $validated['employee_code'] ?? null,
                'status' => $validated['employee_status'],
            ]);

            $this->syncDataEmployeeRelations($employee, $validated);

            return $employee;
        });

        return redirect()
            ->route('authorization.show', ['employee' => $employee])
            ->with('status', 'Data employee berhasil dibuat.');
    }

    public function show(Request $request, Employee $employee): View
    {
        $authenticatedUser = $request->user();

        abort_unless($authenticatedUser instanceof User && $this->canViewEmployee($authenticatedUser, $employee), 404);

        return view('authorization.show', [
            'employee' => $this->loadDataEmployee($employee),
            'canManageDataEmployee' => $this->canManageAuthorization($authenticatedUser),
        ]);
    }

    public function edit(Request $request, Employee $employee): View
    {
        $authenticatedUser = $request->user();

        abort_unless($authenticatedUser instanceof User && $this->canManageAuthorization($authenticatedUser), 403);
        abort_unless($this->canViewEmployee($authenticatedUser, $employee), 404);

        return view('authorization.form', [
            'mode' => 'edit',
            'employee' => $this->loadDataEmployee($employee),
        ] + $this->dataEmployeeFormOptions($employee));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $authenticatedUser = $request->user();

        abort_unless($authenticatedUser instanceof User && $this->canManageAuthorization($authenticatedUser), 403);
        abort_unless($this->canViewEmployee($authenticatedUser, $employee), 404);

        $employee = $this->loadDataEmployee($employee);
        $validated = $this->validatedDataEmployee($request, $employee);

        DB::transaction(function () use ($employee, $validated): void {
            $employee->user?->update([
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'company_id' => $validated['current_company_id'] ?? null,
                'is_active' => (bool) ($validated['is_active'] ?? true),
            ]);

            if (filled($validated['password'] ?? null)) {
                $employee->user?->update([
                    'password' => Hash::make((string) $validated['password']),
                ]);
            }

            $employee->update([
                'employee_code' => $validated['employee_code'] ?? null,
                'status' => $validated['employee_status'],
            ]);

            $this->syncDataEmployeeRelations($employee, $validated);
        });

        return redirect()
            ->route('authorization.show', ['employee' => $employee])
            ->with('status', 'Data employee berhasil diperbarui.');
    }

    public function destroy(Request $request, Employee $employee): RedirectResponse
    {
        $authenticatedUser = $request->user();

        abort_unless($authenticatedUser instanceof User && $this->canManageAuthorization($authenticatedUser), 403);
        abort_unless($this->canViewEmployee($authenticatedUser, $employee), 404);

        DB::transaction(function () use ($employee): void {
            $employee->user?->update(['is_active' => false]);
            $employee->picAssignment?->delete();
            $employee->delete();
        });

        return redirect()
            ->route('authorization')
            ->with('status', 'Data employee berhasil dihapus.');
    }

    public function accessMenus(Request $request): View
    {
        $authenticatedUser = $request->user();

        abort_unless($authenticatedUser instanceof User && $this->canManageAuthorization($authenticatedUser), 403);

        return view('authorization.access-menus', [
            'positions' => $this->authorizationPositions(),
            'menuPermissions' => $this->menuPermissions(),
        ]);
    }

    public function updatePositionPermissions(Request $request): RedirectResponse
    {
        $authenticatedUser = $request->user();

        abort_unless($authenticatedUser instanceof User && $this->canManageAuthorization($authenticatedUser), 403);

        $validated = $request->validate([
            'permission_positions' => ['array'],
            'permission_positions.*' => ['array'],
            'permission_positions.*.*' => ['string', 'exists:positions,id'],
        ]);

        $submittedPositionIds = collect($validated['permission_positions'] ?? []);
        $assignablePositionIds = $this->authorizationPositions()
            ->pluck('id')
            ->all();

        Permission::query()
            ->get(['uuid'])
            ->each(function (Permission $permission) use ($assignablePositionIds, $submittedPositionIds): void {
                $positionIds = collect($submittedPositionIds->get((string) $permission->uuid, []))
                    ->filter()
                    ->intersect($assignablePositionIds)
                    ->unique()
                    ->values()
                    ->all();

                $permission->positions()->sync($positionIds);
            });

        return redirect()
            ->route('authorization.access-menus')
            ->with('status', 'Access menu berhasil diperbarui.');
    }

    /**
     * @return Collection<int, array{id: string, name: string}>
     */
    private function authorizationPositions(): Collection
    {
        return Position::query()
            ->where('name', '<>', 'Super Administrator')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Position $position): array => [
                'id' => (string) $position->id,
                'name' => (string) $position->name,
            ])
            ->values();
    }

    /**
     * @return Collection<int, array{id: string, name: string, label: string, section: string, position_ids: array<int, string>}>
     */
    private function menuPermissions(): Collection
    {
        $menuPermissionMetadata = $this->menuPermissionMetadata();

        return Permission::query()
            ->with(['positions' => fn ($query) => $query->where('name', '<>', 'Super Administrator')->select(['positions.id', 'positions.name'])])
            ->orderBy('name')
            ->get(['uuid', 'name'])
            ->map(function (Permission $permission) use ($menuPermissionMetadata): array {
                $metadata = $menuPermissionMetadata[(string) $permission->name] ?? null;

                return [
                    'id' => (string) $permission->uuid,
                    'name' => (string) $permission->name,
                    'label' => (string) ($metadata['label'] ?? Str::of((string) $permission->name)->replace('-', ' ')->title()),
                    'section' => (string) ($metadata['section'] ?? 'Other'),
                    'position_ids' => $permission->positions
                        ->pluck('id')
                        ->map(fn (mixed $positionId): string => (string) $positionId)
                        ->values()
                        ->all(),
                ];
            })
            ->sortBy([
                ['section', 'asc'],
                ['label', 'asc'],
            ])
            ->values();
    }

    /**
     * @return array<string, array{section: string, label: string}>
     */
    private function menuPermissionMetadata(): array
    {
        return [
            'view-dashboard' => ['section' => 'Main', 'label' => 'Dashboard'],
            'view-calendar' => ['section' => 'Main', 'label' => 'Google Calendar'],
            'view-attendance' => ['section' => 'Siap', 'label' => 'Attendance'],
            'view-timesheet-reporting' => ['section' => 'Siap', 'label' => 'Timesheet & Reporting'],
            'view-meeting' => ['section' => 'Siap', 'label' => 'Zoom Meeting'],
            'view-admin-attendance' => ['section' => 'HR Management', 'label' => 'Admin Attendance'],
            'view-pic-attendance' => ['section' => 'HR Management', 'label' => 'PIC'],
            'view-director-attendance' => ['section' => 'HR Management', 'label' => 'Director'],
            'view-organization' => ['section' => 'HR Management', 'label' => 'Organization'],
            'view-authorization' => ['section' => 'HR Management', 'label' => 'Data Employee'],
            'view-employee-database' => ['section' => 'HR Management', 'label' => 'Employee Database'],
            'view-talent-acquisition' => ['section' => 'HR Management', 'label' => 'Talent Acquisition'],
            'view-payroll' => ['section' => 'Finance Management', 'label' => 'Payroll'],
            'view-employee-services' => ['section' => 'Finance Management', 'label' => 'Employee Services'],
        ];
    }

    /**
     * @return Collection<int, array{
     *     name: string,
     *     position: string,
     *     company: string,
     *     status: string,
     *     initials: string
     * }>
     */
    private function authorizationUsersFor(User $viewer): Collection
    {
        $viewer->loadMissing([
            'roles:uuid,name',
            'employee.deployment.company:id,name',
            'employee.deployment.position:id,name',
            'employee.deployment.positions:id,name',
        ]);

        $query = User::query()
            ->with([
                'roles:uuid,name',
                'employee:id,user_id,employee_code,status',
                'employee.profile:id,employee_id,name',
                'employee.identity:id,employee_id,nik',
                'employee.deployment:id,employee_id,current_company_id,current_position_id,status',
                'employee.deployment.company:id,name',
                'employee.deployment.position:id,name',
                'employee.deployment.positions:id,name',
                'employee.picAssignment',
                'employee.picAssignment.supervisor:id',
                'employee.picAssignment.supervisor.profile:id,employee_id,name',
            ])
            ->whereHas('employee');

        if (! $this->isSuperuser($viewer)) {
            $companyId = $this->administratorCompanyId($viewer);

            if ($companyId === null) {
                return collect();
            }

            $query->whereHas('employee.deployment', function ($query) use ($companyId): void {
                $query->where('current_company_id', $companyId);
            });
        }

        return $query
            ->orderBy('username')
            ->get()
            ->map(fn (User $user): array => $this->presentAuthorizationUser($user))
            ->values();
    }

    private function administratorCompanyId(User $user): ?string
    {
        $deployment = $user->employee?->deployment;

        if ($deployment === null || ! $this->isAdministratorEmployee($user)) {
            return null;
        }

        $companyId = $deployment->current_company_id;

        return is_string($companyId) && trim($companyId) !== '' ? $companyId : null;
    }

    private function isAdministratorEmployee(User $user): bool
    {
        return $this->positionNamesFor($user->employee)
            ->contains(fn (string $positionName): bool => $this->containsAdministrator($positionName));
    }

    private function containsAdministrator(?string $value): bool
    {
        return is_string($value)
            && Str::of($value)->lower()->contains('administrator');
    }

    private function isSuperuser(User $user): bool
    {
        return $user->getRoleNames()
            ->map(fn (string $roleName): string => strtolower(trim($roleName)))
            ->contains('superuser');
    }

    private function canManageAuthorization(User $user): bool
    {
        return $this->isSuperuser($user) || $this->isAdministratorEmployee($user);
    }

    /**
     * @return array{
     *     name: string,
     *     position: string,
     *     company: string,
     *     status: string,
     *     initials: string
     * }
     */
    private function presentAuthorizationUser(User $user): array
    {
        $name = trim((string) ($user->employee?->profile?->name ?? ''));

        if ($name === '') {
            $name = trim((string) ($user->username ?? $user->email));
        }

        $status = trim((string) ($user->employee?->status ?? ''));
        $status = $status !== '' ? Str::title($status) : 'Active';

        if (! $user->is_active) {
            $status = 'Restricted';
        }

        return [
            'id' => (string) $user->employee?->id,
            'name' => $name,
            'position' => $this->positionNamesFor($user->employee)->implode(', ') ?: '-',
            'company' => (string) ($user->employee?->deployment?->company?->name ?? '-'),
            'employee_code' => (string) ($user->employee?->employee_code ?? '-'),
            'nik' => (string) ($user->employee?->identity?->nik ?? '-'),
            'pic' => (string) ($user->employee?->picAssignment?->supervisor?->profile?->name ?? '-'),
            'status' => $status,
            'initials' => $this->initials($name),
        ];
    }

    private function canViewEmployee(User $viewer, Employee $employee): bool
    {
        if ($this->isSuperuser($viewer)) {
            return true;
        }

        $companyId = $this->administratorCompanyId($viewer);

        return $companyId !== null
            && $employee->loadMissing('deployment')->deployment?->current_company_id === $companyId;
    }

    private function loadDataEmployee(Employee $employee): Employee
    {
        return $employee->loadMissing([
            'user:id,company_id,username,phone,email,is_active',
            'profile:id,employee_id,name,nickname,gender,place_of_birth,date_of_birth,marital_status',
            'identity:id,employee_id,nik,npwp,bpjs_ketenagakerjaan,bpjs_kesehatan',
            'deployment:id,employee_id,current_company_id,current_department_id,current_position_id,join_date,resignation_date,workplace,status',
            'deployment.company:id,name',
            'deployment.department:id,name',
            'deployment.position:id,name',
            'deployment.positions:id,name',
            'picAssignment',
            'picAssignment.supervisor:id',
            'picAssignment.supervisor.profile:id,employee_id,name',
        ]);
    }

    /**
     * @return array{
     *     companies: Collection<int, Company>,
     *     departments: Collection<int, Department>,
     *     positions: Collection<int, Position>,
     *     picEmployees: Collection<int, Employee>
     * }
     */
    private function dataEmployeeFormOptions(?Employee $employee = null): array
    {
        return [
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'positions' => Position::query()->orderBy('name')->get(['id', 'name']),
            'picEmployees' => Employee::query()
                ->with('profile:id,employee_id,name')
                ->when($employee instanceof Employee, fn ($query) => $query->whereKeyNot($employee->id))
                ->orderBy('employee_code')
                ->get(['id', 'employee_code']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedDataEmployee(Request $request, ?Employee $employee = null): array
    {
        $userId = $employee?->user_id;
        $passwordRules = $employee instanceof Employee
            ? ['nullable', 'string', 'min:6']
            : ['required', 'string', 'min:6'];

        $request->merge([
            'date_of_birth' => $this->normalizeDateInput($request->input('date_of_birth')),
            'employee_status' => 'Active',
            'join_date' => $this->normalizeDateInput($request->input('join_date')),
            'resignation_date' => $this->normalizeDateInput($request->input('resignation_date')),
        ]);

        return $request->validate([
            'is_active' => ['nullable', 'boolean'],
            'employee_status' => ['required', 'string', 'max:50'],
            'employee_code' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'password' => $passwordRules,
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'nik' => ['nullable', 'string', 'max:100'],
            'npwp' => ['nullable', 'string', 'max:100'],
            'bpjs_ketenagakerjaan' => ['nullable', 'string', 'max:100'],
            'bpjs_kesehatan' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', 'string', 'max:50'],
            'marital_status' => ['nullable', 'string', 'max:50'],
            'current_company_id' => ['nullable', 'string', 'exists:companies,id'],
            'current_department_id' => ['nullable', 'string', 'exists:departments,id'],
            'current_position_id' => ['nullable', 'string', 'exists:positions,id'],
            'current_position_ids' => ['array'],
            'current_position_ids.*' => ['string', 'exists:positions,id'],
            'workplace' => ['nullable', 'string', 'max:255'],
            'join_date' => ['nullable', 'date'],
            'resignation_date' => ['nullable', 'date', 'after_or_equal:join_date'],
            'pic_employee_id' => ['nullable', 'string', 'exists:employees,id'],
        ]);
    }

    private function normalizeDateInput(mixed $value): ?string
    {
        $dateValue = is_string($value) ? trim($value) : '';

        if ($dateValue === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'j/n/Y', 'd-m-Y', 'j-n-Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateValue);
            } catch (\Throwable) {
                continue;
            }

            if ($date instanceof Carbon && $date->format($format) === $dateValue) {
                return $date->format('Y-m-d');
            }
        }

        return $dateValue;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function syncDataEmployeeRelations(Employee $employee, array $validated): void
    {
        EmployeeProfile::query()->updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'name' => $validated['name'],
                'nickname' => $validated['nickname'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'place_of_birth' => $validated['place_of_birth'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'marital_status' => $validated['marital_status'] ?? null,
            ]
        );

        EmployeeIdentity::query()->updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'nik' => $validated['nik'] ?? null,
                'npwp' => $validated['npwp'] ?? null,
                'bpjs_ketenagakerjaan' => $validated['bpjs_ketenagakerjaan'] ?? null,
                'bpjs_kesehatan' => $validated['bpjs_kesehatan'] ?? null,
            ]
        );

        $positionIds = collect($validated['current_position_ids'] ?? [])
            ->filter()
            ->unique()
            ->values();
        $primaryPositionId = $validated['current_position_id'] ?? $positionIds->first();

        if (is_string($primaryPositionId) && trim($primaryPositionId) !== '') {
            $positionIds = $positionIds
                ->prepend($primaryPositionId)
                ->unique()
                ->values();
        } else {
            $primaryPositionId = null;
        }

        $deployment = EmployeeDeployment::query()->updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'current_company_id' => $validated['current_company_id'] ?? null,
                'current_department_id' => $validated['current_department_id'] ?? null,
                'current_position_id' => $primaryPositionId,
                'join_date' => $validated['join_date'] ?? null,
                'resignation_date' => $validated['resignation_date'] ?? null,
                'workplace' => $validated['workplace'] ?? null,
                'status' => $validated['employee_status'],
            ]
        );

        $this->syncDeploymentPositions(
            $deployment,
            $positionIds,
            $primaryPositionId,
            $validated['join_date'] ?? null,
            $validated['resignation_date'] ?? null,
        );

        $this->syncPicAssignment($employee, $validated['pic_employee_id'] ?? null);
    }

    /**
     * @param  Collection<int, string>  $positionIds
     */
    private function syncDeploymentPositions(
        EmployeeDeployment $deployment,
        Collection $positionIds,
        ?string $primaryPositionId,
        ?string $startedAt,
        ?string $endedAt,
    ): void {
        $syncData = $positionIds
            ->mapWithKeys(fn (string $positionId): array => [
                $positionId => [
                    'is_primary' => $primaryPositionId === $positionId,
                    'status' => 'active',
                    'started_at' => $startedAt,
                    'ended_at' => $endedAt,
                ],
            ])
            ->all();

        $deployment->positions()->sync($syncData);
    }

    /**
     * @return Collection<int, string>
     */
    private function positionNamesFor(?Employee $employee): Collection
    {
        $deployment = $employee?->deployment;

        if ($deployment === null) {
            return collect();
        }

        $positionNames = collect();

        if ($deployment->position !== null) {
            $positionNames->push((string) $deployment->position->name);
        }

        if ($deployment->positions !== null) {
            $positionNames = $positionNames->merge(
                $deployment->positions
                    ->pluck('name')
                    ->map(fn (mixed $positionName): string => (string) $positionName)
            );
        }

        return $positionNames
            ->map(fn (string $positionName): string => trim($positionName))
            ->filter()
            ->unique()
            ->values();
    }

    private function syncPicAssignment(Employee $employee, mixed $picEmployeeId): void
    {
        EmployeePicAssignment::query()
            ->where('staff_employee_id', $employee->id)
            ->update(['is_active' => false]);

        $picEmployeeId = is_string($picEmployeeId) ? trim($picEmployeeId) : '';
        if ($picEmployeeId === '' || $picEmployeeId === $employee->id) {
            return;
        }

        $assignment = EmployeePicAssignment::withTrashed()->firstOrNew([
            'supervisor_employee_id' => $picEmployeeId,
            'staff_employee_id' => $employee->id,
        ]);

        if ($assignment->trashed()) {
            $assignment->restore();
        }

        $assignment->fill(['is_active' => true]);
        $assignment->save();
    }

    private function initials(string $name): string
    {
        $initials = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->map(fn (string $part): string => Str::substr($part, 0, 1))
            ->take(2)
            ->implode('');

        return Str::upper($initials !== '' ? $initials : 'U');
    }
}
