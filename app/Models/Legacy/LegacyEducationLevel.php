<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class LegacyEducationLevel extends Model
{
    protected $connection = 'legacy_mysql';

    protected $table = 'opt_education_levels';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = [];
}
