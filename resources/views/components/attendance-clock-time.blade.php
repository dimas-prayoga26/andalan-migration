{{-- Clock in/out cell value for the daily attendance log. HH:MM times carry translate="no" so browser translation (e.g. Chrome to Indonesian) cannot rewrite "08:00" into "Jam 08:00"; leave and holiday labels stay translatable. --}}
@props(['value', 'badge' => ''])
@php
    $isClockTime = preg_match('/^\d{2}:\d{2}$/', (string) $value) === 1;
@endphp
<span @class(["badge badge-sm badge-{$badge} light fw-bold" => $badge !== '', 'fw-bold' => $badge === ''])@if ($isClockTime) translate="no"@endif>{{ $value }}</span>
