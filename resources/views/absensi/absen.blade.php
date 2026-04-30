{{-- ═══════════════════════════════════════
        PANEL BERANDA
═══════════════════════════════════════ --}}
<div id="panel-beranda" class="ag-panel active">

    {{-- Absen Realtime --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div id="ag-live-clock" class="fs-3 fw-semibold text-black">--:--:--</div>
                <div id="ag-live-date" class="small text-muted">Memuat tanggal…</div>
            </div>
            <div class="d-flex gap-2">
                <button
                    id="btn-absen"
                    class="btn btn-sm px-3 btn-primary d-flex align-items-center text-white"
                    data-url="{{ route('absensi.store') }}"
                    data-url-update="{{ route('absensi.update') }}"
                    onclick="agAbsen(1, this, `{{ $absensiHariIni?->jam_masuk }}`, this.dataset.url, this.dataset.urlUpdate)"
                >
                    <span class="ag-label">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Absen Masuk
                    </span>
                    <span class="ag-loading d-none">
                        <span class="spinner-border spinner-border-sm me-1"></span> Loading...
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- Toast Absen --}}
    <div id="ag-toast" class="alert alert-success py-2 small d-none" role="alert"></div>

    {{-- Metric Cards --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="card border mb-0 h-100">
                <div class="card-body text-black p-3">
                    <div class="mb-1">Total Absensi</div>
                    <div class="fs-3 fw-semibold">{{ $absen->count() }} <small class="fs-6 fw-normal text-muted">/ <span id="total_absensi"></span> Hari</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border mb-0 h-100">
                <div class="card-body text-black p-3">
                    <div class="mb-1">Sisa cuti</div>
                    <div class="fs-3 fw-semibold">9 <small class="fs-6 fw-normal text-muted">/ 12 Hari</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border mb-0 h-100">
                <div class="card-body text-black p-3">
                    <div class="mb-1">Izin bulan ini</div>
                    <div class="fs-3 fw-semibold">{{ $izin->where('status_persetujuan', 'Pending')->count()  }} <small class="fs-6 fw-normal text-muted">Pengajuan</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border mb-0 h-100">
                <div class="card-body text-black p-3">
                    <div class="mb-1">Lembur tahun {{ date('Y') }}</div>
                    <div class="fs-3 fw-semibold">{{ $totalLemburJam }} <small class="fs-6 fw-normal text-muted">Jam</small></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Kalender --}}
    <div class="card border shadow-sm rounded-3 mb-3">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <button class="btn btn-sm btn-outline-primary px-3" onclick="agChangeMonth(-1)">&#8249;</button>
                <span class="fw-semibold" id="ag-cal-header"></span>
                <button class="btn btn-sm btn-outline-primary px-3" onclick="agChangeMonth(1)">&#8250;</button>
            </div>

            {{-- Header hari --}}
            <div class="ag-cal-grid mb-1">
                @foreach(['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $d)
                    <div class="small py-1">{{ $d }}</div>
                @endforeach
            </div>

            {{-- Grid hari (diisi JS) --}}
            <div class="ag-cal-grid" id="ag-cal-grid"></div>

            {{-- Legenda --}}
            <div class="d-flex flex-wrap gap-3 mt-3">
                <span class="small d-flex align-items-center gap-1">
                    <span class="ag-dot" style="background:#2beb42;"></span> Absen
                </span>
                <span class="small d-flex align-items-center gap-1">
                    <span class="ag-dot" style="background:#F59E0B;"></span> Izin
                </span>
                <span class="small d-flex align-items-center gap-1">
                    <span class="ag-dot" style="background:#8e3aee;"></span> Lembur
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail Absensi --}}
<div class="modal fade" id="agDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content p-3">
            <span class="d-flex justify-content-between align-items-center">
                <h5 id="agDetailTanggal" class="mb-0"></h5>
                <span class="rounded-pill " id="agDetailStatus"></span>
            </span>

            <hr class="mb-2"/>

            <div>
                <p class="text-black mt-2 mb-2 fs-5"><span id="agDetailMasuk"></span></p>
            </div>

            <div>
                <p class="text-black mb-2 fs-5"><span id="agDetailKeluar"></span></p>
            </div>
        </div>
    </div>
</div>

<script>
    window.agEvent = @json($agEvent);
    window.absensiHariIni = @json($absensiHariIni);
    window.agLembur = @json($lembur->where('status_persetujuan', 'Disetujui')->values());
    window.agIzin = @json($izin->where('status_persetujuan', 'Disetujui')->values());
</script>
<script src="{{ asset('assets/js/agenda/absensi.js') }}"></script>
