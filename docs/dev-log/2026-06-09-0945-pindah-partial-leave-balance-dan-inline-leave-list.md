# Pindah Partial Leave Balance dan Inline Leave List

## Ringkasan

- Memindahkan row analytic `leave-balance-mobile-slider` ke partial `attendance.leave-requests.partials.balance-cards`.
- Mengembalikan render awal `Leave List` di `index.blade.php` agar tidak memakai include partial terpisah.
- Mempertahankan partial history card untuk response AJAX filter agar refresh Leave List tetap berjalan.
- Menyesuaikan test agar struktur partial yang dipisah adalah analytic row, bukan render awal Leave List.

## Verifikasi

- Test halaman Leave Request diperbarui untuk memastikan `balance-cards` dipakai dari index dan Leave List awal dirender inline.
