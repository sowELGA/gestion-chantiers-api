<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserChantier extends Model
{
    protected $table = 'user_chantier';

    protected $fillable = ['debut_affectation', 'fin_affectation', 'user_id', 'chantier_id'];

    protected $casts = [
        'debut_affectation' => 'date',
        'fin_affectation'   => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chantier()
    {
        return $this->belongsTo(Chantier::class);
    }
}
