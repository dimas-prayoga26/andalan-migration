# Dev Log - Fix Blueprint unsignedDecimal di employee_profiles

Tanggal: 2026-05-12  
File: `database/migrations/2026_05_12_095327_create_employee_profiles_table.php`

## Ringkasan
- Memperbaiki error migration:
  - dari: `$table->unsignedDecimal('weight', 5, 2)->nullable();`
  - menjadi: `$table->decimal('weight', 5, 2)->unsigned()->nullable();`

## Alasan
- Method `unsignedDecimal()` tidak tersedia pada `Illuminate\Database\Schema\Blueprint`.
