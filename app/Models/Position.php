<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Position extends Model
{
    use GeneratesCustomSequenceUuid;

    public const KEY_ACCOUNTING_TAXATION = 'accounting_taxation';

    public const KEY_ADMINISTRATOR = 'administrator';

    public const KEY_ARCHITECTURE_DESIGN = 'architecture_design';

    public const KEY_BRANDING_DESIGNER = 'branding_designer';

    public const KEY_CHIEF_OPERATING_OFFICER = 'chief_operating_officer';

    public const KEY_COMMISSIONER = 'commissioner';

    public const KEY_COMMISSIONER_INDEPENDENT = 'commissioner_independent';

    public const KEY_DIRECTOR = 'director';

    public const KEY_DIGITAL_MARKETING = 'digital_marketing';

    public const KEY_DOCUMENTATION_EVENT_EDITOR_VIDEO = 'documentation_event_editor_video';

    public const KEY_DRIVER = 'driver';

    public const KEY_EXECUTIVE_ASSISTANT = 'executive_assistant';

    public const KEY_FINANCE_ADMINISTRATION_COORDINATOR = 'finance_administration_coordinator';

    public const KEY_GRAPHIC_DESIGN = 'graphic_design';

    public const KEY_INTERIOR_DESIGN = 'interior_design';

    public const KEY_LEGAL_OFFICER_PARTNERSHIP = 'legal_officer_partnership';

    public const KEY_OPERATIONS_COORDINATOR = 'operations_coordinator';

    public const KEY_SUPER_ADMINISTRATOR = 'super_administrator';

    public const KEY_SUPERVISOR = 'supervisor';

    public const KEY_WEB_DEVELOPER = 'web_developer';

    public const SYSTEM_KEYS_BY_NAME = [
        'Accounting and Taxation' => self::KEY_ACCOUNTING_TAXATION,
        'Administrator' => self::KEY_ADMINISTRATOR,
        'Architecture Design' => self::KEY_ARCHITECTURE_DESIGN,
        'Branding Designer' => self::KEY_BRANDING_DESIGNER,
        'Chief Operating Officer' => self::KEY_CHIEF_OPERATING_OFFICER,
        'Commissioner' => self::KEY_COMMISSIONER,
        'Commissioner Independent' => self::KEY_COMMISSIONER_INDEPENDENT,
        'Director' => self::KEY_DIRECTOR,
        'Digital Marketing' => self::KEY_DIGITAL_MARKETING,
        'Documentation Event and Editor Video' => self::KEY_DOCUMENTATION_EVENT_EDITOR_VIDEO,
        'Driver' => self::KEY_DRIVER,
        'Executive Assistant' => self::KEY_EXECUTIVE_ASSISTANT,
        'Finance and Administration Coordinator' => self::KEY_FINANCE_ADMINISTRATION_COORDINATOR,
        'Graphic Design' => self::KEY_GRAPHIC_DESIGN,
        'Interior Design' => self::KEY_INTERIOR_DESIGN,
        'Legal Officer & Partnership' => self::KEY_LEGAL_OFFICER_PARTNERSHIP,
        'Operations Coordinator' => self::KEY_OPERATIONS_COORDINATOR,
        'Super Administrator' => self::KEY_SUPER_ADMINISTRATOR,
        'Supervisor' => self::KEY_SUPERVISOR,
        'Web Developer' => self::KEY_WEB_DEVELOPER,
    ];

    public const PROTECTED_SYSTEM_KEYS = [
        self::KEY_SUPER_ADMINISTRATOR,
        self::KEY_ADMINISTRATOR,
        self::KEY_DIRECTOR,
        self::KEY_SUPERVISOR,
    ];

    public const DIRECTOR_APPROVER_SYSTEM_KEYS = [
        self::KEY_CHIEF_OPERATING_OFFICER,
        self::KEY_DIRECTOR,
    ];

    public const ATTENDANCE_REST_DEDUCTION_EXEMPT_SYSTEM_KEYS = [
        self::KEY_DRIVER,
        self::KEY_EXECUTIVE_ASSISTANT,
    ];

    protected $table = 'positions';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $attributes = [
        'is_protected' => false,
    ];

    protected function casts(): array
    {
        return [
            'is_protected' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $position): void {
            if (! is_string($position->id) || trim($position->id) === '') {
                $position->id = static::generateCustomSequenceUuid('id');
            }
        });

        static::saving(function (self $position): void {
            $systemKey = static::systemKeyForName((string) $position->name);

            if (blank($position->system_key) && $systemKey !== null) {
                $position->system_key = $systemKey;
            }

            if (! blank($position->system_key) && static::isProtectedSystemKey((string) $position->system_key)) {
                $position->is_protected = true;
            }
        });
    }

    public static function systemKeyForName(string $name): ?string
    {
        return self::SYSTEM_KEYS_BY_NAME[$name] ?? null;
    }

    public static function isProtectedSystemKey(string $systemKey): bool
    {
        return in_array($systemKey, self::PROTECTED_SYSTEM_KEYS, true);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'position_has_permissions', 'position_id', 'permission_id', 'id', 'uuid')
            ->withTimestamps();
    }

    public function deployments(): BelongsToMany
    {
        return $this->belongsToMany(EmployeeDeployment::class, 'employee_deployment_positions', 'position_id', 'employee_deployment_id', 'id', 'id')
            ->withPivot(['is_primary', 'status', 'started_at', 'ended_at'])
            ->withTimestamps()
            ->wherePivot('status', 'active');
    }

    public function attendanceRules(): BelongsToMany
    {
        return $this->belongsToMany(RulesOfAttendace::class, 'attendance_rule_positions', 'position_id', 'attendance_rule_id', 'id', 'id')
            ->withTimestamps();
    }

    /**
     * @param  string|array<int, string>  $systemKeys
     */
    public function scopeWhereSystemKey(Builder $query, string|array $systemKeys): Builder
    {
        $systemKeys = is_array($systemKeys) ? $systemKeys : [$systemKeys];

        return $query->whereIn('system_key', $systemKeys);
    }

    /**
     * @param  string|array<int, string>  $systemKeys
     */
    public function scopeWhereNotSystemKey(Builder $query, string|array $systemKeys): Builder
    {
        $systemKeys = is_array($systemKeys) ? $systemKeys : [$systemKeys];

        return $query->where(function (Builder $query) use ($systemKeys): void {
            $query
                ->whereNull('system_key')
                ->orWhereNotIn('system_key', $systemKeys);
        });
    }
}
