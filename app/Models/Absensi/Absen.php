<?php

namespace App\Models\Absensi;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absen extends Model
{
    use HasFactory;
    protected $table = 'attendances';

    public $timestamps = false;

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime',
        'jam_keluar' => 'datetime'
    ];

    protected $fillable = [
        'id_user',
        'domisili',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'status'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
