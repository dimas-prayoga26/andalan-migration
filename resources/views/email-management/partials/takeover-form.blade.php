@php
    $takeoverId = $takeover?->id ?? 'create';
    $selectedAccountId = (string) old('mail_business_account_id', $takeover?->mail_business_account_id ?? '');
    $selectedEmployeeId = (string) old('target_employee_id', $takeover?->target_employee_id ?? '');
@endphp

<div class="row g-3">
    <div class="col-12">
        <label class="form-label" for="mail-business-account-id-{{ $takeoverId }}">Staff Lama</label>
        <select id="mail-business-account-id-{{ $takeoverId }}" name="mail_business_account_id" class="form-select" required>
            <option value="">Choose staff</option>
            @foreach ($accountOptions as $account)
                <option value="{{ $account['id'] }}" @selected($selectedAccountId === $account['id'])>{{ $account['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label" for="target-employee-id-{{ $takeoverId }}">Staff Pengganti</label>
        <select id="target-employee-id-{{ $takeoverId }}" name="target_employee_id" class="form-select" required>
            <option value="">Choose staff</option>
            @foreach ($employeeOptions as $employeeOption)
                <option value="{{ $employeeOption['id'] }}" @selected($selectedEmployeeId === $employeeOption['id'])>{{ $employeeOption['label'] }}</option>
            @endforeach
        </select>
    </div>
</div>
