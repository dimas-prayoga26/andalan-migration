<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\OfficeLocation;
use App\Models\Position;
use App\Models\RulesOfAttendace;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AttendanceRuleController extends Controller
{
    public function index(): View
    {
        return view('settings.attendance-rules.index', [
            'pageTitle' => 'Attendance Rules',
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $query = RulesOfAttendace::query()
            ->with(['officeLocation:id,name,address', 'positions:id,name'])
            ->select([
                'id',
                'office_location_id',
                'ip_range',
                'radius',
                'attendance_type',
                'office_start_time',
                'office_end_time',
                'is_active',
                'updated_at',
            ])
            ->latest('updated_at');

        return DataTables::eloquent($query)
            ->filter(function (Builder $query) use ($request): void {
                $search = trim((string) $request->input('search.value', ''));

                if ($search === '') {
                    return;
                }

                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('ip_range', 'like', "%{$search}%")
                        ->orWhere('attendance_type', 'like', "%{$search}%")
                        ->orWhereHas('officeLocation', function (Builder $officeLocationQuery) use ($search): void {
                            $officeLocationQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('address', 'like', "%{$search}%");
                        })
                        ->orWhereHas('positions', function (Builder $positionQuery) use ($search): void {
                            $positionQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->addColumn('office_location_name', fn (RulesOfAttendace $attendanceRule): string => $attendanceRule->officeLocation?->name ?? '-')
            ->addColumn('office_location_address', fn (RulesOfAttendace $attendanceRule): string => $attendanceRule->officeLocation?->address ?? '')
            ->editColumn('radius', fn (RulesOfAttendace $attendanceRule): string => number_format((int) $attendanceRule->radius).' m')
            ->addColumn('positions_label', fn (RulesOfAttendace $attendanceRule): string => $attendanceRule->positions->pluck('name')->join(', '))
            ->editColumn('office_start_time', fn (RulesOfAttendace $attendanceRule): string => $this->formatTimeLabel($attendanceRule->office_start_time))
            ->editColumn('office_end_time', fn (RulesOfAttendace $attendanceRule): string => $this->formatTimeLabel($attendanceRule->office_end_time))
            ->toJson();
    }

    public function create(): View
    {
        return view('settings.attendance-rules.form', [
            'attendanceRule' => null,
            'mode' => 'create',
            'pageTitle' => 'Add Attendance Rule',
            'officeLocationOptions' => $this->officeLocationOptions(),
            'positionOptions' => $this->positionOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validatedData = $this->validatedData($request);
        $positionIds = $validatedData['position_ids'];
        unset($validatedData['position_ids']);

        DB::transaction(function () use ($validatedData, $positionIds): void {
            $attendanceRule = RulesOfAttendace::query()->create($validatedData);
            $attendanceRule->positions()->sync($positionIds);
        });

        return redirect()
            ->route('settings.attendance-rules.index')
            ->with('status', 'Attendance rule has been added.');
    }

    public function edit(RulesOfAttendace $attendanceRule): View
    {
        return view('settings.attendance-rules.form', [
            'attendanceRule' => $attendanceRule->load(['officeLocation:id,name,address', 'positions:id,name']),
            'mode' => 'edit',
            'pageTitle' => 'Update Attendance Rule',
            'officeLocationOptions' => $this->officeLocationOptions($attendanceRule),
            'positionOptions' => $this->positionOptions(),
        ]);
    }

    public function update(Request $request, RulesOfAttendace $attendanceRule): RedirectResponse
    {
        $validatedData = $this->validatedData($request);
        $positionIds = $validatedData['position_ids'];
        unset($validatedData['position_ids']);

        DB::transaction(function () use ($attendanceRule, $validatedData, $positionIds): void {
            $attendanceRule->update($validatedData);
            $attendanceRule->positions()->sync($positionIds);
        });

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
     * @return array{office_location_id: string, attendance_type: string, position_ids: array<int, string>, ip_range: string, radius: int, office_start_time: string, office_end_time: string, is_active: bool}
     */
    private function validatedData(Request $request): array
    {
        $validatedData = $request->validate([
            'office_location_id' => [
                'required',
                'string',
                'exists:office_locations,id',
            ],
            'attendance_type' => ['required', 'string', 'in:fixed,flexible'],
            'position_ids' => ['nullable', 'array'],
            'position_ids.*' => ['string', 'distinct', 'exists:positions,id'],
            'ip_range' => ['required', 'string', 'max:255'],
            'radius' => ['required', 'integer', 'min:1', 'max:100000'],
            'office_start_time' => ['required', 'date_format:H:i'],
            'office_end_time' => ['required', 'date_format:H:i'],
            'is_active' => ['required', 'boolean'],
        ]);

        $validatedData['position_ids'] = array_values($validatedData['position_ids'] ?? []);

        return $validatedData;
    }

    /**
     * @return Collection<int, array{id: string, label: string}>
     */
    private function officeLocationOptions(?RulesOfAttendace $attendanceRule = null): Collection
    {
        return OfficeLocation::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'address'])
            ->map(fn (OfficeLocation $officeLocation): array => [
                'id' => (string) $officeLocation->id,
                'label' => $this->officeLocationLabel($officeLocation),
            ]);
    }

    /**
     * @return Collection<int, array{id: string, label: string}>
     */
    private function positionOptions(): Collection
    {
        return Position::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Position $position): array => [
                'id' => (string) $position->id,
                'label' => is_string($position->name) && trim($position->name) !== '' ? trim($position->name) : 'Unnamed Position',
            ]);
    }

    private function officeLocationLabel(OfficeLocation $officeLocation): string
    {
        $name = is_string($officeLocation->name) ? trim($officeLocation->name) : '';
        $address = is_string($officeLocation->address) ? trim($officeLocation->address) : '';

        if ($name !== '' && $address !== '') {
            return $name.' - '.$address;
        }

        return $name !== '' ? $name : ($address !== '' ? $address : 'Unnamed Work Location');
    }

    private function formatTimeLabel(mixed $value): string
    {
        if (blank($value)) {
            return '-';
        }

        return substr((string) $value, 0, 5);
    }
}
