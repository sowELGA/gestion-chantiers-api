<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeResetMdp extends Model
{
    use HasFactory;
    
    protected $table = 'demande_reset_mdps';

    protected $fillable = ['email', 'statut', 'traitee_le'];

    protected $casts = [
        'traitee_le' => 'datetime',
    ];

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
