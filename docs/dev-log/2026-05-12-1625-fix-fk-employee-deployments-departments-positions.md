# Dev Log - Fix FK employee_deployments ke departments/positions

Tanggal: 2026-05-12  
File: `database/migrations/2026_05_04_080751_create_employee_deployments_table.php`

## Ringkasan
- Memperbaiki tipe kolom FK agar cocok dengan parent table:
  - `current_department_id` diubah dari `foreignUuid(...)` menjadi `foreignId(...)`
  - `current_position_id` diubah dari `foreignUuid(...)` menjadi `foreignId(...)`
- Tetap menggunakan `nullOnDelete()`.

## Alasan
- `departments.id` dan `positions.id` bertipe bigint (`$table->id()`), jadi kolom referensi di `employee_deployments` harus bertipe bigint juga agar FK bisa dibuat.
