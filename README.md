# Andalan Migration

Project internal berbasis Laravel untuk kebutuhan SIAP/HR management, attendance, PIC attendance, project management, timesheet/reporting, data employee, approval leave, overtime, dan konfigurasi authorization menu.

## Requirement

- PHP: `^8.3`
- PHP yang dipakai saat ini: `8.5.5`
- Laravel Framework: `13.9.0`
- Database: MySQL/MariaDB
- Composer

## Setup Project Baru

1. Install atau update dependency PHP.

```bash
composer update
```

4. Buat file `.env`.

```bash
cp .env.example .env
```

Di Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

4. Generate application key.

```bash
php artisan key:generate
```

5. Jalankan migrasi ulang dan seeder.

```bash
php artisan migrate:fresh --seed
```