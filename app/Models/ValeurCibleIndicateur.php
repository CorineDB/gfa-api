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
        return $this->hasMany(SuiviIndicateur::class, 'valeurCibleId');
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
}
