<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class DivisionController extends Controller
{
    public function index(): View
    {
        return view('settings.index', [
            'pageTitle' => 'Division',
            'resourceLabel' => 'Division',
            'routePrefix' => 'settings.divisions',
            'routeParameter' => 'division',
            'datatableRoute' => 'settings.divisions.datatable',
            'tableId' => 'divisionsTable',
            'searchPlaceholder' => 'Search division',
        ]);
    }

    public function datatable(): JsonResponse
    {
        $query = Department::query()
            ->select(['id', 'name', 'status']);

        return DataTables::eloquent($query)
            ->toJson();
    }

    public function create(): View
    {
        return view('settings.form', [
            'item' => null,
            'mode' => 'create',
            'pageTitle' => 'Add Division',
            'resourceLabel' => 'Division',
            'routePrefix' => 'settings.divisions',
            'routeParameter' => 'division',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        Department::query()->create([
            'id' => (string) Str::uuid(),
            'name' => $validated['name'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('settings.divisions.index')
            ->with('status', 'Division has been added.');
    }

    public function edit(Department $division): View
    {
        return view('settings.form', [
            'item' => $division,
            'mode' => 'edit',
            'pageTitle' => 'Update Division',
            'resourceLabel' => 'Division',
            'routePrefix' => 'settings.divisions',
            'routeParameter' => 'division',
        ]);
    }

    public function update(Request $request, Department $division): RedirectResponse
    {
        $validated = $this->validatedData($request, $division);

        $division->update($validated);

        return redirect()
            ->route('settings.divisions.index')
            ->with('status', 'Division has been updated.');
    }

    public function destroy(Department $division): RedirectResponse
    {
        $isUsedByDeployment = DB::table('employee_deployments')
            ->where('current_department_id', $division->id)
            ->exists();

        if ($isUsedByDeployment) {
            return back()->with('error', 'Division is still used by employee deployment data.');
        }

        $division->delete();

        return redirect()
            ->route('settings.divisions.index')
            ->with('status', 'Division has been deleted.');
    }

    /**
     * @return array{name: string, status: string}
     */
    private function validatedData(Request $request, ?Department $division = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')->ignore($division?->id, 'id'),
            ],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ]);
    }
}
