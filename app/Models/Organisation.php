<?php

namespace App\Models;

use App\Http\Resources\user\UserResource;
use App\Models\enquetes_de_gouvernance\EvaluationDeGouvernance;
use App\Models\enquetes_de_gouvernance\SoumissionDePerception;
use App\Models\enquetes_de_gouvernance\SoumissionFactuel;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Organisation extends Model
{
    use HasSecureIds, HasFactory ;

    protected $table = 'organisations';

    public $timestamps = true;

    protected $fillable = ["sigle", "code", "nom_point_focal", "prenom_point_focal", "contact_point_focal", 'type', 'pays', 'departement', 'commune', 'arrondissement', 'programmeId', 'addresse', 'quartier', 'secteurActivite', 'longitude', 'latitude'];

    protected $dates = ['deleted_at'];

    protected $with = ['user'];

    //protected $default = ['type' => 'osc_partenaire'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'updated_at','deleted_at','pivot'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "code" => "integer",
        "longitude" => "float",
        "latitude" => "float",
        "created_at" => "datetime:Y-m-d",
        "updated_at" => "datetime:Y-m-d",
        "deleted_at" => "datetime:Y-m-d"
    ];

    protected static function boot() {
        parent::boot();

        static::deleting(function ($organisation) {

            DB::beginTransaction();
            try {

                if ((($organisation->projet) && ($organisation->projet->statut > -1)) || ($organisation->evaluations_de_gouvernance->count() > 0) || ($organisation->suivis_indicateurs->count() > 0 ) || ($organisation->indicateurs->count() > 0 ) || ($organisation->soumissions->count() > 0 ) || ($organisation->fiches_de_synthese->count() > 0 ) || ($organisation->profiles->count() > 0 )) {
                    // Prevent deletion by throwing an exception
                    throw new Exception("Impossible de supprimer cette organisation car elle est liée à un projet actif ou contient des évaluations, indicateurs, suivis ou soumissions. Veuillez supprimer ou dissocier ces éléments avant de réessayer.");
                }

                $organisation->user()->delete();

                $organisation->teamMembers->each(function ($teamMember) {
                    optional($teamMember->user)->update(['statut' => -1]);
                });
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function user()
    {
        return $this->morphOne(User::class, 'profilable');
    }

    public function teamMembers()
    {
        return $this->morphMany(TeamMember::class, 'profilable');
    }

    public function evaluations()
    {
        return $this->hasMany(ReponseCollecter::class, 'organisationId');
    }

    public function notes_resultat()
    {
        return $this->hasMany(EnqueteResultatNote::class, 'organisationId');
    }

    public function projet()
    {
        return $this->morphOne(Projet::class, 'projetable');//->where('programmeId', $this->user->programmeId)->first();
    }

    public function suivis_indicateurs()
    {
        return $this->morphMany(SuiviIndicateur::class, 'suivi_indicateurable');
    }

    public function survey_forms()
    {
        return $this->morphMany(SurveyForm::class, 'created_by');
    }

    public function surveys()
    {
        return $this->morphMany(Survey::class, 'surveyable');
    }


    /**
     * Get organisations by programme ID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $programmeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByProgramme($query, $programmeId = null)
    {
        return $query->whereHas('user', function ($q) use ($programmeId) {
            if($programmeId == null){
                $programmeId = auth()->user()->programmeId;
            }

            $q->where('programmeId', $programmeId)->where('type', "organisation");
        });
    }

    /**
     * Charger la liste des outcomes d'un projet
     */
    public function outcomes()
    {
        return $this->hasManyThrough(
            Composante::class,    // Final Model
            Projet::class,       // Intermediate Model
            'projetable_id',                  // Foreign key on the types_de_gouvernance table
            'projetId',          // Foreign key on the principes_de_gouvernance table
            'id',                              // Local key on the principes_de_gouvernance table
            'id'                               // Local key on the types_de_gouvernance table
        )->whereNull("composanteId");
    }

    public function fonds()
    {
        return $this->belongsToMany(Fond::class,'fond_organisations', 'organisationId', 'fondId')->wherePivotNull('deleted_at')->withPivot(["id", "budgetAllouer"]);
    }

    public function evaluations_de_gouvernance(?int $organisationId = null, ?string $token = null)
    {
        // Start with the base relationship
        $evaluations_de_gouvernance = $this->belongsToMany(EvaluationDeGouvernance::class,'evaluation_organisations', 'organisationId', 'evaluationDeGouvernanceId')->wherePivotNull('deleted_at')->withPivot(["id", "nbreParticipants", 'participants', 'token']);

        if ($organisationId) {
            $evaluations_de_gouvernance = $evaluations_de_gouvernance->wherePivot("organisationId", $organisationId);
        }

        if ($token) {
            $evaluations_de_gouvernance = $evaluations_de_gouvernance->wherePivot("token", $token);
        }

        return $evaluations_de_gouvernance;
    }

    public function soumissions()
    {
        return $this->hasMany(Soumission::class, 'organisationId');
    }

    public function sousmissions_factuel()
    {
        return $this->hasMany(Soumission::class, 'organisationId')->where("type", "factuel");
    }

    public function sousmissions_de_perception()
    {
        return $this->hasMany(Soumission::class, 'organisationId')->where("type", "perception");
    }

    public function sousmissions_enquete_factuel()
    {
        return $this->hasMany(SoumissionFactuel::class, 'organisationId');
    }

    public function sousmissions_enquete_de_perception()
    {
        return $this->hasMany(SoumissionDePerception::class, 'organisationId');
    }

    public function fiches_de_synthese($evaluationDeGouvernanceId = null, $type = null)
    {
        $fiches_de_synthese = $this->hasMany(FicheDeSynthese::class, 'organisationId');

        if($type){
            $fiches_de_synthese = $fiches_de_synthese->where("type", $type);
        }

        if($evaluationDeGouvernanceId){
            $fiches_de_synthese = $fiches_de_synthese->where("evaluationDeGouvernanceId", $evaluationDeGouvernanceId);
        }

        return $fiches_de_synthese;
    }

    public function profiles(?int $evaluationDeGouvernanceId = null, ?int $evaluationOrganisationId = null): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // Start with the base relationship
        $profiles = $this->hasMany(ProfileDeGouvernance::class, 'organisationId');

        if ($evaluationDeGouvernanceId) {
            $profiles = $profiles->where("evaluationDeGouvernanceId", $evaluationDeGouvernanceId);
        }

        if ($evaluationOrganisationId) {
            $profiles = $profiles->where("evaluationOrganisationId", $evaluationOrganisationId);
        }

        // Get the results and apply grouping on the collection level
        return $profiles;
    }

    public function indicateurs()
    {
        return $this->belongsToMany(Indicateur::class, 'indicateur_responsables', 'responsableable_id', 'indicateurId')->wherePivotNull('deleted_at');
    }

    public function getNbreDeParticipantsAttribute($evaluationDeGouvernanceId){
        // Fetch the number of expected participants for perception evaluation
        return $this->evaluations_de_gouvernance()->where('evaluationDeGouvernanceId', $evaluationDeGouvernanceId)->first()->pivot->nbreParticipants;
    }

    /**
     * Calculate the completion percentage for perception submissions.
     *
     * @param int $evaluationDeGouvernanceId
     * @return float
     */
    public function getPerceptionSubmissionsCompletionAttribute($evaluationDeGouvernanceId)
    {
        // Fetch all perception submissions for this organisation and evaluation
        $perceptionSubmissions = $this->sousmissions_de_perception()->where('evaluationId', $evaluationDeGouvernanceId)->get();

        // Fetch the number of expected participants for perception evaluation
        $nbreOfParticipants = $this->evaluations_de_gouvernance()->where('evaluationDeGouvernanceId', $evaluationDeGouvernanceId)->first()->pivot->nbreParticipants;

        // Calculate perception submission completion (average of submissions)
        $perceptionSubmissionsCompletion = $perceptionSubmissions->count() > 0
            ? $perceptionSubmissions->avg('pourcentage_evolution')
            : 0;

        // Adjust completion percentage based on number of participants
        return $nbreOfParticipants > 0
            ? round((($perceptionSubmissionsCompletion * $perceptionSubmissions->count()) / $nbreOfParticipants), 2)
            : 0;
    }

    /**
     * Calculate the completion percentage for perception submissions.
     *
     * @param int $evaluationDeGouvernanceId
     * @return float
     */
    public function getFactuelSubmissionCompletionAttribute($evaluationDeGouvernanceId)
    {
        // Fetch submissions for the organisation
        $factualSubmission = $this->sousmissions_factuel()->where('evaluationId', $evaluationDeGouvernanceId)->first();

        // Calculate factual completion percentage
        return $factualSubmission ? round($factualSubmission->pourcentage_evolution, 2) : 0;
    }

    /**
     * Calculate the completion percentage for perception submissions.
     *
     * @param int $evaluationDeGouvernanceId
     * @return float
     */
    public function getPerceptionSubmissionsCompletionRateAttribute($evaluationDeGouvernanceId)
    {
        // Fetch all perception submissions for this organisation and evaluation
        $perceptionSubmissions = $this->sousmissions_enquete_de_perception()->where('evaluationId', $evaluationDeGouvernanceId)->get();

        $evaluationDeGouvernance = $this->evaluations_de_gouvernance()->where('evaluationDeGouvernanceId', $evaluationDeGouvernanceId)->first();

        // Fetch the number of expected participants for perception evaluation
        $nbreOfParticipants = $evaluationDeGouvernance ? $evaluationDeGouvernance->pivot->nbreParticipants : 0;

        // Calculate perception submission completion (average of submissions)
        $perceptionSubmissionsCompletion = $perceptionSubmissions->count() > 0
            ? $perceptionSubmissions->avg('pourcentage_evolution')
            : 0;

        // Adjust completion percentage based on number of participants
        return $nbreOfParticipants > 0
            ? round((($perceptionSubmissionsCompletion * $perceptionSubmissions->count()) / $nbreOfParticipants), 2)
            : 0;
    }

    /**
     *
     *
     * @param int $evaluationDeGouvernanceId
     * @return float
     */
    public function getFactuelSubmissionAttribute($evaluationDeGouvernanceId)
    {
        // Fetch all perception submissions for this organisation and evaluation
        return $this->sousmissions_enquete_factuel()->where('evaluationId', $evaluationDeGouvernanceId);
    }

    /**
     *
     *
     * @param int $evaluationDeGouvernanceId
     * @return float
     */
    public function getPerceptionSubmissionsAttribute($evaluationDeGouvernanceId)
    {
        // Fetch all perception submissions for this organisation and evaluation
        return $this->sousmissions_enquete_de_perception()->where('evaluationId', $evaluationDeGouvernanceId);
    }

    /**
     * Calculate the completion percentage for perception submissions.
     *
     * @param int $evaluationDeGouvernanceId
     * @return float
     */
    public function getFactuelSubmissionCompletionRateAttribute($evaluationDeGouvernanceId)
    {
        // Fetch submissions for the organisation
        $factualSubmission = $this->sousmissions_enquete_factuel()->where('evaluationId', $evaluationDeGouvernanceId)->first();

        // Calculate factual completion percentage
        return $factualSubmission ? round($factualSubmission->pourcentage_evolution, 2) : 0;
    }

    public function getPourcentageEvolutionAttribute($evaluationDeGouvernanceId)
    {
        /* // Fetch submissions for the organisation
        $factualSubmission = $this->sousmissions_factuel()->where('evaluationId', $evaluationDeGouvernanceId)->first();

        // Calculate factual completion percentage
        $factualCompletion = $factualSubmission ? $factualSubmission->pourcentage_evolution : 0; */

        // Calculate factual completion percentage
        $factualCompletion = $this->getFactuelSubmissionCompletionAttribute($evaluationDeGouvernanceId);

        // Calculate perception completion using the helper method
        $perceptionCompletion = $this->getPerceptionSubmissionsCompletionAttribute($evaluationDeGouvernanceId);

        // Define weightage
        $weightFactual = 0.5; // 60%
        $weightPerception = 0.5; // 40%

        // Final weighted completion percentage
        return round((($factualCompletion * $weightFactual) + ($perceptionCompletion * $weightPerception)), 2);
    }

    public function getSubmissionRateAttribute($evaluationDeGouvernanceId)
    {
        /* ========== ANCIEN CODE (COMMENTÉ - CAUSAIT null ET POURCENTAGES > 100%) ==========
        $evaluation_de_gouvernance = $this->evaluations_de_gouvernance->where('id', $evaluationDeGouvernanceId)->first();

        // Calculate factual completion percentage
        $factualCompletion = $this->getFactuelSubmissionCompletionRateAttribute($evaluationDeGouvernanceId);

        // Calculate perception completion using the helper method
        $perceptionCompletion = $this->getPerceptionSubmissionsCompletionRateAttribute($evaluationDeGouvernanceId);


        $weightFactual = 0; // 60%
        $weightPerception = 0; // 60%

        if($evaluation_de_gouvernance){
            if($evaluation_de_gouvernance->formulaire_de_perception_de_gouvernance() && $evaluation_de_gouvernance->formulaire_factuel_de_gouvernance()){

                // Calculate factual completion percentage
                $factualCompletion = $this->getFactuelSubmissionCompletionRateAttribute($evaluationDeGouvernanceId);

                // Calculate perception completion using the helper method
                $perceptionCompletion = $this->getPerceptionSubmissionsCompletionRateAttribute($evaluationDeGouvernanceId);

                $weightPerception = 0.5; // 60%
                $weightFactual = 0.5; // 60%
                $percent = (($factualCompletion * $weightFactual) + ($perceptionCompletion * $weightPerception));

                // Final weighted completion percentage
                return round($percent, 2);
            }
            elseif($evaluation_de_gouvernance->formulaire_de_perception_de_gouvernance()){

                // Calculate perception completion using the helper method
                $perceptionCompletion = $this->getPerceptionSubmissionsCompletionRateAttribute($evaluationDeGouvernanceId);

                $weightPerception = 1; // 60%
                // Final weighted completion percentage
                return round(($perceptionCompletion * $weightPerception), 2);
            }
            elseif($evaluation_de_gouvernance->formulaire_factuel_de_gouvernance()){

                // Calculate factual completion percentage
                $factualCompletion = $this->getFactuelSubmissionCompletionRateAttribute($evaluationDeGouvernanceId);

                $weightFactual = 1; // 60%
                // Final weighted completion percentage
                return round(($factualCompletion * $weightFactual), 2);
            }
        }
        ========== FIN ANCIEN CODE ========== */

        // ========== NOUVEAU CODE (CORRIGÉ) ==========
        $evaluation_de_gouvernance = $this->evaluations_de_gouvernance->where('id', $evaluationDeGouvernanceId)->first();

        // Si l'évaluation n'existe pas, retourner 0 au lieu de null
        if(!$evaluation_de_gouvernance){
            return 0;
        }

        // Cas 1: Les deux formulaires (factuel + perception)
        if($evaluation_de_gouvernance->formulaire_de_perception_de_gouvernance() && $evaluation_de_gouvernance->formulaire_factuel_de_gouvernance()){
            // Calculate factual completion percentage
            $factualCompletion = $this->getFactuelSubmissionCompletionRateAttribute($evaluationDeGouvernanceId) ?? 0;

            // Calculate perception completion using the helper method
            $perceptionCompletion = $this->getPerceptionSubmissionsCompletionRateAttribute($evaluationDeGouvernanceId) ?? 0;

            $weightPerception = 0.5; // 50% (corrigé)
            $weightFactual = 0.5; // 50% (corrigé)
            $percent = (($factualCompletion * $weightFactual) + ($perceptionCompletion * $weightPerception));

            // Final weighted completion percentage (limité à 100%)
            return round(min(100, $percent), 2);
        }
        // Cas 2: Formulaire de perception uniquement
        elseif($evaluation_de_gouvernance->formulaire_de_perception_de_gouvernance()){
            // Calculate perception completion using the helper method
            $perceptionCompletion = $this->getPerceptionSubmissionsCompletionRateAttribute($evaluationDeGouvernanceId) ?? 0;

            // Final weighted completion percentage (limité à 100%)
            return round(min(100, $perceptionCompletion), 2);
        }
        // Cas 3: Formulaire factuel uniquement
        elseif($evaluation_de_gouvernance->formulaire_factuel_de_gouvernance()){
            // Calculate factual completion percentage
            $factualCompletion = $this->getFactuelSubmissionCompletionRateAttribute($evaluationDeGouvernanceId) ?? 0;

            // Final weighted completion percentage (limité à 100%)
            return round(min(100, $factualCompletion), 2);
        }

        // Cas 4: Aucun formulaire disponible
        return 0;
    }

    public function getFactuelSubmissionRateAttribute($evaluationDeGouvernanceId)
    {
        // ========== NOUVEAU CODE (CORRIGÉ) ==========
        $evaluation_de_gouvernance = $this->evaluations_de_gouvernance->where('id', $evaluationDeGouvernanceId)->first();

        dd($evaluation_de_gouvernance);
        // Si l'évaluation n'existe pas, retourner 0 au lieu de null
        if(!$evaluation_de_gouvernance){
            return 0;
        }

        // Cas 3: Formulaire factuel uniquement
        if($evaluation_de_gouvernance->formulaire_factuel_de_gouvernance()){
            // Calculate factual completion percentage
            $factualCompletion = $this->getFactuelSubmissionCompletionRateAttribute($evaluationDeGouvernanceId) ?? 0;

            // Final weighted completion percentage (limité à 100%)
            return round(min(100, $factualCompletion), 2);
        }

        // Cas 4: Aucun formulaire disponible
        return 0;
    }

    public function getPerceptionSubmissionRateAttribute($evaluationDeGouvernanceId)
    {

        // ========== NOUVEAU CODE (CORRIGÉ) ==========
        $evaluation_de_gouvernance = $this->evaluations_de_gouvernance->where('id', $evaluationDeGouvernanceId)->first();

        // Si l'évaluation n'existe pas, retourner 0 au lieu de null
        if(!$evaluation_de_gouvernance){
            return 0;
        }

        if($evaluation_de_gouvernance->formulaire_de_perception_de_gouvernance()){
            // Calculate perception completion using the helper method
            $perceptionCompletion = $this->getPerceptionSubmissionsCompletionRateAttribute($evaluationDeGouvernanceId) ?? 0;

            // Final weighted completion percentage (limité à 100%)
            return round(min(100, $perceptionCompletion), 2);
        }

        // Cas 4: Aucun formulaire disponible
        return 0;
    }

    public function actions_a_mener()
    {
        return $this->hasMany(ActionAMener::class, 'organisationId');
    }

    public function recommandations()
    {
        return $this->hasMany(Recommandation::class, 'organisationId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }
}
