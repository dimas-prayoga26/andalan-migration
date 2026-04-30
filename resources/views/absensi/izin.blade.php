{{-- ═══════════════════════════════════════
        PANEL IZIN
═══════════════════════════════════════ --}}
<div id="panel-izin" class="ag-panel">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <span class="fw-semibold">Riwayat Izin</span>
        <button class="btn btn-sm px-3" style="background:#1E3FB4;color:#fff;" onclick="agShowForm('izin')">+ Ajukan Izin</button>
    </div>

    {{-- Form Izin --}}
    <div id="form-izin" class="card border shadow-sm rounded-3 mb-3 d-none">
        <div class="card-body">
            <p class="fw-semibold mb-3">Form Pengajuan Izin</p>
            <form action="#" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label small">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control form-control-sm" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Alasan izin</label>
                    <textarea name="alasan" class="form-control form-control-sm" rows="3" placeholder="Tuliskan alasan izin…" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Lampiran <span class="text-muted">(opsional)</span></label>
                    <input type="file" name="lampiran" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="agHideForm('izin')">Batal</button>
                    <button type="submit" class="btn btn-sm" style="background:#1E3FB4;color:#fff;">Kirim Pengajuan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Daftar Izin — @foreach($izinList as $item) --}}
    <div class="d-flex flex-column gap-2">
        <div class="card border shadow-none">
            <div class="card-body p-3 d-flex align-items-center gap-3 flex-wrap">
                <div class="rounded-2 d-flex align-items-center justify-content-center shrink-0" style="width:38px;height:38px;background:#FEF3C7;font-size:18px;">📋</div>
                <div class="grow">
                    <div class="fw-medium small">Izin keperluan keluarga</div>
                    <div class="text-muted" style="font-size:11px;">10 April 2026 &middot; 1 hari</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill badge-approved">Disetujui</span>
                    <button class="btn btn-sm btn-outline-primary" style="font-size:11px;">Detail</button>
                </div>
            </div>
        </div>
        <div class="card border shadow-none">
            <div class="card-body p-3 d-flex align-items-center gap-3 flex-wrap">
                <div class="rounded-2 d-flex align-items-center justify-content-center shrink-0" style="width:38px;height:38px;background:#FEF3C7;font-size:18px;">📋</div>
                <div class="grow">
                    <div class="fw-medium small">Izin urusan bank</div>
                    <div class="text-muted" style="font-size:11px;">28 April 2026 &middot; 1 hari</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill badge-pending">Menunggu</span>
                    <button class="btn btn-sm btn-outline-primary" style="font-size:11px;" onclick="agShowForm('izin')">Edit</button>
                    <form action="#" method="POST" class="d-inline" onsubmit="return confirm('Batalkan pengajuan ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size:11px;">Batalkan</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="card border shadow-none">
            <div class="card-body p-3 d-flex align-items-center gap-3 flex-wrap">
                <div class="rounded-2 d-flex align-items-center justify-content-center shrink-0" style="width:38px;height:38px;background:#FEE2E2;font-size:18px;">📋</div>
                <div class="grow">
                    <div class="fw-medium small">Izin acara sekolah anak</div>
                    <div class="text-muted" style="font-size:11px;">5 Maret 2026 &middot; 1 hari</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill badge-rejected">Ditolak</span>
                    <form action="#" method="POST" class="d-inline" onsubmit="return confirm('Hapus riwayat ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size:11px;">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- @endforeach --}}
</div>
