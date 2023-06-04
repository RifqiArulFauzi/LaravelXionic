<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voting extends Model
{
    use HasFactory;

    protected $table = 'voting';
    protected $primaryKey = 'IdVoting';
    public $timestamps = false;

    protected $fillable = [
        'IdUser',
        'IdKandidat',
        'IdPemilihan',
        'WaktuVote',
    ];

    // Relasi dengan model User
    public function user()
    {
        return $this->belongsTo(User::class, 'IdUser');
    }

    // Relasi dengan model Kandidat
    public function kandidat()
    {
        return $this->belongsTo(Kandidat::class, 'IdKandidat');
    }

    // Relasi dengan model Pemilihan
    public function pemilihan()
    {
        return $this->belongsTo(Pemilihan::class, 'IdPemilihan');
    }
}
