<?php

namespace App\Http\Requests\Mail;

use App\Models\Company;
use App\Models\Employee;
use App\Models\MailAccessAccount;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMailAccessAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->hasAnyPositionPermission(['view-email-management']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique((new MailAccessAccount)->getTable(), 'email'),
            ],
            'type' => ['required', 'string', Rule::in($this->allowedMailTypeValues())],
            'company_id' => [
                Rule::requiredIf(fn (): bool => $this->input('type') !== MailAccessAccount::TYPE_PERSONAL),
                'nullable',
                'string',
                Rule::exists((new Company)->getTable(), 'id'),
            ],
            'employee_id' => [
                Rule::requiredIf(fn (): bool => $this->input('type') === MailAccessAccount::TYPE_PERSONAL),
                'nullable',
                'string',
                Rule::exists((new Employee)->getTable(), 'id'),
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function allowedMailTypeValues(): array
    {
        $user = $this->user();

        if ($user instanceof User && $user->isSuperAdministrator()) {
            return [
                MailAccessAccount::TYPE_PERSONAL,
                MailAccessAccount::TYPE_DEPARTMENT,
            ];
        }

        return [
            MailAccessAccount::TYPE_PERSONAL,
        ];
    }
}
