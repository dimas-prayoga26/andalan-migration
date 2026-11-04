<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    use GeneratesCustomSequenceUuid;

    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $company): void {
            if (! is_string($company->id) || trim($company->id) === '') {
                $company->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function jobVacancies(): HasMany
    {
        return $this->hasMany(JobVacancy::class, 'company_id', 'id');
    }

    public function mailAccessAccounts(): HasMany
    {
        return $this->hasMany(MailAccessAccount::class, 'company_id', 'id');
    }

    public function departmentMailAccessAccounts(): HasMany
    {
        return $this->hasMany(MailAccessAccount::class, 'company_id', 'id')
            ->department()
            ->active()
            ->orderBy('email');
    }

    public function applicantNotificationMailAccessAccounts(): HasMany
    {
        return $this->hasMany(MailAccessAccount::class, 'company_id', 'id')
            ->applicantNotification()
            ->active()
            ->orderBy('email');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'company_id', 'id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(CompanyAsset::class, 'company_id', 'id');
    }

    /**
     * Brand keys from config/career_brands.php, which match company names case-insensitively.
     *
     * @return list<string>
     */
    public static function careerBrandKeys(): array
    {
        return array_keys((array) config('career_brands.brands', []));
    }

    public function scopeHasCareerBrand(Builder $query): Builder
    {
        return $query->whereIn(DB::raw('LOWER(name)'), static::careerBrandKeys());
    }

    public function scopeActiveForMailManagement(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereRaw('LOWER(COALESCE(name, "")) NOT LIKE ?', ['%andalan%'])
            ->whereRaw('LOWER(COALESCE(legal_name, "")) NOT LIKE ?', ['%andalan%']);
    }
}
