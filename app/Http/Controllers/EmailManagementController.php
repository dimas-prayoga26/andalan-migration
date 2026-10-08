<?php

namespace App\Http\Controllers;

use App\Http\Requests\Mail\StoreMailAccessAccountRequest;
use App\Http\Requests\Mail\StoreMailAccountTakeoverRequest;
use App\Http\Requests\Mail\UpdateMailAccessAccountRequest;
use App\Models\Company;
use App\Models\Employee;
use App\Models\MailAccessAccount;
use App\Models\MailAccountTakeover;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class EmailManagementController extends Controller
{
    private const DEFAULT_MAIL_ACCESS_PIN = '0000';

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $accounts = $this->visibleMailAccessAccountQuery($request)
            ->with([
                'company:id,name',
                'employee:id,user_id,status',
                'employee.profile:id,employee_id,name,nickname',
                'employee.user:id,username,email',
                'employee.deployment:id,employee_id,current_position_id',
                'employee.deployment.position:id,name',
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('email', 'like', "%{$search}%")
                        ->orWhereHas('employee.profile', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('employee.user', fn ($query) => $query->where('email', 'like', "%{$search}%"));
                });
            })
            ->orderBy('email')
            ->paginate(10, ['*'], 'accounts_page')
            ->withQueryString();

        $takeovers = MailAccountTakeover::query()
            ->whereHas(
                'mailAccessAccount',
                fn (Builder $query): Builder => $this->constrainVisibleMailAccessAccountQuery($query, $request),
            )
            ->with([
                'mailAccessAccount:id,email,employee_id',
                'sourceEmployee:id,user_id,status',
                'sourceEmployee.profile:id,employee_id,name,nickname',
                'sourceEmployee.user:id,username,email',
                'targetEmployee:id,user_id,status',
                'targetEmployee.profile:id,employee_id,name,nickname',
                'targetEmployee.user:id,username,email',
                'assignedByEmployee:id,user_id',
                'assignedByEmployee.profile:id,employee_id,name,nickname',
                'revokedByEmployee:id,user_id',
                'revokedByEmployee.profile:id,employee_id,name,nickname',
            ])
            ->latest()
            ->paginate(10, ['*'], 'takeovers_page')
            ->withQueryString();

        $accountOptions = $this->visibleMailAccessAccountQuery($request)
            ->with([
                'employee:id,user_id',
                'employee.profile:id,employee_id,name,nickname',
                'employee.user:id,username,email',
            ])
            ->orderBy('email')
            ->get(['id', 'email', 'employee_id'])
            ->map(fn (MailAccessAccount $mailAccessAccount): array => [
                'id' => (string) $mailAccessAccount->id,
                'label' => $this->mailAccessAccountStaffLabel($mailAccessAccount),
            ])
            ->sortBy('label')
            ->values()
            ->all();

        $employees = Employee::query()
            ->with([
                'profile:id,employee_id,name,nickname',
                'user:id,username,email',
                'deployment:id,employee_id,current_position_id',
                'deployment.position:id,name',
            ])
            ->get();

        $employeeOptions = $this->employeeOptions($employees);
        $takeoverEmployeeOptions = $this->takeoverEmployeeOptions($employees);

        return view('email-management.index', [
            'accounts' => $accounts,
            'accountOptions' => $accountOptions,
            'takeovers' => $takeovers,
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'employeeOptions' => $employeeOptions,
            'takeoverEmployeeOptions' => $takeoverEmployeeOptions,
            'mailTypeLabels' => MailAccessAccount::typeOptions(),
            'mailTypeOptions' => $this->mailTypeOptions($request),
            'protectedMailAccountEmails' => $this->protectedMailAccountEmails(),
            'search' => $search,
        ]);
    }

    public function storeAccount(StoreMailAccessAccountRequest $request): RedirectResponse
    {
        $data = $this->mailAccessAccountData($request->validated());

        if (! $this->canManageSensitiveMailAccounts($request) && $data['type'] !== MailAccessAccount::TYPE_PERSONAL) {
            return back()
                ->withInput()
                ->with('error', 'Sensitive email account hanya bisa dikelola Super Administrator.');
        }

        $data['pin'] = Hash::make(self::DEFAULT_MAIL_ACCESS_PIN);
        $data['is_active'] = true;

        MailAccessAccount::query()->create($data);

        return redirect()
            ->route('email-management.index')
            ->with('status', 'Email account has been added.');
    }

    public function updateAccount(UpdateMailAccessAccountRequest $request, MailAccessAccount $mailAccessAccount): RedirectResponse
    {
        $data = $this->mailAccessAccountData($request->validated());

        if (! $this->canManageMailAccessAccount($request, $mailAccessAccount, $data['type'])) {
            return back()
                ->withInput()
                ->with('error', 'Sensitive email account hanya bisa dikelola Super Administrator.');
        }

        $data['pin'] = Hash::make(self::DEFAULT_MAIL_ACCESS_PIN);
        $data['is_active'] = true;

        $mailAccessAccount->update($data);

        return redirect()
            ->route('email-management.index')
            ->with('status', 'Email account has been updated.');
    }

    public function destroyAccount(Request $request, MailAccessAccount $mailAccessAccount): RedirectResponse
    {
        if (! $this->canManageMailAccessAccount($request, $mailAccessAccount)) {
            return redirect()
                ->route('email-management.index')
                ->with('error', 'Sensitive email account hanya bisa dikelola Super Administrator.');
        }

        if ($this->protectedMailAccountEmails()->contains($this->normalizeEmail((string) $mailAccessAccount->email))) {
            return redirect()
                ->route('email-management.index')
                ->with('error', 'Email dari config/env tidak bisa dihapus.');
        }

        $mailAccessAccount->delete();

        return redirect()
            ->route('email-management.index')
            ->with('status', 'Email account has been deleted.');
    }

    public function storeTakeover(StoreMailAccountTakeoverRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $mailAccessAccount = $this->visibleMailAccessAccountQuery($request)->findOrFail($data['mail_access_account_id']);
        $sourceEmployeeId = $mailAccessAccount->employee_id;

        if ($sourceEmployeeId !== null && $sourceEmployeeId === $data['target_employee_id']) {
            return back()
                ->withInput()
                ->with('error', 'Target employee cannot be the same as source employee.');
        }

        $alreadyReadable = MailAccountTakeover::query()
            ->readable()
            ->where('mail_access_account_id', $mailAccessAccount->id)
            ->where('target_employee_id', $data['target_employee_id'])
            ->exists();

        if ($alreadyReadable) {
            return back()
                ->withInput()
                ->with('error', 'This employee already has active read access for the selected email.');
        }

        MailAccountTakeover::query()->create([
            'mail_access_account_id' => $mailAccessAccount->id,
            'source_employee_id' => $sourceEmployeeId,
            'target_employee_id' => $data['target_employee_id'],
            'assigned_by_employee_id' => $this->currentEmployeeId($request),
            'can_read' => true,
            'starts_at' => null,
            'ends_at' => null,
            'reason' => null,
        ]);

        return redirect()
            ->route('email-management.index')
            ->with('status', 'Email takeover access has been assigned.');
    }

    public function updateTakeover(StoreMailAccountTakeoverRequest $request, MailAccountTakeover $mailAccountTakeover): RedirectResponse
    {
        $data = $request->validated();
        $mailAccessAccount = $this->visibleMailAccessAccountQuery($request)->findOrFail($data['mail_access_account_id']);
        $sourceEmployeeId = $mailAccessAccount->employee_id;

        if ($sourceEmployeeId !== null && $sourceEmployeeId === $data['target_employee_id']) {
            return back()
                ->withInput()
                ->with('error', 'Target employee cannot be the same as source employee.');
        }

        $alreadyReadable = MailAccountTakeover::query()
            ->readable()
            ->whereKeyNot($mailAccountTakeover->id)
            ->where('mail_access_account_id', $mailAccessAccount->id)
            ->where('target_employee_id', $data['target_employee_id'])
            ->exists();

        if ($alreadyReadable) {
            return back()
                ->withInput()
                ->with('error', 'This employee already has active read access for the selected email.');
        }

        $mailAccountTakeover->update([
            'mail_access_account_id' => $mailAccessAccount->id,
            'source_employee_id' => $sourceEmployeeId,
            'target_employee_id' => $data['target_employee_id'],
            'can_read' => true,
            'starts_at' => null,
            'ends_at' => null,
            'reason' => null,
            'revoked_at' => null,
            'revoked_by_employee_id' => null,
        ]);

        return redirect()
            ->route('email-management.index')
            ->with('status', 'Email takeover access has been updated.');
    }

    public function revokeTakeover(Request $request, MailAccountTakeover $mailAccountTakeover): RedirectResponse
    {
        $mailAccountTakeover->update([
            'can_read' => false,
            'revoked_at' => now(),
            'revoked_by_employee_id' => $this->currentEmployeeId($request),
        ]);

        return redirect()
            ->route('email-management.index')
            ->with('status', 'Email takeover access has been revoked.');
    }

    public function destroyTakeover(MailAccountTakeover $mailAccountTakeover): RedirectResponse
    {
        $mailAccountTakeover->delete();

        return redirect()
            ->route('email-management.index')
            ->with('status', 'Email takeover access has been deleted.');
    }

    /**
     * @param  EloquentCollection<int, Employee>  $employees
     * @return array<int, array{id: string, label: string}>
     */
    private function employeeOptions(EloquentCollection $employees): array
    {
        return $employees
            ->map(fn (Employee $employee): array => [
                'id' => (string) $employee->id,
                'label' => $this->employeeLabel($employee),
            ])
            ->sortBy('label')
            ->values()
            ->all();
    }

    /**
     * @param  EloquentCollection<int, Employee>  $employees
     * @return array<int, array{id: string, label: string}>
     */
    private function takeoverEmployeeOptions(EloquentCollection $employees): array
    {
        return $employees
            ->map(fn (Employee $employee): array => [
                'id' => (string) $employee->id,
                'label' => $this->employeeBusinessEmailLabel($employee),
            ])
            ->sortBy('label')
            ->values()
            ->all();
    }

    private function employeeLabel(?Employee $employee): string
    {
        if (! $employee instanceof Employee) {
            return '-';
        }

        $name = $this->employeeDisplayName($employee);
        $email = trim((string) ($employee->user?->email ?: $employee->user?->username));
        $position = trim((string) $employee->deployment?->position?->name);

        $label = $name !== '' ? $name : ($email !== '' ? $email : (string) $employee->id);

        return $position !== '' ? "{$label} - {$position}" : $label;
    }

    private function mailAccessAccountStaffLabel(MailAccessAccount $mailAccessAccount): string
    {
        $employeeName = $this->employeeDisplayName($mailAccessAccount->employee);

        return $this->staffEmailLabel($employeeName, (string) $mailAccessAccount->email, (string) $mailAccessAccount->email);
    }

    private function employeeBusinessEmailLabel(?Employee $employee): string
    {
        if (! $employee instanceof Employee) {
            return '-';
        }

        return $this->staffEmailLabel(
            $this->employeeDisplayName($employee),
            (string) ($employee->user?->email ?: $employee->user?->username),
            (string) $employee->id,
        );
    }

    private function employeeDisplayName(?Employee $employee): string
    {
        if (! $employee instanceof Employee) {
            return '';
        }

        return trim((string) ($employee->profile?->name ?: $employee->profile?->nickname));
    }

    private function staffEmailLabel(string $name, string $email, string $fallback): string
    {
        $name = trim($name);
        $email = trim($email);

        if ($name !== '' && $email !== '') {
            return "{$name} - {$email}";
        }

        if ($name !== '') {
            return $name;
        }

        if ($email !== '') {
            return $email;
        }

        return $fallback;
    }

    private function currentEmployeeId(Request $request): ?string
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return null;
        }

        $user->loadMissing('employee:id,user_id');

        return $user->employee?->id;
    }

    /**
     * @param  array{email:string,type:string,company_id?:string|null,employee_id?:string|null}  $data
     * @return array{email:string,type:string,company_id:?string,employee_id:?string}
     */
    private function mailAccessAccountData(array $data): array
    {
        if ($data['type'] === MailAccessAccount::TYPE_PERSONAL) {
            $data['company_id'] = null;
        } else {
            $data['employee_id'] = null;
        }

        return [
            'email' => $data['email'],
            'type' => $data['type'],
            'company_id' => $data['company_id'] ?? null,
            'employee_id' => $data['employee_id'] ?? null,
        ];
    }

    private function visibleMailAccessAccountQuery(Request $request): Builder
    {
        return $this->constrainVisibleMailAccessAccountQuery(MailAccessAccount::query(), $request);
    }

    private function constrainVisibleMailAccessAccountQuery(Builder $query, Request $request): Builder
    {
        $query->visibleForMailAccess();
        $query->whereIn('type', array_keys($this->mailTypeOptions($request)));

        return $query;
    }

    private function canManageSensitiveMailAccounts(Request $request): bool
    {
        $user = $request->user();

        return $user instanceof User && $user->isSuperAdministrator();
    }

    private function canManageMailAccessAccount(Request $request, MailAccessAccount $mailAccessAccount, ?string $newType = null): bool
    {
        if ($this->canManageSensitiveMailAccounts($request)) {
            return true;
        }

        return $mailAccessAccount->type === MailAccessAccount::TYPE_PERSONAL
            && ($newType === null || $newType === MailAccessAccount::TYPE_PERSONAL);
    }

    /**
     * @return array<string, string>
     */
    private function mailTypeOptions(Request $request): array
    {
        if ($this->canManageSensitiveMailAccounts($request)) {
            return [
                MailAccessAccount::TYPE_PERSONAL => MailAccessAccount::typeOptions()[MailAccessAccount::TYPE_PERSONAL],
                MailAccessAccount::TYPE_DEPARTMENT => MailAccessAccount::typeOptions()[MailAccessAccount::TYPE_DEPARTMENT],
            ];
        }

        return [
            MailAccessAccount::TYPE_PERSONAL => MailAccessAccount::typeOptions()[MailAccessAccount::TYPE_PERSONAL],
        ];
    }

    /**
     * @return Collection<int, string>
     */
    private function protectedMailAccountEmails(): Collection
    {
        $brandEmails = collect(config('career_brands.brands', []))
            ->filter(fn (mixed $brand): bool => is_array($brand))
            ->flatMap(function (array $brand): array {
                $brandEmail = $this->normalizeEmail((string) ($brand['email'] ?? ''));
                $brandDomain = $this->emailDomain($brandEmail);

                return [
                    $brandEmail,
                    $brandDomain !== '' ? 'hr@'.$brandDomain : '',
                ];
            });

        return MailAccessAccount::configuredInboxEmails()
            ->merge($brandEmails)
            ->filter(fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values();
    }

    private function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    private function emailDomain(string $email): string
    {
        $parts = explode('@', $this->normalizeEmail($email));

        if (count($parts) !== 2) {
            return '';
        }

        return preg_replace('/^www\./', '', $parts[1]) ?? '';
    }
}
