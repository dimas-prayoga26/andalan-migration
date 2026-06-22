<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Position;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AuthorizationController extends Controller
{
    public function index(Request $request): View
    {
        $authenticatedUser = $request->user();

        abort_unless($authenticatedUser instanceof User, 403);

        return view('authorization.index', [
            'users' => $this->authorizationUsersFor($authenticatedUser),
        ]);
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

        Permission::query()
            ->get(['uuid'])
            ->each(function (Permission $permission) use ($submittedPositionIds): void {
                $positionIds = collect($submittedPositionIds->get((string) $permission->uuid, []))
                    ->filter()
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
            ->with('positions:id,name')
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
            'view-organization' => ['section' => 'HR Management', 'label' => 'Organization'],
            'view-authorization' => ['section' => 'HR Management', 'label' => 'Authorization'],
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
        ]);

        $query = User::query()
            ->with([
                'roles:uuid,name',
                'employee:id,user_id,status',
                'employee.profile:id,employee_id,name',
                'employee.deployment:id,employee_id,current_company_id,current_position_id,status',
                'employee.deployment.company:id,name',
                'employee.deployment.position:id,name',
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
        $positionName = $user->employee?->deployment?->position?->name;

        return $this->containsAdministrator($positionName);
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
            'name' => $name,
            'position' => (string) ($user->employee?->deployment?->position?->name ?? '-'),
            'company' => (string) ($user->employee?->deployment?->company?->name ?? '-'),
            'status' => $status,
            'initials' => $this->initials($name),
        ];
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
