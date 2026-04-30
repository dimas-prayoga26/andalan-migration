{{-- ═══════════════════════════════════════
        PANEL SAKIT
═══════════════════════════════════════ --}}
<div id="panel-sakit" class="ag-panel">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <span class="fw-semibold">Riwayat Sakit</span>
        <button class="btn btn-sm px-3" style="background:#1E3FB4;color:#fff;" onclick="agShowForm('sakit')">+ Laporkan Sakit</button>
    </div>

    {{-- Form Sakit --}}
    <div id="form-sakit" class="card border shadow-sm rounded-3 mb-3 d-none">
        <div class="card-body">
            <p class="fw-semibold mb-3">Form Laporan Sakit</p>
            <form action="#" method="POST" enctype="multipart/form-data">
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
                    <label class="form-label small">Keluhan / Diagnosis</label>
                    <textarea name="keluhan" class="form-control form-control-sm" rows="2" placeholder="Contoh: demam, flu, maag…" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Upload surat dokter <span class="text-muted">(opsional)</span></label>
                    <input type="file" name="surat_dokter" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="agHideForm('sakit')">Batal</button>
                    <button type="submit" class="btn btn-sm" style="background:#1E3FB4;color:#fff;">Kirim Laporan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Daftar Sakit --}}
    <div class="d-flex flex-column gap-2">
        <div class="card border shadow-none">
            <div class="card-body p-3 d-flex align-items-center gap-3 flex-wrap">
                <div class="rounded-2 d-flex align-items-center justify-content-center shrink-0" style="width:38px;height:38px;background:#FEE2E2;font-size:18px;">🤒</div>
                <div class="grow">
                    <div class="fw-medium small">Demam &amp; flu</div>
                    <div class="text-muted" style="font-size:11px;">12–13 Feb 2026 &middot; 2 hari &middot; Ada surat dokter</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill badge-approved">Disetujui</span>
                    <button class="btn btn-sm btn-outline-primary" style="font-size:11px;">Detail</button>
                </div>
            </div>
        </div>
        <div class="card border shadow-none">
            <div class="card-body p-3 d-flex align-items-center gap-3 flex-wrap">
                <div class="rounded-2 d-flex align-items-center justify-content-center shrink-0" style="width:38px;height:38px;background:#FEE2E2;font-size:18px;">🤒</div>
                <div class="grow">
                    <div class="fw-medium small">Maag kambuh</div>
                    <div class="text-muted" style="font-size:11px;">3 Nov 2025 &middot; 1 hari</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill badge-approved">Disetujui</span>
                    <button class="btn btn-sm btn-outline-primary" style="font-size:11px;">Detail</button>
                </div>
            </div>
        </div>
    </div>
</div>
