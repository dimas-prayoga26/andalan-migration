@php
    $accountId = $account?->id ?? 'create';
    $personalType = \App\Models\MailAccessAccount::TYPE_PERSONAL;
    $selectedType = old('type', $account?->type ?? \App\Models\MailAccessAccount::TYPE_PERSONAL);
    $selectedCompanyId = (string) old('company_id', $account?->company_id ?? '');
    $selectedEmployeeId = (string) old('employee_id', $account?->employee_id ?? '');
    $isPersonalType = $selectedType === $personalType;
@endphp

<div class="row g-3" data-mail-account-form>
    <div class="col-12">
        <label class="form-label" for="email-{{ $accountId }}">Email</label>
        <input id="email-{{ $accountId }}" name="email" type="email" class="form-control" value="{{ old('email', $account?->email) }}" placeholder="dimas@tims.co.id" required>
    </div>
    <div class="col-12">
        <label class="form-label" for="type-{{ $accountId }}">Type</label>
        <select id="type-{{ $accountId }}" name="type" class="form-select js-skip-selectpicker" data-mail-account-type required>
            @foreach ($mailTypeOptions as $value => $label)
                <option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 @if ($isPersonalType) d-none @endif" data-mail-account-company-field>
        <label class="form-label" for="company-id-{{ $accountId }}">Company</label>
        <select id="company-id-{{ $accountId }}" name="company_id" class="form-select js-skip-selectpicker" @disabled($isPersonalType) @required(! $isPersonalType)>
            <option value="">Select company</option>
            @foreach ($companies as $company)
                <option value="{{ $company->id }}" @selected($selectedCompanyId === (string) $company->id)>{{ $company->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 @if (! $isPersonalType) d-none @endif" data-mail-account-owner-field>
        <label class="form-label" for="employee-id-{{ $accountId }}">Owner Staff</label>
        <select id="employee-id-{{ $accountId }}" name="employee_id" class="form-select js-skip-selectpicker" @disabled(! $isPersonalType) @required($isPersonalType)>
            <option value="">Select owner staff</option>
            @foreach ($employeeOptions as $employeeOption)
                <option value="{{ $employeeOption['id'] }}" @selected($selectedEmployeeId === $employeeOption['id'])>{{ $employeeOption['label'] }}</option>
            @endforeach
        </select>
    </div>
</div>
