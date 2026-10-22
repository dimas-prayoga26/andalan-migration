{{--
    One input of the employee data wizard.
    Params: name, label, value, type (text|email|url|number|date|select|textarea), column, options (select), required, disabled,
    placeholder, help, attributes (extra HTML attributes), inputGroupText (prefix, e.g. "Rp").
--}}
@php
    $fieldType = $type ?? 'text';
    $fieldValue = $value ?? null;
    $fieldOptions = $options ?? [];
    $fieldIsMultiple = $multiple ?? false;
    $fieldSelectedValues = collect($fieldIsMultiple ? (array) $fieldValue : [$fieldValue])
        ->filter(fn (mixed $selectedValue): bool => filled($selectedValue))
        ->map(fn (mixed $selectedValue): string => (string) $selectedValue);
    $fieldUnknownValues = $fieldSelectedValues->reject(fn (string $selectedValue): bool => array_key_exists($selectedValue, $fieldOptions));
    $fieldPlaceholder = $placeholder ?? $label;
    $fieldAttributes = $attributes ?? '';
    $fieldIsDisabled = $disabled ?? false;
@endphp
<div class="{{ $column ?? 'col-lg-3' }} mb-2">
    <div class="mb-3">
        <label class="text-label form-label">{{ $label }}@if ($required ?? false)<span class="employee-data-required-mark">*</span>@endif</label>
        @if ($fieldType === 'select')
            <select name="{{ $name }}{{ $fieldIsMultiple ? '[]' : '' }}" class="form-select" data-size="8" @if ($fieldIsMultiple) multiple @endif @if (count($fieldOptions) > 8) data-live-search="true" @endif @disabled($fieldIsDisabled) {!! $fieldAttributes !!}>
                @unless ($fieldIsMultiple)
                    <option value="">{{ $placeholder ?? 'Pilih '.$label }}</option>
                @endunless
                @foreach ($fieldOptions as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" @selected($fieldSelectedValues->contains((string) $optionValue))>{{ $optionLabel }}</option>
                @endforeach
                @foreach ($fieldUnknownValues as $unknownValue)
                    <option value="{{ $unknownValue }}" selected>{{ $unknownValue }}</option>
                @endforeach
            </select>
        @elseif ($fieldType === 'textarea')
            <textarea name="{{ $name }}" class="form-control" rows="2" placeholder="{{ $fieldPlaceholder }}" @disabled($fieldIsDisabled) {!! $fieldAttributes !!}>{{ $fieldValue }}</textarea>
        @elseif (filled($inputGroupText ?? null))
            <div class="input-group">
                <span class="input-group-text">{{ $inputGroupText }}</span>
                <input type="{{ $fieldType }}" name="{{ $name }}" class="form-control" value="{{ $fieldValue }}" placeholder="{{ $fieldPlaceholder }}" @disabled($fieldIsDisabled) {!! $fieldAttributes !!}>
            </div>
        @else
            <input type="{{ $fieldType }}" name="{{ $name }}" class="form-control" value="{{ $fieldValue }}" placeholder="{{ $fieldPlaceholder }}" @disabled($fieldIsDisabled) {!! $fieldAttributes !!}>
        @endif
        @if (filled($help ?? null))
            <small class="text-muted">{{ $help }}</small>
        @endif
    </div>
</div>
