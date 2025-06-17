<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ValeurCibleIndicateur extends Model
{
    use HasFactory, HasSecureIds ;

    protected $table = "valeur_cible_d_indicateurs";

    /**
    * Transtypage des attributs de type json
    *
    * @var array
    */
    protected $casts = [
        'valeurCible'  => 'array'
    ];

    /**
    * Transtypage des attributs de type json
    *
    * @var array
    */
    protected $appends = [
        //'valeur_realiser'
    ];

    /* Les attributs qui sont assignés en masse */
    protected $fillable = [
        'annee',
        'valeurCible',
        'cibleable_type',
        'cibleable_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public function cibleable()
    {
        return $this->morphTo();
    }

    /**
     * Récupération de la liste des suivies réalisés par rapport à la valeur cible d'un indicateur
     *
     * @return List<SuiviIndicateur>
     */
    public function suivisIndicateur()
    {
        return $this->hasMany(SuiviIndicateur::class, 'valeurCibleId')/*
            ->when(
                auth()->check() &&
                (auth()->user()->type == 'organisation' || (auth()->user()->profilable_id != 0 && auth()->user()->profilable_type == Organisation::class)), function($query) {
                    // Filter by organisation responsible using both 'suivi_indicateurable_type' and 'suivi_indicateurable_id'
                    $query->whereHas('suivi_indicateurable', function($query) {
                        $query->where('suivi_indicateurable_type', get_class(auth()->user()->profilable))
                            ->where('suivi_indicateurable_id', auth()->user()->profilable->id);
                    });
                }) */;
    }

    /**
     * Récupération de la liste des suivies trimestriels réalisés par rapport à un indicateur
     *
     * @param $query
     * @param string $trimestre // Attribut sur lequel le filtre se fera
     * @return List<SuiviIndicateur>
     */
    public function scopeSuivisTrimestrielIndicateur($query, $trimestre)
    {
        return $query->where('trimestre', $trimestre);
    }

    /**
     * Récupération de la liste des suivies réalisés par rapport à la valeur cible d'un indicateur
     *
     * @return List<SuiviIndicateurMOD>
     */
    public function suivisIndicateurMOD()
    {
        return $this->hasMany(SuiviIndicateurMOD::class, 'valeurCibleId');
    }

    /**
     * Récupération de la liste des suivies trimestriels réalisés par rapport à un indicateur
     *
     * @param $query
     * @param string $trimestre // Attribut sur lequel le filtre se fera
     * @return List<SuiviIndicateurMOD>
     */
    public function scopeSuivisTrimestrielIndicateurMOD($query, $trimestre)
    {
        return $query->where('trimestre', $trimestre);
    }

    public function valeursCible()
    {
        return $this->morphMany(IndicateurValeur::class, 'indicateur_valueable');
    }

    public function getValeurRealiserAttribute()
    {
        $totals = [];

        //return $this->suivisIndicateur;

        $this->suivisIndicateur->pluck("valeurRealise")->each(function ($item) use (&$totals) {
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
}
