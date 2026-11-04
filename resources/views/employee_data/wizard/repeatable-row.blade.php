<div class="row js-repeatable-row">
    <input type="hidden" name="{{ $group }}[{{ $rowIndex }}][id]" value="{{ $rowValues['id'] ?? '' }}" data-row-field="id">
    @foreach ($fields as $rowField)
        @include('employee_data.wizard.field', [
            'name' => $group.'['.$rowIndex.']['.$rowField['name'].']',
            'value' => $rowValues[$rowField['name']] ?? null,
            'attributes' => 'data-row-field="'.$rowField['name'].'"',
        ] + $rowField)
    @endforeach
    <div class="{{ $actionColumn ?? 'col-lg-2' }} mb-2">
        <div class="mb-3">
            <label class="text-label form-label">Action</label>
            <div class="employee-data-row-actions">
                <button type="button" class="btn btn-success light js-repeatable-add" title="Tambah baris" aria-label="Tambah baris"><i class="fa fa-plus"></i></button>
                <button type="button" class="btn btn-danger light js-repeatable-remove" title="Hapus baris" aria-label="Hapus baris"><i class="fa fa-trash"></i></button>
            </div>
        </div>
    </div>
</div>
