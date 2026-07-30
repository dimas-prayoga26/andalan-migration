<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DivisionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $items = Department::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('settings.index', [
            'items' => $items,
            'search' => $search,
            'pageTitle' => 'Division',
            'resourceLabel' => 'Division',
            'routePrefix' => 'settings.divisions',
            'routeParameter' => 'division',
        ]);
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
