{{--
    Address inputs of one address type (ktp|domicile). Province → regency → district → village are cascading selects filled
    from the region API; the stored names stay selectable even when the API cannot be reached.
    Params: addressType, addressValues, isAddressRequired.
--}}
@php
    $regionLevels = [
        'province' => 'Provinsi',
        'regency' => 'Kabupaten/Kota',
        'subdistrict' => 'Kecamatan',
        'village' => 'Kelurahan / Desa',
    ];
@endphp
@foreach ($regionLevels as $regionLevel => $regionLabel)
    @php
        $selectedRegionName = $addressValues[$regionLevel] ?? null;
    @endphp
    <div class="col-lg-2 mb-2">
        <div class="mb-3">
            <label class="text-label form-label">{{ $regionLabel }}</label>
            <select name="addresses[{{ $addressType }}][{{ $regionLevel }}]" class="form-select js-region-select" data-region-level="{{ $regionLevel }}" data-live-search="true" data-size="8">
                <option value="">Pilih {{ $regionLabel }}</option>
                @if (filled($selectedRegionName))
                    <option value="{{ $selectedRegionName }}" selected>{{ $selectedRegionName }}</option>
                @endif
            </select>
        </div>
    </div>
@endforeach
<div class="col-lg-4 mb-2">
    <div class="mb-3">
        <label class="text-label form-label">Nama Jalan, No Rumah dan RT/RW{!! ($isAddressRequired ?? false) ? '<span class="employee-data-required-mark">*</span>' : '' !!}</label>
        <input type="text" name="addresses[{{ $addressType }}][address_line]" class="form-control" value="{{ $addressValues['address_line'] ?? '' }}" placeholder="Nama Jalan, No Rumah dan RT/RW">
    </div>
</div>
