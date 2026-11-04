{{-- Three-dot action menu for table rows; the slot holds the <li> dropdown items. Mirrors window.actionMenu() in layouts.commonjs. --}}
<div {{ $attributes->merge(['class' => 'dropdown action-menu']) }}>
    <button type="button" class="btn btn-light action-menu-toggle" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" aria-label="Action" title="Action">
        <i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        {{ $slot }}
    </ul>
</div>
