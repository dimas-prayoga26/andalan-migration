<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\OfficeLocation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OfficeLocationController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $officeLocations = OfficeLocation::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('settings.office-locations.index', [
            'officeLocations' => $officeLocations,
            'search' => $search,
            'pageTitle' => 'Office Locations',
        ]);
    }

    public function create(): View
    {
        return view('settings.office-locations.form', [
            'officeLocation' => null,
            'mode' => 'create',
            'pageTitle' => 'Add Office Location',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        OfficeLocation::query()->create($this->validatedData($request));

        return redirect()
            ->route('settings.office-locations.index')
            ->with('status', 'Office location has been added.');
    }

    public function edit(OfficeLocation $officeLocation): View
    {
        return view('settings.office-locations.form', [
            'officeLocation' => $officeLocation,
            'mode' => 'edit',
            'pageTitle' => 'Update Office Location',
        ]);
    }

    public function update(Request $request, OfficeLocation $officeLocation): RedirectResponse
    {
        $officeLocation->update($this->validatedData($request, $officeLocation));

        return redirect()
            ->route('settings.office-locations.index')
            ->with('status', 'Office location has been updated.');
    }

    public function destroy(OfficeLocation $officeLocation): RedirectResponse
    {
        $isUsedByDeployment = DB::table('employee_deployments')
            ->where('current_office_location_id', $officeLocation->id)
            ->exists();

        $isUsedByAttendanceRule = DB::table('rules_of_attendaces')
            ->where('office_location_id', $officeLocation->id)
            ->exists();

        if ($isUsedByDeployment || $isUsedByAttendanceRule) {
            return back()->with('error', 'Office location is still used by employee deployment or attendance rule data.');
        }

        $officeLocation->delete();

        return redirect()
            ->route('settings.office-locations.index')
            ->with('status', 'Office location has been deleted.');
    }

    /**
     * @return array{name: string, address: string|null, latitude: float, longitude: float, is_active: bool}
     */
    private function validatedData(Request $request, ?OfficeLocation $officeLocation = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('office_locations', 'name')->ignore($officeLocation?->id, 'id'),
            ],
            'address' => ['nullable', 'string', 'max:2000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
