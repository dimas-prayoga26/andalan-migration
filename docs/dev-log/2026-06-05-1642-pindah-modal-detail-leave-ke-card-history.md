# Pindah Modal Detail Leave ke Card History

## Ringkasan

- Menghapus trigger modal dari card analytic Annual Leave dan Sick Leave.
- Menambahkan trigger modal pada card Leave List/history hasil pengajuan time off.
- Menambahkan modal detail generic `leaveHistoryDetailModal` yang menampilkan leave type, durasi, reason, due date, status, dan timeline request.
- Memakai event delegated agar card Leave List yang di-refresh lewat filter AJAX tetap bisa membuka modal detail.

## Verifikasi

- Test halaman Leave Request diperbarui untuk memastikan trigger modal berada di card history, bukan card analytic.
