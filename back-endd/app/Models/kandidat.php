<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Pemilihan;

class kandidat extends Model
{
    //use SoftDeletes;

    protected $table = 'kandidat';
    protected $primaryKey = 'IdKandidat';
    protected $dates = ['deleted_at'];
    protected $fillable = ['IdUser', 'IdPemilihan', 'visi', 'misi', 'gambar', 'setuju'];
    
    // Relasi dengan model User
    public function user()
    {
        return $this->belongsTo(User::class, 'IdUser');
    }

    public function pemilihan()
    {
        return $this->belongsTo(Pemilihan::class, 'IdPemilihan');
    }
    
    // Relasi dengan model Pemilihan
    // public function pemilihan()
    // {
    //     return $this->belongsTo(Pemilihan::class, 'IdPemilihan');
    // }
}
