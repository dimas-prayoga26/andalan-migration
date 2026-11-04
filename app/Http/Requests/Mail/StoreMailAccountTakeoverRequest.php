<?php

namespace App\Http\Requests\Mail;

use App\Models\Employee;
use App\Models\MailAccessAccount;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMailAccountTakeoverRequest extends FormRequest
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
            'mail_business_account_id' => ['required', 'integer', Rule::exists((new MailAccessAccount)->getTable(), 'id')],
            'target_employee_id' => ['required', 'string', Rule::exists((new Employee)->getTable(), 'id')],
        ];
    }
}
