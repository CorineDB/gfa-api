<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Resultat extends Model
{
    use HasFactory, HasSecureIds ;

    protected $fillable = ['nom', 'description', 'resultable_id', 'resultable_type', 'indicateurId'];

    public function resultable()
    {
        return $this->morphTo();
    }

    public function indicateur_cadre_logique()
    {
        return $this->belongsTo(Indicateur::class, 'indicateurId');
    }
}
