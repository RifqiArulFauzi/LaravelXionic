<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Status;
use App\Models\periode;

class Pemilihan extends Model
{
    protected $table = 'pemilihan';
    protected $primaryKey = 'IdPemilihan';
    public $timestamps = true;
    protected $fillable = [
        'nama',
        'IdPeriode',
        'IdStatus',
        'deskripsi',
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'IdPeriode');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'IdStatus');
    }
}
