<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class CadreDeMesureRendement extends Model
{
    protected $table = 'cadres_de_mesure_rendement';
    public $timestamps = true;

    use HasSecureIds, HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = array('position', 'type', 'rendementable_id', 'rendementable_type', 'resultatCadreDeRendementId');

    protected $casts = ['position' => 'integer'];

    protected static function boot()
    {
        parent::boot();
    }

    public function rendementable()
    {
        return $this->morphTo();
    }

    public function resultat_cadre_de_rendement()
    {
        return $this->belongsTo(ResultatCadreDeRendement::class, 'resultatCadreDeRendementId');
    }

    public function mesures()
    {
        return $this->belongsToMany(Indicateur::class, 'cadre_de_mesure_rendement_mesures', 'cadreDeMesureRendementId', 'indicateurId')->wherePivotNull('deleted_at')->withPivot(['position']);
    }

    public function cadreDeMesures()
    {
        return $this->hasMany(CadreDeMesureRendementMesure::class, 'cadreDeMesureRendementId');
    }
}
