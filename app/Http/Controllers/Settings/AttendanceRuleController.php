<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\OfficeLocation;
use App\Models\RulesOfAttendace;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class AttendanceRuleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $attendanceRules = RulesOfAttendace::query()
            ->with('officeLocation:id,name,address')
            ->when($search !== '', function ($query) use ($search): void {
                $query
                    ->where('ip_range', 'like', "%{$search}%")
                    ->orWhereHas('officeLocation', function ($officeLocationQuery) use ($search): void {
                        $officeLocationQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%");
                    });
            })
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('settings.attendance-rules.index', [
            'attendanceRules' => $attendanceRules,
            'search' => $search,
            'pageTitle' => 'Attendance Rules',
        ]);
    }

    public function create(): View
    {
        return view('settings.attendance-rules.form', [
            'attendanceRule' => null,
            'mode' => 'create',
            'pageTitle' => 'Add Attendance Rule',
            'officeLocationOptions' => $this->officeLocationOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        RulesOfAttendace::query()->create($this->validatedData($request));

        return redirect()
            ->route('settings.attendance-rules.index')
            ->with('status', 'Attendance rule has been added.');
    }

    public function edit(RulesOfAttendace $attendanceRule): View
    {
        return view('settings.attendance-rules.form', [
            'attendanceRule' => $attendanceRule->load('officeLocation:id,name,address'),
            'mode' => 'edit',
            'pageTitle' => 'Update Attendance Rule',
            'officeLocationOptions' => $this->officeLocationOptions($attendanceRule),
        ]);
    }

    public function update(Request $request, RulesOfAttendace $attendanceRule): RedirectResponse
    {
        $attendanceRule->update($this->validatedData($request, $attendanceRule));

        return redirect()
            ->route('settings.attendance-rules.index')
            ->with('status', 'Attendance rule has been updated.');
    }

    public function destroy(RulesOfAttendace $attendanceRule): RedirectResponse
    {
        $attendanceRule->delete();

        return redirect()
            ->route('settings.attendance-rules.index')
            ->with('status', 'Attendance rule has been deleted.');
    }

    /**
     * @return array{office_location_id: string, ip_range: string, radius: int, office_start_time: string, office_end_time: string, is_active: bool}
     */
    private function validatedData(Request $request, ?RulesOfAttendace $attendanceRule = null): array
    {
        return $request->validate([
            'office_location_id' => [
                'required',
                'string',
                'exists:office_locations,id',
                Rule::unique('rules_of_attendaces', 'office_location_id')->ignore($attendanceRule?->id, 'id'),
            ],
            'ip_range' => ['required', 'string', 'max:255'],
            'radius' => ['required', 'integer', 'min:1', 'max:100000'],
            'office_start_time' => ['required', 'date_format:H:i'],
            'office_end_time' => ['required', 'date_format:H:i'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    /**
     * @return Collection<int, array{id: string, label: string}>
     */
    private function officeLocationOptions(?RulesOfAttendace $attendanceRule = null): Collection
    {
        $selectedOfficeLocationId = $attendanceRule?->office_location_id;

        return OfficeLocation::query()
            ->where(function ($query) use ($selectedOfficeLocationId): void {
                $query
                    ->where('is_active', true)
                    ->whereDoesntHave('attendanceRules');

                if (is_string($selectedOfficeLocationId) && trim($selectedOfficeLocationId) !== '') {
                    $query->orWhere((new OfficeLocation)->getKeyName(), $selectedOfficeLocationId);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'address'])
            ->map(fn (OfficeLocation $officeLocation): array => [
                'id' => (string) $officeLocation->id,
                'label' => $this->officeLocationLabel($officeLocation),
            ]);
    }

    private function officeLocationLabel(OfficeLocation $officeLocation): string
    {
        $name = is_string($officeLocation->name) ? trim($officeLocation->name) : '';
        $address = is_string($officeLocation->address) ? trim($officeLocation->address) : '';

        if ($name !== '' && $address !== '') {
            return $name.' - '.$address;
        }

        return $name !== '' ? $name : ($address !== '' ? $address : 'Unnamed Office Location');
    }
}
