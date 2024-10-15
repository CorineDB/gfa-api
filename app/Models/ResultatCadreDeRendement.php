<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ResultatCadreDeRendement extends Model
{
    protected $table = 'resultats_cadre_de_rendement';
    public $timestamps = true;

    use HasSecureIds, HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = array('libelle', 'description', 'programmeId');

    protected static function boot()
    {
        parent::boot();
    }

    /**
     * Get the cadre de mesure rendement associated with this resultat cadre de rendement.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cadres_de_mesure_rendement()
    {
        return $this->hasMany(CadreDeMesureRendement::class, 'resultatCadreDeRendementId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }
}
