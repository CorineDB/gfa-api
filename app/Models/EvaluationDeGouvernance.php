<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class EvaluationDeGouvernance extends Model
{
    protected $table = 'evaluations_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('intitule', 'objectif_attendu', 'annee_exercice', 'description', 'debut', 'fin', 'statut', 'programmeId');

    protected $casts = ['statut'  => 'integer', 'debut'  => 'datetime', 'fin'  => 'datetime', 'annee_exercice' => 'integer', 'objectif_attendu' => 'integer'];

    protected $appends = ['pourcentage_evolution', 'pourcentage_evolution_des_soumissions_factuel', 'pourcentage_evolution_des_soumissions_de_perception', 'total_soumissions_factuel', 'total_soumissions_de_perception', 'total_soumissions_factuel_terminer', 'total_soumissions_de_perception_terminer', 'total_participants_evaluation_factuel', 'total_participants_evaluation_de_perception'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($evaluation_de_gouvernance) {

            DB::beginTransaction();
            try {

                if($evaluation_de_gouvernance->soumissions->count() == 0 && $evaluation_de_gouvernance->statut != 1){
                    $evaluation_de_gouvernance->delete();
                    DB::commit();
                }

            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });

        static::deleted(function ($evaluation_de_gouvernance) {

            DB::beginTransaction();
            try {

                $evaluation_de_gouvernance->recommandations()->delete();
                $evaluation_de_gouvernance->actions_a_mener()->delete();
                $evaluation_de_gouvernance->fiches_de_synthese()->delete();
                $evaluation_de_gouvernance->soumissions()->delete();
                $evaluation_de_gouvernance->formulaires_de_gouvernance()->delete();
                $evaluation_de_gouvernance->organisations()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function soumissions()
    {
        return $this->hasMany(Soumission::class, 'evaluationId');
    }

    public function soumissionsDePerception()
    {
        return $this->hasMany(Soumission::class, 'evaluationId')->where("type", 'perception');
    }

    public function soumissionFactuel()
    {
        return $this->hasMany(Soumission::class, 'evaluationId')->where("type", 'factuel');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function organisations(?int $organisationId = null, ?string $token = null)
    {
        // Start with the base relationship
        $organisations = $this->belongsToMany(Organisation::class,'evaluation_organisations', 'evaluationDeGouvernanceId', 'organisationId')->wherePivotNull('deleted_at')->withPivot(['id', 'nbreParticipants', 'participants', 'token'])->whereHas('user.profilable');

        if ($organisationId) {
            $organisations = $organisations->wherePivot("organisationId", $organisationId);
        }

        if ($token) {
            $organisations = $organisations->wherePivot("token", $token);
        }
        
        return $organisations;
    }


    /**
     * Get the users associated with the organisations of the evaluation.
     */
    public function organisations_user(){
        return User::whereHas('profilable', function ($query) {
            $query->whereIn('profilable_id', $this->organisations->pluck('id'))->where('profilable_type', Organisation::class);
        })->get();
    }

    public function formulaires_de_gouvernance()
    {
        return $this->belongsToMany(FormulaireDeGouvernance::class,'evaluation_formulaires_de_gouvernance', 'evaluationDeGouvernanceId', 'formulaireDeGouvernanceId')->wherePivotNull('deleted_at');
    }

    public function formulaire_factuel_de_gouvernance()
    {
        return $this->belongsToMany(FormulaireDeGouvernance::class,'evaluation_formulaires_de_gouvernance', 'evaluationDeGouvernanceId', 'formulaireDeGouvernanceId')->wherePivotNull('deleted_at')->where("type", 'factuel')->first();
    }

    public function formulaire_de_perception_de_gouvernance()
    {
        return $this->belongsToMany(FormulaireDeGouvernance::class,'evaluation_formulaires_de_gouvernance', 'evaluationDeGouvernanceId', 'formulaireDeGouvernanceId')->wherePivotNull('deleted_at')->where("type", 'perception')->first();
    }

    public function recommandations()
    {
        return $this->morphMany(Recommandation::class, "recommandationable");
    }

    public function actions_a_mener()
    {
        return $this->morphMany(ActionAMener::class, "actionable");
    }

    /*public function fiches_de_synthese()
    {
        return $this->hasManyThrough(
            FicheDeSynthese::class,
            Soumission::class,
            'evaluationId',
            'soumissionId',
            'id',
            'id'
        );
    }*/

    /**
     * Retrieve fiches de synthese associated with the evaluation.
     *
     * This method returns a collection of FicheDeSynthese models related to the 
     * EvaluationDeGouvernance, with optional filtering by organisation ID and type.
     *
     * @param int|null $organisationId Optional organisation ID to filter the fiches de synthese.
     * @param string|null $type Optional type to filter the fiches de synthese.
     * @return \Illuminate\Database\Eloquent\Builder
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fiches_de_synthese(?int $organisationId = null, ?string $type = null): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // Start with the base relationship
        $fiches_de_synthese = $this->hasMany(FicheDeSynthese::class, 'evaluationDeGouvernanceId');

        // Apply additional filtering conditions if needed
        if ($type) {
            $fiches_de_synthese = $fiches_de_synthese->where("type", $type);
        }

        if ($organisationId) {
            $fiches_de_synthese = $fiches_de_synthese->where("organisationId", $organisationId);
        }

        // Get the results and apply grouping on the collection level

        return $fiches_de_synthese;
    }

    public function profiles(?int $organisationId = null, ?int $evaluationOrganisationId = null): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // Start with the base relationship
        $profiles = $this->hasMany(ProfileDeGouvernance::class, 'evaluationDeGouvernanceId');

        if ($organisationId) {
            $profiles = $profiles->where("organisationId", $organisationId);
        }

        if ($evaluationOrganisationId) {
            $profiles = $profiles->where("evaluationOrganisationId", $evaluationOrganisationId);
        }

        // Get the results and apply grouping on the collection level

        return $profiles;
    }

    public function failedProfilesDeGouvernance(?int $organisationId = null, ?int $evaluationOrganisationId = null, ?float $threshold  = 0.5){
       
        // Start the query by calling the profiles method
        $profile = $this->profiles();

        $threshold = $this->objectif_attendu ? $this->objectif_attendu : $threshold;

        // Apply filtering for 'indice_synthetique' under the dynamic threshold using MySQL JSON functions
        return $profile->whereRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_synthetique")) AS UNSIGNED) < ?', [$threshold]);
    }

    public function getPourcentageEvolutionAttribute()
    {
        // Avoid division by zero by checking that total participants are non-zero
        if ($this->pourcentage_evolution_des_soumissions_factuel == 0 && $this->pourcentage_evolution_des_soumissions_de_perception == 0) {
            return 0;
        }

        return ($this->pourcentage_evolution_des_soumissions_factuel + $this->pourcentage_evolution_des_soumissions_de_perception) / 2;
    }

    public function getPourcentageEvolutionDesSoumissionsFactuelAttribute()
    {
        // Avoid division by zero by checking that total soumissions factuel or total participants are non-zero
        if ($this->total_soumissions_factuel == 0 || $this->total_participants_evaluation_factuel == 0) {
            return 0;
        }

        return ($this->total_soumissions_factuel * 100) / $this->total_participants_evaluation_factuel; 
    }

    public function getPourcentageEvolutionDesSoumissionsDePerceptionAttribute()
    {
        // Avoid division by zero by checking that total soumissions de perception or total participants are non-zero
        if ($this->total_soumissions_de_perception == 0 || $this->total_participants_evaluation_de_perception == 0) {
            return 0;
        }

        return ($this->total_soumissions_de_perception  * 100) / $this->total_participants_evaluation_de_perception; 
    }

    public function getTotalSoumissionsFactuelAttribute()
    {
        return $this->soumissionFactuel()->count();
    }

    public function getTotalSoumissionsDePerceptionAttribute()
    {
        return $this->soumissionsDePerception()->count();
    }

    public function getTotalSoumissionsFactuelTerminerAttribute()
    {
        return $this->soumissionFactuel()->where('statut', true)->count();
    }

    public function getTotalSoumissionsDePerceptionTerminerAttribute()
    {
        return $this->soumissionsDePerception()->where('statut', true)->count();
    }

    public function getTotalParticipantsEvaluationFactuelAttribute(){
        return $this->organisations()->count();
    }

    public function getTotalParticipantsEvaluationDePerceptionAttribute(){
        // Sum the 'nbreParticipants' attribute from the pivot table
        return $this->organisations->sum(function ($organisation) {
            return $organisation->pivot->nbreParticipants;
        });
    }

}
