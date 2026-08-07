<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class LegacyApplicantVacancy extends Model
{
    protected $connection = 'legacy_mysql';

    protected $table = 'opt_applicants_vacancies';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = [];
}
