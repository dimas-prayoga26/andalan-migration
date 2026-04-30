@extends('layouts.main')

@section('title', 'Absensi Andalan')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/absensi.css') }}">
@endsection

@section('navbarTitle', 'Data Absensi')

@section('content')
<div class="container-fluid px-0">

    {{-- ── Tab Navigation ── --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        <button class="btn btn-sm btn-outline-primary rounded-pill px-3 ag-tab active" onclick="agSwitchTab('beranda', this)">Beranda</button>
        <button class="btn btn-sm btn-outline-primary rounded-pill px-3 ag-tab" onclick="agSwitchTab('izin', this)">Izin</button>
        <button class="btn btn-sm btn-outline-primary rounded-pill px-3 ag-tab" onclick="agSwitchTab('cuti', this)">Cuti</button>
        <button class="btn btn-sm btn-outline-primary rounded-pill px-3 ag-tab" onclick="agSwitchTab('sakit', this)">Sakit</button>
    </div>

    @include('absensi.absen')

    @include('absensi.izin')

    @include('absensi.cuti')

    @include('absensi.sakit')
</div>
@endsection

@section('script')
<script>
// ── TAB ─────────────────────────────────
function agSwitchTab(name, el) {
    document.querySelectorAll('.ag-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    document.querySelectorAll('.ag-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('panel-' + name).classList.add('active');
}
</script>
@endsection
