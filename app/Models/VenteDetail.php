<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenteDetail extends Model
{
    protected $fillable = ['vente_id', 'medicament_id', 'quantite', 'prix'];

    public function medicament()
    {
        return $this->belongsTo(Medicament::class);
    }
}
