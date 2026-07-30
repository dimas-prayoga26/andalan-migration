<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PositionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $items = Position::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('settings.index', [
            'items' => $items,
            'search' => $search,
            'pageTitle' => 'Position',
            'resourceLabel' => 'Position',
            'routePrefix' => 'settings.positions',
            'routeParameter' => 'position',
        ]);
    }

    public function create(): View
    {
        return view('settings.form', [
            'item' => null,
            'mode' => 'create',
            'pageTitle' => 'Add Position',
            'resourceLabel' => 'Position',
            'routePrefix' => 'settings.positions',
            'routeParameter' => 'position',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Position::query()->create($this->validatedData($request));

        return redirect()
            ->route('settings.positions.index')
            ->with('status', 'Position has been added.');
    }

    public function edit(Position $position): View
    {
        return view('settings.form', [
            'item' => $position,
            'mode' => 'edit',
            'pageTitle' => 'Update Position',
            'resourceLabel' => 'Position',
            'routePrefix' => 'settings.positions',
            'routeParameter' => 'position',
        ]);
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        $position->update($this->validatedData($request, $position));

        return redirect()
            ->route('settings.positions.index')
            ->with('status', 'Position has been updated.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        $isUsedByDeployment = DB::table('employee_deployments')
            ->where('current_position_id', $position->id)
            ->exists();

        $isUsedByDeploymentHistory = DB::table('employee_deployment_positions')
            ->where('position_id', $position->id)
            ->exists();

        if ($isUsedByDeployment || $isUsedByDeploymentHistory) {
            return back()->with('error', 'Position is still used by employee deployment data.');
        }

        $position->delete();

        return redirect()
            ->route('settings.positions.index')
            ->with('status', 'Position has been deleted.');
    }

    /**
     * @return array{name: string, status: string}
     */
    private function validatedData(Request $request, ?Position $position = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('positions', 'name')->ignore($position?->id, 'id'),
            ],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ]);
    }
}
