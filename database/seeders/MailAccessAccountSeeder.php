<?php

namespace Database\Seeders;

use App\Models\MailAccessAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class MailAccessAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $legacyAccessAccounts = $this->legacyAccessAccounts();

        foreach (config('mail_inboxes.accounts', []) as $accountKey => $account) {
            $email = mb_strtolower(trim((string) ($account['username'] ?? '')));

            if ($email === '') {
                continue;
            }

            MailAccessAccount::query()->updateOrCreate(
                ['email' => $email],
                [
                    'pin' => Hash::make($this->pinFor((string) $accountKey)),
                    'is_active' => $legacyAccessAccounts[$email] ?? true,
                ],
            );

            unset($legacyAccessAccounts[$email]);
        }

        foreach ($legacyAccessAccounts as $email => $isActive) {
            MailAccessAccount::query()->updateOrCreate(
                ['email' => $email],
                [
                    'pin' => Hash::make($this->pinFor('default')),
                    'is_active' => $isActive,
                ],
            );
        }
    }

    private function pinFor(string $accountKey): string
    {
        $normalizedKey = str($accountKey)->upper()->replace(['-', ' '], '_')->toString();
        $pin = (string) env("MAIL_ACCESS_{$normalizedKey}_PIN", env('MAIL_ACCESS_DEFAULT_PIN', '0000'));

        if (preg_match('/^[0-9]{4}$/', $pin) !== 1) {
            throw new RuntimeException('Mail access PIN harus berupa 4 digit angka.');
        }

        return $pin;
    }

    /**
     * @return array<string, bool>
     */
    private function legacyAccessAccounts(): array
    {
        $usersPath = base_path('users.sql');
        $employeesPath = base_path('employees.sql');

        if (! File::exists($usersPath) || ! File::exists($employeesPath)) {
            return [];
        }

        $usersDump = File::get($usersPath);
        $employeesDump = File::get($employeesPath);
        $employeesByUserId = $this->parseInsertRows($employeesDump, 'employees')
            ->keyBy(static fn (array $employee): string => mb_strtolower((string) ($employee['user_id'] ?? '')));
        $accounts = [];

        $this->parseInsertRows($usersDump, 'users')->each(function (array $user) use ($employeesByUserId, &$accounts): void {
            $email = $this->legacyMailAccessEmail($user);

            if ($email === null) {
                return;
            }

            $isActive = $this->legacyUserIsActive($user, $employeesByUserId);
            $accounts[$email] = ($accounts[$email] ?? false) || $isActive;
        });

        return $accounts;
    }

    /**
     * @param  array<string, mixed>  $user
     */
    private function legacyMailAccessEmail(array $user): ?string
    {
        $businessEmail = trim((string) ($user['business_email'] ?? ''));
        $personalEmail = trim((string) ($user['email'] ?? ''));
        $email = mb_strtolower($businessEmail !== '' ? $businessEmail : $personalEmail);

        return filter_var($email, FILTER_VALIDATE_EMAIL) === false ? null : $email;
    }

    /**
     * @param  array<string, mixed>  $user
     * @param  Collection<string, array<string, mixed>>  $employeesByUserId
     */
    private function legacyUserIsActive(array $user, Collection $employeesByUserId): bool
    {
        if ((int) ($user['is_active'] ?? 0) !== 1 || filled($user['deleted_at'] ?? null)) {
            return false;
        }

        $employee = $employeesByUserId->get(mb_strtolower((string) ($user['id'] ?? '')));

        if ($employee === null) {
            return true;
        }

        return mb_strtolower(trim((string) ($employee['status'] ?? ''))) === 'active'
            && blank($employee['deleted_at'] ?? null);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function parseInsertRows(string $dump, string $table): Collection
    {
        $pattern = '/INSERT INTO `'.preg_quote($table, '/').'` \((.*?)\) VALUES\s*(.*?);/s';

        if (preg_match($pattern, $dump, $matches) === 1) {
            preg_match_all('/`([^`]+)`/', $matches[1], $columnMatches);
            $columns = $columnMatches[1] ?? [];
            $valuesSql = $matches[2];
        } else {
            $pattern = '/INSERT INTO `'.preg_quote($table, '/').'` VALUES\s*(.*?);/s';

            if (preg_match($pattern, $dump, $matches) !== 1) {
                return collect();
            }

            $columns = $this->createTableColumns($dump, $table);
            $valuesSql = $matches[1];
        }

        if ($columns === []) {
            return collect();
        }

        return collect($this->splitSqlTuples($valuesSql))
            ->map(function (string $tuple) use ($columns): array {
                $values = $this->parseSqlTuple($tuple);

                if (count($columns) !== count($values)) {
                    return [];
                }

                return array_combine($columns, $values) ?: [];
            })
            ->filter(static fn (array $row): bool => $row !== [])
            ->values();
    }

    /**
     * @return array<int, string>
     */
    private function createTableColumns(string $dump, string $table): array
    {
        $pattern = '/CREATE TABLE `'.preg_quote($table, '/').'` \((.*?)\)\s*ENGINE=/s';

        if (preg_match($pattern, $dump, $matches) !== 1) {
            return [];
        }

        preg_match_all('/^\s*`([^`]+)`/m', $matches[1], $columnMatches);

        return $columnMatches[1] ?? [];
    }

    /**
     * @return array<int, string>
     */
    private function splitSqlTuples(string $valuesSql): array
    {
        $tuples = [];
        $buffer = '';
        $depth = 0;
        $inString = false;
        $length = strlen($valuesSql);

        for ($index = 0; $index < $length; $index++) {
            $char = $valuesSql[$index];
            $previous = $index > 0 ? $valuesSql[$index - 1] : '';

            if ($char === "'" && $previous !== '\\') {
                $inString = ! $inString;
            }

            if (! $inString && $char === '(') {
                $depth++;

                if ($depth === 1) {
                    $buffer = '';

                    continue;
                }
            }

            if (! $inString && $char === ')') {
                $depth--;

                if ($depth === 0) {
                    $tuples[] = $buffer;
                    $buffer = '';

                    continue;
                }
            }

            if ($depth > 0) {
                $buffer .= $char;
            }
        }

        return $tuples;
    }

    /**
     * @return array<int, mixed>
     */
    private function parseSqlTuple(string $tuple): array
    {
        $values = [];
        $buffer = '';
        $inString = false;
        $length = strlen($tuple);

        for ($index = 0; $index < $length; $index++) {
            $char = $tuple[$index];
            $previous = $index > 0 ? $tuple[$index - 1] : '';

            if ($char === "'" && $previous !== '\\') {
                $inString = ! $inString;
                $buffer .= $char;

                continue;
            }

            if ($char === ',' && ! $inString) {
                $values[] = $this->normalizeSqlValue($buffer);
                $buffer = '';

                continue;
            }

            $buffer .= $char;
        }

        $values[] = $this->normalizeSqlValue($buffer);

        return $values;
    }

    private function normalizeSqlValue(string $value): mixed
    {
        $value = trim($value);

        if (strcasecmp($value, 'NULL') === 0) {
            return null;
        }

        if (str_starts_with($value, "'") && str_ends_with($value, "'")) {
            $value = substr($value, 1, -1);
            $value = str_replace(["\\'", '\\\\', '\r', '\n'], ["'", '\\', "\r", "\n"], $value);
        }

        return $value;
    }
}
