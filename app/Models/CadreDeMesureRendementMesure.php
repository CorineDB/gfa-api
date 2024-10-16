<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class CadreDeMesureRendementMesure extends Model
{
    protected $table = 'cadre_de_mesure_rendement_mesures';
    public $timestamps = true;

    use HasSecureIds, HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = array('position', 'cadreDeMesureRendementId', 'indicateurId');

    protected static function boot()
    {
        parent::boot();
    }

    public function cadre_de_rendement()
    {
        return $this->belongsTo(CadreDeMesureRendement::class, 'cadreDeMesureRendementId');
    }

    public function indicateur()
    {
        return $this->belongsTo(Indicateur::class, 'indicateurId');
    }
}
