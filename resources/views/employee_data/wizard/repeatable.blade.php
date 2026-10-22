{{--
    A repeatable row group (e.g. education history). Inputs are named "{group}[{index}][{field}]" and the row id is kept
    in a hidden input so a save updates the row instead of creating a new one. A blank row is shown when there is no data.
    Params: group, fields (list of field definitions for employee_data.wizard.field), rows, actionColumn.
--}}
@php
    $repeatableRows = collect($rows ?? [])->values();

    if ($repeatableRows->isEmpty()) {
        $repeatableRows = collect([[]]);
    }
@endphp
<div class="js-repeatable" data-repeatable-group="{{ $group }}">
    <div class="js-repeatable-rows">
        @foreach ($repeatableRows as $rowIndex => $rowValues)
            @include('employee_data.wizard.repeatable-row', ['rowIndex' => $rowIndex, 'rowValues' => $rowValues])
        @endforeach
    </div>
    <template class="js-repeatable-template">
        @include('employee_data.wizard.repeatable-row', ['rowIndex' => '__INDEX__', 'rowValues' => []])
    </template>
</div>
