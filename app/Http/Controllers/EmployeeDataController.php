<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmployeeDataController extends Controller
{
    public function index(): View
    {
        return view('employee_data.index');
    }

    public function datatable(): JsonResponse
    {
        return response()->json([
            'data' => $this->employeeRows(),
        ]);
    }

    public function authorization(): View
    {
        return view('employee_data.authorization');
    }

    public function authorizationDatatable(): JsonResponse
    {
        return response()->json([
            'data' => $this->authorizationRows(),
        ]);
    }

    public function updateEventProjectAdmin(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'is_event_project_admin' => ['required', 'boolean'],
        ]);

        $employee->update([
            'is_event_project_admin' => (bool) $validated['is_event_project_admin'],
        ]);

        return back()->with('status', 'Event Project Admin status has been updated.');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function employeeRows(): Collection
    {
        return Employee::query()
            ->with([
                'user:id,is_active',
                'profile:id,employee_id,name,profile_picture_path',
                'identity:id,employee_id,nik',
                'deployment:id,employee_id,current_company_id,current_position_id',
                'deployment.company:id,name',
                'deployment.position:id,name',
                'deployment.positions:id,name',
            ])
            ->orderBy('employee_code')
            ->get(['id', 'user_id', 'employee_code', 'status', 'is_event_project_admin'])
            ->map(fn (Employee $employee): array => [
                'id' => (string) $employee->id,
                'initials' => $this->initials((string) ($employee->profile?->name ?? $employee->employee_code ?? '')),
                'avatar_url' => $this->employeeAvatarUrl($employee->profile?->profile_picture_path),
                'nik' => (string) ($employee->identity?->nik ?? '-'),
                'name' => (string) ($employee->profile?->name ?? '-'),
                'position' => $this->positionNamesFor($employee)->implode(', ') ?: '-',
                'company' => (string) ($employee->deployment?->company?->name ?? '-'),
                'is_event_project_admin' => (bool) $employee->is_event_project_admin,
            ])
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function positionNamesFor(Employee $employee): Collection
    {
        $deployment = $employee->deployment;

        if ($deployment === null) {
            return collect();
        }

        return collect([$deployment->position?->name])
            ->merge($deployment->positions?->pluck('name') ?? [])
            ->map(fn (mixed $positionName): string => trim((string) $positionName))
            ->filter()
            ->unique()
            ->values();
    }

    private function initials(string $name): string
    {
        $initials = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->map(fn (string $part): string => Str::substr($part, 0, 1))
            ->take(2)
            ->implode('');

        return Str::upper($initials !== '' ? $initials : 'E');
    }

    private function employeeAvatarUrl(mixed $profilePicturePath): string
    {
        $defaultAvatarUrl = asset('assets/default_user.jpg');
        $profilePicturePath = trim((string) $profilePicturePath);

        if ($profilePicturePath === '') {
            return $defaultAvatarUrl;
        }

        if (Str::startsWith($profilePicturePath, ['http://', 'https://'])) {
            return $profilePicturePath;
        }

        $publicPath = ltrim($profilePicturePath, '/');
        $storagePath = Str::startsWith($publicPath, 'storage/')
            ? Str::after($publicPath, 'storage/')
            : $publicPath;

        if (Storage::disk('public')->exists($storagePath)) {
            return asset('storage/'.$storagePath);
        }

        return File::exists(public_path($publicPath)) ? asset($publicPath) : $defaultAvatarUrl;
    }

    /**
     * @return Collection<int, array{menu: string, departments: array<int, string>}>
     */
    private function authorizationRows(): Collection
    {
        return collect([
            [
                'menu' => 'Administrations',
                'departments' => [
                    'Board of Directors',
                    'Administrator',
                    'Finance and Administration Coordinator',
                    'Accounting and Taxation',
                ],
            ],
            [
                'menu' => 'Applicants',
                'departments' => [
                    'Board of Directors',
                    'Administrator',
                    'Finance and Administration Coordinator',
                ],
            ],
            [
                'menu' => 'Attendances',
                'departments' => [
                    'All Staff',
                    'Legal Officer & Partnership',
                    'Finance and Administration Coordinator',
                    'Accounting and Taxation',
                    'Operations Coordinator',
                    'Interior Design',
                    'Architecture Design',
                    'Web Developer',
                    'Documentation Staff',
                ],
            ],
            [
                'menu' => 'Attendances-data',
                'departments' => [
                    'Board of Directors',
                    'Administrator',
                    'Legal Officer & Partnership',
                    'Finance and Administration Coordinator',
                ],
            ],
            [
                'menu' => 'Blogs',
                'departments' => [
                    'Select All',
                    'Administrator',
                    'Legal Officer & Partnership',
                    'Finance and Administration Coordinator',
                    'Accounting and Taxation',
                    'Operations Coordinator',
                    'Interior Design',
                    'Architecture Design',
                    'Web Developer',
                ],
            ],
            [
                'menu' => 'Calendar',
                'departments' => [
                    'Select All',
                    'Administrator',
                    'Legal Officer & Partnership',
                    'Finance and Administration Coordinator',
                    'Accounting and Taxation',
                    'Operations Coordinator',
                    'Interior Design',
                    'Architecture Design',
                    'Web Developer',
                ],
            ],
            [
                'menu' => 'Cms',
                'departments' => [
                    'Select All',
                    'Administrator',
                    'Legal Officer & Partnership',
                    'Finance and Administration Coordinator',
                    'Accounting and Taxation',
                    'Operations Coordinator',
                    'Interior Design',
                    'Architecture Design',
                    'Web Developer',
                ],
            ],
            [
                'menu' => 'Dashboard',
                'departments' => [
                    'Select All',
                    'Administrator',
                    'Legal Officer & Partnership',
                    'Finance and Administration Coordinator',
                    'Accounting and Taxation',
                    'Operations Coordinator',
                    'Interior Design',
                    'Architecture Design',
                    'Web Developer',
                ],
            ],
            [
                'menu' => 'Finances',
                'departments' => [
                    'Board of Directors',
                    'Administrator',
                    'Finance and Administration Coordinator',
                    'Accounting and Taxation',
                ],
            ],
            [
                'menu' => 'Options',
                'departments' => [
                    'Administrator',
                ],
            ],
        ]);
    }
}
