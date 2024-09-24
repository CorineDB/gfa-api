<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ObjectifGlobaux extends Model
{
    use HasFactory, HasSecureIds;

    protected $table = 'objectif_globauxes';

    protected $fillable = ['nom', 'description', 'objectifable_id', 'objectifable_type', 'indicateurId'];

    public function objectifable()
    {
        return $this->morphTo();
    }

    public function indicateur_cadre_logique()
    {
        return $this->belongsTo(Indicateur::class, 'indicateurId');
    }
}
