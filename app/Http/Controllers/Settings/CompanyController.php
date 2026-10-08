<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        return view('settings.companies.index', [
            'pageTitle' => 'Company',
        ]);
    }

    public function datatable(): JsonResponse
    {
        $query = Company::query()
            ->select(['id', 'name', 'legal_name', 'website', 'is_active']);

        return DataTables::eloquent($query)
            ->editColumn('legal_name', fn (Company $company): string => $company->legal_name ?: '-')
            ->editColumn('website', fn (Company $company): string => $company->website ?: '-')
            ->toJson();
    }

    public function create(): View
    {
        return view('settings.companies.form', [
            'company' => null,
            'mode' => 'create',
            'pageTitle' => 'Add Company',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Company::query()->create($this->validatedData($request));

        return redirect()
            ->route('settings.companies.index')
            ->with('status', 'Company has been added.');
    }

    public function edit(Company $company): View
    {
        return view('settings.companies.form', [
            'company' => $company,
            'mode' => 'edit',
            'pageTitle' => 'Update Company',
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $company->update($this->validatedData($request, $company));

        return redirect()
            ->route('settings.companies.index')
            ->with('status', 'Company has been updated.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $isUsedByUser = $company->users()->exists();
        $isUsedByDeployment = DB::table('employee_deployments')
            ->where('current_company_id', $company->id)
            ->exists();
        $isUsedByProject = $company->projects()->exists();
        $isUsedByMailAccount = $company->mailAccessAccounts()->exists();
        $isUsedByJobVacancy = $company->jobVacancies()->exists();

        if ($isUsedByUser || $isUsedByDeployment || $isUsedByProject || $isUsedByMailAccount || $isUsedByJobVacancy) {
            return back()->with('error', 'Company is still used by employee, project, email, or job vacancy data.');
        }

        $company->delete();

        return redirect()
            ->route('settings.companies.index')
            ->with('status', 'Company has been deleted.');
    }

    /**
     * @return array{name: string, legal_name: string|null, website: string|null, is_active: bool}
     */
    private function validatedData(Request $request, ?Company $company = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('companies', 'name')->ignore($company?->id, 'id'),
            ],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
