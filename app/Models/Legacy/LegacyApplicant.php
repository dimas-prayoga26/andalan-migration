<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class LegacyApplicant extends Model
{
    protected $connection = 'legacy_mysql';

    protected $table = 'applicants';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = [];
}
