<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

#[Hidden(['password', 'remember_token', 'email_token', 'password_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use GeneratesCustomSequenceUuid;

    use HasFactory, HasRoles, Notifiable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_telegram_verified' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if (! is_string($user->id) || trim($user->id) === '') {
                $user->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'user_id', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function createdProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'created_by', 'id');
    }

    public function googleOauthTokens(): HasMany
    {
        return $this->hasMany(GoogleOauthToken::class, 'user_id', 'id');
    }

    public function approvedBusinessTrips(): HasMany
    {
        return $this->hasMany(BusinessTrip::class, 'approved_by', 'id');
    }

    public function userActivityLogs(): HasMany
    {
        return $this->hasMany(UserActivityLog::class, 'user_id', 'id');
    }

    /**
     * @param  array<int, string>  $permissionNames
     */
    public function hasAnyPositionPermission(array $permissionNames): bool
    {
        if ($this->hasRole('superuser')) {
            return true;
        }

        $this->loadMissing([
            'employee.deployment.position.permissions:uuid,name',
            'employee.deployment.positions.permissions:uuid,name',
        ]);

        $deployment = $this->employee?->deployment;
        $permissionPositions = $this->permissionPositionsForDeployment($deployment);

        if ($permissionPositions->contains('name', 'Super Administrator')) {
            return true;
        }

        return $permissionPositions
            ->flatMap(static fn (Position $position) => $position->permissions)
            ->pluck('name')
            ->intersect($permissionNames)
            ->isNotEmpty();
    }

    /**
     * @return Collection<int, Position>
     */
    private function permissionPositionsForDeployment(?EmployeeDeployment $deployment): Collection
    {
        if ($deployment === null) {
            return collect();
        }

        $primaryPositions = $deployment->positions
            ->filter(static fn (Position $position): bool => (bool) ($position->pivot?->is_primary ?? false))
            ->values();

        if ($primaryPositions->isNotEmpty()) {
            return $primaryPositions;
        }

        return $deployment->position !== null
            ? collect([$deployment->position])
            : collect();
    }
}
