<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeIdentity;
use App\Models\EmployeePicAssignment;
use App\Models\EmployeeProfile;
use App\Models\OfficeLocation;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthorizationController extends Controller
{
    private const DEFAULT_EMPLOYEE_PASSWORD = 'passwrod';

    public function index(Request $request): View
    {
        $authenticatedUser = $request->user();

        abort_unless($authenticatedUser instanceof User, 403);

        $search = $request->string('search')->trim()->toString();

        return view('authorization.index', [
            'users' => $this->authorizationUsersFor($authenticatedUser, $search),
            'search' => $search,
            'canManageDataEmployee' => $this->canManageAuthorization($authenticatedUser),
            'canManagePositionPermissions' => $this->canManagePositionPermissions($authenticatedUser),
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
                'password' => Hash::make(self::DEFAULT_EMPLOYEE_PASSWORD),
                'is_active' => (bool) $validated['is_active'],
            ]);
            $user->assignRole($this->defaultStaffRole());

            $employee = Employee::query()->create([
                'user_id' => $user->id,
                'employee_code' => $this->generateEmployeeCode($user),
                'status' => $validated['employee_status'],
            ]);

            $this->syncDataEmployeeRelations($employee, $validated);

            return $employee;
        });

        return redirect()
            ->route('authorization.show', ['employee' => $employee])
            ->with('status', 'Employee has been added successfully.');
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
        ] + $this->dataEmployeeFormOptions());
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
                'is_active' => (bool) $validated['is_active'],
            ]);

            $employee->update([
                'status' => $validated['employee_status'],
            ]);

            $this->syncDataEmployeeRelations($employee, $validated);
        });

        return redirect()
            ->route('authorization.show', ['employee' => $employee])
            ->with('status', 'Employee has been updated successfully.');
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
            ->with('status', 'Employee has been deleted successfully.');
    }

    public function accessMenus(Request $request): View
    {
        $authenticatedUser = $request->user();

        abort_unless($authenticatedUser instanceof User && $this->canManagePositionPermissions($authenticatedUser), 403);

        return view('authorization.access-menus', [
            'positions' => $this->authorizationPositions(),
            'menuPermissions' => $this->menuPermissions(),
        ]);
    }

    public function updatePositionPermissions(Request $request): RedirectResponse
    {
        $authenticatedUser = $request->user();

        abort_unless($authenticatedUser instanceof User && $this->canManagePositionPermissions($authenticatedUser), 403);

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
            'view-calendar' => ['section' => 'Main', 'label' => 'Activity Calendar'],
            'view-attendance' => ['section' => 'Siap', 'label' => 'Attendance'],
            'view-timesheet-reporting' => ['section' => 'Siap', 'label' => 'Timesheet & Reporting'],
            'view-meeting' => ['section' => 'Siap', 'label' => 'Zoom Meeting'],
            'view-admin-attendance' => ['section' => 'HR Management', 'label' => 'Admin Attendance'],
            'view-pic-attendance' => ['section' => 'HR Management', 'label' => 'PIC'],
            'view-director-attendance' => ['section' => 'HR Management', 'label' => 'Director'],
            'view-organization' => ['section' => 'HR Management', 'label' => 'Organization'],
            'view-authorization' => ['section' => 'HR Management', 'label' => 'Employee Data'],
            'view-employee-database' => ['section' => 'HR Management', 'label' => 'Employee Database'],
            'view-talent-acquisition' => ['section' => 'HR Management', 'label' => 'Talent Acquisition'],
            'view-payroll' => ['section' => 'Finance Management', 'label' => 'Payroll'],
            'view-employee-services' => ['section' => 'Finance Management', 'label' => 'Employee Services'],
        ];
    }

    /**
     * @return LengthAwarePaginator<int, array{
     *     name: string,
     *     position: string,
     *     company: string,
     *     status: string,
     *     initials: string
     * }>
     */
    private function authorizationUsersFor(User $viewer, string $search = ''): LengthAwarePaginator
    {
        $viewer->loadMissing([
            'roles:uuid,name',
            'employee.deployment.company:id,name',
            'employee.deployment.position:id,name',
            'employee.deployment.positions:id,name',
        ]);

        $query = User::query()
            ->addSelect([
                'authorization_company_name' => $this->authorizationCompanyNameSubquery(),
                'authorization_pic_name' => $this->authorizationPicNameSubquery(),
                'authorization_employee_name' => $this->authorizationEmployeeNameSubquery(),
            ])
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
            ->where('is_active', true)
            ->whereDoesntHave('roles', function (Builder $roleQuery): void {
                $roleQuery->where('name', 'superuser');
            })
            ->whereHas('employee', function (Builder $employeeQuery): void {
                $employeeQuery
                    ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])
                    ->whereHas('deployment', function (Builder $deploymentQuery): void {
                        $deploymentQuery->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active']);
                    });
            });

        if (! $this->canManageAuthorization($viewer)) {
            $companyId = $this->viewerCompanyId($viewer);

            if ($companyId === null) {
                return new LengthAwarePaginator([], 0, 10);
            }

            $query->whereHas('employee.deployment', function ($query) use ($companyId): void {
                $query->where('current_company_id', $companyId);
            });
        }

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $searchTerm = '%'.$search.'%';

                $query
                    ->where('username', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm)
                    ->orWhereHas('employee', function (Builder $employeeQuery) use ($searchTerm): void {
                        $employeeQuery
                            ->where('employee_code', 'like', $searchTerm)
                            ->orWhere('status', 'like', $searchTerm)
                            ->orWhereHas('profile', fn (Builder $profileQuery) => $profileQuery->where('name', 'like', $searchTerm))
                            ->orWhereHas('identity', fn (Builder $identityQuery) => $identityQuery->where('nik', 'like', $searchTerm))
                            ->orWhereHas('deployment.company', fn (Builder $companyQuery) => $companyQuery->where('name', 'like', $searchTerm))
                            ->orWhereHas('deployment.position', fn (Builder $positionQuery) => $positionQuery->where('name', 'like', $searchTerm))
                            ->orWhereHas('deployment.positions', fn (Builder $positionsQuery) => $positionsQuery->where('name', 'like', $searchTerm))
                            ->orWhereHas('picAssignment.supervisor.profile', fn (Builder $profileQuery) => $profileQuery->where('name', 'like', $searchTerm));
                    });
            });
        }

        return $query
            ->orderByRaw('CASE WHEN authorization_company_name IS NULL THEN 1 ELSE 0 END')
            ->orderBy('authorization_company_name')
            ->orderByRaw('CASE WHEN authorization_pic_name IS NULL THEN 1 ELSE 0 END')
            ->orderBy('authorization_pic_name')
            ->orderBy('authorization_employee_name')
            ->orderBy('username')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (User $user): array => $this->presentAuthorizationUser($user));
    }

    private function authorizationCompanyNameSubquery(): Builder
    {
        return Company::query()
            ->select('name')
            ->where(
                'id',
                EmployeeDeployment::query()
                    ->select('current_company_id')
                    ->where('employee_id', $this->authorizationEmployeeIdSubquery())
                    ->limit(1)
            )
            ->limit(1);
    }

    private function authorizationPicNameSubquery(): Builder
    {
        return EmployeeProfile::query()
            ->select('name')
            ->where(
                'employee_id',
                EmployeePicAssignment::query()
                    ->select('supervisor_employee_id')
                    ->where('staff_employee_id', $this->authorizationEmployeeIdSubquery())
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->latest('created_at')
                    ->limit(1)
            )
            ->limit(1);
    }

    private function authorizationEmployeeNameSubquery(): Builder
    {
        return EmployeeProfile::query()
            ->select('name')
            ->where('employee_id', $this->authorizationEmployeeIdSubquery())
            ->limit(1);
    }

    private function authorizationEmployeeIdSubquery(): Builder
    {
        return Employee::query()
            ->select('id')
            ->whereColumn('user_id', (new User)->qualifyColumn('id'))
            ->limit(1);
    }

    private function viewerCompanyId(User $user): ?string
    {
        $deployment = $user->employee?->deployment;

        if ($deployment === null) {
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

    private function isChiefOperatingOfficerEmployee(User $user): bool
    {
        return $this->positionNamesFor($user->employee)
            ->contains(static fn (string $positionName): bool => strtolower(trim($positionName)) === 'chief operating officer');
    }

    private function canManageAuthorization(User $user): bool
    {
        return $this->isSuperuser($user)
            || $this->isAdministratorEmployee($user)
            || $this->isChiefOperatingOfficerEmployee($user);
    }

    private function canManagePositionPermissions(User $user): bool
    {
        return $this->canManageAuthorization($user);
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
        if ($this->canManageAuthorization($viewer)) {
            return true;
        }

        $companyId = $this->viewerCompanyId($viewer);

        return $companyId !== null
            && $employee->loadMissing('deployment')->deployment?->current_company_id === $companyId;
    }

    private function loadDataEmployee(Employee $employee): Employee
    {
        return $employee->loadMissing([
            'user:id,company_id,username,phone,email,is_active',
            'profile:id,employee_id,name,nickname,gender,place_of_birth,date_of_birth,marital_status',
            'identity:id,employee_id,nik,npwp,bpjs_ketenagakerjaan,bpjs_kesehatan',
            'deployment:id,employee_id,current_company_id,current_office_location_id,current_department_id,current_position_id,join_date,resignation_date,workplace,status',
            'deployment.company:id,name',
            'deployment.officeLocation:id,name,address',
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
     *     officeLocationOptions: Collection<int, array{id: string, label: string}>,
     *     departments: Collection<int, Department>,
     *     positions: Collection<int, Position>,
     *     picEmployees: Collection<int, Employee>
     * }
     */
    private function dataEmployeeFormOptions(): array
    {
        return [
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'officeLocationOptions' => OfficeLocation::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->orderBy('address')
                ->get(['id', 'name', 'address'])
                ->map(fn (OfficeLocation $officeLocation): array => [
                    'id' => (string) $officeLocation->id,
                    'label' => $officeLocation->name
                        ?: trim((string) $officeLocation->address),
                ])
                ->values(),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'positions' => Position::query()->orderBy('name')->get(['id', 'name']),
            'picEmployees' => Employee::query()
                ->with('profile:id,employee_id,name')
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

        $request->merge([
            'is_active' => $request->boolean('is_active'),
            'date_of_birth' => $this->normalizeDateInput($request->input('date_of_birth')),
            'employee_status' => 'Active',
            'join_date' => $this->normalizeDateInput($request->input('join_date')),
            'resignation_date' => $this->normalizeDateInput($request->input('resignation_date')),
        ]);

        return $request->validate([
            'is_active' => ['required', 'boolean'],
            'employee_status' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'nik' => ['nullable', 'string', 'max:100'],
            'npwp' => ['nullable', 'string', 'max:100'],
            'bpjs_ketenagakerjaan' => ['nullable', 'string', 'max:100'],
            'bpjs_kesehatan' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', 'string', 'max:50'],
            'marital_status' => ['nullable', 'string', 'max:50'],
            'current_company_id' => ['nullable', 'string', 'exists:companies,id'],
            'current_office_location_id' => [
                'required_with:current_company_id',
                'nullable',
                'string',
                Rule::exists('office_locations', 'id')->where(function ($query): void {
                    $query->where('is_active', true);
                }),
            ],
            'current_department_id' => ['nullable', 'string', 'exists:departments,id'],
            'current_position_id' => ['nullable', 'string', 'exists:positions,id'],
            'current_position_ids' => ['array'],
            'current_position_ids.*' => ['string', 'exists:positions,id'],
            'join_date' => ['nullable', 'date'],
            'resignation_date' => ['nullable', 'date', 'after_or_equal:join_date'],
            'pic_employee_id' => ['nullable', 'string', 'exists:employees,id'],
        ]);
    }

    private function generateEmployeeCode(User $user): string
    {
        $baseCode = 'EMP-'.$this->shortNumericToken((string) $user->id, 'EMP');
        $employeeCode = $baseCode;
        $suffix = 1;

        while (Employee::query()->where('employee_code', $employeeCode)->exists()) {
            $employeeCode = $baseCode.'-'.$suffix;
            $suffix++;
        }

        return $employeeCode;
    }

    private function defaultStaffRole(): Role
    {
        return Role::query()->firstOrCreate([
            'name' => 'Staff',
            'guard_name' => 'web',
        ]);
    }

    private function shortNumericToken(string $value, string $salt): string
    {
        $hexHash = substr(hash('sha256', $salt.'|'.$value), 0, 16);
        $decimalValue = (string) hexdec(substr($hexHash, 0, 8));

        return str_pad(substr($decimalValue, 0, 8), 8, '0', STR_PAD_LEFT);
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

        $officeLocation = filled($validated['current_office_location_id'] ?? null)
            ? OfficeLocation::query()
                ->find($validated['current_office_location_id'])
            : null;
        $workplace = $officeLocation?->name;

        $deployment = EmployeeDeployment::query()->updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'current_company_id' => $validated['current_company_id'] ?? null,
                'current_office_location_id' => $officeLocation?->id,
                'current_department_id' => $validated['current_department_id'] ?? null,
                'current_position_id' => $primaryPositionId,
                'join_date' => $validated['join_date'] ?? null,
                'resignation_date' => $validated['resignation_date'] ?? null,
                'workplace' => $workplace,
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
        if ($picEmployeeId === '') {
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
