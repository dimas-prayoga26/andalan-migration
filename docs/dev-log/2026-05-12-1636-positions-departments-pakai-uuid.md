# Dev Log - Positions dan Departments Pakai UUID

Tanggal: 2026-05-12  
File:
- `database/migrations/2026_05_04_080748_create_positions_table.php`
- `database/migrations/2026_05_04_080749_create_departments_table.php`
- `database/migrations/2026_05_04_080751_create_employee_deployments_table.php`

## Ringkasan
- Mengubah primary key tabel `positions` dari `id()` menjadi `uuid('id')->primary()`.
- Mengubah primary key tabel `departments` dari `id()` menjadi `uuid('id')->primary()`.
- Menyesuaikan FK di `employee_deployments`:
  - `current_department_id` -> `foreignUuid(...)`
  - `current_position_id` -> `foreignUuid(...)`
