<?php

namespace App\Console\Commands;

use App\Console\Commands\Support\LegacyApplicantMapper;
use App\Models\Applicant;
use App\Models\ApplicantEducation;
use App\Models\ApplicantWorkExperience;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

#[Signature('applicants:migrate-legacy {--date= : Tanggal created_at pelamar legacy yang diambil (Y-m-d), default hari ini} {--brand= : brand_key yang diisi ke pelamar hasil migrasi, default kosong (fallback RNB)} {--dry-run : Tampilkan pratinjau tanpa menyimpan data apa pun}')]
#[Description('Migrate applicants created on a given date from the legacy careers database into this app')]
class MigrateLegacyApplicants extends Command
{
    private const LEGACY_CONNECTION = 'legacy_careers';

    public function handle(): int
    {
        $date = $this->targetDate();

        if ($date === null) {
            return self::FAILURE;
        }

        if (! $this->configureLegacyConnection()) {
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $brandKey = $this->normalizedBrandKey();

        try {
            $legacyRows = DB::connection(self::LEGACY_CONNECTION)
                ->table('applicants')
                ->whereDate('created_at', $date->toDateString())
                ->orderBy('id')
                ->get();
        } catch (Throwable $throwable) {
            $this->error('Gagal membaca database legacy: '.$throwable->getMessage());

            return self::FAILURE;
        }

        if ($legacyRows->isEmpty()) {
            $this->info("Tidak ada pelamar legacy dengan created_at = {$date->toDateString()}.");

            return self::SUCCESS;
        }

        $this->info(sprintf('Ditemukan %d pelamar legacy pada %s.%s', $legacyRows->count(), $date->toDateString(), $dryRun ? ' (mode pratinjau, tidak ada yang disimpan)' : ''));

        $mapper = $this->makeMapper();

        $imported = 0;
        $skipped = 0;

        foreach ($legacyRows as $legacyRow) {
            $legacyRowArray = (array) $legacyRow;
            $email = $this->stringOrNull($legacyRowArray['email'] ?? null);
            $fullName = $this->stringOrNull($legacyRowArray['full_name'] ?? null) ?? '(tanpa nama)';

            if ($this->alreadyImported($email, $date)) {
                $skipped++;
                $this->line("  [skip] {$fullName} <{$email}> sudah ada di database baru untuk tanggal ini.");

                continue;
            }

            $attributes = $mapper->mapApplicantAttributes($legacyRowArray, $brandKey);
            $educationRows = $mapper->mapEducationRows($legacyRowArray);
            $workExperienceRows = $mapper->mapWorkExperienceRows($legacyRowArray);

            if ($dryRun) {
                $this->line(sprintf(
                    '  [preview] %s <%s> | status_id=%s | job_vacancy_id=%s | pendidikan=%d baris | pengalaman=%d baris',
                    $attributes['full_name'],
                    $attributes['email'] ?? '-',
                    $attributes['applicant_status_id'] ?? '-',
                    $attributes['job_vacancy_id'] ?? '-',
                    count($educationRows),
                    count($workExperienceRows),
                ));

                $imported++;

                continue;
            }

            $attributes['slug'] = $this->uniqueSlug($attributes['slug']);

            DB::transaction(function () use ($attributes, $educationRows, $workExperienceRows): void {
                $applicant = new Applicant($attributes);
                $applicant->timestamps = false;
                $applicant->save();

                foreach ($educationRows as $index => $row) {
                    ApplicantEducation::query()->create([
                        'applicant_id' => $applicant->id,
                        'sequence' => $index + 1,
                    ] + $row);
                }

                foreach ($workExperienceRows as $index => $row) {
                    ApplicantWorkExperience::query()->create([
                        'applicant_id' => $applicant->id,
                        'sequence' => $index + 1,
                    ] + $row);
                }
            });

            $this->line("  [ok] {$fullName} <{$email}> berhasil diimpor.");

            $imported++;
        }

        $this->info(sprintf(
            '%s selesai. Diproses: %d, %s: %d, dilewati (sudah ada): %d.',
            $dryRun ? 'Pratinjau' : 'Migrasi',
            $legacyRows->count(),
            $dryRun ? 'akan diimpor' : 'diimpor',
            $imported,
            $skipped,
        ));

        return self::SUCCESS;
    }

    private function targetDate(): ?Carbon
    {
        $dateOption = $this->option('date');

        if ($dateOption === null || trim((string) $dateOption) === '') {
            return Carbon::today();
        }

        try {
            return Carbon::parse((string) $dateOption)->startOfDay();
        } catch (Throwable) {
            $this->error("Format tanggal tidak valid: {$dateOption}. Gunakan format Y-m-d, contoh 2026-09-21.");

            return null;
        }
    }

    private function normalizedBrandKey(): ?string
    {
        $brand = trim((string) $this->option('brand'));

        return $brand === '' ? null : $brand;
    }

    private function configureLegacyConnection(): bool
    {
        $host = env('LEGACY_DB_HOST');
        $database = env('LEGACY_DB_DATABASE');
        $username = env('LEGACY_DB_USERNAME');

        if (blank($host) || blank($database) || blank($username)) {
            $this->error('LEGACY_DB_HOST, LEGACY_DB_DATABASE dan LEGACY_DB_USERNAME harus diisi di .env sebelum menjalankan command ini.');

            return false;
        }

        config(['database.connections.'.self::LEGACY_CONNECTION => [
            'driver' => 'mysql',
            'host' => $host,
            'port' => env('LEGACY_DB_PORT', 3306),
            'database' => $database,
            'username' => $username,
            'password' => env('LEGACY_DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
        ]]);

        return true;
    }

    private function makeMapper(): LegacyApplicantMapper
    {
        return new LegacyApplicantMapper(
            genderIdsByName: $this->idsByLowerName(DB::table('meta_data_gender')->pluck('id', 'name')),
            maritalStatusIdsByName: $this->idsByLowerName(DB::table('meta_data_marital_statuses')->pluck('id', 'name')),
            educationLevelIdsByName: $this->idsByLowerName(DB::table('education_levels')->pluck('id', 'name')),
            applicantStatusIdsByValue: DB::table('applicant_statuses')->pluck('id', 'value'),
            jobVacancyIdsByName: $this->idsByLowerName(DB::table('job_vacancies')->pluck('id', 'name')),
            legacyVacancyNamesByValue: $this->legacyVacancyNamesByValue(),
        );
    }

    /**
     * @param  Collection<string, string>  $idsByName
     * @return Collection<string, string>
     */
    private function idsByLowerName(Collection $idsByName): Collection
    {
        return $idsByName->mapWithKeys(fn (string $id, string $name): array => [Str::lower(trim($name)) => $id]);
    }

    /**
     * @return Collection<int, string>
     */
    private function legacyVacancyNamesByValue(): Collection
    {
        try {
            if (! Schema::connection(self::LEGACY_CONNECTION)->hasTable('opt_applicants_vacancies')) {
                return collect();
            }

            return DB::connection(self::LEGACY_CONNECTION)
                ->table('opt_applicants_vacancies')
                ->pluck('name', 'value');
        } catch (Throwable $throwable) {
            $this->warn('Tabel lowongan legacy (opt_applicants_vacancies) tidak terbaca, job_vacancy_id akan dikosongkan: '.$throwable->getMessage());

            return collect();
        }
    }

    private function alreadyImported(?string $email, Carbon $date): bool
    {
        if ($email === null) {
            return false;
        }

        return Applicant::withTrashed()
            ->whereDate('created_at', $date->toDateString())
            ->where('email', $email)
            ->exists();
    }

    private function uniqueSlug(?string $slug): ?string
    {
        if ($slug === null) {
            return null;
        }

        if (! Applicant::withTrashed()->where('slug', $slug)->exists()) {
            return $slug;
        }

        $suffix = 2;
        $candidate = $slug.'-'.$suffix;

        while (Applicant::withTrashed()->where('slug', $candidate)->exists()) {
            $suffix++;
            $candidate = $slug.'-'.$suffix;
        }

        return $candidate;
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }
}
