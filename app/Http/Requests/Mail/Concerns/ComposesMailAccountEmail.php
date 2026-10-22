<?php

namespace App\Http\Requests\Mail\Concerns;

use App\Models\MailAccessAccount;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Builds the mail account address from the typed mailbox name and the selected company mail domain.
 */
trait ComposesMailAccountEmail
{
    protected function prepareForValidation(): void
    {
        $localPart = Str::lower(trim((string) $this->input('email_local_part')));
        $domain = Str::lower(trim((string) $this->input('email_domain')));

        $this->merge([
            'email_local_part' => $localPart,
            'email_domain' => $domain,
            'email' => $localPart !== '' && $domain !== '' ? $localPart.'@'.$domain : '',
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function mailAccountEmailPartRules(?MailAccessAccount $mailAccessAccount = null): array
    {
        return [
            'email_local_part' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9._%+-]+$/'],
            'email_domain' => ['required', 'string', Rule::in(MailAccessAccount::selectableMailDomains($mailAccessAccount)->all())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email_local_part' => 'email name',
            'email_domain' => 'email domain',
        ];
    }
}
