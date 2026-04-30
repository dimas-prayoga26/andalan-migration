{{-- ═══════════════════════════════════════
        PANEL CUTI
═══════════════════════════════════════ --}}
<div id="panel-cuti" class="ag-panel">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <span class="fw-semibold">Riwayat Cuti</span>
        <button class="btn btn-sm px-3" style="background:#1E3FB4;color:#fff;" onclick="agShowForm('cuti')">+ Ajukan Cuti</button>
    </div>

    {{-- Kuota bar (Bootstrap progress) --}}
    <div class="card border shadow-sm rounded-3 mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-1">
                <span class="small text-muted">Kuota cuti tahunan</span>
                <span class="small fw-semibold">9 / 12 hari</span>
            </div>
            <div class="progress" style="height:6px;">
                <div class="progress-bar" style="width:25%;background:#1E3FB4;" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <p class="small text-muted mt-1 mb-0">3 hari telah digunakan &middot; Kuota bertambah 1 setiap bulan</p>
        </div>
    </div>

    {{-- Form Cuti --}}
    <div id="form-cuti" class="card border shadow-sm rounded-3 mb-3 d-none">
        <div class="card-body">
            <p class="fw-semibold mb-3">Form Pengajuan Cuti</p>
            <form action="#" method="POST">
                @csrf
                <div class="row g-2 mb-3">
                    <div class="col-12 col-sm-6">
                        <label class="form-label small">Tanggal mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label small">Tanggal selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control form-control-sm" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Keperluan cuti</label>
                    <textarea name="keperluan" class="form-control form-control-sm" rows="3" placeholder="Tuliskan keperluan cuti…" required></textarea>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="agHideForm('cuti')">Batal</button>
                    <button type="submit" class="btn btn-sm" style="background:#1E3FB4;color:#fff;">Kirim Pengajuan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Daftar Cuti --}}
    <div class="d-flex flex-column gap-2">
        <div class="card border shadow-none">
            <div class="card-body p-3 d-flex align-items-center gap-3 flex-wrap">
                <div class="rounded-2 d-flex align-items-center justify-content-center shrink-0" style="width:38px;height:38px;background:#EDE9FE;font-size:18px;">🏖️</div>
                <div class="grow">
                    <div class="fw-medium small">Cuti tahunan</div>
                    <div class="text-muted" style="font-size:11px;">5–7 Mei 2026 &middot; 3 hari</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill badge-pending">Menunggu</span>
                    <button class="btn btn-sm btn-outline-primary" style="font-size:11px;" onclick="agShowForm('cuti')">Edit</button>
                    <form action="#" method="POST" class="d-inline" onsubmit="return confirm('Batalkan pengajuan cuti ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size:11px;">Batalkan</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="card border shadow-none">
            <div class="card-body p-3 d-flex align-items-center gap-3 flex-wrap">
                <div class="rounded-2 d-flex align-items-center justify-content-center shrink-0" style="width:38px;height:38px;background:#EDE9FE;font-size:18px;">🏖️</div>
                <div class="grow">
                    <div class="fw-medium small">Cuti tahunan</div>
                    <div class="text-muted" style="font-size:11px;">10–11 Jan 2026 &middot; 2 hari</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill badge-approved">Disetujui</span>
                    <button class="btn btn-sm btn-outline-primary" style="font-size:11px;">Detail</button>
                </div>
            </div>
        </div>
    </div>
</div>
