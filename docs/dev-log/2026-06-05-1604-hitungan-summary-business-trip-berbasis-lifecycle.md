# Hitungan Summary Business Trip Berbasis Lifecycle

## Ringkasan

- Mengubah kartu summary Business Trip agar eager-load `lifecycleLogs` bersama cash advance dan reimbursement.
- Menghitung `Pending Approvals` dari status pending pada lifecycle `supervisor_review`.
- Menghitung `Overdue Reports` dari trip yang sudah melewati `end_date` dan belum menyelesaikan lifecycle `trip_report`.
- Menghitung `Successfully Settled` dari `payment_status = paid` atau lifecycle `payment_distribution` yang sudah complete.
- Menjaga nominal `Active Cash Advance` dari cash advance non-rejected dan `Pending Reimbursement` dari reimbursement pending.

## Verifikasi

- Menambahkan test reflection untuk memastikan seluruh nilai summary memakai kombinasi lifecycle, tanggal, cash advance, dan reimbursement.
