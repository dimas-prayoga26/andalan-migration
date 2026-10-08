<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class MailAccessAccount extends Model
{
    public const TYPE_PERSONAL = 'personal';

    public const TYPE_DEPARTMENT = 'department';

    public const TYPE_APPLICANT_NOTIFICATION = 'applicant_notification';

    protected $fillable = [
        'company_id',
        'employee_id',
        'email',
        'type',
        'pin',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'pin',
    ];

    protected $attributes = [
        'type' => self::TYPE_PERSONAL,
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function takeovers(): HasMany
    {
        return $this->hasMany(MailAccountTakeover::class, 'mail_access_account_id', 'id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDepartment(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_DEPARTMENT);
    }

    public function scopeApplicantNotification(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_APPLICANT_NOTIFICATION);
    }

    public function scopeVisibleForMailAccess(Builder $query): Builder
    {
        $catchAllInboxEmails = self::catchAllInboxEmails();

        if ($catchAllInboxEmails->isEmpty()) {
            return $query;
        }

        return $query->whereNotIn('email', $catchAllInboxEmails->all());
    }

    /**
     * @return Attribute<string, string>
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            set: static fn (string $value): string => mb_strtolower(trim($value)),
        );
    }

    public function pinMatches(string $pin): bool
    {
        $storedPin = (string) $this->pin;

        if (hash_equals($storedPin, $pin)) {
            return true;
        }

        return Hash::isHashed($storedPin) && Hash::check($pin, $storedPin);
    }

    public function ensurePinIsHashed(string $pin): void
    {
        if (Hash::isHashed((string) $this->pin) && Hash::check($pin, (string) $this->pin)) {
            return;
        }

        $this->forceFill([
            'pin' => Hash::make($pin),
        ])->save();
    }

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return [
            self::TYPE_PERSONAL => 'Personal',
            self::TYPE_DEPARTMENT => 'Department',
            self::TYPE_APPLICANT_NOTIFICATION => 'Applicant Notification',
        ];
    }

    /**
     * @return Collection<int, string>
     */
    public static function catchAllInboxEmails(): Collection
    {
        return collect(config('mail_inboxes.accounts', []))
            ->reject(static fn (mixed $account, string|int $accountKey): bool => str_ends_with((string) $accountKey, '_hr'))
            ->map(static fn (mixed $account): string => is_array($account) ? mb_strtolower(trim((string) ($account['username'] ?? ''))) : '')
            ->filter(static fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    public static function configuredInboxEmails(): Collection
    {
        return collect(config('mail_inboxes.accounts', []))
            ->map(static fn (mixed $account): string => is_array($account) ? mb_strtolower(trim((string) ($account['username'] ?? ''))) : '')
            ->filter(static fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values();
    }
}
