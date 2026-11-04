@php
    $participantGroupValue = $selectedParticipantGroupValue ?? 'all_staff';
    $participantIds = collect($selectedParticipants ?? [])
        ->map(fn (mixed $participantId): string => (string) $participantId)
        ->all();
@endphp

<label class="form-label">Invited Participants</label>
<div class="hr-participant-picker" data-hr-participant-picker>
    <input type="hidden" class="hr-participant-group-input" name="participant_group" value="{{ $participantGroupValue }}">
    <button type="button" class="form-control hr-participant-toggle" aria-expanded="false">
        <span class="hr-participant-summary">Nothing selected</span>
        <i class="fa fa-chevron-down"></i>
    </button>
    <div class="hr-participant-menu">
        <input type="text" class="hr-participant-search" placeholder="Search staff">

        <div class="hr-participant-section-title">Group</div>
        <button type="button" class="hr-participant-group-option" data-group-option="all_staff">
            <span>All Staff</span>
            <i class="fa fa-check hr-participant-check"></i>
        </button>
        <button type="button" class="hr-participant-group-option" data-group-option="bod">
            <span>BOD</span>
            <i class="fa fa-check hr-participant-check"></i>
        </button>
        <button type="button" class="hr-participant-group-option" data-group-option="custom">
            <span>Custom</span>
            <i class="fa fa-check hr-participant-check"></i>
        </button>

        <div class="hr-participant-divider"></div>
        <div class="hr-participant-section-title">Employees</div>
        <div class="hr-participant-employees">
            @forelse ($employeeOptions as $employee)
                <label class="hr-participant-employee-option" data-employee-option data-employee-name="{{ strtolower((string) $employee['name']) }}">
                    <input type="checkbox" name="participant_ids[]" value="{{ $employee['id'] }}" class="hr-participant-employee-checkbox" data-supervisor="{{ $employee['is_supervisor'] ? 'true' : 'false' }}" @checked(in_array((string) $employee['id'], $participantIds, true))>
                    <span>{{ $employee['name'] }}</span>
                </label>
            @empty
                <div class="hr-participant-empty">No active staff available.</div>
            @endforelse
        </div>
    </div>
</div>
