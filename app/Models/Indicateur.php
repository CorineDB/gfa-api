<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Indicateur extends Model
{

    use HasSecureIds, HasFactory ;

    protected $table = 'indicateurs';

    public $timestamps = true;

    protected $dates = ["deleted_at"];

    protected $fillable = ["nom", "description", "type_de_variable", "agreger", "anneeDeBase", "valeurDeBase", "uniteeMesureId", "bailleurId", "categorieId", "programmeId", "hypothese", 'responsable', 'frequence_de_la_collecte', 'sources_de_donnee', 'methode_de_la_collecte', "kobo", "koboVersion", "valeurCibleTotal"];
    
    protected static function boot() {
        parent::boot();

        static::deleting(function($indicateur) {

            DB::beginTransaction();
            try {

                $indicateur->valeursCible()->delete();
                $indicateur->valueKeys()->delete();
                $indicateur->valeursDeBase()->delete();
                $indicateur->sites()->delete();
                $indicateur->cadres_de_mesure_rendement()->delete();
                
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }

        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'updated_at','deleted_at', "bailleurId"
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "created_at" => "datetime:Y-m-d",
        "updated_at" => "datetime:Y-m-d",
        "deleted_at" => "datetime:Y-m-d",
        'valeurDeBase' =>  'array',
        'valeurCibleTotal' =>  'array',
        //"anneeDeBase" => "datetime:Y-m-d"
    ];

    /**
    * Transtypage des attributs de type json
    *
    * @var array
    */
    protected $appends = [
        'taux_realisation'
    ];

    /**
     * Unitée de mésure d'un indicateur
     *
     * return Unitee
     */
    public function unitee_mesure()
    {
        return $this->belongsTo(Unitee::class, 'uniteeMesureId');
    }

    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class, 'bailleurId');
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorieId');
    }

    public function unitee()
    {
        return $this->belongsTo(Unitee::class, 'uniteeMesureId');
    }

    public function suivis()
    {
        return $this->valeursCible()->with("suivisIndicateur");
    }

    public function valeursCible()
    {
        return $this->morphMany(ValeurCibleIndicateur::class, 'cibleable');
    }

    public function valeursRealiser(){

        return $this->hasManyThrough(
            SuiviIndicateur::class,    // Final Model
            ValeurCibleIndicateur::class,       // Intermediate Model
            'cibleable_id',                  // Foreign key on the types_de_gouvernance table
            'valeurCibleId',          // Foreign key on the principes_de_gouvernance table
            'id',                              // Local key on the principes_de_gouvernance table
            'id'                               // Local key on the types_de_gouvernance table
        );
    }

    public function valeursDeBase()
    {
        return $this->morphMany(IndicateurValeur::class, 'indicateur_valueable');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function valueKeys()
    {
        return $this->belongsToMany(IndicateurValueKey::class, 'indicateur_value_keys_mapping', 'indicateurId', 'indicateurValueKeyId')->withPivot(["id", "uniteeMesureId", "type"])->wherePivotNull('deleted_at');
    }

    public function valueKey()
    {
        return $this->valueKeys->first();
    }

    public function valeurDeBase()
    {
        return $this->morphOne(IndicateurValeur::class, 'indicateur_valueable');
        return $this->valeursDeBase->first();
    }

    public function valeurCibleTotal()
    {

        $totals = [];

        $this->valeursCible->pluck("valeurCible")->each(function ($item) use (&$totals) {
            foreach ($item as $key => $value) {
                if (is_numeric($value)) {
                    if (!isset($totals[$key])) {
                        $totals[$key] = 0;
                    }
                    $totals[$key] += $value;
                }
            }
        });
        return $totals;
    }

    public function valeurRealiserTotal()
    {
        $totals = [];

        $this->valeursCible->pluck("valeurRealiser")->each(function ($item) use (&$totals) {
            foreach ($item as $key => $value) {
                if (is_numeric($value)) {
                    if (!isset($totals[$key])) {
                        $totals[$key] = 0;
                    }
                    $totals[$key] += $value;
                }
            }
        });
        return $totals;
    }

    public function getTauxRealisationAttribute()
    {
        $data = [
            $this->valeurCibleTotal(),
            $this->valeurRealiserTotal()
        ];

        $taux_realisation = [];

        // Dynamically iterate over valeurCibleTotal keys
        foreach ($data[0] as $key => $valeurCible) {
            $valeurRealiser = $data[1][$key] ?? 0; // Get the corresponding valeurRealiserTotal value

            // Perform the division, check if valeurCible is not zero to avoid division by zero
            $taux_realisation[$key] = $valeurCible != 0 ? $valeurRealiser / $valeurCible : 0;
        }

        return $taux_realisation;

        return $this->valeurCibleTotal();

        $totals = [];

        $this->valeursCible->pluck("valeurCible")->each(function ($item) use (&$totals) {
            
            foreach ($item as $key => $value) {
                if (is_numeric($value)) {
                    if (!isset($totals[$key])) {
                        $totals[$key] = 0;
                    }
                    $totals[$key] += $value;
                }
            }
        });
        return $totals;
    }

    /**
     * Get all of the sites for the indicateur.
     */
    public function sites(): MorphToMany
    {
        return $this->morphToMany(Site::class, 'siteable');
    }

    public function cadres_de_mesure_rendement()
    {
        return $this->belongsToMany(CadreDeMesureRendement::class, 'cadre_de_mesure_rendement_mesures', 'indicateurId', 'cadreDeMesureRendementId')->wherePivotNull('deleted_at')->withPivot(['position']);
    }

    public function cadreDeMesures()
    {
        return $this->hasMany(CadreDeMesureRendementMesure::class, 'indicateurId');
    }

    public function getIndice(){
        
    }
}
