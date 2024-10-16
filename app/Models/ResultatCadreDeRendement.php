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
    
    /**
     * Charger la liste des indicateurs de tous les criteres de gouvernance
     */
    public function indicateurs($pivotId = null)
    {
        return Indicateur::whereHas('cadres_de_mesure_rendement', function($query) use ($pivotId){
            $query->where('cadre_de_mesure_rendement_mesures.cadreDeMesureRendementId', $pivotId ?: $this->pivot->id);
        })->get();
        // Get the related sites through the projets of the programme
        return Indicateur::whereHas('cadreDeMesures', function($query) use ($pivotId){
            $query->where('cadreDeMesures.cadreDeMesureRendementId', $pivotId ?: $this->pivot->id);
        })->get();
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    /**
     * Mutator for 'description' attribute.
     * 
     * Modify the value before saving it to the database.
     * 
     * @param string $value The value of the 'description' attribute
     * 
     * @return void
     */
    public function setDescriptionAttribute($value)
    {
        // If the value is null or an empty string, set it to 'Not defined'
        $this->attributes['description'] = !empty($value) ? ucfirst(strtolower($value)) : 'Not defined';
    }
}
