<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\models\User;


class Vente extends Model
{
    
    protected $fillable = ['total','user_id'];

    public function details()
    {
        return $this->hasMany(VenteDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(App\Models\User::class, 'user_id');
    }
        

}



