<?php

namespace App\Models\Absensi;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Izin extends Model
{
    use HasFactory, HasRoles;
    protected $table = 'attendances_leave';
    protected $fillable = [
        'id_user',
        'tanggal_awal',
        'tanggal_akhir',
        'alasan',
        'status_persetujuan',
    ];

    public $timestamps = false;
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
