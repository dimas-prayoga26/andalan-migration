<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

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

    public function applicantMailSenderAccounts(): HasMany
    {
        return $this->hasMany(MailAccessAccount::class, 'company_id', 'id')
            ->department()
            ->applicantMailSender()
            ->active()
            ->orderBy('email');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'company_id', 'id');
    }
}
